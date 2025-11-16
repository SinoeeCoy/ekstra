<?php
session_start();
include 'koneksi.php';
var_dump($_POST);
echo "Password md5: " . md5($_POST['password']) . "<br>";

$username = $_POST['username'];
$password = md5($_POST['password']);

$cek_petugas = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' AND password='$password'");

if(mysqli_num_rows($cek_petugas) > 0) {
    $data = mysqli_fetch_assoc($cek_petugas);

    // Simpan data user ke session
    $_SESSION['user'] = $data;

    echo '<script>alert("Selamat, anda berhasil login");location.href="home.php";</script>';
} else {
    echo '<script>alert("Username Atau Password Salah");location.href="index.php";</script>';
}
?>
