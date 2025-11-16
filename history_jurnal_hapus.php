<?php
include "koneksi.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// Ambil role dari session
$user_role = '';
if (isset($_SESSION['user']['role'])) {
    $user_role = $_SESSION['user']['role'];
} elseif (isset($_SESSION['role'])) {
    $user_role = $_SESSION['role'];
}

// Konversi ke lowercase
$user_role_lower = strtolower(trim($user_role));

// Daftar role yang diizinkan menghapus
$allowed_roles = ['admin', 'administrator', 'pembina', 'superadmin'];

// Cek akses
if (!in_array($user_role_lower, $allowed_roles)) {
    $username = isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : 'Unknown';
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Akses Ditolak</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak!',
            html: 'Anda tidak memiliki akses untuk menghapus data.<br><strong>User:</strong> " . htmlspecialchars($username) . "<br><strong>Role:</strong> " . htmlspecialchars($user_role) . "',
            confirmButtonText: 'Kembali'
        }).then(() => {
            window.location = '?page=history_jurnal';
        });
    </script>
    </body>
    </html>";
    exit;
}

// Proses hapus data
$id = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';

if (empty($id)) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Error</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'ID tidak ditemukan!',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location = '?page=history_jurnal';
        });
    </script>
    </body>
    </html>";
    exit;
}

// Ambil data foto untuk dihapus
$get_foto = mysqli_query($koneksi, "SELECT foto FROM absen WHERE id = '$id'");
$data = mysqli_fetch_array($get_foto);

if (!$data) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Error</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Data tidak ditemukan!',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location = '?page=history_jurnal';
        });
    </script>
    </body>
    </html>";
    exit;
}

// Hapus foto jika ada
if (!empty($data['foto']) && file_exists('uploads/kegiatan/' . $data['foto'])) {
    unlink('uploads/kegiatan/' . $data['foto']);
}

// Hapus data dari database
$hapus = mysqli_query($koneksi, "DELETE FROM absen WHERE id = '$id'");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Hapus Data</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
<?php if ($hapus): ?>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Data berhasil dihapus',
        showConfirmButton: false,
        timer: 1500
    }).then(() => {
        window.location = '?page=history_jurnal';
    });
<?php else: ?>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'Data gagal dihapus: <?= mysqli_error($koneksi); ?>',
        confirmButtonText: 'OK'
    }).then(() => {
        window.location = '?page=history_jurnal';
    });
<?php endif; ?>
</script>
</body>
</html>