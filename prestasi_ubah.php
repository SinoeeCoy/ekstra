<?php 
include "koneksi.php";

// Cek apakah user sudah login
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$user_role = strtolower($user['role'] ?? '');

// Fungsi untuk cek akses
function hasAccess($allowed_roles) {
    global $user_role;
    return in_array($user_role, $allowed_roles);
}

// Cek akses halaman
if (!hasAccess(['pembina','waka','admin','kepala'])) {
    echo "<script>alert('Anda tidak memiliki akses ke halaman ini!'); window.location='home.php';</script>";
    exit;
}

// Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Query data prestasi
$query = mysqli_query($koneksi, "SELECT * FROM data_prestasi WHERE id_prestasi='$id'");

if (mysqli_num_rows($query) == 0) {
    echo "<script>alert('Data prestasi tidak ditemukan!'); window.location='home.php?page=prestasi';</script>";
    exit;
}

$data = mysqli_fetch_array($query);

// Proses form submit
if (isset($_POST['simpan'])) {
    $nama_ekstra = mysqli_real_escape_string($koneksi, $_POST['nama_ekstra']);
    $nama_siswa = mysqli_real_escape_string($koneksi, $_POST['nama_siswa']);
    $prestasi = mysqli_real_escape_string($koneksi, $_POST['prestasi']);
    $tingkat = mysqli_real_escape_string($koneksi, $_POST['tingkat']);
    $tahun = mysqli_real_escape_string($koneksi, $_POST['tahun']);
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    // Validasi input
    if (empty($nama_ekstra) || empty($nama_siswa) || empty($prestasi) || empty($tingkat) || empty($tahun)) {
        echo "<script>alert('Semua field wajib diisi kecuali keterangan!');</script>";
    } else {
        $update = mysqli_query($koneksi, "UPDATE data_prestasi SET 
            nama_ekstra='$nama_ekstra',
            nama_siswa='$nama_siswa',
            prestasi='$prestasi',
            tingkat='$tingkat',
            tahun='$tahun',
            keterangan='$keterangan'
            WHERE id_prestasi='$id'
        ");

        if ($update) {
            echo "<script>
                alert('Data prestasi berhasil diperbarui!');
                window.location='home.php?page=prestasi';
            </script>";
        } else {
            echo "<script>alert('Gagal memperbarui data: " . mysqli_error($koneksi) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Prestasi</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .container {
            background: #fff;
            margin-top: 40px;
            margin-bottom: 40px;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 8px 25px rgba(0,0,0,0.25);
            max-width: 800px;
        }

        h3 {
            color: #1e3c72;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 600;
            color: #2a5298;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 15px;
            transition: border-color 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #2a5298;
            box-shadow: 0 0 0 0.2rem rgba(42, 82, 152, 0.25);
        }

        .required {
            color: #e63946;
        }

        .btn {
            border-radius: 8px;
            font-size: 14px;
            padding: 10px 20px;
            transition: 0.2s;
        }

        .btn-primary {
            background-color: #2a5298;
            border: none;
        }

        .btn-primary:hover {
            background-color: #1e3c72;
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .form-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .button-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 30px;
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h3><i class="fa-solid fa-pen-to-square"></i> Edit Prestasi Ekstrakurikuler</h3>
        
        <div class="form-card">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="nama_ekstra" class="form-label">
                        <i class="fa-solid fa-users"></i> Nama Ekstrakurikuler <span class="required">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="nama_ekstra" 
                           name="nama_ekstra" 
                           value="<?= htmlspecialchars($data['nama_ekstra']); ?>" 
                           placeholder="Contoh: Pramuka, PMR, Paskibra" 
                           required>
                </div>

                <div class="mb-3">
                    <label for="nama_siswa" class="form-label">
                        <i class="fa-solid fa-user"></i> Nama Siswa <span class="required">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="nama_siswa" 
                           name="nama_siswa" 
                           value="<?= htmlspecialchars($data['nama_siswa']); ?>" 
                           placeholder="Masukkan nama lengkap siswa" 
                           required>
                </div>

                <div class="mb-3">
                    <label for="prestasi" class="form-label">
                        <i class="fa-solid fa-award"></i> Prestasi <span class="required">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="prestasi" 
                           name="prestasi" 
                           value="<?= htmlspecialchars($data['prestasi']); ?>" 
                           placeholder="Contoh: Juara 1 Lomba Debat" 
                           required>
                </div>

                <div class="mb-3">
                    <label for="tingkat" class="form-label">
                        <i class="fa-solid fa-layer-group"></i> Tingkat Prestasi <span class="required">*</span>
                    </label>
                    <select class="form-select" id="tingkat" name="tingkat" required>
                        <option value="">-- Pilih Tingkat --</option>
                        <option value="Internasional" <?= $data['tingkat'] == 'Internasional' ? 'selected' : ''; ?>>Internasional</option>
                        <option value="Nasional" <?= $data['tingkat'] == 'Nasional' ? 'selected' : ''; ?>>Nasional</option>
                        <option value="Provinsi" <?= $data['tingkat'] == 'Provinsi' ? 'selected' : ''; ?>>Provinsi</option>
                        <option value="Kabupaten" <?= $data['tingkat'] == 'Kabupaten' ? 'selected' : ''; ?>>Kabupaten/Kota</option>
                        <option value="Kecamatan" <?= $data['tingkat'] == 'Kecamatan' ? 'selected' : ''; ?>>Kecamatan</option>
                        <option value="Sekolah" <?= $data['tingkat'] == 'Sekolah' ? 'selected' : ''; ?>>Sekolah</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="tahun" class="form-label">
                        <i class="fa-solid fa-calendar"></i> Tahun <span class="required">*</span>
                    </label>
                    <input type="number" 
                           class="form-control" 
                           id="tahun" 
                           name="tahun" 
                           value="<?= htmlspecialchars($data['tahun']); ?>" 
                           placeholder="Contoh: 2024" 
                           min="2000" 
                           max="<?= date('Y') + 1; ?>" 
                           required>
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label">
                        <i class="fa-solid fa-comment"></i> Keterangan
                    </label>
                    <textarea class="form-control" 
                              id="keterangan" 
                              name="keterangan" 
                              rows="4" 
                              placeholder="Masukkan keterangan tambahan (opsional)"><?= htmlspecialchars($data['keterangan']); ?></textarea>
                    <small class="text-muted">Contoh: Lokasi lomba, penyelenggara, dll.</small>
                </div>

                <div class="button-group">
                    <a href="home.php?page=prestasi" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left"></i> Batal
                    </a>
                    <button type="submit" name="simpan" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>