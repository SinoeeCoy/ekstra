<?php
include 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $query = mysqli_query($koneksi, "DELETE FROM pembayaran WHERE id_pembayaran = '$id'");

    if ($query) {
        echo '<script>alert("Data berhasil dihapus."); window.location.href="home.php?page=pembayaran";</script>';
    } else {
        echo '<script>alert("Gagal menghapus data."); window.history.back();</script>';
    }
} else {
    echo '<script>alert("ID tidak ditemukan."); window.history.back();</script>';
}
?>
