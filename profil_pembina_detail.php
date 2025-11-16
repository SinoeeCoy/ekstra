<?php
// File: profil_pembina_detail.php
// Variabel $pembina_detail sudah diset dari file sebelumnya

$nama_pembina = htmlspecialchars($pembina_detail['nama_pembina']);
$foto = $pembina_detail['foto'];
$pendidikan = htmlspecialchars($pembina_detail['pendidikan'] ?? 'Pembina Ekstrakurikuler');
$tempat_lahir = htmlspecialchars($pembina_detail['tempat_lahir'] ?? '-');
$tanggal_lahir = $pembina_detail['tanggal_lahir'] ?? '';
$alamat = htmlspecialchars($pembina_detail['alamat'] ?? '-');
$no_telp = htmlspecialchars($pembina_detail['no_telp'] ?? '-');
$pengalaman = htmlspecialchars($pembina_detail['pengalaman'] ?? '');
$prestasi = htmlspecialchars($pembina_detail['prestasi'] ?? '');
$motto = htmlspecialchars($pembina_detail['motto'] ?? '');

?>

<style>
    .profile-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 0 0 30px 30px;
        padding: 2rem 0 8rem 0;
        position: relative;
        overflow: hidden;
    }
    
    .profile-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    
    .profile-hero::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -5%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.08);
        border-radius: 50%;
    }
    
    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
        margin-top: -6rem;
        position: relative;
        z-index: 10;
    }
    
    .profile-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        padding: 2rem;
    }
    
    .profile-photo-large {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        border: 6px solid white;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        object-fit: cover;
        margin-top: -5rem;
    }
    
    .profile-name {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }
    
    .profile-title {
        font-size: 1.2rem;
        color: #718096;
        margin-bottom: 1rem;
    }
    
    .contact-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0;
        color: #4a5568;
    }
    
    .contact-info i {
        width: 24px;
        text-align: center;
    }
    
    .rating-section {
        background: #f7fafc;
        border-radius: 15px;
        padding: 1.5rem;
        text-align: center;
        margin-top: 1.5rem;
    }
    
    .rating-number {
        font-size: 3rem;
        font-weight: 700;
        color: #2d3748;
        line-height: 1;
    }
    
    .stars-large {
        color: #ffc107;
        font-size: 1.5rem;
        margin: 0.5rem 0;
    }
    
    .intro-video {
        background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 100%);
        border-radius: 20px;
        padding: 3rem;
        text-align: center;
        position: relative;
        overflow: hidden;
        min-height: 350px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    .intro-text {
        font-size: 3rem;
        font-weight: 700;
        color: rgba(102, 126, 234, 0.3);
        margin-bottom: 2rem;
        letter-spacing: 2px;
    }
    
    .play-button {
        width: 80px;
        height: 80px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        transition: all 0.3s;
        border: none;
    }
    
    .play-button:hover {
        transform: scale(1.1);
        box-shadow: 0 15px 40px rgba(0,0,0,0.3);
    }
    
    .play-button i {
        color: #667eea;
        font-size: 2rem;
        margin-left: 5px;
    }
    
    .section-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
    }
    
    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .section-title i {
        color: #667eea;
        font-size: 1.75rem;
    }
    
    .section-content {
        color: #4a5568;
        line-height: 1.8;
        font-size: 1rem;
    }
    
    .chat-button {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 1rem 2.5rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.1rem;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        transition: all 0.3s;
        cursor: pointer;
    }
    
    .chat-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(102, 126, 234, 0.5);
        background: linear-gradient(135deg, #5568d3 0%, #6b3fa0 100%);
    }
    
    .chat-button i {
        margin-right: 0.5rem;
    }
    
    .back-button {
        background: white;
        color: #667eea;
        border: 2px solid #667eea;
        padding: 0.75rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }
    
    .back-button:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }
    
    .info-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #f7fafc;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.9rem;
        color: #4a5568;
        margin: 0.25rem;
    }
    
    .info-badge i {
        color: #667eea;
    }
    
    .action-buttons {
        position: absolute;
        top: 2rem;
        right: 2rem;
        display: flex;
        gap: 0.75rem;
        z-index: 20;
    }
    
    .btn-action-top {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        transition: all 0.3s;
        cursor: pointer;
        background: white;
    }
    
    .btn-action-top:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.25);
    }
    
    .btn-action-top.btn-edit {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: white;
    }
    
    .btn-action-top.btn-edit:hover {
        background: linear-gradient(135deg, #ffb300 0%, #ff8f00 100%);
    }
    
    .btn-action-top.btn-delete {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }
    
    .btn-action-top.btn-delete:hover {
        background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
    }
    
    .btn-action-top i {
        font-size: 1.1rem;
    }
</style>

<div class="profile-detail-page">
    <!-- Hero Section -->
    <div class="profile-hero">
        <!-- Tombol Action (Edit & Hapus) untuk Admin atau Pembina sendiri -->
        <?php if ($is_admin || ($is_pembina && $pembina_detail['nama_pembina'] === $username_login)): ?>
        <div class="action-buttons">
            <a href="home.php?page=sistemprofil_pembina&edit=<?= urlencode($pembina_detail['nama_pembina']) ?>" 
               class="btn-action-top btn-edit" 
               title="Edit Profil">
                <i class="fas fa-edit"></i>
            </a>
            <?php if ($is_admin): ?>
            <button onclick="confirmDelete('<?= htmlspecialchars($pembina_detail['nama_pembina'], ENT_QUOTES) ?>')" 
                    class="btn-action-top btn-delete" 
                    title="Hapus Profil"
                    type="button">
                <i class="fas fa-trash-alt"></i>
            </button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="container">
            <a href="home.php?page=sistemprofil_pembina" class="back-button">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container profile-container">
        <div class="row">
            <!-- Left Column - Profile Info -->
            <div class="col-lg-4">
                <div class="profile-card text-center">
                    <!-- Photo -->
                    <?php if (!empty($foto) && file_exists('uploads/pembina/' . $foto)): ?>
                        <img src="uploads/pembina/<?= $foto ?>" 
                             alt="<?= $nama_pembina ?>" 
                             class="profile-photo-large">
                    <?php else: ?>
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($nama_pembina) ?>&size=150&background=667eea&color=fff&bold=true" 
                             alt="<?= $nama_pembina ?>" 
                             class="profile-photo-large">
                    <?php endif; ?>
                    
                    <h1 class="profile-name mt-3"><?= $nama_pembina ?></h1>
                    <p class="profile-title"><?= $pendidikan ?></p>
                    
                    <!-- Contact Info -->
                    <div class="mt-4 text-start">
                        <?php if (!empty($no_telp)): ?>
                        <div class="contact-info">
                            <i class="fas fa-phone-alt text-success"></i>
                            <span><?= $no_telp ?></span>
                            <small class="ms-auto text-muted">(Office)</small>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($no_telp)): ?>
                        <div class="contact-info">
                            <i class="fas fa-mobile-alt text-success"></i>
                            <span><?= $no_telp ?></span>
                            <small class="ms-auto text-muted">(Mobile)</small>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($tempat_lahir)): ?>
                        <div class="contact-info">
                            <i class="fas fa-map-marker-alt text-danger"></i>
                            <span><?= $tempat_lahir ?>, Indonesia</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                </div>
            </div>

            <!-- Right Column - Content -->
            <div class="col-lg-8">
                <!-- Informasi Pribadi -->
                <div class="section-card">
                    <h3 class="section-title">
                        <i class="fas fa-user-circle"></i>
                        Informasi Pribadi
                    </h3>
                    <div class="section-content">
                        <div class="row g-4">
                            <?php if (!empty($tempat_lahir)): ?>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="text-primary">
                                        <i class="fas fa-map-marker-alt fa-lg"></i>
                                    </div>
                                    <div>
                                        <strong class="d-block mb-1">Tempat Lahir</strong>
                                        <span class="text-muted"><?= $tempat_lahir ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($tanggal_lahir)): ?>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="text-success">
                                        <i class="fas fa-calendar-alt fa-lg"></i>
                                    </div>
                                    <div>
                                        <strong class="d-block mb-1">Tanggal Lahir</strong>
                                        <span class="text-muted"><?= date('d F Y', strtotime($tanggal_lahir)) ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($alamat)): ?>
                            <div class="col-12">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="text-danger">
                                        <i class="fas fa-home fa-lg"></i>
                                    </div>
                                    <div>
                                        <strong class="d-block mb-1">Alamat Rumah</strong>
                                        <span class="text-muted"><?= nl2br($alamat) ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($no_telp)): ?>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="text-info">
                                        <i class="fas fa-phone fa-lg"></i>
                                    </div>
                                    <div>
                                        <strong class="d-block mb-1">No. Telepon</strong>
                                        <span class="text-muted"><?= $no_telp ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($pendidikan)): ?>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="text-warning">
                                        <i class="fas fa-graduation-cap fa-lg"></i>
                                    </div>
                                    <div>
                                        <strong class="d-block mb-1">Pendidikan Terakhir</strong>
                                        <span class="text-muted"><?= $pendidikan ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Pengalaman -->
                <?php if (!empty($pengalaman)): ?>
                <div class="section-card">
                    <h3 class="section-title">
                        <i class="fas fa-briefcase"></i>
                        Pengalaman Mengajar & Membina
                    </h3>
                    <div class="section-content">
                        <?= nl2br($pengalaman) ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Prestasi -->
                <?php if (!empty($prestasi)): ?>
                <div class="section-card">
                    <h3 class="section-title">
                        <i class="fas fa-trophy"></i>
                        Prestasi & Penghargaan
                    </h3>
                    <div class="section-content">
                        <?= nl2br($prestasi) ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Motto -->
                <?php if (!empty($motto)): ?>
                <div class="section-card">
                    <h3 class="section-title">
                        <i class="fas fa-quote-left"></i>
                        Motto & Filosofi Mengajar
                    </h3>
                    <div class="section-content">
                        <div class="alert alert-light border-start border-5 border-primary">
                            <em class="fs-5">"<?= nl2br($motto) ?>"</em>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Info Tambahan -->
                <div class="section-card">
                    <div class="d-flex flex-wrap gap-2">
                        <span class="info-badge">
                            <i class="fas fa-calendar-plus"></i>
                            Terdaftar sejak <?= date('d M Y', strtotime($pembina_detail['created_at'])) ?>
                        </span>
                        <?php if (!empty($pembina_detail['updated_at'])): ?>
                        <span class="info-badge">
                            <i class="fas fa-sync"></i>
                            Update <?= date('d M Y', strtotime($pembina_detail['updated_at'])) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="height: 3rem;"></div>
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
            window.location.href = 'home.php?page=sistemprofil_pembina&hapus=' + encodeURIComponent(namaPembina);
        }
    });
}
</script>