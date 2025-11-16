<?php
include 'koneksi.php'; 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
if (isset($_POST['nama_ekstra'])) {
    $nama_ekstra = $_POST['nama_ekstra'];
    $nama_siswa = $_POST['nama_siswa'];
    $prestasi = $_POST['prestasi'];
    $tingkat = $_POST['tingkat'];
    $tahun= $_POST['tahun'];
    $keterangan = $_POST['keterangan'];

    
    $stmt = $koneksi->prepare("INSERT INTO data_prestasi (nama_ekstra, nama_siswa, prestasi, tingkat, tahun, keterangan) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $nama_ekstra, $nama_siswa, $prestasi, $tingkat, $tahun, $keterangan);
    
    if ($stmt->execute()) {
        $stmt->close();
        header("Location:prestasi.php?pesan=input");
        exit;
    } else {
        echo "Error: " . $stmt->error;
        $stmt->close();
    }
}
?>

<h1 class="h3 mb-3">Tambah Data Prestasi</h1>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <a href="home.php?page=prestasi" class="btn btn-primary">Kembali</a>
                <hr>
                <form method="POST" action="prestasi_tambah.php">
                    <table class="table">
                        <tr>
                            <td width="200">Nama Ekstra</td>
                            <td width="1">:</td>
                            <td><input class="form-control" type="text" name="nama_ekstra" required></td>
                        </tr>
                        <tr>
                            <td width="200">Nama Siswa</td>
                            <td width="1">:</td>
                            <td><input class="form-control" type="text" name="nama_siswa" required></td>
                        </tr>

                    <tr>
                            <td width="200">Prestasi</td>
                            <td width="1">:</td>
                            <td><input class="form-control" type="text" name="prestasi" required></td>
                        </tr>

                        <tr>
                            <td width="200">Tingkat</td>
                            <td width="1">:</td>
                            <td><input class="form-control" type="date" name="tingkat" required></td>
                        </tr>

                        <tr>
                            <td width="200">Tahun</td>
                            <td width="1">:</td>
                            <td><input class="form-control" type="time" name="Tahun" required></td>
                        </tr>

                        <tr>
                            <td width="200">Keterangan</td>
                            <td width="1">:</td>
                            <td><input class="form-control" type="number" name="keterangan" required></td>
                        </tr>

                        <tr>
                            <td></td>
                            <td></td>
                            <td><button class="btn btn-success" type="submit">Simpan</button></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>
