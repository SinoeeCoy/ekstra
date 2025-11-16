<?php
include "koneksi.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// Filter ekstrakurikuler dan tahun
$filter_ekstra = isset($_GET['filter_ekstra']) ? $_GET['filter_ekstra'] : '';
$filter_tahun = isset($_GET['filter_tahun']) ? $_GET['filter_tahun'] : date('Y');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Ringkasan Ekstrakurikuler</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .stats-card {
            border-left: 4px solid #007bff;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        .table-blue {
            background-color: #1e40af !important;
            color: white !important;
        }
        .table-blue th {
            background-color: #1e40af !important;
            color: white !important;
            border-color: #1e3a8a !important;
        }
        .section-title {
            color: #1e40af;
            font-weight: 600;
            margin-top: 2rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid #e9ecef;
        }
        
        /* Style untuk expand/collapse */
        .expandable-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .expandable-row:hover {
            background-color: #f8f9fa;
        }
        .detail-row {
            display: none;
            background-color: #f8f9fa;
        }
        .detail-row.show {
            display: table-row;
        }
        .detail-table {
            margin: 10px 0;
            font-size: 0.9em;
        }
        .expand-icon {
            transition: transform 0.3s;
            display: inline-block;
        }
        .expand-icon.expanded {
            transform: rotate(90deg);
        }
        .month-badge {
            display: inline-block;
            min-width: 80px;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            .detail-row {
                display: table-row !important;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h3 class="mb-0">
                                <i class="fas fa-chart-bar text-primary"></i> Laporan Ringkasan Ekstrakurikuler
                            </h3>
                            <p class="text-muted mb-0">Ringkasan pertemuan dan kehadiran dengan detail per bulan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <?php
        // Build WHERE clause untuk statistik
        $where_stats = "WHERE 1=1";
        if (!empty($filter_ekstra)) {
            $filter_ekstra_escaped = mysqli_real_escape_string($koneksi, $filter_ekstra);
            $where_stats .= " AND a.nama_ekstra = '$filter_ekstra_escaped'";
        }
        if (!empty($filter_tahun)) {
            $where_stats .= " AND YEAR(a.tanggal) = '$filter_tahun'";
        }
        
        // Perbaikan query statistik
        $query_total_ekstra = "SELECT COUNT(DISTINCT a.nama_ekstra) as total FROM absen a $where_stats";
        $result_total_ekstra = mysqli_query($koneksi, $query_total_ekstra);
        $total_ekstra = mysqli_fetch_assoc($result_total_ekstra)['total'];
        
        $query_total_pertemuan = "SELECT COUNT(DISTINCT CONCAT(a.nama_ekstra, '-', a.tanggal)) as total FROM absen a $where_stats";
        $result_total_pertemuan = mysqli_query($koneksi, $query_total_pertemuan);
        $total_pertemuan = mysqli_fetch_assoc($result_total_pertemuan)['total'];
        
        $query_total_kehadiran = "SELECT COUNT(*) as total FROM absen a $where_stats";
        $result_total_kehadiran = mysqli_query($koneksi, $query_total_kehadiran);
        $total_kehadiran = mysqli_fetch_assoc($result_total_kehadiran)['total'];
        
        // Ekstra paling aktif
        $aktif_query = mysqli_query($koneksi, "
            SELECT a.nama_ekstra, COUNT(DISTINCT a.tanggal) as jumlah_pertemuan 
            FROM absen a
            $where_stats
            GROUP BY a.nama_ekstra 
            ORDER BY jumlah_pertemuan DESC 
            LIMIT 1
        ");
        $aktif_data = mysqli_fetch_array($aktif_query);
        $ekstra_aktif = $aktif_data ? $aktif_data['nama_ekstra'] . " (" . $aktif_data['jumlah_pertemuan'] . "x)" : "Belum ada data";
        ?>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card border-0 h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <p class="text-muted mb-1">Total Ekstrakurikuler</p>
                            <h3 class="mb-0"><?php echo $total_ekstra; ?></h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card border-0 h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <p class="text-muted mb-1">Total Pertemuan</p>
                            <h3 class="mb-0"><?php echo $total_pertemuan; ?></h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card border-0 h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <p class="text-muted mb-1">Total Kehadiran</p>
                            <h3 class="mb-0"><?php echo $total_kehadiran; ?></h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card border-0 h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <p class="text-muted mb-1">Ekstra Paling Aktif</p>
                            <small class="mb-0"><?php echo $ekstra_aktif; ?></small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="row mb-4 no-print">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label for="filterEkstra" class="form-label fw-bold">
                                <i class="fas fa-filter text-primary"></i> Ekstrakurikuler:
                            </label>
                            <select id="filterEkstra" class="form-select">
                                <option value="">Semua Ekstrakurikuler</option>
                                <?php
                                // Perbaikan: ambil dari tabel ekstra
                                $ekstra_query = mysqli_query($koneksi, "SELECT nama_ekstra FROM ekstra ORDER BY nama_ekstra ASC");
                                if ($ekstra_query && mysqli_num_rows($ekstra_query) > 0) {
                                    while ($ekstra = mysqli_fetch_array($ekstra_query)) {
                                        $selected = ($filter_ekstra == $ekstra['nama_ekstra']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($ekstra['nama_ekstra']) . "' $selected>" 
                                             . htmlspecialchars($ekstra['nama_ekstra']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filterTahun" class="form-label fw-bold">
                                <i class="fas fa-calendar text-info"></i> Tahun:
                            </label>
                            <select id="filterTahun" class="form-select">
                                <?php
                                $tahun_query = mysqli_query($koneksi, "SELECT DISTINCT YEAR(tanggal) as tahun FROM absen ORDER BY tahun DESC");
                                if ($tahun_query && mysqli_num_rows($tahun_query) > 0) {
                                    while ($tahun = mysqli_fetch_array($tahun_query)) {
                                        $selected = ($filter_tahun == $tahun['tahun']) ? 'selected' : '';
                                        echo "<option value='{$tahun['tahun']}' $selected>{$tahun['tahun']}</option>";
                                    }
                                } else {
                                    // Default current year if no data
                                    echo "<option value='" . date('Y') . "' selected>" . date('Y') . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" onclick="filterData()">
                                <i class="fas fa-search"></i> Terapkan Filter
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-success w-100 no-print" onclick="toggleAllDetails()">
                                <i class="fas fa-expand-alt"></i> <span id="toggleText">Buka Semua</span>
                            </button>
                        </div>
                        <div class="col-md-2">
                            <div class="btn-group w-100" role="group">
                                <button class="btn btn-outline-success" onclick="exportToExcel()">
                                    <i class="fas fa-file-excel"></i> Excel
                                </button>
                                <button class="btn btn-outline-danger" onclick="exportToPDF()">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </button>
                                <?php if (!empty($filter_ekstra)): ?>
                                <button class="btn btn-outline-secondary" onclick="window.location.href='?page=laporan'">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Filter Aktif -->
    <?php if (!empty($filter_ekstra) || $filter_tahun != date('Y')): ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Filter Aktif:</strong>
                <?php 
                if (!empty($filter_ekstra)) echo " Ekstrakurikuler: <strong>" . htmlspecialchars($filter_ekstra) . "</strong>";
                echo " | Tahun: <strong>" . $filter_tahun . "</strong>";
                ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Ringkasan Total Per Ekstrakurikuler dengan Detail Bulan -->
    <div class="table-container">
        <h5 class="section-title">
            <i class="fas fa-chart-bar"></i> Ringkasan Per Ekstrakurikuler 
            <small class="text-muted">(Klik baris untuk lihat detail per bulan)</small>
        </h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="tabelRingkasan">
                <thead class="table-blue">
                    <tr>
                        <th width="5%">No</th>
                        <th width="30%">Ekstrakurikuler</th>
                        <th width="15%">Total Pertemuan</th>
                        <th width="15%">Total Kehadiran</th>
                        <th width="20%">Rata-rata Kehadiran</th>
                        <th width="15%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $where_clause = "WHERE 1=1";
                    if (!empty($filter_ekstra)) {
                        $filter_ekstra_escaped = mysqli_real_escape_string($koneksi, $filter_ekstra);
                        $where_clause .= " AND a.nama_ekstra = '$filter_ekstra_escaped'";
                    }
                    if (!empty($filter_tahun)) {
                        $where_clause .= " AND YEAR(a.tanggal) = '$filter_tahun'";
                    }

                    // Perbaikan query utama
                    $query_ringkasan = "
                        SELECT 
                            a.nama_ekstra,
                            COUNT(DISTINCT a.tanggal) AS total_pertemuan,
                            COUNT(*) AS total_kehadiran
                        FROM absen a
                        $where_clause
                        GROUP BY a.nama_ekstra
                        ORDER BY total_pertemuan DESC, a.nama_ekstra ASC
                    ";
                    $result_ringkasan = mysqli_query($koneksi, $query_ringkasan);
                    
                    $nama_bulan = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                    
                    if ($result_ringkasan && mysqli_num_rows($result_ringkasan) > 0) {
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result_ringkasan)) {
                            $rata_rata = $row['total_pertemuan'] > 0 ? round($row['total_kehadiran'] / $row['total_pertemuan'], 1) : 0;
                            $ekstra_id = 'ekstra_' . $no;
                            
                            // Row utama (ringkasan)
                            echo "<tr class='expandable-row' onclick='toggleDetail(\"$ekstra_id\")'>
                                <td class='text-center'>{$no}</td>
                                <td>
                                    <i class='fas fa-chevron-right expand-icon me-2' id='icon_$ekstra_id'></i>
                                    <strong>" . htmlspecialchars($row['nama_ekstra']) . "</strong>
                                </td>
                                <td class='text-center'><span class='badge bg-info text-dark'>{$row['total_pertemuan']} kali</span></td>
                                <td class='text-center'><span class='badge bg-warning text-dark'>{$row['total_kehadiran']} siswa</span></td>
                                <td class='text-center'><span class='badge bg-success'>{$rata_rata} siswa/pertemuan</span></td>
                                <td class='text-center'>
                                    <button class='btn btn-sm btn-outline-primary' onclick='event.stopPropagation(); toggleDetail(\"$ekstra_id\")'>
                                        <i class='fas fa-eye'></i> Detail
                                    </button>
                                </td>
                            </tr>";
                            
                            // Row detail (per bulan) - hidden by default
                            echo "<tr class='detail-row' id='$ekstra_id'>
                                <td colspan='6' style='padding: 15px;'>
                                    <div class='card border-0 bg-light'>
                                        <div class='card-body'>
                                            <h6 class='mb-3'>
                                                <i class='fas fa-calendar-alt text-primary'></i> 
                                                Detail Per Bulan - " . htmlspecialchars($row['nama_ekstra']) . " (Tahun $filter_tahun)
                                            </h6>
                                            <table class='table table-sm table-bordered detail-table mb-0'>
                                                <thead class='table-secondary'>
                                                    <tr>
                                                        <th width='5%'>No</th>
                                                        <th width='25%'>Bulan</th>
                                                        <th width='20%'>Jumlah Pertemuan</th>
                                                        <th width='20%'>Total Kehadiran</th>
                                                        <th width='30%'>Rata-rata Siswa</th>
                                                    </tr>
                                                </thead>
                                                <tbody>";
                            
                            // Query detail per bulan untuk ekstrakurikuler ini
                            $ekstra_escaped = mysqli_real_escape_string($koneksi, $row['nama_ekstra']);
                            $query_detail = "
                                SELECT 
                                    MONTH(a.tanggal) AS bulan,
                                    COUNT(DISTINCT a.tanggal) AS jumlah_pertemuan,
                                    COUNT(*) AS total_kehadiran
                                FROM absen a
                                WHERE a.nama_ekstra = '$ekstra_escaped' AND YEAR(a.tanggal) = '$filter_tahun'
                                GROUP BY MONTH(a.tanggal)
                                ORDER BY bulan ASC
                            ";
                            $result_detail = mysqli_query($koneksi, $query_detail);
                            
                            if ($result_detail && mysqli_num_rows($result_detail) > 0) {
                                $no_detail = 1;
                                while ($detail = mysqli_fetch_assoc($result_detail)) {
                                    $rata_detail = $detail['jumlah_pertemuan'] > 0 ? round($detail['total_kehadiran'] / $detail['jumlah_pertemuan'], 1) : 0;
                                    echo "<tr>
                                        <td class='text-center'>{$no_detail}</td>
                                        <td><span class='badge bg-primary month-badge'>{$nama_bulan[$detail['bulan']]}</span></td>
                                        <td class='text-center'>{$detail['jumlah_pertemuan']} kali</td>
                                        <td class='text-center'>{$detail['total_kehadiran']} siswa</td>
                                        <td class='text-center'><span class='badge bg-success'>{$rata_detail} siswa/pertemuan</span></td>
                                    </tr>";
                                    $no_detail++;
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center text-muted'>Tidak ada data detail bulan</td></tr>";
                            }
                            
                            echo "              </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>";
                            
                            $no++;
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center text-muted'>
                                <i class='fas fa-inbox fa-2x mb-2'></i><br>
                                Tidak ada data sesuai filter
                              </td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#tabelRingkasan').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
        },
        "pageLength": 25,
        "order": [[2, 'desc']]
    });
});

function toggleDetail(id) {
    var detailRow = document.getElementById(id);
    var icon = document.getElementById('icon_' + id);
    
    if (detailRow.classList.contains('show')) {
        detailRow.classList.remove('show');
        icon.classList.remove('expanded');
    } else {
        detailRow.classList.add('show');
        icon.classList.add('expanded');
    }
}

var allExpanded = false;
function toggleAllDetails() {
    var detailRows = document.querySelectorAll('.detail-row');
    var icons = document.querySelectorAll('.expand-icon');
    var toggleText = document.getElementById('toggleText');
    
    if (!allExpanded) {
        detailRows.forEach(row => row.classList.add('show'));
        icons.forEach(icon => icon.classList.add('expanded'));
        toggleText.textContent = 'Tutup Semua';
        allExpanded = true;
    } else {
        detailRows.forEach(row => row.classList.remove('show'));
        icons.forEach(icon => icon.classList.remove('expanded'));
        toggleText.textContent = 'Buka Semua';
        allExpanded = false;
    }
}

function filterData() {
    var filterEkstra = document.getElementById('filterEkstra').value;
    var filterTahun = document.getElementById('filterTahun').value;
    
    var url = '?page=laporan';
    var params = [];
    
    if (filterEkstra) params.push('filter_ekstra=' + encodeURIComponent(filterEkstra));
    if (filterTahun) params.push('filter_tahun=' + filterTahun);
    
    if (params.length > 0) url += '&' + params.join('&');
    
    window.location.href = url;
}

function exportToExcel() {
    var filterEkstra = document.getElementById('filterEkstra').value;
    var filterTahun = document.getElementById('filterTahun').value;
    
    var url = 'export_laporan_excel.php?';
    var params = [];
    
    if (filterEkstra) params.push('filter_ekstra=' + encodeURIComponent(filterEkstra));
    if (filterTahun) params.push('filter_tahun=' + filterTahun);
    
    url += params.join('&');
    window.open(url, '_blank');
}

function exportToPDF() {
    var filterEkstra = document.getElementById('filterEkstra').value;
    var filterTahun = document.getElementById('filterTahun').value;
    
    var url = 'export_laporan_pdf.php?';
    var params = [];
    
    if (filterEkstra) params.push('filter_ekstra=' + encodeURIComponent(filterEkstra));
    if (filterTahun) params.push('filter_tahun=' + filterTahun);
    
    url += params.join('&');
    window.open(url, '_blank');
}
</script>

</body>
</html>