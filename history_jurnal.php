<?php
// File: history_jurnal.php
// History Jurnal Latihan dengan Export Excel & PDF

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

include "koneksi.php";

// PERBAIKAN: Ambil role dari session
$user_role = '';
if (isset($_SESSION['user']['role'])) {
    $user_role = $_SESSION['user']['role'];
} elseif (isset($_SESSION['role'])) {
    $user_role = $_SESSION['role'];
}

// Konversi ke lowercase
$user_role_lower = strtolower(trim($user_role));

// Cek apakah user adalah admin/pembina
$is_admin = in_array($user_role_lower, ['admin', 'administrator', 'pembina', 'superadmin']);

// Filter
$filter_ekstra = isset($_GET['filter_ekstra']) ? $_GET['filter_ekstra'] : '';
$filter_tanggal_dari = isset($_GET['tanggal_dari']) ? $_GET['tanggal_dari'] : '';
$filter_tanggal_sampai = isset($_GET['tanggal_sampai']) ? $_GET['tanggal_sampai'] : '';

// Build WHERE clause
$where_conditions = [];
if ($filter_ekstra != '') {
    $where_conditions[] = "nama_ekstra = '" . mysqli_real_escape_string($koneksi, $filter_ekstra) . "'";
}
if ($filter_tanggal_dari != '') {
    $where_conditions[] = "tanggal >= '" . mysqli_real_escape_string($koneksi, $filter_tanggal_dari) . "'";
}
if ($filter_tanggal_sampai != '') {
    $where_conditions[] = "tanggal <= '" . mysqli_real_escape_string($koneksi, $filter_tanggal_sampai) . "'";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Query history
$query_history = mysqli_query($koneksi, "
    SELECT * FROM absen 
    $where_clause 
    ORDER BY tanggal DESC, id DESC
");

// Hitung statistik
$stats_query = mysqli_query($koneksi, "
    SELECT 
        COUNT(*) as total_record,
        SUM(CASE WHEN keterangan = 'Hadir' THEN 1 ELSE 0 END) as total_hadir,
        SUM(CASE WHEN keterangan = 'Izin' THEN 1 ELSE 0 END) as total_izin,
        SUM(CASE WHEN keterangan = 'Alfa' THEN 1 ELSE 0 END) as total_alfa
    FROM absen 
    $where_clause
");
$stats = mysqli_fetch_array($stats_query);
?>

<style>
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    .stat-item {
        text-align: center;
        padding: 15px;
    }
    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    .filter-section {
        background: #f9fafb;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 25px;
        border: 1px solid #e5e7eb;
    }
    .export-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .btn-excel {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        color: white;
        font-weight: 500;
    }
    .btn-excel:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
    }
    .btn-pdf {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        border: none;
        color: white;
        font-weight: 500;
    }
    .btn-pdf:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
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
    .badge-hadir { 
        background: #dcfce7; 
        color: #166534; 
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 500;
    }
    .badge-izin { 
        background: #fef3c7; 
        color: #92400e;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 500;
    }
    .badge-alfa { 
        background: #fee2e2; 
        color: #991b1b;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 500;
    }
    .foto-kegiatan {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e5e7eb;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .foto-kegiatan:hover {
        transform: scale(1.05);
    }
    .btn-action {
        padding: 5px 10px;
        font-size: 0.85rem;
    }
    .user-info-badge {
        background: #f3f4f6;
        padding: 8px 15px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }
    
    /* Modal untuk preview foto */
    .modal-foto {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.9);
    }
    .modal-foto-content {
        margin: auto;
        display: block;
        max-width: 90%;
        max-height: 90%;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    .close-modal {
        position: absolute;
        top: 15px;
        right: 35px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
    }
</style>

<div class="container-fluid p-0">
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <h1 class="h3 d-inline align-middle">
            <i class="align-middle me-2" data-feather="book-open"></i>
            History Jurnal Latihan
        </h1>
     </div>

    <!-- Statistik Card -->
    <div class="stats-card">
        <div class="row">
            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?= $stats['total_record']; ?></div>
                    <div class="stat-label">Total Record</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?= $stats['total_hadir']; ?></div>
                    <div class="stat-label">Total Hadir</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?= $stats['total_izin']; ?></div>
                    <div class="stat-label">Total Izin</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-number"><?= $stats['total_alfa']; ?></div>
                    <div class="stat-label">Total Alfa</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="get" class="row g-3">
            <input type="hidden" name="page" value="history_jurnal">
            
            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="align-middle me-1" data-feather="filter"></i> Ekstrakurikuler
                </label>
                <select name="filter_ekstra" class="form-select">
                    <option value="">-- Semua --</option>
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
            
            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="align-middle me-1" data-feather="calendar"></i> Tanggal Dari
                </label>
                <input type="date" name="tanggal_dari" class="form-control" value="<?= htmlspecialchars($filter_tanggal_dari); ?>">
            </div>
            
            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="align-middle me-1" data-feather="calendar"></i> Tanggal Sampai
                </label>
                <input type="date" name="tanggal_sampai" class="form-control" value="<?= htmlspecialchars($filter_tanggal_sampai); ?>">
            </div>
            
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="align-middle" data-feather="search"></i> Filter
                </button>
                <a href="?page=history_jurnal" class="btn btn-secondary">
                    <i class="align-middle" data-feather="refresh-cw"></i> Reset
                </a>
            </div>
        </form>

        <!-- Export Buttons - Hanya untuk Admin/Pembina -->
        <?php if ($is_admin): ?>
        <div class="export-buttons mt-3">
            <form action="export_jurnal_excel.php" method="post" style="display: inline;">
                <input type="hidden" name="filter_ekstra" value="<?= htmlspecialchars($filter_ekstra); ?>">
                <input type="hidden" name="tanggal_dari" value="<?= htmlspecialchars($filter_tanggal_dari); ?>">
                <input type="hidden" name="tanggal_sampai" value="<?= htmlspecialchars($filter_tanggal_sampai); ?>">
                <button type="submit" class="btn btn-excel">
                    <i class="align-middle me-1" data-feather="file-text"></i> Export Excel
                </button>
            </form>
            
            <form action="export_jurnal_pdf.php" method="post" style="display: inline;" target="_blank">
                <input type="hidden" name="filter_ekstra" value="<?= htmlspecialchars($filter_ekstra); ?>">
                <input type="hidden" name="tanggal_dari" value="<?= htmlspecialchars($filter_tanggal_dari); ?>">
                <input type="hidden" name="tanggal_sampai" value="<?= htmlspecialchars($filter_tanggal_sampai); ?>">
                <button type="submit" class="btn btn-pdf">
                    <i class="align-middle me-1" data-feather="file"></i> Export PDF
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- Tabel History -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">
                <i class="align-middle me-2" data-feather="list"></i>
                Data History Jurnal
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="historyTable" class="table table-bordered table-hover align-middle">
                    <thead class="table-blue">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Jurusan</th>
                            <th>Ekstrakurikuler</th>
                            <th>Materi</th>
                            <th>Keterangan</th>
                            <th>Nilai</th>
                            <th>Foto</th>
                            <?php if ($is_admin): ?>
                            <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($data = mysqli_fetch_array($query_history)) { 
                            $badge_class = '';
                            switch($data['keterangan']) {
                                case 'Hadir': $badge_class = 'badge-hadir'; break;
                                case 'Izin': $badge_class = 'badge-izin'; break;
                                case 'Alfa': $badge_class = 'badge-alfa'; break;
                            }
                        ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td class="text-center"><?= date('d/m/Y', strtotime($data['tanggal'])); ?></td>
                            <td><?= htmlspecialchars($data['nama_siswa']); ?></td>
                            <td class="text-center"><?= htmlspecialchars($data['kelas']); ?></td>
                            <td class="text-center"><?= !empty($data['jurusan']) ? htmlspecialchars($data['jurusan']) : '<span class="text-muted">-</span>'; ?></td>
                            <td><?= htmlspecialchars($data['nama_ekstra']); ?></td>
                            <td><?= !empty($data['materi']) ? htmlspecialchars($data['materi']) : '<span class="text-muted">-</span>'; ?></td>
                            <td class="text-center">
                                <span class="<?= $badge_class; ?>"><?= $data['keterangan']; ?></span>
                            </td>
                            <td class="text-center">
                                <?= !empty($data['nilai']) ? $data['nilai'] : '<span class="text-muted">-</span>'; ?>
                            </td>
                            <td class="text-center">
                                <?php if (!empty($data['foto']) && file_exists('uploads/kegiatan/' . $data['foto'])): ?>
                                    <img src="uploads/kegiatan/<?= htmlspecialchars($data['foto']); ?>" 
                                         class="foto-kegiatan" 
                                         onclick="showModal(this.src)"
                                         alt="Foto Kegiatan">
                                <?php else: ?>
                                    <span class="text-muted">Tidak ada foto</span>
                                <?php endif; ?>
                            </td>
                            
                            <?php if ($is_admin): ?>
                            <td class="text-center">
                                <!-- Tombol Edit - Hanya Admin/Pembina -->
                                <a href="?page=history_jurnal_edit&id=<?= $data['id']; ?>" 
                                   class="btn btn-warning btn-action"
                                   title="Edit Data">
                                    <i class="align-middle" data-feather="edit-2"></i>
                                </a>

                                <!-- Tombol Hapus - Hanya Admin/Pembina -->
                                <a href="history_jurnal_hapus.php?id=<?= $data['id']; ?>" 
                                   class="btn btn-danger btn-action"
                                   title="Hapus Data"
                                   onclick="return confirm('Yakin ingin menghapus data ini?')">
                                    <i class="align-middle" data-feather="trash-2"></i>
                                </a>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview Foto -->
<div id="modalFoto" class="modal-foto" onclick="closeModal()">
    <span class="close-modal">&times;</span>
    <img class="modal-foto-content" id="imgModal">
</div>

<script>
// DataTable initialization
$(document).ready(function() {
    $('#historyTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
        },
        "pageLength": 25,
        "order": [[1, "desc"]]
    });
});

// Modal foto functions
function showModal(imgSrc) {
    document.getElementById('modalFoto').style.display = 'block';
    document.getElementById('imgModal').src = imgSrc;
}

function closeModal() {
    document.getElementById('modalFoto').style.display = 'none';
}
</script>