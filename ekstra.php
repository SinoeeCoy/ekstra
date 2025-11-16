<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "koneksi.php";

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user = $_SESSION['user'];
$user_role = strtolower($user['role'] ?? '');
$user_id = $user['id'] ?? $user['user_id'] ?? ''; // fleksibel ambil id

// Fungsi untuk cek akses
function hasAccess($allowed_roles) {
    global $user_role;
    return in_array($user_role, $allowed_roles);
}

// Fungsi untuk cek apakah siswa sudah terdaftar di ekstra tertentu
function isAlreadyRegistered($koneksi, $user_id, $ekstra_id) {
    $user_id = intval($user_id);
    $ekstra_id = intval($ekstra_id);
    $query = mysqli_query(
        $koneksi,
        "SELECT * FROM data_siswa WHERE user_id = '$user_id' AND FIND_IN_SET('$ekstra_id', ekstra_id)"
    );
    return mysqli_num_rows($query) > 0;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Ekstrakurikuler</title>

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
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            color: white;
        }

        /* ====== Action Buttons ====== */
        .action-buttons {
            display: flex;
            gap: 6px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 8px 16px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            transition: all 0.2s ease;
            font-size: 13px;
            font-weight: 500;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .btn-success {
            background-color: #27ae60;
            color: white;
        }
        .btn-success:hover {
            background-color: #229954;
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

        .btn-info {
            background-color: #3498db;
            color: white;
            cursor: default;
        }
        .btn-info:hover {
            background-color: #3498db;
            color: white;
            transform: none;
            box-shadow: none;
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

        .badge-day {
            background: #667eea;
            color: white;
        }

        .badge-registered {
            background: #3498db;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

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

            .action-buttons {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <div class="main-container">
        <!-- Header -->
        <div class="header-card">
            <h3>
                <i class="fa-solid fa-users-gear"></i> Data Ekstrakurikuler
            </h3>
            <p>Kelola data ekstrakurikuler yang tersedia di sekolah</p>
        </div>

        <!-- Content Card -->
        <div class="content-card">
            <!-- Tombol Tambah Data - Hanya untuk Admin -->
            <?php if (hasAccess(['admin'])): ?>
            <div class="mb-3 text-end">
                <a href="home.php?page=ekstra_tambah" class="btn btn-primary">
                    <i class="fa-solid fa-plus me-2"></i> Tambah Data Ekstrakurikuler
                </a>
            </div>
            <?php endif; ?>

            <!-- DataTable -->
            <div class="table-wrapper">
                <table id="sppTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Ekstrakurikuler</th>
                            <th>Pembina</th>
                            <th style="width: 120px;">Hari</th>
                            <th style="width: 100px;">Waktu</th>
                            <th>Lokasi</th>
                            <th style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $i = 1;
                    $query = mysqli_query($koneksi, "SELECT * FROM ekstra ORDER BY nama_ekstra ASC");
                    while ($data = mysqli_fetch_array($query)) {
                        $ekstra_id = $data['id'] ?? '';
                        
                        // Cek apakah siswa sudah terdaftar
                        $sudah_terdaftar = false;
                        if ($user_role == 'siswa' && !empty($user_id) && !empty($ekstra_id)) {
                            $sudah_terdaftar = isAlreadyRegistered($koneksi, $user_id, $ekstra_id);
                        }
                    ?>
                        <tr>
                            <td class="text-center"><?= $i; ?></td>
                            <td><strong><?= htmlspecialchars($data['nama_ekstra']); ?></strong></td>
                            <td><?= htmlspecialchars($data['pembina']); ?></td>
                            <td class="text-center">
                                <span class="badge badge-day">
                                    <?= htmlspecialchars($data['hari']); ?>
                                </span>
                            </td>
                            <td class="text-center"><?= htmlspecialchars($data['waktu']); ?></td>
                            <td>
                                <?php if (!empty($data['lokasi'])): ?>
                                    <?= htmlspecialchars($data['lokasi']); ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                <?php if ($user_role == 'siswa'): ?> 
                                    <?php if ($sudah_terdaftar): ?>
                                        <!-- Siswa sudah terdaftar -->
                                        <span class="badge-registered">
                                            <i class="fa-solid fa-circle-check"></i> Sudah Mendaftar
                                        </span>
                                    <?php else: ?>
                                        <!-- Siswa belum terdaftar -->
                                        <a href="?page=ekstra_daftar&nama_ekstra=<?= urlencode($data['nama_ekstra']); ?>" 
                                           class="btn btn-success btn-action">
                                           <i class="fa-solid fa-user-plus"></i> Daftar
                                        </a>
                                    <?php endif; ?>

                                <?php elseif (hasAccess(['admin'])): ?>
                                    <!-- Admin: Bisa ubah dan hapus -->
                                    <a href="?page=ekstra_ubah&nama_ekstra=<?= urlencode($data['nama_ekstra']); ?>" 
                                       class="btn btn-warning btn-action">
                                       <i class="fa-solid fa-pen-to-square"></i> Ubah
                                    </a>

                                    <a href="?page=ekstra_hapus&nama_ekstra=<?= urlencode($data['nama_ekstra']); ?>" 
                                       class="btn btn-danger btn-action" 
                                       onclick="return confirm('Yakin ingin menghapus ekstrakurikuler ini?')">
                                       <i class="fa-solid fa-trash"></i> Hapus
                                    </a>

                                <?php elseif (hasAccess(['pembina', 'waka', 'kepala'])): ?>
                                    <!-- Pembina/Waka/Kepala: Hanya bisa ubah -->
                                    <a href="?page=ekstra_ubah&nama_ekstra=<?= urlencode($data['nama_ekstra']); ?>" 
                                       class="btn btn-warning btn-action">
                                       <i class="fa-solid fa-pen-to-square"></i> Ubah
                                    </a>
                                <?php endif; ?>
                                </div>
                            </td>
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
        $('#sppTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
            },
            "pageLength": 10,
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, "Semua"]
            ],
            "order": [[1, 'asc']], // Urutkan berdasarkan nama ekstrakurikuler
            "columnDefs": [
                { "orderable": false, "targets": 6 } // Kolom Aksi tidak bisa di-sort
            ]
        });
    });
    </script>

</body>
</html>