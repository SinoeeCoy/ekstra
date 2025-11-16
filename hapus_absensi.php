<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

include "koneksi.php";

$id = $_GET['id'];
mysqli_query($koneksi, "DELETE FROM data_siswa WHERE id='$id'");

header("Location: absensi.php");
?>
