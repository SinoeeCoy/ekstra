<?php
// File ini di-include dari home.php, jadi session sudah dimulai
// Tidak perlu session_start() lagi

if (!isset($_SESSION['user'])) {
    echo '<script>window.location.href = "index.php";</script>';
    exit;
}

$user = $_SESSION['user'];
$user_role = strtolower($user['role'] ?? '');

if ($user_role !== 'siswa') {
    echo '<script>window.location.href = "home.php?page=ekstra";</script>';
    exit;
}

$nama_ekstra = isset($_GET['nama_ekstra']) ? $_GET['nama_ekstra'] : '';
$ekstra = null;

if (empty($nama_ekstra)) {
    echo '<script>window.location.href = "home.php?page=ekstra";</script>';
    exit;
}

// Ambil data ekstrakurikuler
$stmt = $koneksi->prepare("SELECT * FROM ekstra WHERE nama_ekstra = ?");
$stmt->bind_param("s", $nama_ekstra);
$stmt->execute();
$result = $stmt->get_result();
$ekstra = $result->fetch_assoc();
$stmt->close();

if (!$ekstra) {
    echo '<script>window.location.href = "home.php?page=ekstra";</script>';
    exit;
}

$ekstra_id = $ekstra['id'];
$user_id = $_SESSION['user']['id'];

// Cek apakah siswa sudah punya data berdasarkan user_id
$data_siswa_sebelumnya = null;
$stmt_prev = $koneksi->prepare("SELECT * FROM data_siswa WHERE user_id = ? LIMIT 1");
$stmt_prev->bind_param("i", $user_id);
$stmt_prev->execute();
$result_prev = $stmt_prev->get_result();
if ($result_prev->num_rows > 0) {
    $data_siswa_sebelumnya = $result_prev->fetch_assoc();
}
$stmt_prev->close();

// Proses form submission
if (isset($_POST['submit'])) {
    $nama_siswa = trim($_POST['nama_siswa']);
    $nisn = trim($_POST['nisn']);
    $kelas = trim($_POST['kelas']);
    $jurusan = trim($_POST['jurusan']);
    $jenis_kelamin = trim($_POST['jenis_kelamin']);
    $tempat_lahir = trim($_POST['tempat_lahir']);
    $tanggal_lahir = trim($_POST['tanggal_lahir']);
    $agama = trim($_POST['agama']);
    $no_hp = trim($_POST['no_hp']);
    
    // Validasi input
    if (empty($nama_siswa) || empty($nisn) || empty($kelas) || empty($jurusan) || empty($jenis_kelamin) || empty($tempat_lahir) || empty($tanggal_lahir) || empty($agama)) {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = "Semua field yang wajib harus diisi!";
        echo '<script>window.location.href = "home.php?page=ekstra_daftar&nama_ekstra=' . urlencode($nama_ekstra) . '";</script>';
        exit;
    }
    
    // Validasi NISN 10 digit
    if (!preg_match('/^\d{10}$/', $nisn)) {
        $_SESSION['alert_type'] = 'error';
        $_SESSION['alert_message'] = "NISN harus 10 digit angka!";
        echo '<script>window.location.href = "home.php?page=ekstra_daftar&nama_ekstra=' . urlencode($nama_ekstra) . '";</script>';
        exit;
    }
    
    // Cek apakah siswa sudah punya record berdasarkan user_id
    $stmt_check = $koneksi->prepare("SELECT * FROM data_siswa WHERE user_id = ?");
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        // Data siswa sudah ada, update ekstra_id saja
        $data_existing = $result_check->fetch_assoc();
        $stmt_check->close();
        
        // Ambil ekstra_id yang sudah ada
        $ekstra_ids_existing = $data_existing['ekstra_id'];
        $ekstra_array = !empty($ekstra_ids_existing) ? explode(',', $ekstra_ids_existing) : [];
        
        // Cek apakah sudah terdaftar di ekstra ini
        if (in_array($ekstra_id, $ekstra_array)) {
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_message'] = "Anda sudah terdaftar di ekstrakurikuler <strong>$nama_ekstra</strong>!";
            echo '<script>window.location.href = "home.php?page=ekstra_daftar&nama_ekstra=' . urlencode($nama_ekstra) . '";</script>';
            exit;
        }
        
        // Tambahkan ekstra_id baru
        $ekstra_array[] = $ekstra_id;
        $ekstra_ids_new = implode(',', $ekstra_array);
        
        // Update semua data termasuk ekstra_id baru
        $stmt_update = $koneksi->prepare("UPDATE data_siswa SET nama_siswa = ?, nisn = ?, kelas = ?, jurusan = ?, jenis_kelamin = ?, tempat_lahir = ?, tanggal_lahir = ?, agama = ?, no_hp = ?, ekstra_id = ? WHERE user_id = ?");
        $stmt_update->bind_param("ssssssssssi", $nama_siswa, $nisn, $kelas, $jurusan, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $agama, $no_hp, $ekstra_ids_new, $user_id);
        
        if ($stmt_update->execute()) {
            $stmt_update->close();
            $_SESSION['alert_type'] = 'success';
            $_SESSION['alert_message'] = "Pendaftaran berhasil! Anda telah terdaftar di ekstrakurikuler <strong>$nama_ekstra</strong>.";
            $_SESSION['redirect'] = true;
            echo '<script>window.location.href = "home.php?page=ekstra";</script>';
            exit;
        } else {
            $stmt_update->close();
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_message'] = "Terjadi kesalahan saat menyimpan data: " . htmlspecialchars($koneksi->error);
            echo '<script>window.location.href = "home.php?page=ekstra_daftar&nama_ekstra=' . urlencode($nama_ekstra) . '";</script>';
            exit;
        }
    } else {
        // Data siswa belum ada, insert baru
        $stmt_check->close();
        
        // Cek apakah NISN sudah digunakan user lain
        $stmt_nisn_check = $koneksi->prepare("SELECT id FROM data_siswa WHERE nisn = ?");
        $stmt_nisn_check->bind_param("s", $nisn);
        $stmt_nisn_check->execute();
        $result_nisn = $stmt_nisn_check->get_result();
        
        if ($result_nisn->num_rows > 0) {
            $stmt_nisn_check->close();
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_message'] = "NISN sudah digunakan oleh siswa lain!";
            echo '<script>window.location.href = "home.php?page=ekstra_daftar&nama_ekstra=' . urlencode($nama_ekstra) . '";</script>';
            exit;
        }
        $stmt_nisn_check->close();
        
        $ekstra_ids_new = strval($ekstra_id);
        
        $stmt_insert = $koneksi->prepare("INSERT INTO data_siswa (user_id, nama_siswa, nisn, kelas, jurusan, jenis_kelamin, tempat_lahir, tanggal_lahir, agama, no_hp, ekstra_id, tanggal_daftar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt_insert->bind_param("issssssssss", $user_id, $nama_siswa, $nisn, $kelas, $jurusan, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $agama, $no_hp, $ekstra_ids_new);
        
        if ($stmt_insert->execute()) {
            $stmt_insert->close();
            $_SESSION['alert_type'] = 'success';
            $_SESSION['alert_message'] = "Pendaftaran berhasil! Anda telah terdaftar di ekstrakurikuler <strong>$nama_ekstra</strong>.";
            $_SESSION['redirect'] = true;
            echo '<script>window.location.href = "home.php?page=ekstra";</script>';
            exit;
        } else {
            $stmt_insert->close();
            $_SESSION['alert_type'] = 'error';
            $_SESSION['alert_message'] = "Terjadi kesalahan saat menyimpan data: " . htmlspecialchars($koneksi->error);
            echo '<script>window.location.href = "home.php?page=ekstra_daftar&nama_ekstra=' . urlencode($nama_ekstra) . '";</script>';
            exit;
        }
    }
}

// Ambil alert dari session
$alert_type = isset($_SESSION['alert_type']) ? $_SESSION['alert_type'] : '';
$alert_message = isset($_SESSION['alert_message']) ? $_SESSION['alert_message'] : '';
$should_redirect = isset($_SESSION['redirect']) ? $_SESSION['redirect'] : false;

// Hapus session alert
unset($_SESSION['alert_type']);
unset($_SESSION['alert_message']);
unset($_SESSION['redirect']);
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-user-plus"></i> Form Pendaftaran Ekstrakurikuler</h4>
            </div>
            <div class="card-body">
                <div class="info-box" style="background-color: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px; margin-bottom: 20px;">
                    <h5>Informasi Ekstrakurikuler:</h5>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Nama Ekstra:</strong></td>
                            <td><?php echo htmlspecialchars($ekstra['nama_ekstra']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Pembina:</strong></td>
                            <td><?php echo htmlspecialchars($ekstra['pembina']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Hari:</strong></td>
                            <td><?php echo htmlspecialchars($ekstra['hari']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Waktu:</strong></td>
                            <td><?php echo htmlspecialchars($ekstra['waktu']); ?></td>
                        </tr>
                        <?php if (!empty($ekstra['lokasi'])): ?>
                        <tr>
                            <td><strong>Lokasi:</strong></td>
                            <td><?php echo htmlspecialchars($ekstra['lokasi']); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                
                <?php if ($data_siswa_sebelumnya): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Data Anda akan otomatis terisi berdasarkan pendaftaran sebelumnya. Anda dapat mengubahnya jika diperlukan.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-edit"></i> Data Siswa</h5>
            </div>
            <div class="card-body">
                <form method="POST" id="formDaftar">
                    <h6 class="mb-3"><i class="fas fa-id-card"></i> Data Pribadi</h6>
                    
                    <div class="mb-3">
                        <label for="nama_siswa" class="form-label" style="font-weight: 600; color: #495057;">
                            Nama Lengkap Siswa <span style="color: #dc3545;">*</span>
                        </label>
                        <input type="text" class="form-control" id="nama_siswa" name="nama_siswa" 
                            value="<?php echo htmlspecialchars($data_siswa_sebelumnya['nama_siswa'] ?? $_SESSION['user']['nama_lengkap'] ?? ''); ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="nisn" class="form-label" style="font-weight: 600; color: #495057;">
                            NISN (10 Digit) <span style="color: #dc3545;">*</span>
                        </label>
                        <input type="text" class="form-control" id="nisn" name="nisn" 
                            placeholder="Contoh: 0012345678" maxlength="10" pattern="\d{10}"
                            value="<?php echo htmlspecialchars($data_siswa_sebelumnya['nisn'] ?? ''); ?>"
                            required>
                        <small class="text-muted">NISN harus 10 digit angka</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; color: #495057;">
                            Jenis Kelamin <span style="color: #dc3545;">*</span>
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="jenis_kelamin" 
                                           id="laki" value="Laki-laki" 
                                           <?php echo (isset($data_siswa_sebelumnya['jenis_kelamin']) && $data_siswa_sebelumnya['jenis_kelamin'] == 'Laki-laki') ? 'checked' : ''; ?>
                                           required>
                                    <label class="form-check-label" for="laki">
                                        <i class="fas fa-mars text-primary"></i> Laki-laki
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="jenis_kelamin" 
                                           id="perempuan" value="Perempuan"
                                           <?php echo (isset($data_siswa_sebelumnya['jenis_kelamin']) && $data_siswa_sebelumnya['jenis_kelamin'] == 'Perempuan') ? 'checked' : ''; ?>
                                           required>
                                    <label class="form-check-label" for="perempuan">
                                        <i class="fas fa-venus text-danger"></i> Perempuan
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tempat_lahir" class="form-label" style="font-weight: 600; color: #495057;">
                                    Tempat Lahir <span style="color: #dc3545;">*</span>
                                </label>
                                <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" 
                                    placeholder="Contoh: Jakarta"
                                    value="<?php echo htmlspecialchars($data_siswa_sebelumnya['tempat_lahir'] ?? ''); ?>"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tanggal_lahir" class="form-label" style="font-weight: 600; color: #495057;">
                                    Tanggal Lahir <span style="color: #dc3545;">*</span>
                                </label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir"
                                    value="<?php echo htmlspecialchars($data_siswa_sebelumnya['tanggal_lahir'] ?? ''); ?>"
                                    required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="agama" class="form-label" style="font-weight: 600; color: #495057;">
                            Agama <span style="color: #dc3545;">*</span>
                        </label>
                        <select class="form-select" id="agama" name="agama" required>
                            <option value="">-- Pilih Agama --</option>
                            <option value="Islam" <?php echo (isset($data_siswa_sebelumnya['agama']) && $data_siswa_sebelumnya['agama'] == 'Islam') ? 'selected' : ''; ?>>Islam</option>
                            <option value="Kristen" <?php echo (isset($data_siswa_sebelumnya['agama']) && $data_siswa_sebelumnya['agama'] == 'Kristen') ? 'selected' : ''; ?>>Kristen</option>
                            <option value="Katolik" <?php echo (isset($data_siswa_sebelumnya['agama']) && $data_siswa_sebelumnya['agama'] == 'Katolik') ? 'selected' : ''; ?>>Katolik</option>
                            <option value="Hindu" <?php echo (isset($data_siswa_sebelumnya['agama']) && $data_siswa_sebelumnya['agama'] == 'Hindu') ? 'selected' : ''; ?>>Hindu</option>
                            <option value="Buddha" <?php echo (isset($data_siswa_sebelumnya['agama']) && $data_siswa_sebelumnya['agama'] == 'Buddha') ? 'selected' : ''; ?>>Buddha</option>
                            <option value="Konghucu" <?php echo (isset($data_siswa_sebelumnya['agama']) && $data_siswa_sebelumnya['agama'] == 'Konghucu') ? 'selected' : ''; ?>>Konghucu</option>
                        </select>
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3"><i class="fas fa-school"></i> Kelas & Jurusan</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kelas" class="form-label" style="font-weight: 600; color: #495057;">
                                    Kelas <span style="color: #dc3545;">*</span>
                                </label>
                                <select class="form-select" id="kelas" name="kelas" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    <option value="X" <?php echo (isset($data_siswa_sebelumnya['kelas']) && $data_siswa_sebelumnya['kelas'] == 'X') ? 'selected' : ''; ?>>X</option>
                                    <option value="XI" <?php echo (isset($data_siswa_sebelumnya['kelas']) && $data_siswa_sebelumnya['kelas'] == 'XI') ? 'selected' : ''; ?>>XI</option>
                                    <option value="XII" <?php echo (isset($data_siswa_sebelumnya['kelas']) && $data_siswa_sebelumnya['kelas'] == 'XII') ? 'selected' : ''; ?>>XII</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jurusan" class="form-label" style="font-weight: 600; color: #495057;">
                                    Jurusan <span style="color: #dc3545;">*</span>
                                </label>
                                <select class="form-select" id="jurusan" name="jurusan" required>
                                    <option value="">-- Pilih Jurusan --</option>
                                    <option value="PPLG" <?php echo (isset($data_siswa_sebelumnya['jurusan']) && $data_siswa_sebelumnya['jurusan'] == 'PPLG') ? 'selected' : ''; ?>>PPLG</option>
                                    <option value="TJKT" <?php echo (isset($data_siswa_sebelumnya['jurusan']) && $data_siswa_sebelumnya['jurusan'] == 'TJKT') ? 'selected' : ''; ?>>TJKT</option>
                                    <option value="BUSANA" <?php echo (isset($data_siswa_sebelumnya['jurusan']) && $data_siswa_sebelumnya['jurusan'] == 'BUSANA') ? 'selected' : ''; ?>>BUSANA</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3"><i class="fas fa-address-book"></i> Informasi Kontak</h6>

                    <div class="mb-3">
                        <label for="no_hp" class="form-label" style="font-weight: 600; color: #495057;">No. HP/WA</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="text" class="form-control" id="no_hp" name="no_hp" 
                                   placeholder="Contoh: 08123456789" maxlength="13"
                                   value="<?php echo htmlspecialchars($data_siswa_sebelumnya['no_hp'] ?? ''); ?>">
                        </div>
                        <small class="text-muted">Format: 08xxxxxxxxxx (10-13 digit)</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="persetujuan" required>
                            <label class="form-check-label" for="persetujuan">
                                Saya menyetujui untuk mengikuti ekstrakurikuler ini dan mematuhi semua peraturan yang berlaku. <span style="color: #dc3545;">*</span>
                            </label>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="home.php?page=ekstra" class="btn btn-secondary me-md-2">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" name="submit" class="btn btn-primary" id="btnSubmit">
                            <i class="fas fa-save"></i> Daftar Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6><i class="fas fa-info-circle"></i> Informasi Penting</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Pastikan data yang dimasukkan benar</li>
                    <li class="mb-2"><i class="fas fa-check text-success"></i> NISN harus 10 digit angka</li>
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Anda bisa mendaftar di banyak ekstrakurikuler</li>
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Data otomatis terisi dari pendaftaran sebelumnya</li>
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Anda dapat mengubah data jika diperlukan</li>
                </ul>
            </div>
        </div>

        <?php
        // Tampilkan ekstrakurikuler yang sudah diikuti berdasarkan user_id
        $stmt_my_ekstra = $koneksi->prepare("SELECT ekstra_id FROM data_siswa WHERE user_id = ?");
        $stmt_my_ekstra->bind_param("i", $user_id);
        $stmt_my_ekstra->execute();
        $result_my = $stmt_my_ekstra->get_result();
        
        $my_ekstra_ids = [];
        if ($result_my->num_rows > 0) {
            $data_my = $result_my->fetch_assoc();
            $my_ekstra_ids = !empty($data_my['ekstra_id']) ? explode(',', $data_my['ekstra_id']) : [];
        }
        $stmt_my_ekstra->close();
        ?>
        
        <?php if (!empty($my_ekstra_ids)): ?>
        <div class="card mt-3">
            <div class="card-header bg-warning text-dark">
                <h6><i class="fas fa-list"></i> Ekstrakurikuler Anda</h6>
            </div>
            <div class="card-body">
                <p><strong>Anda sudah terdaftar di:</strong></p>
                <ul class="list-unstyled">
                    <?php 
                    $placeholders = implode(',', array_fill(0, count($my_ekstra_ids), '?'));
                    $stmt_ekstra_names = $koneksi->prepare("SELECT nama_ekstra FROM ekstra WHERE id IN ($placeholders)");
                    $types = str_repeat('i', count($my_ekstra_ids));
                    $stmt_ekstra_names->bind_param($types, ...$my_ekstra_ids);
                    $stmt_ekstra_names->execute();
                    $result_names = $stmt_ekstra_names->get_result();
                    
                    while ($ekstra_item = $result_names->fetch_assoc()): 
                    ?>
                    <li class="mb-1">
                        <i class="fas fa-check-circle text-success"></i> 
                        <?php echo htmlspecialchars($ekstra_item['nama_ekstra']); ?>
                    </li>
                    <?php endwhile; 
                    $stmt_ekstra_names->close();
                    ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <h6><i class="fas fa-users"></i> Siswa Terdaftar</h6>
            </div>
            <div class="card-body">
                <?php
                // Hitung siswa yang terdaftar di ekstra ini (cek dalam kolom ekstra_id yang berisi comma-separated values)
                $stmt = $koneksi->prepare("SELECT * FROM data_siswa WHERE FIND_IN_SET(?, ekstra_id) ORDER BY tanggal_daftar DESC LIMIT 5");
                $stmt->bind_param("i", $ekstra_id);
                $stmt->execute();
                $query_siswa = $stmt->get_result();
                
                $stmt2 = $koneksi->prepare("SELECT COUNT(*) as total FROM data_siswa WHERE FIND_IN_SET(?, ekstra_id)");
                $stmt2->bind_param("i", $ekstra_id);
                $stmt2->execute();
                $jumlah_siswa = $stmt2->get_result()->fetch_assoc()['total'];
                $stmt2->close();
                ?>
                <p><strong>Total Siswa: <?php echo $jumlah_siswa; ?> orang</strong></p>
                
                <?php if ($query_siswa->num_rows > 0): ?>
                    <small class="text-muted">5 Pendaftar Terakhir:</small>
                    <ul class="list-unstyled mt-2">
                        <?php while ($siswa = $query_siswa->fetch_assoc()): ?>
                        <li class="mb-1">
                            <small>
                                <i class="fas fa-<?php echo ($siswa['jenis_kelamin'] == 'Laki-laki') ? 'mars' : 'venus'; ?> text-<?php echo ($siswa['jenis_kelamin'] == 'Laki-laki') ? 'primary' : 'danger'; ?>"></i> 
                                <?php echo htmlspecialchars($siswa['nama_siswa']); ?> 
                                (<?php echo htmlspecialchars($siswa['kelas']); ?> - <?php echo htmlspecialchars($siswa['jurusan']); ?>)
                            </small>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">Belum ada siswa yang terdaftar.</p>
                <?php endif; ?>
                <?php $stmt->close(); ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Tampilkan alert jika ada
    <?php if (!empty($alert_type) && !empty($alert_message)): ?>
    Swal.fire({
        icon: '<?php echo $alert_type; ?>',
        title: '<?php echo ($alert_type == "success") ? "Berhasil!" : "Gagal!"; ?>',
        html: '<?php echo $alert_message; ?>',
        confirmButtonText: 'OK'
    });
    <?php endif; ?>

    // Validasi NISN hanya angka
    document.getElementById('nisn').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Validasi nomor HP
    document.getElementById('no_hp').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Konfirmasi sebelum submit
    document.getElementById('formDaftar').addEventListener('submit', function(e) {
        const nisn = document.getElementById('nisn').value;
        const kelas = document.getElementById('kelas').value;
        const jurusan = document.getElementById('jurusan').value;
        const jenisKelamin = document.querySelector('input[name="jenis_kelamin"]:checked');
        const tempatLahir = document.getElementById('tempat_lahir').value;
        const tanggalLahir = document.getElementById('tanggal_lahir').value;
        const agama = document.getElementById('agama').value;
        
        if (!nisn || !kelas || !jurusan || !jenisKelamin || !tempatLahir || !tanggalLahir || !agama) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: 'Mohon lengkapi semua field yang wajib diisi!',
                confirmButtonText: 'OK'
            });
            return false;
        }
        
        if (nisn.length !== 10) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: 'NISN harus 10 digit angka!',
                confirmButtonText: 'OK'
            });
            return false;
        }
    });
</script>