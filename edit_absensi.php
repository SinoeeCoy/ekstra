<?php
include "koneksi.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$result = mysqli_query($koneksi, "SELECT * FROM data_siswa WHERE id='$id'");
$data = mysqli_fetch_assoc($result);

if(isset($_POST['update'])){
    $nama_siswa = $_POST['nama_siswa'];
    $kelas = $_POST['kelas'];
    $nama_ekstra = $_POST['nama_ekstra'];

    mysqli_query($koneksi, "UPDATE data_siswa SET 
        nama_siswa='$nama_siswa',
        kelas='$kelas',
        nama_ekstra='$nama_ekstra'
        WHERE id='$id'
    ");

    header("Location: absensi.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Siswa</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,.1);
        }
        .btn-custom {
            background: #1e40af;
            color: #fff;
            transition: 0.3s;
        }
        .btn-custom:hover {
            background: #0f2167;
            color: #fff;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4">
                <h3 class="mb-4 text-center text-primary">✏️ Edit Data Siswa</h3>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Nama Siswa</label>
                        <input type="text" name="nama_siswa" value="<?= htmlspecialchars($data['nama_siswa']) ?>" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <input type="text" name="kelas" value="<?= htmlspecialchars($data['kelas']) ?>" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ekstrakurikuler</label>
                        <select name="nama_ekstra" class="form-select" required>
                            <?php
                            $ekstra_query = mysqli_query($koneksi, "SELECT nama_ekstra FROM ekstra ORDER BY nama_ekstra");
                            while($ekstra = mysqli_fetch_array($ekstra_query)){
                                $selected = ($data['nama_ekstra'] == $ekstra['nama_ekstra']) ? 'selected' : '';
                                echo "<option value='".htmlspecialchars($ekstra['nama_ekstra'])."' $selected>".htmlspecialchars($ekstra['nama_ekstra'])."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="absensi.php" class="btn btn-secondary">⬅ Kembali</a>
                        <button type="submit" name="update" class="btn btn-custom">💾 Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
