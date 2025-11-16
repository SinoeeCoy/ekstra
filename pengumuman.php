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
$user_role = strtolower($user['role']);
$username = $user['username'];

// Fungsi cek akses
function hasAccess($allowed_roles) {
    global $user_role;
    return in_array($user_role, $allowed_roles);
}

// Fungsi escape output untuk mencegah XSS
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Untuk siswa, ambil ekstra-nya dengan prepared statement
$ekstra_siswa = '';
if ($user_role == 'siswa') {
    // Ambil nama ekstra berdasarkan ekstra_id dengan JOIN
    $stmt = mysqli_prepare($koneksi, "
        SELECT e.nama_ekstra 
        FROM data_siswa ds
        JOIN ekstra e ON ds.ekstra_id = e.id
        WHERE ds.nama_siswa = ? 
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    $ekstra_siswa = $data['nama_ekstra'] ?? '';
    mysqli_stmt_close($stmt);
}

// Proses tambah pengumuman (Admin/Pembina)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_pengumuman'])) {
    if (hasAccess(['admin', 'pembina'])) {
        $judul = $_POST['judul'] ?? '';
        $isi = $_POST['isi'] ?? '';
        $prioritas = $_POST['prioritas'] ?? 'sedang';
        $kategori = $_POST['kategori'] ?? '';
        $target_audience = $_POST['target_audience'] ?? 'semua';
        $status = $_POST['status'] ?? 'aktif';
        $tanggal_mulai = $_POST['tanggal_mulai'] ?? date('Y-m-d');
        $tanggal_berakhir = $_POST['tanggal_berakhir'] ?? date('Y-m-d');
        
        // Validasi tanggal
        if (strtotime($tanggal_mulai) > strtotime($tanggal_berakhir)) {
            echo "<script>alert('Tanggal mulai tidak boleh lebih besar dari tanggal berakhir!'); window.history.back();</script>";
            exit;
        }
        
        $stmt = mysqli_prepare($koneksi, "
            INSERT INTO pengumuman (judul, isi, prioritas, kategori, target_audience, status, tanggal_mulai, tanggal_berakhir, dibuat_oleh)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, "sssssssss", $judul, $isi, $prioritas, $kategori, $target_audience, $status, $tanggal_mulai, $tanggal_berakhir, $username);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Pengumuman berhasil ditambahkan!'); window.location.href='home.php?page=pengumuman';</script>";
        } else {
            echo "<script>alert('Gagal menambahkan pengumuman: " . mysqli_error($koneksi) . "'); window.history.back();</script>";
        }
        mysqli_stmt_close($stmt);
        exit;
    }
}

// Proses edit pengumuman
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_pengumuman'])) {
    if (hasAccess(['admin', 'pembina'])) {
        $id = $_POST['id'] ?? 0;
        $judul = $_POST['judul'] ?? '';
        $isi = $_POST['isi'] ?? '';
        $prioritas = $_POST['prioritas'] ?? 'sedang';
        $kategori = $_POST['kategori'] ?? '';
        $target_audience = $_POST['target_audience'] ?? 'semua';
        $status = $_POST['status'] ?? 'aktif';
        $tanggal_mulai = $_POST['tanggal_mulai'] ?? date('Y-m-d');
        $tanggal_berakhir = $_POST['tanggal_berakhir'] ?? date('Y-m-d');
        
        // Validasi tanggal
        if (strtotime($tanggal_mulai) > strtotime($tanggal_berakhir)) {
            echo "<script>alert('Tanggal mulai tidak boleh lebih besar dari tanggal berakhir!'); window.history.back();</script>";
            exit;
        }
        
        $stmt = mysqli_prepare($koneksi, "
            UPDATE pengumuman SET 
            judul=?, isi=?, prioritas=?, kategori=?, 
            target_audience=?, tanggal_mulai=?, 
            tanggal_berakhir=?, status=?
            WHERE id=?
        ");
        mysqli_stmt_bind_param($stmt, "ssssssssi", $judul, $isi, $prioritas, $kategori, $target_audience, $tanggal_mulai, $tanggal_berakhir, $status, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Pengumuman berhasil diupdate!'); window.location.href='home.php?page=pengumuman';</script>";
        } else {
            echo "<script>alert('Gagal mengupdate pengumuman!'); window.history.back();</script>";
        }
        mysqli_stmt_close($stmt);
        exit;
    }
}

// Proses hapus pengumuman
if (isset($_GET['hapus']) && hasAccess(['admin', 'pembina'])) {
    $id = intval($_GET['hapus']);
    $stmt = mysqli_prepare($koneksi, "DELETE FROM pengumuman WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Pengumuman berhasil dihapus!'); window.location.href='home.php?page=pengumuman';</script>";
    } else {
        echo "<script>alert('Gagal menghapus pengumuman!'); window.location.href='home.php?page=pengumuman';</script>";
    }
    mysqli_stmt_close($stmt);
    exit;
}

// Proses tambah komentar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_komentar'])) {
    $pengumuman_id = intval($_POST['pengumuman_id'] ?? 0);
    $komentar = $_POST['komentar'] ?? '';
    $parent_id = isset($_POST['parent_id']) && $_POST['parent_id'] != '' ? intval($_POST['parent_id']) : null;
    
    if (!empty($komentar) && $pengumuman_id > 0) {
        if ($parent_id === null) {
            $stmt = mysqli_prepare($koneksi, "
                INSERT INTO komentar_pengumuman (pengumuman_id, komentar, nama_pengirim, role_pengirim, parent_id, tanggal_kirim)
                VALUES (?, ?, ?, ?, NULL, NOW())
            ");
            mysqli_stmt_bind_param($stmt, "isss", $pengumuman_id, $komentar, $username, $user_role);
        } else {
            $stmt = mysqli_prepare($koneksi, "
                INSERT INTO komentar_pengumuman (pengumuman_id, komentar, nama_pengirim, role_pengirim, parent_id, tanggal_kirim)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            mysqli_stmt_bind_param($stmt, "isssi", $pengumuman_id, $komentar, $username, $user_role, $parent_id);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Komentar berhasil ditambahkan!'); window.location.href='home.php?page=pengumuman#pengumuman-{$pengumuman_id}';</script>";
        } else {
            echo "<script>alert('Gagal menambahkan komentar!'); window.history.back();</script>";
        }
        mysqli_stmt_close($stmt);
        exit;
    }
}

// Proses hapus komentar (hanya admin atau pemilik komentar)
if (isset($_GET['hapus_komentar'])) {
    $id = intval($_GET['hapus_komentar'] ?? 0);
    $pengumuman_id = intval($_GET['pid'] ?? 0);
    
    // Cek apakah user adalah admin atau pemilik komentar
    if ($user_role == 'admin') {
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM komentar_pengumuman WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
    } else {
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM komentar_pengumuman WHERE id=? AND nama_pengirim=?");
        mysqli_stmt_bind_param($stmt, "is", $id, $username);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        mysqli_stmt_close($stmt);
        
        $stmt_delete = mysqli_prepare($koneksi, "DELETE FROM komentar_pengumuman WHERE id=?");
        mysqli_stmt_bind_param($stmt_delete, "i", $id);
        
        if (mysqli_stmt_execute($stmt_delete)) {
            echo "<script>alert('Komentar berhasil dihapus!'); window.location.href='home.php?page=pengumuman#pengumuman-{$pengumuman_id}';</script>";
        } else {
            echo "<script>alert('Gagal menghapus komentar!'); window.location.href='home.php?page=pengumuman';</script>";
        }
        mysqli_stmt_close($stmt_delete);
    } else {
        mysqli_stmt_close($stmt);
        echo "<script>alert('Anda tidak memiliki izin untuk menghapus komentar ini!'); window.location.href='home.php?page=pengumuman';</script>";
    }
    exit;
}

/// Query pengumuman (filter per role)
if ($user_role == 'siswa') {
    // Query yang lebih fleksibel untuk siswa
    $stmt = mysqli_prepare($koneksi, "
        SELECT * FROM pengumuman
        WHERE (
            target_audience = 'semua' 
            OR (
                target_audience = 'siswa' 
                AND (
                    kategori = '' 
                    OR kategori IS NULL 
                    OR kategori = ?
                )
            )
        )
        AND status = 'aktif'
        AND tanggal_mulai <= CURDATE()
        AND tanggal_berakhir >= CURDATE()
        ORDER BY prioritas DESC, tanggal_dibuat DESC
    ");
    mysqli_stmt_bind_param($stmt, "s", $ekstra_siswa);
    mysqli_stmt_execute($stmt);
    $query = mysqli_stmt_get_result($stmt);
} else {
    // Untuk admin, pembina: tampilkan semua
    $query = mysqli_query($koneksi, "
        SELECT * FROM pengumuman
        ORDER BY tanggal_dibuat DESC
    ");
}

// Fungsi untuk menampilkan komentar bersarang
function tampilkanKomentar($koneksi, $pengumuman_id, $parent_id = null, $level = 0, $username, $user_role) {
    if ($parent_id === null) {
        $stmt = mysqli_prepare($koneksi, "
            SELECT * FROM komentar_pengumuman
            WHERE pengumuman_id = ? AND parent_id IS NULL
            ORDER BY tanggal_kirim ASC
        ");
        mysqli_stmt_bind_param($stmt, "i", $pengumuman_id);
    } else {
        $stmt = mysqli_prepare($koneksi, "
            SELECT * FROM komentar_pengumuman
            WHERE pengumuman_id = ? AND parent_id = ?
            ORDER BY tanggal_kirim ASC
        ");
        mysqli_stmt_bind_param($stmt, "ii", $pengumuman_id, $parent_id);
    }
    
    mysqli_stmt_execute($stmt);
    $query_komentar = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($query_komentar) > 0) {
        while ($kom = mysqli_fetch_assoc($query_komentar)) {
            $margin = $level * 40;
            $bg_class = $kom['role_pengirim'] == 'admin' ? 'bg-info bg-opacity-10' : 'bg-light';
            $can_delete = ($kom['nama_pengirim'] == $username || $user_role == 'admin');
            
            echo "<div class='komentar-item p-3 mb-2 rounded border " . e($bg_class) . "' style='margin-left: {$margin}px;'>
                    <div class='d-flex justify-content-between align-items-start'>
                        <div>
                            <strong>" . e($kom['nama_pengirim']) . "</strong> 
                            <span class='badge bg-secondary'>" . e($kom['role_pengirim']) . "</span>
                            <small class='text-muted d-block'>" . e($kom['tanggal_kirim']) . "</small>
                        </div>";
            
            if ($can_delete) {
                echo "<a href='?hapus_komentar=" . intval($kom['id']) . "&pid=" . intval($pengumuman_id) . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin hapus komentar ini?\")'>
                        <i class='fas fa-trash'></i>
                      </a>";
            }
            
            echo "</div>
                    <p class='mt-2 mb-2'>" . nl2br(e($kom['komentar'])) . "</p>
                    <button class='btn btn-sm btn-outline-primary btn-balas' 
                            data-pengumuman-id='" . intval($pengumuman_id) . "' 
                            data-parent-id='" . intval($kom['id']) . "' 
                            data-nama='" . e($kom['nama_pengirim']) . "'>
                        <i class='fas fa-reply'></i> Balas
                    </button>
                  </div>";
            
            // Rekursif untuk komentar balasan
            tampilkanKomentar($koneksi, $pengumuman_id, $kom['id'], $level + 1, $username, $user_role);
        }
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengumuman Ekstrakurikuler</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/a2e0e6ad02.js" crossorigin="anonymous"></script>
    <style>
        .prioritas-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .komentar-item {
            transition: all 0.3s ease;
        }
        .komentar-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .form-balas {
            display: none;
            margin-top: 10px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .komentar-section {
            display: none;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
        }
        .komentar-section.show {
            display: block;
        }
        .prestasi-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            margin: 0.1rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4">

    <h2 class="mb-4 text-center"><i class="fas fa-bullhorn"></i> Pengumuman Ekstrakurikuler</h2>

    <!-- Statistik Singkat -->
    <div class="row mb-4 text-center">
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php 
                    $result_aktif = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengumuman WHERE status='aktif'");
                    $data_aktif = mysqli_fetch_assoc($result_aktif);
                    $total_pengumuman = $data_aktif['total'];
                    ?>
                    <h5 class="card-title">Pengumuman Aktif</h5>
                    <p class="fs-4 fw-bold text-warning"><?= $total_pengumuman ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php 
                    $prestasi_total = 0;
                    $cek_prestasi = @mysqli_query($koneksi, "SHOW TABLES LIKE 'data_prestasi'");
                    if ($cek_prestasi && mysqli_num_rows($cek_prestasi) > 0) {
                        $result_prestasi = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM data_prestasi");
                        $data_prestasi = mysqli_fetch_assoc($result_prestasi);
                        $prestasi_total = $data_prestasi['total'];
                    }
                    ?>
                    <h5 class="card-title">Total Prestasi</h5>
                    <p class="fs-4 fw-bold text-danger"><?= $prestasi_total ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php 
                    $result_ekstra = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM ekstra");
                    $data_ekstra = mysqli_fetch_assoc($result_ekstra);
                    $total_ekstra = $data_ekstra['total'];
                    ?>
                    <h5 class="card-title">Ekstrakurikuler</h5>
                    <p class="fs-4 fw-bold text-primary"><?= $total_ekstra ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php 
                    $result_siswa = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM data_siswa");
                    $data_siswa = mysqli_fetch_assoc($result_siswa);
                    $total_siswa = $data_siswa['total'];
                    ?>
                    <h5 class="card-title">Jumlah Siswa</h5>
                    <p class="fs-4 fw-bold text-success"><?= $total_siswa ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Pengumuman dengan Komentar -->
    <div class="card shadow-sm mb-5">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <span><i class="fas fa-bell"></i> Daftar Pengumuman</span>
            <?php if (hasAccess(['admin', 'pembina'])): ?>
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fas fa-plus"></i> Tambah Pengumuman
            </button>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php 
            if (mysqli_num_rows($query) > 0) {
                while ($row = mysqli_fetch_assoc($query)) {
                    // Badge prioritas
                    $prioritas_class = '';
                    $prioritas_text = '';
                    switch($row['prioritas']) {
                        case 'sangat penting':
                            $prioritas_class = 'bg-danger';
                            $prioritas_text = 'Prioritas Sangat Penting';
                            break;
                        case 'penting':
                            $prioritas_class = 'bg-warning';
                            $prioritas_text = 'Prioritas Penting';
                            break;
                        case 'biasa':
                            $prioritas_class = 'bg-info';
                            $prioritas_text = 'Prioritas Biasa';
                            break;
                    }
                    
                    // Badge status
                    $status_badge = $row['status'] == 'aktif' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>';
                    
                    // Hitung jumlah komentar
                    $stmt_count = mysqli_prepare($koneksi, "SELECT COUNT(*) as total FROM komentar_pengumuman WHERE pengumuman_id = ?");
                    mysqli_stmt_bind_param($stmt_count, "i", $row['id']);
                    mysqli_stmt_execute($stmt_count);
                    $result_count = mysqli_stmt_get_result($stmt_count);
                    $count_data = mysqli_fetch_assoc($result_count);
                    $count_komentar = $count_data['total'];
                    mysqli_stmt_close($stmt_count);
                    
                    echo "
                    <div class='card mb-4 position-relative' id='pengumuman-" . intval($row['id']) . "'>
                        <span class='badge {$prioritas_class} prioritas-badge'>{$prioritas_text}</span>
                        <div class='card-body'>
                            <h5 class='mb-2'>
                                <i class='fas fa-bullhorn text-warning'></i> " . e($row['judul']) . " 
                                {$status_badge}
                            </h5>
                            <p class='mb-2'>" . nl2br(e($row['isi'])) . "</p>
                            <div class='d-flex justify-content-between align-items-center mb-3'>
                                <small class='text-muted'>
                                    <i class='fas fa-user'></i> " . e($row['dibuat_oleh']) . " • 
                                    <i class='fas fa-calendar'></i> " . e($row['tanggal_dibuat']) . " • 
                                    <i class='fas fa-tag'></i> " . e($row['kategori'] ?: 'Semua') . " • 
                                    <i class='fas fa-users'></i> " . e($row['target_audience']) . "
                                    <br>
                                    <i class='fas fa-calendar-check'></i> Berlaku: " . e($row['tanggal_mulai']) . " s/d " . e($row['tanggal_berakhir']) . "
                                </small>
                            </div>
                            
                            <div class='d-flex gap-2 mb-3'>
                                <button class='btn btn-sm btn-primary toggle-komentar' data-id='" . intval($row['id']) . "' data-count='{$count_komentar}'>
                                    <i class='fas fa-comment'></i> Lihat Komentar ({$count_komentar})
                                </button>";
                    
                    if (hasAccess(['admin', 'pembina'])) {
                        echo "
                                <button class='btn btn-sm btn-warning' data-bs-toggle='modal' data-bs-target='#modalEdit" . intval($row['id']) . "'>
                                    <i class='fas fa-edit'></i> Edit
                                </button>
                                <a href='?hapus=" . intval($row['id']) . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin hapus pengumuman ini?\")'>
                                    <i class='fas fa-trash'></i> Hapus
                                </a>";
                    }
                    
                    echo "
                            </div>
                            
                            <!-- Bagian Komentar (Hidden by default) -->
                            <div class='komentar-section' id='komentar-" . intval($row['id']) . "'>
                                <h6 class='mb-3'><i class='fas fa-comments'></i> Komentar</h6>
                                
                                <!-- Form Komentar Utama -->
                                <form method='POST' class='mb-4'>
                                    <input type='hidden' name='pengumuman_id' value='" . intval($row['id']) . "'>
                                    <div class='mb-2'>
                                        <textarea name='komentar' class='form-control' rows='3' placeholder='Tulis komentar Anda...' required></textarea>
                                    </div>
                                    <button type='submit' name='tambah_komentar' class='btn btn-primary btn-sm'>
                                        <i class='fas fa-paper-plane'></i> Kirim Komentar
                                    </button>
                                </form>
                                
                                <!-- Daftar Komentar -->
                                <div class='komentar-list'>";
                    
                    // Tampilkan komentar
                    tampilkanKomentar($koneksi, $row['id'], null, 0, $username, $user_role);
                    
                    echo "
                                </div>
                            </div>
                        </div>
                    </div>";
                    
                    // Modal Edit (untuk admin/pembina)
                    if (hasAccess(['admin', 'pembina'])) {
                        echo "
                        <div class='modal fade' id='modalEdit" . intval($row['id']) . "' tabindex='-1'>
                            <div class='modal-dialog modal-lg'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title'>Edit Pengumuman</h5>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                    </div>
                                    <form method='POST'>
                                        <div class='modal-body'>
                                            <input type='hidden' name='id' value='" . intval($row['id']) . "'>
                                            <div class='mb-3'>
                                                <label class='form-label'>Judul</label>
                                                <input type='text' name='judul' class='form-control' value='" . e($row['judul']) . "' required>
                                            </div>
                                            <div class='mb-3'>
                                                <label class='form-label'>Isi</label>
                                                <textarea name='isi' class='form-control' rows='4' required>" . e($row['isi']) . "</textarea>
                                            </div>
                                            <div class='row'>
                                                <div class='col-md-6 mb-3'>
                                                    <label class='form-label'>Prioritas</label>
                                                    <select name='prioritas' class='form-select' required>
                                                        <option value='sangat penting' " . ($row['prioritas']=='sangat penting'?'selected':'') . ">Sangat Penting</option>
                                                        <option value='penting' " . ($row['prioritas']=='penting'?'selected':'') . ">Penting</option>
                                                        <option value='biasa' " . ($row['prioritas']=='biasa'?'selected':'') . ">Biasa</option>
                                                    </select>
                                                </div>
                                                <div class='col-md-6 mb-3'>
                                                    <label class='form-label'>Kategori (Ekstrakurikuler)</label>
                                                    <input type='text' name='kategori' class='form-control' value='" . e($row['kategori']) . "' placeholder='Kosongkan untuk semua'>
                                                </div>
                                            </div>
                                            <div class='row'>
                                                <div class='col-md-6 mb-3'>
                                                    <label class='form-label'>Target Audience</label>
                                                    <select name='target_audience' class='form-select' required>
                                                        <option value='semua' " . ($row['target_audience']=='semua'?'selected':'') . ">Semua</option>
                                                        <option value='siswa' " . ($row['target_audience']=='siswa'?'selected':'') . ">Siswa</option>
                                                        <option value='pembina' " . ($row['target_audience']=='pembina'?'selected':'') . ">Pembina</option>
                                                        <option value='admin' " . ($row['target_audience']=='admin'?'selected':'') . ">Admin</option>
                                                    </select>
                                                </div>
                                                <div class='col-md-6 mb-3'>
                                                    <label class='form-label'>Status</label>
                                                    <select name='status' class='form-select' required>
                                                        <option value='aktif' " . ($row['status']=='aktif'?'selected':'') . ">Aktif</option>
                                                        <option value='nonaktif' " . ($row['status']=='nonaktif'?'selected':'') . ">Nonaktif</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class='row'>
                                                <div class='col-md-6 mb-3'>
                                                    <label class='form-label'>Tanggal Mulai</label>
                                                    <input type='date' name='tanggal_mulai' class='form-control' value='" . e($row['tanggal_mulai']) . "' required>
                                                </div>
                                                <div class='col-md-6 mb-3'>
                                                    <label class='form-label'>Tanggal Berakhir</label>
                                                    <input type='date' name='tanggal_berakhir' class='form-control' value='" . e($row['tanggal_berakhir']) . "' required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='modal-footer'>
                                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Batal</button>
                                            <button type='submit' name='edit_pengumuman' class='btn btn-primary'>Update</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>";
                    }
                }
            } else {
                echo "<p class='text-muted'>Tidak ada pengumuman saat ini.</p>";
            }
            ?>
        </div>
    </div>

    <!-- Prestasi 5 Tahun Terakhir -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-trophy"></i> Prestasi 5 Tahun Terakhir
        </div>
        <div class="card-body">
            <?php
            $tahun_batas = date('Y') - 5;
            $cek_prestasi = @mysqli_query($koneksi, "SHOW TABLES LIKE 'data_prestasi'");
            if ($cek_prestasi && mysqli_num_rows($cek_prestasi) > 0) {
                $prestasi_query = mysqli_query($koneksi, "
                    SELECT id_prestasi, nama_ekstra, nama_siswa, prestasi, tingkat, tahun, keterangan, tanggal_input
                    FROM data_prestasi 
                    WHERE tahun >= '$tahun_batas'
                    ORDER BY tahun DESC, tanggal_input DESC
                ");
                
                if (mysqli_num_rows($prestasi_query) > 0) {
                    echo '<div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Ekstrakurikuler</th>
                                    <th>Nama Siswa</th>
                                    <th>Prestasi</th>
                                    <th>Tingkat</th>
                                    <th width="80">Tahun</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead><tbody>';
                    $no = 1;
                    while ($p = mysqli_fetch_assoc($prestasi_query)) {
                        // Badge tingkat dengan warna berbeda
                        $tingkat_badge = '';
                        switch(strtolower($p['tingkat'])) {
                            case 'internasional':
                                $tingkat_badge = '<span class="prestasi-badge bg-danger text-white">Internasional</span>';
                                break;
                            case 'nasional':
                                $tingkat_badge = '<span class="prestasi-badge bg-warning text-dark">Nasional</span>';
                                break;
                            case 'provinsi':
                                $tingkat_badge = '<span class="prestasi-badge bg-info text-white">Provinsi</span>';
                                break;
                            case 'kabupaten':
                                $tingkat_badge = '<span class="prestasi-badge bg-success text-white">Kabupaten</span>';
                                break;
                            default:
                                $tingkat_badge = '<span class="prestasi-badge bg-secondary text-white">' . e($p['tingkat']) . '</span>';
                        }
                        
                        echo "<tr>
                                <td class='text-center'>{$no}</td>
                                <td><strong>" . e($p['nama_ekstra']) . "</strong></td>
                                <td>" . e($p['nama_siswa']) . "</td>
                                <td>" . e($p['prestasi']) . "</td>
                                <td class='text-center'>{$tingkat_badge}</td>
                                <td class='text-center'><strong>" . e($p['tahun']) . "</strong></td>
                                <td><small>" . e($p['keterangan'] ?: '-') . "</small></td>
                            </tr>";
                        $no++;
                    }
                    echo '</tbody></table></div>';
                } else {
                    echo '<p class="text-muted mb-0">Belum ada data prestasi dalam 5 tahun terakhir.</p>';
                }
            } else {
                echo '<p class="text-muted mb-0">Tabel data_prestasi belum tersedia.</p>';
            }
            ?>
        </div>
    </div>

    <!-- Statistik Prestasi Per Ekstrakurikuler -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-info text-white">
            <i class="fas fa-chart-bar"></i> Statistik Prestasi Per Ekstrakurikuler
        </div>
        <div class="card-body">
            <?php
            $cek_prestasi = @mysqli_query($koneksi, "SHOW TABLES LIKE 'data_prestasi'");
            if ($cek_prestasi && mysqli_num_rows($cek_prestasi) > 0) {
                $stat_query = mysqli_query($koneksi, "
                    SELECT 
                        nama_ekstra,
                        COUNT(*) as total_prestasi,
                        SUM(CASE WHEN LOWER(tingkat) = 'internasional' THEN 1 ELSE 0 END) as internasional,
                        SUM(CASE WHEN LOWER(tingkat) = 'nasional' THEN 1 ELSE 0 END) as nasional,
                        SUM(CASE WHEN LOWER(tingkat) = 'provinsi' THEN 1 ELSE 0 END) as provinsi,
                        SUM(CASE WHEN LOWER(tingkat) IN ('kabupaten', 'kota') THEN 1 ELSE 0 END) as kab_kota
                    FROM data_prestasi
                    GROUP BY nama_ekstra
                    ORDER BY total_prestasi DESC
                ");
                
                if (mysqli_num_rows($stat_query) > 0) {
                    echo '<div class="table-responsive">
                            <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Ekstrakurikuler</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Internasional</th>
                                    <th class="text-center">Nasional</th>
                                    <th class="text-center">Provinsi</th>
                                    <th class="text-center">Kab/Kota</th>
                                </tr>
                            </thead><tbody>';
                    while ($stat = mysqli_fetch_assoc($stat_query)) {
                        echo "<tr>
                                <td><strong>" . e($stat['nama_ekstra']) . "</strong></td>
                                <td class='text-center'><span class='badge bg-primary'>" . e($stat['total_prestasi']) . "</span></td>
                                <td class='text-center'>" . ($stat['internasional'] > 0 ? "<span class='badge bg-danger'>" . e($stat['internasional']) . "</span>" : "-") . "</td>
                                <td class='text-center'>" . ($stat['nasional'] > 0 ? "<span class='badge bg-warning text-dark'>" . e($stat['nasional']) . "</span>" : "-") . "</td>
                                <td class='text-center'>" . ($stat['provinsi'] > 0 ? "<span class='badge bg-info'>" . e($stat['provinsi']) . "</span>" : "-") . "</td>
                                <td class='text-center'>" . ($stat['kab_kota'] > 0 ? "<span class='badge bg-success'>" . e($stat['kab_kota']) . "</span>" : "-") . "</td>
                            </tr>";
                    }
                    echo '</tbody></table></div>';
                } else {
                    echo '<p class="text-muted mb-0">Belum ada data prestasi.</p>';
                }
            } else {
                echo '<p class="text-muted mb-0">Tabel data_prestasi belum tersedia.</p>';
            }
            ?>
        </div>
    </div>

    <!-- Daftar Siswa Per Ekstrakurikuler - COMPLETELY FIXED -->
    <div class="card mb-5 shadow-sm">
        <div class="card-header bg-success text-white">
            <i class="fas fa-users"></i> Daftar Siswa Per Ekstrakurikuler
        </div>
        <div class="card-body">
            <?php
            // Get ALL ekstrakurikuler dari tabel ekstra
            $ekskul_query = mysqli_query($koneksi, "SELECT id, nama_ekstra FROM ekstra ORDER BY nama_ekstra");
            
            if (mysqli_num_rows($ekskul_query) > 0) {
                while ($ekskul = mysqli_fetch_assoc($ekskul_query)) {
                    $nama_ekskul = $ekskul['nama_ekstra'];
                    $ekstra_id = $ekskul['id'];
                    
                    // Hitung jumlah siswa per ekskul
                    $stmt_count_siswa = mysqli_prepare($koneksi, "
                        SELECT COUNT(*) as total 
                        FROM data_siswa 
                        WHERE ekstra_id = ?
                    ");
                    mysqli_stmt_bind_param($stmt_count_siswa, "i", $ekstra_id);
                    mysqli_stmt_execute($stmt_count_siswa);
                    $result_count = mysqli_stmt_get_result($stmt_count_siswa);
                    $count_data = mysqli_fetch_assoc($result_count);
                    $total_siswa_ekskul = $count_data['total'];
                    mysqli_stmt_close($stmt_count_siswa);
                    
                    // Hitung prestasi ekskul ini
                    $total_prestasi_ekskul = 0;
                    $cek_prestasi = @mysqli_query($koneksi, "SHOW TABLES LIKE 'data_prestasi'");
                    if ($cek_prestasi && mysqli_num_rows($cek_prestasi) > 0) {
                        $stmt_prestasi = mysqli_prepare($koneksi, "SELECT COUNT(*) as total FROM data_prestasi WHERE nama_ekstra = ?");
                        mysqli_stmt_bind_param($stmt_prestasi, "s", $nama_ekskul);
                        mysqli_stmt_execute($stmt_prestasi);
                        $result_prestasi = mysqli_stmt_get_result($stmt_prestasi);
                        $prestasi_data = mysqli_fetch_assoc($result_prestasi);
                        $total_prestasi_ekskul = $prestasi_data['total'];
                        mysqli_stmt_close($stmt_prestasi);
                    }
                    
                    echo "<h6 class='mt-3 mb-2'>
                            <i class='fas fa-tag'></i> " . e($nama_ekskul) . " 
                            <span class='badge bg-primary'>{$total_siswa_ekskul} Siswa</span> 
                            <span class='badge bg-warning text-dark'>{$total_prestasi_ekskul} Prestasi</span>
                          </h6>";
                    
                    // Get students for this ekskul
                    $stmt_siswa = mysqli_prepare($koneksi, "
                        SELECT nama_siswa, kelas, jenis_kelamin 
                        FROM data_siswa 
                        WHERE ekstra_id = ?
                        ORDER BY nama_siswa
                    ");
                    mysqli_stmt_bind_param($stmt_siswa, "i", $ekstra_id);
                    mysqli_stmt_execute($stmt_siswa);
                    $siswa_query = mysqli_stmt_get_result($stmt_siswa);
                    
                    if (mysqli_num_rows($siswa_query) > 0) {
                        echo '<table class="table table-bordered table-sm table-hover mb-4">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">No</th>
                                        <th>Nama Siswa</th>
                                        <th width="100">Kelas</th>
                                        <th width="150">Jenis Kelamin</th>
                                    </tr>
                                </thead><tbody>';
                        $no = 1;
                        while ($siswa = mysqli_fetch_assoc($siswa_query)) {
                            echo "<tr>
                                    <td class='text-center'>{$no}</td>
                                    <td>" . e($siswa['nama_siswa']) . "</td>
                                    <td class='text-center'>" . e($siswa['kelas']) . "</td>
                                    <td>" . e($siswa['jenis_kelamin']) . "</td>
                                </tr>";
                            $no++;
                        }
                        echo '</tbody></table>';
                    } else {
                        echo '<p class="text-muted mb-3"><small>Belum ada siswa yang terdaftar.</small></p>';
                    }
                    mysqli_stmt_close($stmt_siswa);
                }
            } else {
                echo '<p class="text-muted mb-0">Belum ada data ekstrakurikuler.</p>';
            }
            ?>
        </div>
    </div>

</div>

<!-- Modal Tambah Pengumuman -->
<?php if (hasAccess(['admin', 'pembina'])): ?>
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Pengumuman Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul</label>
                        <input type="text" name="judul" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Isi</label>
                        <textarea name="isi" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prioritas</label>
                            <select name="prioritas" class="form-select" required>
                                <option value="sangat penting">Sangat Penting</option>
                                <option value="penting" selected>Penting</option>
                                <option value="biasa">Biasa</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori (Ekstrakurikuler)</label>
                            <input type="text" name="kategori" class="form-control" placeholder="Kosongkan untuk semua">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Target Audience</label>
                            <select name="target_audience" class="form-select" required>
                                <option value="semua" selected>Semua</option>
                                <option value="siswa">Siswa</option>
                                <option value="pembina">Pembina</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="aktif" selected>Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Berakhir</label>
                            <input type="date" name="tanggal_berakhir" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_pengumuman" class="btn btn-success">Tambah Pengumuman</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle komentar section
    document.querySelectorAll('.toggle-komentar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const count = this.getAttribute('data-count');
            const komentarSection = document.getElementById('komentar-' + id);
            
            if (komentarSection.classList.contains('show')) {
                komentarSection.classList.remove('show');
                this.innerHTML = '<i class="fas fa-comment"></i> Lihat Komentar (' + count + ')';
            } else {
                komentarSection.classList.add('show');
                this.innerHTML = '<i class="fas fa-comment-slash"></i> Sembunyikan Komentar';
            }
        });
    });
    
    // Handle tombol balas
    document.querySelectorAll('.btn-balas').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const pengumumanId = this.getAttribute('data-pengumuman-id');
            const parentId = this.getAttribute('data-parent-id');
            const namaBalasan = this.getAttribute('data-nama');
            
            // Cek apakah form balas sudah ada
            let formBalas = this.parentElement.querySelector('.form-balas');
            
            if (formBalas) {
                // Toggle show/hide
                if (formBalas.style.display === 'none' || formBalas.style.display === '') {
                    formBalas.style.display = 'block';
                } else {
                    formBalas.style.display = 'none';
                }
            } else {
                // Buat form balas baru
                formBalas = document.createElement('div');
                formBalas.className = 'form-balas';
                formBalas.style.display = 'block';
                
                // Escape HTML untuk mencegah XSS
                const escapeHtml = (text) => {
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                };
                
                formBalas.innerHTML = `
                    <form method="POST" action="home.php?page=pengumuman#pengumuman-${escapeHtml(pengumumanId)}">
                        <input type="hidden" name="pengumuman_id" value="${escapeHtml(pengumumanId)}">
                        <input type="hidden" name="parent_id" value="${escapeHtml(parentId)}">
                        <label class="form-label"><small>Membalas: <strong>${escapeHtml(namaBalasan)}</strong></small></label>
                        <textarea name="komentar" class="form-control mb-2" rows="2" placeholder="Tulis balasan Anda..." required></textarea>
                        <button type="submit" name="tambah_komentar" class="btn btn-sm btn-success me-2">
                            <i class="fas fa-paper-plane"></i> Kirim
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary btn-batal">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </form>
                `;
                this.parentElement.appendChild(formBalas);
                
                // Handle tombol batal
                formBalas.querySelector('.btn-batal').addEventListener('click', function() {
                    formBalas.style.display = 'none';
                });
            }
        });
    });
    
    // Auto-scroll ke pengumuman jika ada hash
    if (window.location.hash) {
        const element = document.querySelector(window.location.hash);
        if (element) {
            setTimeout(function() {
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Buka section komentar jika ada
                const id = window.location.hash.replace('#pengumuman-', '');
                const komentarSection = document.getElementById('komentar-' + id);
                if (komentarSection) {
                    komentarSection.classList.add('show');
                    const btn = document.querySelector(`.toggle-komentar[data-id="${id}"]`);
                    if (btn) {
                        btn.innerHTML = '<i class="fas fa-comment-slash"></i> Sembunyikan Komentar';
                    }
                }
            }, 100);
        }
    }
});
</script>

</body>
</html>