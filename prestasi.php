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
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prestasi Ekstrakurikuler</title>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        /* ====== Global Style ====== */
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        /* ====== Container ====== */
        .main-container {
            padding: 24px;
            max-width: 100%;
        }

        /* ====== Header Card ====== */
        .header-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            padding: 32px;
            color: white;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
        }

        .header-card h3 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: white;
        }

        .header-card p {
            margin: 0;
            opacity: 0.95;
            font-size: 14px;
        }

        /* ====== Statistics Cards ====== */
        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            height: 100%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 4px solid;
        }

        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        }

        .stats-card.card-primary { border-left-color: #667eea; }
        .stats-card.card-warning { border-left-color: #f39c12; }
        .stats-card.card-info { border-left-color: #3498db; }
        .stats-card.card-success { border-left-color: #27ae60; }

        .stats-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 16px;
        }

        .stats-card.card-primary .icon { background: rgba(102, 126, 234, 0.1); color: #667eea; }
        .stats-card.card-warning .icon { background: rgba(243, 156, 18, 0.1); color: #f39c12; }
        .stats-card.card-info .icon { background: rgba(52, 152, 219, 0.1); color: #3498db; }
        .stats-card.card-success .icon { background: rgba(39, 174, 96, 0.1); color: #27ae60; }

        .stats-card h6 {
            font-size: 13px;
            color: #6c757d;
            font-weight: 600;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stats-card .number {
            font-size: 32px;
            font-weight: 700;
            color: #2d3748;
            margin: 0;
        }

        /* ====== Content Card ====== */
        .content-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        /* ====== Buttons ====== */
        .btn {
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            padding: 10px 20px;
            transition: all 0.2s;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        /* ====== Action Buttons ====== */
        .action-buttons {
            display: flex;
            gap: 6px;
            justify-content: center;
        }

        .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.2s ease;
            font-size: 13px;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .btn-info {
            background-color: #17a2b8;
            color: white;
        }
        .btn-info:hover {
            background-color: #138496;
            color: white;
        }

        .btn-warning {
            background-color: #f4a261;
            color: white;
        }
        .btn-warning:hover {
            background-color: #e76f51;
            color: white;
        }

        .btn-danger {
            background-color: #e63946;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c1121f;
            color: white;
        }

        /* ====== DataTable Styling ====== */
        .table-wrapper {
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        table.dataTable {
            width: 100% !important;
            margin: 0 !important;
        }

        table.dataTable thead th {
            background: #f8f9fa;
            color: #2d3748;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
            padding: 16px 12px;
            white-space: nowrap;
        }

        table.dataTable tbody td {
            padding: 14px 12px;
            vertical-align: middle;
            font-size: 14px;
            color: #4a5568;
            border-bottom: 1px solid #e2e8f0;
        }

        table.dataTable tbody tr:hover {
            background-color: #f7fafc;
        }

        /* ====== Badge Style ====== */
        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .tingkat-internasional { background: #e74c3c; color: #fff; }
        .tingkat-nasional { background: #f39c12; color: #fff; }
        .tingkat-provinsi { background: #3498db; color: #fff; }
        .tingkat-kabupaten { background: #27ae60; color: #fff; }
        .tingkat-kecamatan { background: #95a5a6; color: #fff; }
        .tingkat-sekolah { background: #34495e; color: #fff; }

        /* ====== DataTables Controls ====== */
        .dataTables_wrapper {
            padding: 0;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            padding: 16px 24px;
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 13px;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            padding: 16px 24px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 6px 12px;
            margin: 0 2px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: white;
            color: #4a5568 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #667eea !important;
            color: white !important;
            border-color: #667eea !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #667eea !important;
            color: white !important;
            border-color: #667eea !important;
        }

        /* ====== Responsive ====== */
        @media (max-width: 768px) {
            .header-card {
                padding: 24px;
            }

            .header-card h3 {
                font-size: 22px;
            }

            .stats-card {
                margin-bottom: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="main-container">
        <!-- Header -->
        <div class="header-card">
            <h3>
                <i class="fa-solid fa-trophy"></i> Data Prestasi Ekstrakurikuler
            </h3>
            <p>Data total prestasi dari setiap ekstrakurikuler</p>
        </div>

        <!-- Statistics Cards -->
        <?php if (hasAccess(['pembina','waka','admin','kepala'])): ?>
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card card-primary">
                    <div class="icon">
                        <i class="fa-solid fa-medal"></i>
                    </div>
                    <h6>Total Prestasi</h6>
                    <div class="number">
                        <?php 
                        $total = mysqli_fetch_array(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM data_prestasi"));
                        echo $total['total'];
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card card-warning">
                    <div class="icon">
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <h6>Tingkat Nasional</h6>
                    <div class="number">
                        <?php 
                        $nasional = mysqli_fetch_array(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM data_prestasi WHERE tingkat='Nasional'"));
                        echo $nasional['total'];
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card card-info">
                    <div class="icon">
                        <i class="fa-solid fa-certificate"></i>
                    </div>
                    <h6>Tingkat Provinsi</h6>
                    <div class="number">
                        <?php 
                        $provinsi = mysqli_fetch_array(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM data_prestasi WHERE tingkat='Provinsi'"));
                        echo $provinsi['total'];
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card card-success">
                    <div class="icon">
                        <i class="fa-solid fa-award"></i>
                    </div>
                    <h6>Prestasi Tahun Ini</h6>
                    <div class="number">
                        <?php 
                        $tahun_ini = mysqli_fetch_array(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM data_prestasi WHERE tahun=YEAR(NOW())"));
                        echo $tahun_ini['total'];
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Content Card -->
        <div class="content-card">
            <!-- Tombol Tambah Data -->
            <?php if (hasAccess(['pembina','waka','admin','kepala'])): ?>
            <div class="mb-3 text-end">
                <a href="home.php?page=prestasi_tambah" class="btn btn-primary">
                    <i class="fa-solid fa-plus me-2"></i> Tambah Data Prestasi
                </a>
            </div>
            <?php endif; ?>

            <!-- DataTable -->
            <div class="table-wrapper">
                <table id="prestasiTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Ekstrakurikuler</th>
                            <th>Nama Siswa</th>
                            <th>Prestasi</th>
                            <th style="width: 120px;">Tingkat</th>
                            <th style="width: 80px;">Tahun</th>
                            <th>Keterangan</th>
                            <?php if (hasAccess(['pembina','waka','admin','kepala'])): ?>
                            <th style="width: 120px;">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $i = 1;
                    $query = mysqli_query($koneksi, "SELECT * FROM data_prestasi ORDER BY tahun DESC, tingkat ASC");
                    while ($data = mysqli_fetch_array($query)) {
                        // Badge tingkat
                        $tingkat = $data['tingkat'];
                        $badge_class = '';
                        switch(strtolower($tingkat)) {
                            case 'internasional': $badge_class = 'tingkat-internasional'; break;
                            case 'nasional': $badge_class = 'tingkat-nasional'; break;
                            case 'provinsi': $badge_class = 'tingkat-provinsi'; break;
                            case 'kabupaten': $badge_class = 'tingkat-kabupaten'; break;
                            case 'kecamatan': $badge_class = 'tingkat-kecamatan'; break;
                            case 'sekolah': $badge_class = 'tingkat-sekolah'; break;
                        }
                    ?>
                        <tr>
                            <td class="text-center"><?= $i; ?></td>
                            <td><?= htmlspecialchars($data['nama_ekstra']); ?></td>
                            <td><?= htmlspecialchars($data['nama_siswa']); ?></td>
                            <td><strong><?= htmlspecialchars($data['prestasi']); ?></strong></td>
                            <td class="text-center">
                                <span class="badge <?= $badge_class; ?>">
                                    <?= htmlspecialchars($tingkat); ?>
                                </span>
                            </td>
                            <td class="text-center"><?= htmlspecialchars($data['tahun']); ?></td>
                            <td><?= !empty($data['keterangan']) ? htmlspecialchars($data['keterangan']) : '<span class="text-muted">-</span>'; ?></td>
                            
                            <?php if (hasAccess(['pembina','waka','admin','kepala'])): ?>
                            <td>
                                <div class="action-buttons">
                                    <a href="home.php?page=prestasi_detail&id=<?= $data['id_prestasi']; ?>" 
                                       class="btn btn-info btn-action" title="Lihat Detail">
                                       <i class="fa-solid fa-eye"></i>
                                    </a>
                                    
                                    <a href="home.php?page=prestasi_ubah&id=<?= $data['id_prestasi']; ?>" 
                                       class="btn btn-warning btn-action" title="Edit">
                                       <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <a href="home.php?page=prestasi_hapus&id=<?= $data['id_prestasi']; ?>" 
                                       class="btn btn-danger btn-action" 
                                       onclick="return confirm('Yakin ingin menghapus prestasi ini?')" title="Hapus">
                                       <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php 
                        $i++;
                    } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#prestasiTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
            },
            "pageLength": 10,
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, "Semua"]
            ],
            "order": [[5, 'desc'], [4, 'asc']], // Urutkan berdasarkan tahun & tingkat
            "columnDefs": [
                { "orderable": false, "targets": <?php echo hasAccess(['pembina','waka','admin','kepala']) ? '7' : '-1'; ?> } // Kolom aksi tidak bisa diurutkan
            ]
        });
    });
    </script>

</body>
</html>