<?php

include 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

if (isset($_GET['nama_ekstra']) && !empty($_GET['nama_ekstra'])) {
    $nama_ekstra = $_GET['nama_ekstra'];
} elseif (isset($_GET['id']) && !empty($_GET['id'])) {
    $nama_ekstra = $_GET['id'];
} else {
    echo '<script>alert("Data tidak valid!"); location.href="home.php?page=ekstra";</script>';
    exit;
}


$stmt = $koneksi->prepare("DELETE FROM ekstra WHERE nama_ekstra = ?");

if ($stmt) {
    $stmt->bind_param("s", $nama_ekstra);
    
    if ($stmt->execute()) {
        
        if ($stmt->affected_rows > 0) {
            echo '<script>alert("Data berhasil dihapus!"); location.href="home.php?page=ekstra";</script>';
        } else {
            echo '<script>alert("Data tidak ditemukan!"); location.href="home.php?page=ekstra";</script>';
        }
    } else {
        echo '<script>alert("Gagal menghapus data: '.$stmt->error.'"); location.href="home.php?page=ekstra";</script>';
    }
    
    $stmt->close();
} else {
    echo '<script>alert("Error: '.mysqli_error($koneksi).'"); location.href="home.php?page=ekstra";</script>';
}

$koneksi->close();
?>