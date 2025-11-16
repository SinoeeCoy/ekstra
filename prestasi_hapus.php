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
    echo "<script>
        alert('Anda tidak memiliki akses ke halaman ini!');
        window.location='home.php?page=prestasi';
    </script>";
    exit;
}

// Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validasi ID
if ($id <= 0) {
    echo "<script>
        alert('ID prestasi tidak valid!');
        window.location='home.php?page=prestasi';
    </script>";
    exit;
}

// Cek apakah data prestasi ada
$check = mysqli_query($koneksi, "SELECT * FROM data_prestasi WHERE id_prestasi='$id'");

if (mysqli_num_rows($check) == 0) {
    echo "<script>
        alert('Data prestasi tidak ditemukan!');
        window.location='home.php?page=prestasi';
    </script>";
    exit;
}

$data = mysqli_fetch_array($check);

// Proses hapus data
$delete = mysqli_query($koneksi, "DELETE FROM data_prestasi WHERE id_prestasi='$id'");

if ($delete) {
    // Log aktivitas hapus (opsional)
    $nama_prestasi = $data['prestasi'];
    $nama_siswa = $data['nama_siswa'];
    $log_message = "Prestasi '$nama_prestasi' oleh $nama_siswa berhasil dihapus oleh " . $user['nama'];
    
    // Jika Anda punya tabel log, bisa tambahkan di sini
    // mysqli_query($koneksi, "INSERT INTO log_aktivitas (user_id, aktivitas, waktu) VALUES (...)");
    
    echo "<script>
        alert('Data prestasi berhasil dihapus!');
        window.location='home.php?page=prestasi';
    </script>";
} else {
    echo "<script>
        alert('Gagal menghapus data: " . mysqli_error($koneksi) . "');
        window.location='home.php?page=prestasi';
    </script>";
}
?>