<?php
include "koneksi.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $nama_ekstra = $_POST['nama_ekstra'];
    $nama_siswa  = $_POST['nama_siswa'];
    $prestasi    = $_POST['prestasi'];
    $tingkat     = $_POST['tingkat'];
    $tahun       = $_POST['tahun'];
    $keterangan  = $_POST['keterangan'];

    $query = mysqli_query($koneksi, "INSERT INTO data_prestasi 
        (nama_ekstra, nama_siswa, prestasi, tingkat, tahun, keterangan) 
        VALUES ('$nama_ekstra','$nama_siswa','$prestasi','$tingkat','$tahun','$keterangan')");

    if ($query) {
        header("Location: home.php?page=prestasi_data&status=sukses");
    } else {
        header("Location: home.php?page=prestasi_tambah&status=gagal");
    }
}
?>
