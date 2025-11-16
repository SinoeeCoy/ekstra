<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// Simpan waktu login jika belum ada
if (!isset($_SESSION['login_time'])) {
    $_SESSION['login_time'] = date('d-m-Y H:i:s');
}

$user = $_SESSION['user'];
$username = htmlspecialchars($user['username'] ?? '-', ENT_QUOTES, 'UTF-8');
$login_time = $_SESSION['login_time'];

function getUserLevel($role) {
    switch(strtolower($role)) {
        case 'siswa':
            return 'Siswa';
        case 'pembina':
            return 'Pembina';
        case 'admin':
            return 'Admin';
        default:
            return 'User';
    }
}

$user_level = getUserLevel($user['role'] ?? 'user');
$user_role = htmlspecialchars($user['role'] ?? '-', ENT_QUOTES, 'UTF-8');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 mb-3">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <span class="badge bg-<?php echo ($user_level == 'Admin') ? 'danger' : (($user_level == 'Pembina') ? 'warning' : 'success'); ?> fs-6">
                <?php echo $user_level; ?>
            </span>
        </div>
    </div>
</div>

<div class="row">
    <!-- Welcome Card -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <i class="fas fa-home me-2"></i>
                <h5 class="card-title mb-0">Selamat Datang di Sistem Informasi Ekstrakurikuler Sekolah</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tr>
                                <td width="150" class="fw-semibold">Nama User</td>
                                <td width="10">:</td>
                                <td><?php echo $username; ?></td>
                            </tr>
                            <tr>
                                <td width="150" class="fw-semibold">Tanggal Login</td>
                                <td width="10">:</td>
                                <td><?php echo $login_time; ?></td>
                            </tr>
                            <?php if (isset($user['email'])): ?>
                            <tr>
                                <td width="150" class="fw-semibold">Email</td>
                                <td width="10">:</td>
                                <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($user_level == 'Admin'): ?>
<!-- Admin Dashboard Stats -->
<div class="row mt-4">
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="small text-light">Total Siswa</div>
                        <div class="h5 mb-0">
                            <?php
                            if (isset($koneksi)) {
                                $result = $koneksi->query("SELECT COUNT(*) as total FROM data_siswa");
                                $total_siswa = $result ? $result->fetch_assoc()['total'] : 0;
                                echo $total_siswa;
                            } else {
                                echo "0";
                            }
                            ?>
                        </div>
                    </div>
                    <div><i class="fas fa-users fa-2x"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card bg-success text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="small text-light">Total Pembina</div>
                        <div class="h5 mb-0">
                            <?php
                            if (isset($koneksi)) {
                                $result = $koneksi->query("SELECT COUNT(*) as total FROM profil_pembina");
                                $total_pembina = $result ? $result->fetch_assoc()['total'] : 0;
                                echo $total_pembina;
                            } else {
                                echo "0";
                            }
                            ?>
                        </div>
                    </div>
                    <div><i class="fas fa-chalkboard-teacher fa-2x"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card bg-warning text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="small text-light">Total Ekstrakurikuler</div>
                        <div class="h5 mb-0">
                            <?php
                            if (isset($koneksi)) {
                                $result = $koneksi->query("SELECT COUNT(*) as total FROM ekstra");
                                $total_ekstra = $result ? $result->fetch_assoc()['total'] : 0;
                                echo $total_ekstra;
                            } else {
                                echo "0";
                            }
                            ?>
                        </div>
                    </div>
                    <div><i class="fas fa-futbol fa-2x"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card bg-info text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="small text-light">Total Prestasi</div>
                        <div class="h5 mb-0">
                            <?php
                            if (isset($koneksi)) {
                                $result = $koneksi->query("SELECT COUNT(*) as total FROM data_prestasi");
                                $total_prestasi = $result ? $result->fetch_assoc()['total'] : 0;
                                echo $total_prestasi;
                            } else {
                                echo "0";
                            }
                            ?>
                        </div>
                    </div>
                    <div><i class="fas fa-trophy fa-2x"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if ($user_level == 'Admin'): ?>
                    <!-- Admin Quick Actions -->
                    <div class="col-md-3 mb-2">
                        <a href="home.php?page=siswa" class="btn btn-primary btn-block w-100">
                            <i class="fas fa-user-plus"></i> Tambah Siswa
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="home.php?page=ekstra_tambah" class="btn btn-success btn-block w-100">
                            <i class="fas fa-plus-circle"></i> Tambah Ekstrakurikuler
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="home.php?page=absensi" class="btn btn-warning btn-block w-100">
                            <i class="fas fa-calendar-check"></i> Kelola Absensi
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="home.php?page=galeri" class="btn btn-info btn-block w-100">
                            <i class="fas fa-images"></i> Kelola Galeri
                        </a>
                    </div>
                    
                    <?php elseif ($user_level == 'Pembina'): ?>
                    <!-- Pembina Quick Actions -->
                    <div class="col-md-3 mb-2">
                        <a href="home.php?page=sistemprofil_pembina" class="btn btn-info btn-block w-100">
                            <i class="fas fa-user"></i> Profil Pembina
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="home.php?page=absensi" class="btn btn-warning btn-block w-100">
                            <i class="fas fa-calendar-check"></i> Kelola Absensi
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="home.php?page=siswa" class="btn btn-success btn-block w-100">
                            <i class="fas fa-users"></i> Data Siswa
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="home.php?page=galeri" class="btn btn-primary btn-block w-100">
                            <i class="fas fa-images"></i> Galeri
                        </a>
                    </div>
                    
                    <?php else: ?>
                    <!-- Student Quick Actions -->
                    <div class="col-md-3 mb-2">
                        <a href="home.php?page=sistemprofil_pembina" class="btn btn-info btn-block w-100">
                            <i class="fas fa-user"></i> Profil Pembina
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="home.php?page=ekstra" class="btn btn-primary btn-block w-100">
                            <i class="fas fa-search"></i> Jelajahi Ekstrakurikuler
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="home.php?page=siswa" class="btn btn-success btn-block w-100">
                            <i class="fas fa-bookmark"></i> Data Siswa
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="home.php?page=galeri" class="btn btn-warning btn-block w-100">
                            <i class="fas fa-calendar"></i> Galeri
                        </a>
                    </div>
                   
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>