<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "koneksi.php";

// Cek login
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$user_role = strtolower($user['role'] ?? '');

// Fungsi cek akses
function hasAccess($allowed_roles) {
    global $user_role;
    return in_array($user_role, $allowed_roles);
}

// Hanya admin yang boleh akses
if (!hasAccess(['admin'])) {
    echo "<script>alert('Anda tidak memiliki akses!');window.location.href='home.php?page=ekstra';</script>";
    exit;
}

// SIMPAN DATA KE DATABASE
if (isset($_POST['simpan'])) {
    $nama_ekstra = mysqli_real_escape_string($koneksi, $_POST['nama_ekstra']);
    $pembina = mysqli_real_escape_string($koneksi, $_POST['pembina']);
    $hari = mysqli_real_escape_string($koneksi, $_POST['hari']);
    $waktu = mysqli_real_escape_string($koneksi, $_POST['waktu']);
    $lokasi = mysqli_real_escape_string($koneksi, $_POST['lokasi']);

    $query = mysqli_query($koneksi, "INSERT INTO ekstra (nama_ekstra, pembina, hari, waktu, lokasi)
                                     VALUES ('$nama_ekstra', '$pembina', '$hari', '$waktu', '$lokasi')");

    if ($query) {
        echo "<script>
                alert('Data ekstrakurikuler berhasil ditambahkan!');
                window.location.href='home.php?page=ekstra';
              </script>";
        exit;
    } else {
        echo "<script>alert('Gagal menambahkan data!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Ekstrakurikuler</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8f9fa;
        }
        .card {
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }
        .header-title {
            font-size: 22px;
            font-weight: 700;
            color: #333;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="card p-4">
        <div class="header-title mb-3">
            <i class="fa-solid fa-plus"></i> Tambah Ekstrakurikuler
        </div>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nama Ekstrakurikuler</label>
                <input type="text" name="nama_ekstra" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Pembina</label>
                <input type="text" name="pembina" class="form-control" placeholder="Nama pembina" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Hari Pelaksanaan</label>
                <select name="hari" class="form-control" required>
                    <option value="">-- Pilih Hari --</option>
                    <option>Senin</option>
                    <option>Selasa</option>
                    <option>Rabu</option>
                    <option>Kamis</option>
                    <option>Jumat</option>
                    <option>Sabtu</option>
                    <option>Ahad</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Waktu</label>
                <input type="text" name="waktu" class="form-control" placeholder="Contoh: 14.00 - 16.00" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Lokasi</label>
                <input type="text" name="lokasi" class="form-control" placeholder="Contoh: Lapangan, Ruang Musik, dll">
            </div>

            <div class="text-end">
                <a href="home.php?page=ekstra" class="btn btn-secondary">Kembali</a>
                <button type="submit" name="simpan" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
