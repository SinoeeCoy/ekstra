<?php
// File: absensi.php
// Versi dengan input tanggal, materi, foto di atas tabel (layout vertikal)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

include "koneksi.php";

// Filter ekstrakurikuler
$filter_ekstra = isset($_GET['filter_ekstra']) ? $_GET['filter_ekstra'] : '';
$where_clause = $filter_ekstra ?
    "WHERE e.nama_ekstra = '" . mysqli_real_escape_string($koneksi, $filter_ekstra) . "'"
    : "";

// Query untuk form absensi (dengan JOIN ke tabel ekstra)
$query_siswa = mysqli_query($koneksi, "
    SELECT ds.id, ds.nama_siswa, ds.kelas, ds.jurusan, e.nama_ekstra
    FROM data_siswa ds
    LEFT JOIN ekstra e ON ds.ekstra_id = e.id
    $where_clause
    ORDER BY e.nama_ekstra ASC, ds.nama_siswa ASC 
"); 

// Query untuk history absensi
if ($filter_ekstra != '') {
    $query_history = mysqli_query($koneksi, "SELECT * FROM absen WHERE nama_ekstra = '" . mysqli_real_escape_string($koneksi, $filter_ekstra) . "' ORDER BY tanggal DESC, id DESC");
} else {
    $query_history = mysqli_query($koneksi, "SELECT * FROM absen ORDER BY tanggal DESC, id DESC");
}
?>

<style>
    .section-title {
        background: #f9fafb;
        padding: 15px 20px;
        border-radius: 10px 10px 0 0;
        border: 1px solid #e5e7eb;
        border-bottom: 3px solid #1e40af;
        margin-bottom: 0;
    }
    .section-title h5 {
        margin: 0;
        color: #1e40af;
        font-weight: 600;
    }
    .filter-section {
        background: #f9fafb;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 25px;
        border: 1px solid #e5e7eb;
    }
    .input-section {
        background: #fff;
        padding: 25px;
        border: 1px solid #e5e7eb;
        border-top: none;
        margin-bottom: 0;
    }
    .input-group-custom {
        margin-bottom: 20px;
    }
    .input-group-custom:last-child {
        margin-bottom: 0;
    }
    .table-container {
        background: #fff;
        border-radius: 0 0 10px 10px;
        border: 1px solid #e5e7eb;
        border-top: none;
        overflow: hidden;
        margin-bottom: 30px;
    }
    .table-blue th {
        background: #1e40af !important;
        color: #fff;
        text-align: center;
        font-weight: 600;
        padding: 15px 10px;
        font-size: 14px;
    }
    .table tbody tr:hover {
        background: #f3f4f6;
    }
    .btn-primary { 
        background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
        border: none;
        padding: 12px 30px;
        font-weight: 500;
        transition: all 0.3s;
    }
    .btn-primary:hover { 
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(30, 64, 175, 0.3);
    }
    .btn-success {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        border: none;
        font-weight: 500;
    }
    .btn-secondary {
        background: #6b7280;
        border: none;
    }
    img.foto-siswa {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e5e7eb;
    }
    .badge-status {
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 500;
        font-size: 13px;
    }
    .badge-hadir { background: #dcfce7; color: #166534; }
    .badge-izin { background: #fef3c7; color: #92400e; }
    .badge-alfa { background: #fee2e2; color: #991b1b; }
    
    .new-entry {
        animation: highlight 2s ease-in-out;
    }
    @keyframes highlight {
        0% { background-color: #dbeafe; }
        100% { background-color: transparent; }
    }
    
    @media print {
        .filter-section, .btn, #formAbsensiSection,
        .dataTables_length, .dataTables_filter, 
        .dataTables_info, .dataTables_paginate {
            display: none !important;
        }
        .section-title {
            background: #ddd !important;
            border: 1px solid #000 !important;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #000 !important;
            padding: 8px !important;
        }
        th {
            background-color: #ddd !important;
            color: #000 !important;
        }
    }
</style>

<!-- Notifikasi -->
<?php if (isset($_GET['status'])): ?>
<div class="container-fluid mb-3">
    <?php if ($_GET['status'] == 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="align-middle me-2" data-feather="check-circle"></i> 
            Berhasil menyimpan <?= $_GET['count']; ?> absensi! Data telah ditambahkan ke history.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($_GET['status'] == 'error'): ?>
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="align-middle me-2" data-feather="alert-triangle"></i> 
            Berhasil: <?= $_GET['success']; ?>, Gagal: <?= $_GET['error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($_GET['status'] == 'empty'): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <i class="align-middle me-2" data-feather="info"></i> 
            Tidak ada data yang disimpan. Pilih keterangan terlebih dahulu!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="container-fluid p-0">
    <div class="mb-3">
        <h1 class="h3 d-inline align-middle">Jurnal Latihan</h1>
    </div>

    <!-- Filter Ekstrakurikuler -->
    <div class="filter-section">
        <form method="get" class="row g-3 align-items-end">
            <input type="hidden" name="page" value="absensi">
            <div class="col-md-6">
                <label class="form-label fw-bold">
                    <i class="align-middle me-1" data-feather="filter"></i> Filter Ekstrakurikuler
                </label>
                <select name="filter_ekstra" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Ekstrakurikuler --</option>
                    <?php
                    $ekstra_query = mysqli_query($koneksi, "SELECT nama_ekstra FROM ekstra ORDER BY nama_ekstra");
                    while ($ekstra = mysqli_fetch_array($ekstra_query)) {
                        $selected = ($filter_ekstra == $ekstra['nama_ekstra']) ? 'selected' : '';
                        echo "<option value='".htmlspecialchars($ekstra['nama_ekstra'])."' $selected>" . 
                             htmlspecialchars($ekstra['nama_ekstra']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6">
                <?php if ($filter_ekstra): ?>
                    <a href="home.php?page=absensi" class="btn btn-secondary">
                        <i class="align-middle" data-feather="x"></i> Reset Filter
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Form Absensi Section -->
    <div id="formAbsensiSection">
        <div class="section-title">
            <h5><i class="align-middle me-2" data-feather="edit"></i> Form Absensi</h5>
        </div>
        
        <form method="POST" action="simpan_absensi.php" enctype="multipart/form-data">
            <input type="hidden" name="filter_ekstra" value="<?= htmlspecialchars($filter_ekstra); ?>">
            
            <!-- Input Section (Layout Vertikal) -->
            <div class="input-section">
                <div class="input-group-custom">
                    <label class="form-label fw-bold">
                        <i class="align-middle me-1" data-feather="calendar"></i> Tanggal
                    </label>
                    <input type="date" name="tanggal" class="form-control" 
                           value="<?= date('Y-m-d'); ?>" required>
                </div>
                
                <div class="input-group-custom">
                    <label class="form-label fw-bold">
                        <i class="align-middle me-1" data-feather="book"></i> Materi
                    </label>
                    <input type="text" name="materi" class="form-control" 
                           placeholder="Tuliskan materi latihan" required>
                </div>
                
                <div class="input-group-custom">
                    <label class="form-label fw-bold">
                        <i class="align-middle me-1" data-feather="camera"></i> Foto Kegiatan
                    </label>
                    <input type="file" name="foto" accept="image/*" class="form-control">
                    <small class="text-muted">Opsional - Upload foto kegiatan</small>
                </div>
            </div>

            <!-- Tabel Absensi -->
            <div class="table-container">
                <div class="table-responsive">
                    <table id="absenTable" class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-blue">
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Jurusan</th>
                                <th>Ekstrakurikuler</th>
                                <th>Keterangan</th>
                                <th>Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if (mysqli_num_rows($query_siswa) > 0) {
                                while ($data = mysqli_fetch_array($query_siswa)) { ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($data['nama_siswa']); ?></td>
                                        <td class="text-center"><?= htmlspecialchars($data['kelas']); ?></td>
                                        <td class="text-center"><?= !empty($data['jurusan']) ? htmlspecialchars($data['jurusan']) : '<span class="text-muted">-</span>'; ?></td>
                                        <td><?= !empty($data['nama_ekstra']) ? htmlspecialchars($data['nama_ekstra']) : '<span class="text-muted">-</span>'; ?></td>
                                        <td>
                                            <select name="absen[<?= $data['id']; ?>][keterangan]" class="form-select form-select-sm">
                                                <option value="">-- Pilih --</option>
                                                <option value="Hadir">Hadir</option>
                                                <option value="Izin">Izin</option>
                                                <option value="Alfa">Alfa</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="absen[<?= $data['id']; ?>][nilai]" 
                                                class="form-control form-control-sm" placeholder="0-100" min="0" max="100">
                                        </td>
                                    </tr>
                                <?php } 
                            } else { ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="align-middle" data-feather="inbox"></i><br>
                                        Tidak ada data siswa<?= $filter_ekstra ? ' untuk ekstrakurikuler ' . htmlspecialchars($filter_ekstra) : ''; ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (mysqli_num_rows($query_siswa) > 0): ?>
                <div class="p-3 bg-light border-top">
                    <button type="submit" class="btn btn-primary">
                        <i class="align-middle me-2" data-feather="save"></i> Simpan Absensi
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>