<?php
include "koneksi.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$user_role = strtolower($user['role'] ?? '');

// Hanya siswa yang bisa akses halaman ini
if ($user_role !== 'siswa') {
    header("Location: home.php?page=ekstra");
    exit;
}

$nama_ekstra = isset($_GET['nama_ekstra']) ? $_GET['nama_ekstra'] : '';
$ekstra = null;

if (empty($nama_ekstra)) {
    header("Location: home.php?page=ekstra");
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
    header("Location: home.php?page=ekstra");
    exit;
}

$ekstra_id = $ekstra['id']; // Simpan ID ekstrakurikuler

// Proses form submission
if (isset($_POST['submit'])) {
    $nama_siswa = trim($_POST['nama_siswa']);
    $nis = trim($_POST['nis']);
    $kelas = trim($_POST['kelas']);
    $jurusan = trim($_POST['jurusan']);
    $jenis_kelamin = trim($_POST['jenis_kelamin']);
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    
    // Cek apakah NIS sudah terdaftar di ekstrakurikuler yang sama
    $stmt = $koneksi->prepare("SELECT * FROM data_siswa WHERE nis = ? AND ekstra_id = ?");
    $stmt->bind_param("si", $nis, $ekstra_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    <i class='fas fa-exclamation-triangle'></i> NIS <strong>$nis</strong> sudah terdaftar di ekstrakurikuler <strong>$nama_ekstra</strong>!
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                  </div>";
    } else {
        // Insert data siswa baru
        $stmt = $koneksi->prepare("INSERT INTO data_siswa (nama_siswa, nis, kelas, jurusan, jenis_kelamin, no_hp, alamat, ekstra_id, tanggal_daftar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssssi", $nama_siswa, $nis, $kelas, $jurusan, $jenis_kelamin, $no_hp, $alamat, $ekstra_id);
        
        if ($stmt->execute()) {
            $pesan = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <i class='fas fa-check-circle'></i> Pendaftaran berhasil! Siswa <strong>$nama_siswa</strong> telah terdaftar di ekstrakurikuler <strong>$nama_ekstra</strong>.
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                      </div>";
            
            echo "<script>
                    setTimeout(function() {
                        window.location.href = 'home.php?page=ekstra';
                    }, 2000);
                  </script>";
        } else {
            $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <i class='fas fa-times-circle'></i> Terjadi kesalahan saat menyimpan data!
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                      </div>";
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Ekstrakurikuler - <?php echo htmlspecialchars($nama_ekstra); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        .required {
            color: #dc3545;
        }
        .info-box {
            background-color: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-user-plus"></i> Form Pendaftaran Ekstrakurikuler</h4>
                    </div>
                    <div class="card-body">
                        <div class="info-box">
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Pesan -->
        <?php if (isset($pesan)) echo $pesan; ?>

        <!-- Form Pendaftaran -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-edit"></i> Data Siswa</h5>
                    </div>
                    <div class="card-body">
                        <form id="formDaftar" method="POST">
                            <!-- Data Pribadi -->
                            <h6 class="mb-3"><i class="fas fa-id-card"></i> Data Pribadi</h6>
                            
                            <div class="mb-3">
                                <label for="nama_siswa" class="form-label">Nama Lengkap Siswa <span class="required">*</span></label>
                                <input type="text" class="form-control" id="nama_siswa" name="nama_siswa" 
                                       placeholder="Masukkan nama lengkap siswa" required>
                            </div>

                            <div class="mb-3">
                                <label for="nis" class="form-label">NIS (Nomor Induk Siswa) <span class="required">*</span></label>
                                <input type="text" class="form-control" id="nis" name="nis" 
                                       placeholder="Contoh: 12345" maxlength="20" required>
                                <small class="text-muted"><i class="fas fa-info-circle"></i> Minimal 4 digit angka</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Jenis Kelamin <span class="required">*</span></label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="jenis_kelamin" 
                                                   id="laki" value="Laki-laki" required>
                                            <label class="form-check-label" for="laki">
                                                <i class="fas fa-mars text-primary"></i> Laki-laki
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="jenis_kelamin" 
                                                   id="perempuan" value="Perempuan" required>
                                            <label class="form-check-label" for="perempuan">
                                                <i class="fas fa-venus text-danger"></i> Perempuan
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Data Kelas -->
                            <h6 class="mb-3"><i class="fas fa-school"></i> Kelas & Jurusan</h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="kelas" class="form-label">Kelas <span class="required">*</span></label>
                                        <select class="form-select" id="kelas" name="kelas" required>
                                            <option value="">-- Pilih Kelas --</option>
                                            <option value="X">X</option>
                                            <option value="XI">XI</option>
                                            <option value="XII">XII</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="jurusan" class="form-label">Jurusan <span class="required">*</span></label>
                                        <select class="form-select" id="jurusan" name="jurusan" required>
                                            <option value="">-- Pilih Jurusan --</option>
                                            <option value="PPLG">PPLG (Pengembangan Perangkat Lunak dan Gim)</option>
                                            <option value="TJKT">TJKT (Teknik Jaringan Komputer dan Telekomunikasi)</option>
                                            <option value="BUSANA">BUSANA (Tata Busana)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Kontak -->
                            <h6 class="mb-3"><i class="fas fa-address-book"></i> Informasi Kontak</h6>

                            <div class="mb-3">
                                <label for="no_hp" class="form-label">No. HP/WA</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control" id="no_hp" name="no_hp" 
                                           placeholder="Contoh: 08123456789" maxlength="13">
                                </div>
                                <small class="text-muted">Format: 08xxxxxxxxxx (10-13 digit)</small>
                            </div>

                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat Rumah</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" 
                                          placeholder="Masukkan alamat lengkap"></textarea>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="persetujuan" required>
                                    <label class="form-check-label" for="persetujuan">
                                        Saya menyetujui untuk mengikuti ekstrakurikuler ini dan mematuhi semua peraturan yang berlaku.
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="home.php?page=ekstra" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Daftar Sekarang
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6><i class="fas fa-info-circle"></i> Informasi Penting</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-success"></i> Pastikan data yang dimasukkan benar</li>
                            <li class="mb-2"><i class="fas fa-check text-success"></i> NIS harus sesuai dengan data sekolah</li>
                            <li class="mb-2"><i class="fas fa-check text-success"></i> Pilih jurusan sesuai dengan kelas Anda</li>
                            <li class="mb-2"><i class="fas fa-check text-success"></i> No. HP akan digunakan untuk komunikasi</li>
                            <li class="mb-2"><i class="fas fa-check text-success"></i> Pendaftaran tidak dapat dibatalkan</li>
                        </ul>
                    </div>
                </div>

                <!-- Daftar Siswa yang Sudah Terdaftar -->
                <div class="card mt-3">
                    <div class="card-header bg-success text-white">
                        <h6><i class="fas fa-users"></i> Siswa Terdaftar</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $stmt = $koneksi->prepare("SELECT * FROM data_siswa WHERE ekstra_id = ? ORDER BY tanggal_daftar DESC LIMIT 5");
                        $stmt->bind_param("i", $ekstra_id);
                        $stmt->execute();
                        $query_siswa = $stmt->get_result();
                        
                        $stmt2 = $koneksi->prepare("SELECT COUNT(*) as total FROM data_siswa WHERE ekstra_id = ?");
                        $stmt2->bind_param("i", $ekstra_id);
                        $stmt2->execute();
                        $jumlah_siswa = $stmt2->get_result()->fetch_assoc()['total'];
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Validasi hanya angka untuk NIS dan No HP
        document.getElementById('nis').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        document.getElementById('no_hp').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        // Handle form submit dengan SweetAlert
        document.getElementById('formDaftar').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Ambil nilai form
            const nama_siswa = document.getElementById('nama_siswa').value.trim();
            const nis = document.getElementById('nis').value.trim();
            const jenis_kelamin = document.querySelector('input[name="jenis_kelamin"]:checked')?.value;
            const kelas = document.getElementById('kelas').value;
            const jurusan = document.getElementById('jurusan').value;
            const no_hp = document.getElementById('no_hp').value.trim();
            const persetujuan = document.getElementById('persetujuan').checked;
            
            // Validasi
            if (!nama_siswa || !nis || !jenis_kelamin || !kelas || !jurusan) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Mohon lengkapi semua field yang wajib diisi (bertanda *)',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }
            
            if (nis.length < 4) {
                Swal.fire({
                    icon: 'warning',
                    title: 'NIS Tidak Valid!',
                    text: 'NIS minimal 4 digit',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }
            
            if (no_hp && (no_hp.length < 10 || no_hp.length > 13)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nomor HP Tidak Valid!',
                    text: 'Nomor HP harus 10-13 digit',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }
            
            if (!persetujuan) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Persetujuan Diperlukan!',
                    text: 'Anda harus menyetujui peraturan untuk melanjutkan pendaftaran',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }
            
            const jenisKelaminIcon = jenis_kelamin === 'Laki-laki' ? '♂️' : '♀️';
            
            // Konfirmasi sebelum simpan
            Swal.fire({
                title: 'Konfirmasi Pendaftaran',
                html: `
                    <div class="text-start">
                        <p><strong>Apakah data berikut sudah benar?</strong></p>
                        <table class="table table-sm table-bordered">
                            <tr><td><strong>Nama</strong></td><td>${nama_siswa}</td></tr>
                            <tr><td><strong>NIS</strong></td><td>${nis}</td></tr>
                            <tr><td><strong>Jenis Kelamin</strong></td><td>${jenisKelaminIcon} ${jenis_kelamin}</td></tr>
                            <tr><td><strong>Kelas</strong></td><td>${kelas}</td></tr>
                            <tr><td><strong>Jurusan</strong></td><td>${jurusan}</td></tr>
                            <tr><td><strong>Ekstrakurikuler</strong></td><td><?php echo htmlspecialchars($nama_ekstra); ?></td></tr>
                            ${no_hp ? `<tr><td><strong>No. HP</strong></td><td>${no_hp}</td></tr>` : ''}
                        </table>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check"></i> Ya, Daftar!',
                cancelButtonText: '<i class="fas fa-edit"></i> Cek Lagi',
                width: '600px'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tampilkan loading
                    Swal.fire({
                        title: 'Memproses Pendaftaran...',
                        html: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Submit form
                    this.submit();
                }
            });
        });
    </script>
</body>
</html>