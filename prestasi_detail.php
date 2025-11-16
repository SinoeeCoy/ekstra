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
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Prestasi</title>

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

        .detail-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .detail-row {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #2a5298;
            width: 200px;
            flex-shrink: 0;
        }

        .detail-value {
            flex-grow: 1;
            color: #333;
        }

        .badge-tingkat {
            font-weight: 600;
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 8px;
        }

        .tingkat-internasional { background: #e74c3c; color: #fff; }
        .tingkat-nasional { background: #f39c12; color: #fff; }
        .tingkat-provinsi { background: #3498db; color: #fff; }
        .tingkat-kabupaten { background: #27ae60; color: #fff; }
        .tingkat-kecamatan { background: #95a5a6; color: #fff; }
        .tingkat-sekolah { background: #34495e; color: #fff; }

        .btn {
            border-radius: 8px;
            font-size: 14px;
            padding: 10px 20px;
            transition: 0.2s;
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-warning {
            background-color: #f4a261;
            border: none;
            color: #fff;
        }

        .btn-warning:hover {
            background-color: #e76f51;
        }

        .btn-danger {
            background-color: #e63946;
            border: none;
        }

        .btn-danger:hover {
            background-color: #c1121f;
        }

        .action-buttons {
            margin-top: 30px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .trophy-icon {
            text-align: center;
            margin-bottom: 20px;
        }

        .trophy-icon i {
            font-size: 60px;
            color: #f39c12;
        }
    </style>
</head>

<body>
    <div class="container">
        <h3><i class="fa-solid fa-trophy"></i> Detail Prestasi Ekstrakurikuler</h3>
        
        <div class="detail-card">
            <div class="trophy-icon">
                <i class="fa-solid fa-medal"></i>
            </div>

            <div class="detail-row">
                <div class="detail-label">
                    <i class="fa-solid fa-users"></i> Ekstrakurikuler
                </div>
                <div class="detail-value">
                    <strong><?= htmlspecialchars($data['nama_ekstra']); ?></strong>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">
                    <i class="fa-solid fa-user"></i> Nama Siswa
                </div>
                <div class="detail-value">
                    <?= htmlspecialchars($data['nama_siswa']); ?>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">
                    <i class="fa-solid fa-award"></i> Prestasi
                </div>
                <div class="detail-value">
                    <strong><?= htmlspecialchars($data['prestasi']); ?></strong>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">
                    <i class="fa-solid fa-layer-group"></i> Tingkat
                </div>
                <div class="detail-value">
                    <?php
                    $tingkat = $data['tingkat'];
                    $badge_class = '';
                    switch(strtolower($tingkat)) {
                        case 'internasional': $badge_class = 'tingkat-internasional'; break;
                        case 'nasional': $badge_class = 'tingkat-nasional'; break;
                        case 'provinsi': $badge_class = 'tingkat-provinsi'; break;
                        case 'kabupaten': $badge_class = 'tingkat-kabupaten'; break;
                        case 'kecamatan': $badge_class = 'tingkat-kecamatan'; break;
                        case 'sekolah': $badge_class = 'tingkat-sekolah'; break;
                    }
                    ?>
                    <span class="badge badge-tingkat <?= $badge_class; ?>">
                        <?= htmlspecialchars($tingkat); ?>
                    </span>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">
                    <i class="fa-solid fa-calendar"></i> Tahun
                </div>
                <div class="detail-value">
                    <?= htmlspecialchars($data['tahun']); ?>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">
                    <i class="fa-solid fa-comment"></i> Keterangan
                </div>
                <div class="detail-value">
                    <?= !empty($data['keterangan']) ? nl2br(htmlspecialchars($data['keterangan'])) : '<span class="text-muted">Tidak ada keterangan</span>'; ?>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="home.php?page=prestasi" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <a href="home.php?page=prestasi_ubah&id=<?= $data['id_prestasi']; ?>" class="btn btn-warning">
                <i class="fa-solid fa-pen-to-square"></i> Edit
            </a>
            <a href="home.php?page=prestasi_hapus&id=<?= $data['id_prestasi']; ?>" 
               class="btn btn-danger" 
               onclick="return confirm('Yakin ingin menghapus prestasi ini?')">
                <i class="fa-solid fa-trash"></i> Hapus
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>