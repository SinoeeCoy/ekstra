<?php
include "koneksi.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$user_role = strtolower($user['role'] ?? '');
$username_login = $_SESSION['user']['username'];

// Cek role
$is_siswa = ($user_role === 'siswa');
$is_pembina = ($user_role === 'pembina');
$is_admin = in_array($user_role, ['admin', 'waka', 'kepala']);

// Jika ada parameter view (untuk melihat detail profil)
if (isset($_GET['view'])) {
    $nama_pembina_view = mysqli_real_escape_string($koneksi, $_GET['view']);
    $query_detail = mysqli_query($koneksi, "SELECT * FROM profil_pembina WHERE nama_pembina = '$nama_pembina_view'");
    
    if (mysqli_num_rows($query_detail) > 0) {
        $pembina_detail = mysqli_fetch_assoc($query_detail);
        include 'profil_pembina_detail.php';
        exit;
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Data Tidak Ditemukan',
                text: 'Profil pembina tidak ditemukan.'
            }).then(() => {
                window.location.href = '?page=sistemprofil_pembina';
            });
        </script>";
        exit;
    }
}

// Jika ada parameter edit, redirect ke halaman edit
if (isset($_GET['edit'])) {
    $nama_pembina_edit = mysqli_real_escape_string($koneksi, $_GET['edit']);
    
    // Cek hak akses edit
    if ($is_pembina && $nama_pembina_edit !== $username_login) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Akses Ditolak!',
                text: 'Anda hanya bisa mengedit profil sendiri.'
            }).then(() => {
                window.location.href = '?page=sistemprofil_pembina';
            });
        </script>";
        exit;
    }
    
    include 'profil_pembina_edit.php';
    exit;
}

// Proses hapus (hanya untuk admin)
if (isset($_GET['hapus']) && $is_admin) {
    $nama_pembina_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Ambil data foto untuk dihapus
    $get_foto = mysqli_query($koneksi, "SELECT foto FROM profil_pembina WHERE nama_pembina = '$nama_pembina_hapus'");
    $data_foto = mysqli_fetch_assoc($get_foto);
    
    // Hapus file foto jika ada
    if (!empty($data_foto['foto']) && file_exists('uploads/pembina/' . $data_foto['foto'])) {
        unlink('uploads/pembina/' . $data_foto['foto']);
    }
    
    // Hapus data dari database
    $delete_query = mysqli_query($koneksi, "DELETE FROM profil_pembina WHERE nama_pembina = '$nama_pembina_hapus'");
    
    if ($delete_query) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Profil pembina berhasil dihapus.',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = '?page=sistemprofil_pembina';
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Gagal menghapus profil pembina.'
            });
        </script>";
    }
}

// Ambil semua data pembina dari database
$query_pembina = mysqli_query($koneksi, "SELECT * FROM profil_pembina ORDER BY nama_pembina ASC");
$total_pembina = mysqli_num_rows($query_pembina);

// Simpan semua data ke array
$pembina_list = [];
while ($row = mysqli_fetch_array($query_pembina)) {
    $pembina_list[] = $row;
}

// Cek apakah pembina sudah punya profil
$has_profile = false;
if ($is_pembina) {
    $check_profile = mysqli_query($koneksi, "SELECT * FROM profil_pembina WHERE nama_pembina = '$username_login'");
    $has_profile = mysqli_num_rows($check_profile) > 0;
}
?>

<style>
    .pembina-header {
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.12);
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        position: relative;
        overflow: hidden;
        padding: 2.5rem;
    }
    
    .pembina-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    
    .pembina-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -5%;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.08);
        border-radius: 50%;
    }
    
    .header-content {
        position: relative;
        z-index: 2;
    }
    
    .header-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }
    
    .header-title .icon-wrapper {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        margin-right: 15px;
        backdrop-filter: blur(10px);
    }
    
    .header-subtitle {
        font-size: 1.1rem;
        opacity: 0.95;
        font-weight: 300;
        letter-spacing: 0.3px;
    }
    
    .stats-badge {
        background: rgba(255,255,255,0.25);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 1.5rem;
        text-align: center;
        border: 2px solid rgba(255,255,255,0.3);
    }
    
    .stats-badge h3 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    }
    
    .stats-badge p {
        margin: 0;
        font-size: 0.95rem;
        opacity: 0.9;
    }
    
    .pembina-card {
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: transform 0.3s, box-shadow 0.3s;
        border: none;
        background: white;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    
    .pembina-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }
    
    .pembina-card.my-profile {
        border: 3px solid #667eea;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }
    
    .my-profile-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 8px 18px;
        border-radius: 25px;
        font-size: 0.8rem;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        z-index: 10;
    }
    
    .pembina-photo {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid white;
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        margin: 0 auto;
        display: block;
        transition: transform 0.3s;
    }
    
    .pembina-card:hover .pembina-photo {
        transform: scale(1.05);
    }
    
    .card-actions {
        position: absolute;
        top: 15px;
        left: 15px;
        display: flex;
        gap: 8px;
        z-index: 10;
    }
    
    .btn-action {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        font-size: 0.9rem;
        border: none;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        cursor: pointer;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.25);
    }
    
    .btn-action.btn-edit {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: white;
    }
    
    .btn-action.btn-delete {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }
    
    .btn-create-profile {
        background: white;
        color: #667eea;
        border: 2px solid white;
        padding: 12px 30px;
        font-weight: 600;
        border-radius: 50px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        transition: all 0.3s;
    }
    
    .btn-create-profile:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.25);
        background: #f8f9fa;
        color: #5568d3;
    }
    
    .btn-view-profile {
        background: #667eea;
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-view-profile:hover {
        background: #5568d3;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }
    
    .info-item {
        display: flex;
        align-items: flex-start;
        padding: 10px 0;
        font-size: 0.9rem;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .info-item i {
        width: 30px;
        font-size: 1.1rem;
        margin-top: 3px;
    }

    .info-item small {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-item strong {
        font-size: 0.9rem;
        color: #2d3748;
        word-break: break-word;
    }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="pembina-header">
                <div class="header-content">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="header-title mb-3">
                                <span class="icon-wrapper">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </span>
                                Profil Pembina Ekstrakurikuler
                            </h2>
                            <p class="header-subtitle mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                <?php if ($is_siswa): ?>
                                    Kenali lebih dekat pembina ekstrakurikuler di sekolah
                                <?php elseif ($is_pembina): ?>
                                    Lihat profil rekan pembina dan kelola profil Anda
                                <?php else: ?>
                                    Kelola profil pembina ekstrakurikuler dengan mudah dan efisien
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <?php if ($is_pembina && !$has_profile): ?>
                                <a href="?page=sistemprofil_pembina&edit=<?= urlencode($username_login) ?>" class="btn btn-create-profile">
                                    <i class="fas fa-user-plus me-2"></i> Buat Profil Saya
                                </a>
                            <?php else: ?>
                                <div class="stats-badge">
                                    <h3 class="mb-0"><?= $total_pembina ?></h3>
                                    <p><i class="fas fa-users me-1"></i> Total Pembina</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($total_pembina == 0): ?>
        <!-- Empty State -->
        <div class="row">
            <div class="col-12">
                <div class="card pembina-card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-user-slash fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Belum Ada Data Pembina</h5>
                        <p class="text-muted">
                            <?php if ($is_pembina): ?>
                                Jadilah yang pertama! Klik tombol "Buat Profil Saya" untuk membuat profil Anda.
                            <?php else: ?>
                                Saat ini belum ada pembina yang mendaftarkan profil mereka.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Pembina Cards Grid -->
        <div class="row">
            <?php foreach ($pembina_list as $pembina): ?>
                <?php 
                $is_my_profile = ($is_pembina && $pembina['nama_pembina'] === $username_login);
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card pembina-card <?= $is_my_profile ? 'my-profile' : '' ?>">
                        <!-- Badge "Profil Saya" untuk pembina -->
                        <?php if ($is_my_profile): ?>
                            <span class="my-profile-badge">
                                <i class="fas fa-star me-1"></i> Profil Saya
                            </span>
                        <?php endif; ?>

                        <!-- Tombol Action untuk Admin atau Pembina (profil sendiri) -->
                        <?php if ($is_admin || $is_my_profile): ?>
                        <div class="card-actions">
                            <a href="?page=sistemprofil_pembina&edit=<?= urlencode($pembina['nama_pembina']) ?>" 
                               class="btn-action btn-edit" 
                               title="Edit Profil">
                                <i class="fas fa-pen"></i>
                            </a>
                            <?php if ($is_admin): ?>
                            <button onclick="confirmDelete('<?= htmlspecialchars($pembina['nama_pembina'], ENT_QUOTES) ?>')" 
                                    class="btn-action btn-delete" 
                                    title="Hapus Profil"
                                    type="button">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <div class="card-body text-center p-4">
                            <!-- Foto Pembina -->
                            <div class="mb-3 mt-2">
                                <?php if (!empty($pembina['foto']) && file_exists('uploads/pembina/' . $pembina['foto'])): ?>
                                    <img src="uploads/pembina/<?= htmlspecialchars($pembina['foto']) ?>" 
                                         alt="<?= htmlspecialchars($pembina['nama_pembina']) ?>" 
                                         class="pembina-photo">
                                <?php else: ?>
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($pembina['nama_pembina']) ?>&size=120&background=667eea&color=fff&bold=true" 
                                         alt="<?= htmlspecialchars($pembina['nama_pembina']) ?>" 
                                         class="pembina-photo">
                                <?php endif; ?>
                            </div>

                            <!-- Nama Pembina -->
                            <h5 class="mb-1 fw-bold"><?= htmlspecialchars($pembina['nama_pembina']) ?></h5>
                            
                            <!-- Ekstrakurikuler Diampu (Badge) -->
                            <?php if (!empty($pembina['ekstrakurikuler_diampu'])): ?>
                            <p class="mb-2">
                                <span class="badge bg-primary">
                                    <i class="fas fa-futbol me-1"></i><?= htmlspecialchars($pembina['ekstrakurikuler_diampu']) ?>
                                </span>
                            </p>
                            <?php endif; ?>
                            
                            <!-- Pendidikan -->
                            <p class="text-muted small mb-3">
                                <?php if (!empty($pembina['pendidikan'])): ?>
                                    <i class="fas fa-graduation-cap me-1"></i><?= htmlspecialchars($pembina['pendidikan']) ?>
                                <?php else: ?>
                                    Pembina Ekstrakurikuler
                                <?php endif; ?>
                            </p>

                            <hr class="my-3">

                            <!-- Info Detail -->
                            <div class="text-start">
                                <?php if (!empty($pembina['tempat_lahir']) && !empty($pembina['tanggal_lahir'])): ?>
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    <div style="flex: 1;">
                                        <small class="text-muted d-block">Tempat, Tanggal Lahir</small>
                                        <strong><?= htmlspecialchars($pembina['tempat_lahir']) ?>, <?= date('d-m-Y', strtotime($pembina['tanggal_lahir'])) ?></strong>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($pembina['pendidikan'])): ?>
                                <div class="info-item">
                                    <i class="fas fa-graduation-cap text-info"></i>
                                    <div style="flex: 1;">
                                        <small class="text-muted d-block">Pendidikan</small>
                                        <strong><?= htmlspecialchars($pembina['pendidikan']) ?></strong>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($pembina['bidang_keahlian'])): ?>
                                <div class="info-item">
                                    <i class="fas fa-star text-warning"></i>
                                    <div style="flex: 1;">
                                        <small class="text-muted d-block">Bidang Keahlian</small>
                                        <strong><?= htmlspecialchars($pembina['bidang_keahlian']) ?></strong>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($pembina['pengalaman_mengajar'])): ?>
                                <div class="info-item">
                                    <i class="fas fa-briefcase text-secondary"></i>
                                    <div style="flex: 1;">
                                        <small class="text-muted d-block">Pengalaman Mengajar</small>
                                        <strong><?= htmlspecialchars($pembina['pengalaman_mengajar']) ?></strong>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($pembina['no_telp'])): ?>
                                <div class="info-item">
                                    <i class="fas fa-phone text-success"></i>
                                    <div style="flex: 1;">
                                        <small class="text-muted d-block">No. Telepon</small>
                                        <strong><?= htmlspecialchars($pembina['no_telp']) ?></strong>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($pembina['email'])): ?>
                                <div class="info-item">
                                    <i class="fas fa-envelope text-primary"></i>
                                    <div style="flex: 1;">
                                        <small class="text-muted d-block">Email</small>
                                        <strong><?= htmlspecialchars($pembina['email']) ?></strong>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($pembina['ekstrakurikuler_diampu'])): ?>
                                <div class="info-item">
                                    <i class="fas fa-futbol text-danger"></i>
                                    <div style="flex: 1;">
                                        <small class="text-muted d-block">Ekstrakurikuler Diampu</small>
                                        <strong><?= htmlspecialchars($pembina['ekstrakurikuler_diampu']) ?></strong>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="mt-4">
                                <a href="?page=sistemprofil_pembina&view=<?= urlencode($pembina['nama_pembina']) ?>" 
                                   class="btn-view-profile w-100">
                                    <i class="fas fa-eye me-1"></i> Lihat Profil Lengkap
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Info Box -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info mb-0">
                <div class="d-flex align-items-start">
                    <i class="fas fa-lightbulb fa-2x me-3 mt-1"></i>
                    <div>
                        <strong><i class="fas fa-info-circle"></i> Informasi</strong>
                        <p class="mb-0 mt-2">
                            <?php if ($is_siswa): ?>
                                Klik tombol "Lihat Profil Lengkap" untuk melihat informasi detail tentang pembina, termasuk latar belakang pendidikan, pengalaman, prestasi, dan kontak lengkap.
                            <?php elseif ($is_pembina): ?>
                                Anda dapat melihat profil rekan pembina lainnya dan mengelola profil Anda sendiri. Profil Anda ditandai dengan badge "Profil Saya" dan dapat diedit dengan mengklik ikon <i class="fas fa-pen"></i> (Edit).
                            <?php else: ?>
                                Sebagai admin, Anda dapat mengelola semua profil pembina. Gunakan ikon <i class="fas fa-pen"></i> (Edit) untuk mengubah data atau ikon <i class="fas fa-trash-alt"></i> (Hapus) untuk menghapus profil.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(namaPembina) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        html: '<i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i><br>Yakin ingin menghapus profil pembina <strong>"' + namaPembina + '"</strong>?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="fas fa-trash-alt"></i> Ya, Hapus!',
        cancelButtonText: '<i class="fas fa-times"></i> Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '?page=sistemprofil_pembina&hapus=' + encodeURIComponent(namaPembina);
        }
    });
}
</script>