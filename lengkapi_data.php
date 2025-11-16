<?php
session_start();

// Cek apakah user sudah login dan sudah pilih ekstra
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'siswa' || !isset($_SESSION['pilihan_ekstra'])) {
    header("Location: pilih_ekstra.php");
    exit();
}

require 'koneksi.php';

$user_id = $_SESSION['user']['id'];

// Proses submit form
if (isset($_POST['submit_data'])) {
    $nama_siswa = trim($_POST['nama_lengkap']);
    $nis = trim($_POST['nis']);
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $kelas = $_POST['kelas'];
    $jurusan = $_POST['jurusan'];
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    $setuju = isset($_POST['setuju']);
    
    $errors = [];
    
    // Validasi
    if (empty($nama_siswa)) $errors[] = "Nama lengkap harus diisi";
    if (empty($nis)) $errors[] = "NIS harus diisi";
    if (!preg_match('/^[0-9]{4,10}$/', $nis)) $errors[] = "NIS harus 4-10 digit angka";
    if (empty($jenis_kelamin)) $errors[] = "Jenis kelamin harus dipilih";
    if (empty($kelas)) $errors[] = "Kelas harus dipilih";
    if (empty($jurusan)) $errors[] = "Jurusan harus dipilih";
    if (empty($no_hp)) $errors[] = "No HP harus diisi";
    if (!preg_match('/^[0-9]{10,13}$/', $no_hp)) $errors[] = "No HP tidak valid (10-13 digit)";
    if (empty($alamat)) $errors[] = "Alamat harus diisi";
    if (!$setuju) $errors[] = "Anda harus menyetujui untuk mengikuti ekstrakurikuler";
    
    if (empty($errors)) {
        // Cek NIS sudah digunakan atau belum
        $checkNIS = $koneksi->prepare("SELECT id FROM data_siswa WHERE nis = ?");
        $checkNIS->bind_param("s", $nis);
        $checkNIS->execute();
        $resultNIS = $checkNIS->get_result();
        
        if ($resultNIS->num_rows > 0) {
            $errors[] = "NIS sudah digunakan oleh siswa lain";
        } else {
            $koneksi->begin_transaction();
            
            try {
                // Gabungkan pilihan ekstra menjadi string (pisahkan dengan koma)
                $ekstra_dipilih = implode(',', $_SESSION['pilihan_ekstra']);
                
                // Insert data siswa
                $insertStmt = $koneksi->prepare("INSERT INTO data_siswa (user_id, nama_siswa, nis, kelas, jenis_kelamin, jurusan, no_hp, alamat, ekstra_id, tanggal_daftar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $insertStmt->bind_param("issssssss", $user_id, $nama_siswa, $nis, $kelas, $jenis_kelamin, $jurusan, $no_hp, $alamat, $ekstra_dipilih);
                $insertStmt->execute();
                
                $koneksi->commit();
                
                // Hapus session pilihan ekstra
                unset($_SESSION['pilihan_ekstra']);
                
                echo '<script>alert("Data berhasil disimpan! Selamat bergabung."); window.location.href = "home.php";</script>';
            } catch (Exception $e) {
                $koneksi->rollback();
                $errors[] = "Gagal menyimpan data: " . $e->getMessage();
            }
        }
    }
}

// Ambil data ekstrakurikuler yang dipilih
$ekstraIds = implode(',', array_map('intval', $_SESSION['pilihan_ekstra']));
$ekstraStmt = $koneksi->query("SELECT * FROM ekstra WHERE id IN ($ekstraIds)");
$ekstraSelected = $ekstraStmt->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lengkapi Data Siswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .main-content {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .sidebar {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 30px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
        }

        .header-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 26px;
            margin-bottom: 8px;
        }

        .header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .content {
            padding: 40px;
        }

        .section {
            margin-bottom: 35px;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }

        .section-title i {
            color: #667eea;
            font-size: 22px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-size: 14px;
            font-weight: 500;
        }

        .form-group label .required {
            color: #f44336;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-group small {
            display: block;
            margin-top: 5px;
            color: #999;
            font-size: 12px;
        }

        .radio-group {
            display: flex;
            gap: 25px;
            margin-top: 10px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .radio-option input[type="radio"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }

        .radio-option label {
            margin: 0;
            cursor: pointer;
        }

        .checkbox-group {
            display: flex;
            align-items: start;
            gap: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            margin-top: 10px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-top: 2px;
            cursor: pointer;
            accent-color: #667eea;
        }

        .checkbox-group label {
            margin: 0;
            font-size: 14px;
            line-height: 1.5;
            cursor: pointer;
        }

        .error-list {
            background: #fee;
            border-left: 4px solid #f44336;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .error-list strong {
            color: #c62828;
            display: block;
            margin-bottom: 10px;
        }

        .error-list ul {
            margin-left: 20px;
            color: #c62828;
        }

        .error-list ul li {
            margin: 5px 0;
        }

        .btn-container {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #e0e0e0;
        }

        .btn {
            flex: 1;
            padding: 14px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        .btn-secondary {
            background: white;
            color: #666;
            border: 2px solid #e0e0e0;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
            border-color: #667eea;
            color: #667eea;
        }

        /* Sidebar Styles */
        .info-card {
            background: linear-gradient(135deg, #e8f4f8 0%, #d4e9f5 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .info-card h3 {
            color: #17a2b8;
            font-size: 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-card ul {
            margin-left: 20px;
            color: #555;
            font-size: 13px;
        }

        .info-card ul li {
            margin: 8px 0;
            line-height: 1.5;
        }

        .ekstra-list {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
        }

        .ekstra-list h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ekstra-item {
            background: white;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 3px solid #667eea;
        }

        .ekstra-item:last-child {
            margin-bottom: 0;
        }

        .ekstra-item strong {
            color: #333;
            display: block;
            margin-bottom: 5px;
        }

        .ekstra-item small {
            color: #666;
            font-size: 12px;
        }

        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                order: -1;
            }
        }

        @media (max-width: 768px) {
            .content {
                padding: 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .btn-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-content">
            <div class="header">
                <div class="header-icon"><i class="fas fa-edit"></i></div>
                <h1>Data Siswa</h1>
                <p>Lengkapi data pribadi Anda untuk melanjutkan pendaftaran</p>
            </div>

            <div class="content">
                <?php if (!empty($errors)): ?>
                    <div class="error-list">
                        <strong><i class="fas fa-exclamation-triangle"></i> Terdapat kesalahan:</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <!-- Data Pribadi -->
                    <div class="section">
                        <div class="section-title">
                            <i class="fas fa-id-card"></i>
                            <span>Data Pribadi</span>
                        </div>

                        <div class="form-group">
                            <label>Nama Lengkap Siswa <span class="required">*</span></label>
                            <input type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap siswa" 
                                   value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>NIS (Nomor Induk Siswa) <span class="required">*</span></label>
                                <input type="text" name="nis" placeholder="Contoh: 12345" pattern="[0-9]{4,10}" maxlength="10"
                                       value="<?php echo isset($_POST['nis']) ? htmlspecialchars($_POST['nis']) : ''; ?>" required>
                                <small>Minimal 4 digit angka</small>
                            </div>

                            <div class="form-group">
                                <label>Jenis Kelamin <span class="required">*</span></label>
                                <div class="radio-group">
                                    <div class="radio-option">
                                        <input type="radio" id="laki" name="jenis_kelamin" value="Laki-laki" 
                                               <?php echo (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] == 'Laki-laki') ? 'checked' : ''; ?> required>
                                        <label for="laki"><i class="fas fa-male"></i> Laki-laki</label>
                                    </div>
                                    <div class="radio-option">
                                        <input type="radio" id="perempuan" name="jenis_kelamin" value="Perempuan"
                                               <?php echo (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] == 'Perempuan') ? 'checked' : ''; ?> required>
                                        <label for="perempuan"><i class="fas fa-female"></i> Perempuan</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kelas & Jurusan -->
                    <div class="section">
                        <div class="section-title">
                            <i class="fas fa-school"></i>
                            <span>Kelas & Jurusan</span>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Kelas <span class="required">*</span></label>
                                <select name="kelas" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    <option value="X" <?php echo (isset($_POST['kelas']) && $_POST['kelas'] == 'X') ? 'selected' : ''; ?>>X</option>
                                    <option value="XI" <?php echo (isset($_POST['kelas']) && $_POST['kelas'] == 'XI') ? 'selected' : ''; ?>>XI</option>
                                    <option value="XII" <?php echo (isset($_POST['kelas']) && $_POST['kelas'] == 'XII') ? 'selected' : ''; ?>>XII</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Jurusan <span class="required">*</span></label>
                                <select name="jurusan" required>
                                    <option value="">-- Pilih Jurusan --</option>
                                    <option value="PPLG" <?php echo (isset($_POST['jurusan']) && $_POST['jurusan'] == 'PPLG') ? 'selected' : ''; ?>>PPLG</option>
                                    <option value="TJKT" <?php echo (isset($_POST['jurusan']) && $_POST['jurusan'] == 'TJKT') ? 'selected' : ''; ?>>TJKT</option>
                                    <option value="BUSANA" <?php echo (isset($_POST['jurusan']) && $_POST['jurusan'] == 'BUSANA') ? 'selected' : ''; ?>>BUSANA</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Kontak -->
                    <div class="section">
                        <div class="section-title">
                            <i class="fas fa-address-book"></i>
                            <span>Informasi Kontak</span>
                        </div>

                        <div class="form-group">
                            <label>No. HP/WA <span class="required">*</span></label>
                            <input type="tel" name="no_hp" placeholder="Contoh: 08123456789" pattern="[0-9]{10,13}" maxlength="13"
                                   value="<?php echo isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : ''; ?>" required>
                            <small>Format: 08xxxxxxxxxx (10-13 digit)</small>
                        </div>

                        <div class="form-group">
                            <label>Alamat Rumah <span class="required">*</span></label>
                            <textarea name="alamat" placeholder="Masukkan alamat lengkap" required><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
                        </div>
                    </div>

                    <!-- Persetujuan -->
                    <div class="section">
                        <div class="checkbox-group">
                            <input type="checkbox" id="setuju" name="setuju" required>
                            <label for="setuju">
                                Saya menyetujui untuk mengikuti ekstrakurikuler ini dan mematuhi semua peraturan yang berlaku. 
                                <span class="required">*</span>
                            </label>
                        </div>
                    </div>

                    <div class="btn-container">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='pilih_ekstra.php'">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </button>
                        <button type="submit" name="submit_data" class="btn btn-primary">
                            <i class="fas fa-check"></i> Simpan & Lanjutkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="info-card">
                <h3><i class="fas fa-info-circle"></i> Informasi Penting</h3>
                <ul>
                    <li><i class="fas fa-check"></i> Pastikan data yang dimasukkan benar</li>
                    <li><i class="fas fa-check"></i> NIS harus sesuai dengan data sekolah</li>
                    <li><i class="fas fa-check"></i> Pilih jurusan sesuai dengan kelas Anda</li>
                    <li><i class="fas fa-check"></i> No. HP akan digunakan untuk komunikasi</li>
                    <li><i class="fas fa-check"></i> Pendaftaran tidak dapat dibatalkan</li>
                </ul>
            </div>

            <div class="ekstra-list">
                <h3><i class="fas fa-star"></i> Ekstrakurikuler Dipilih</h3>
                <?php foreach ($ekstraSelected as $ekstra): ?>
                    <div class="ekstra-item">
                        <strong><?php echo htmlspecialchars($ekstra['nama_ekstra']); ?></strong>
                        <small>
                            <i class="fas fa-calendar"></i> <?php echo htmlspecialchars($ekstra['hari'] ?? 'Belum ditentukan'); ?> | 
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($ekstra['lokasi'] ?? 'Belum ditentukan'); ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>