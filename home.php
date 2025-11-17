<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
$user_role = strtolower($user['role'] ?? '');
$current_page = $_GET['page'] ?? 'dashboard';

include "koneksi.php";

// Fungsi untuk mendapatkan role display
function getRoleDisplay($role) {
    $roles = [
        'siswa' => 'Siswa',
        'pembina' => 'Pembina',
        'admin' => 'Admin',
    ];
    return $roles[strtolower($role)] ?? 'User';
}

// Fungsi untuk mendapatkan badge color
function getRoleBadge($role) {
    $badges = [
        'siswa' => 'success',
        'pembina' => 'warning',
        'admin' => 'danger',
    ];
    return $badges[strtolower($role)] ?? 'secondary';
}

// Definisi menu berdasarkan role
$menus = [
    'siswa' => [
        'dashboard' => ['icon' => 'home', 'label' => 'Beranda', 'header' => 'Dashboard'],
        'sistemprofil_pembina' => ['icon' => 'user-check', 'label' => 'Profil Pembina'],
        'ekstra' => ['icon' => 'activity', 'label' => 'Data Ekstrakurikuler', 'header' => 'Ekstrakurikuler'],
        'siswa' => ['icon' => 'users', 'label' => 'Data Siswa'],
        'pengumuman' => ['icon' => 'bell', 'label' => 'Pengumuman', 'header' => 'Informasi'],
        'galeri' => ['icon' => 'image', 'label' => 'Galeri'],
        'prestasi' => ['icon' => 'award', 'label' => 'Prestasi']
    ],

    'pembina' => [
        'dashboard' => ['icon' => 'home', 'label' => 'Beranda', 'header' => 'Dashboard'],
        'sistemprofil_pembina' => ['icon' => 'user', 'label' => 'Profil Pembina', 'header' => 'Manajemen'],
        'ekstra' => ['icon' => 'activity', 'label' => 'Data Ekstrakurikuler'],
        'siswa' => ['icon' => 'users', 'label' => 'Data Siswa'],
        'jurnal_absensi' => [
            'icon' => 'calendar', 
            'label' => 'Jurnal Absensi',
            'type' => 'dropdown',
            'submenu' => [
                'absensi' => ['label' => 'Jurnal Latihan'],
                'history_jurnal' => ['label' => 'History Jurnal']
            ]
        ],
        'pengumuman' => ['icon' => 'bell', 'label' => 'Pengumuman', 'header' => 'Informasi'],
        'galeri' => ['icon' => 'image', 'label' => 'Galeri'],
        'prestasi' => ['icon' => 'award', 'label' => 'Prestasi']
    ],

    'admin' => [
        'dashboard' => ['icon' => 'home', 'label' => 'Beranda', 'header' => 'Dashboard'],
        'sistemprofil_pembina' => ['icon' => 'user', 'label' => 'Profil Pembina', 'header' => 'Manajemen'],
        'ekstra' => ['icon' => 'activity', 'label' => 'Data Ekstrakurikuler'],
        'siswa' => ['icon' => 'users', 'label' => 'Data Siswa'],
        'jurnal_absensi' => [
            'icon' => 'calendar', 
            'label' => 'Jurnal Absensi',
            'type' => 'dropdown',
            'submenu' => [
                'absensi' => ['label' => 'Jurnal Latihan'],
                'history_jurnal' => ['label' => 'History Jurnal']
            ]
        ],
        'galeri' => ['icon' => 'image', 'label' => 'Galeri'],
        'laporan' => ['icon' => 'file-text', 'label' => 'Laporan'],
        'pengumuman' => ['icon' => 'bell', 'label' => 'Pengumuman', 'header' => 'Informasi'],
        'prestasi' => ['icon' => 'award', 'label' => 'Prestasi']
    ]
];

// Set menu berdasarkan role
if (in_array($user_role, ['admin'])) {
    $current_menu = $menus['admin'];
} else {
    $current_menu = $menus[$user_role] ?? $menus['siswa'];
}

// Definisi allowed pages berdasarkan role
$allowed_pages = [
    'siswa' => [
        'dashboard',
        'sistemprofil_pembina', 
        'profil_pembina',  
        'ekstra', 
        'ekstra_daftar',
        'siswa', 
        'pengumuman', 
        'galeri', 
        'prestasi', 
        'profil_siswa', 
        'browse_ekskul',  
        'pendaftaran', 
        'jadwal', 
        'absensi_siswa', 
        'tugas', 
        'pengaturan', 
        'history', 
        'nilai', 
        'sertifikat'
    ],
    
    'pembina' => [
        'dashboard', 
        'sistemprofil_pembina', 
        'profil_pembina', 
        'ekstra', 
        'ekstra_daftar_manual',
        'siswa', 
        'siswa_tambah_manual',
        'siswa_ubah',
        'siswa_hapus',
        'absensi',
        'history_jurnal',
        'history_jurnal_edit',
        'history_jurnal_hapus',
        'data_absensi', 
        'edit_absensi',
        'hapus_absensi',
        'simpan_absensi',
        'laporan', 
        'pengumuman', 
        'galeri', 
        'profil', 
        'pengaturan',
        'prestasi_tambah',
        'prestasi_simpan',
        'prestasi',
        'history'
    ],
    
    'admin' => [
        'dashboard', 
        'sistemprofil_pembina', 
        'profil_pembina',
        'profil_pembina_detail',
        'profil_pembina_edit',
        'siswa', 
        'ekstra', 
        'ekstra_daftar',
        'absensi',
        'history_jurnal',
        'history_jurnal_edit',
        'history_jurnal_hapus',
        'data_absensi',
        'edit_absensi',
        'hapus_absensi',
        'simpan_absensi',
        'cetak_absensi',
        'galeri', 
        'kelas_tambah', 
        'kelas_ubah', 
        'kelas_hapus',
        'siswa_edit', 
        'siswa_tambah', 
        'siswa_tambah_manual',
        'siswa_ubah', 
        'siswa_hapus',
        'history',  
        'ekstra_tambah', 
        'ekstra_ubah', 
        'ekstra_hapus', 
        'ekstra_daftar_manual',
        'data_siswa', 
        'laporan',
        'export_laporan_pdf',
        'export_laporan_excel',
        'export_jurnal_pdf',
        'export_jurnal_excel',
        'export_pdf',
        'export_excel',
        'prestasi',
        'prestasi_tambah',
        'prestasi_simpan',
        'prestasi_detail',
        'prestasi_hapus',
        'prestasi_ubah',
        'manajemen_user', 
        'pembayaran', 
        'pembayaran_hapus', 
        'ubahdataspp', 
        'petugas', 
        'petugas_tambah', 
        'petugas_ubah', 
        'petugas_hapus', 
        'pengumuman', 
        'profil', 
        'pengaturan'
    ]
];

if (in_array($user_role, ['admin'])) {
    $user_allowed_pages = $allowed_pages['admin'];
} else {
    $user_allowed_pages = $allowed_pages[$user_role] ?? $allowed_pages['siswa'];
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistem Manajemen Ekstrakurikuler">
    <meta name="author" content="SIMABANG">
    <meta name="keywords" content="ekstrakurikuler, siswa, pembina, sekolah">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="shortcut icon" href="img/icons/icon-48x48.png" />
    <title>Sistem Ekstrakurikuler - <?php echo getRoleDisplay($user_role); ?></title>

    <link rel="stylesheet" href="assets/extensions/simple-datatables/style.css" />
    <link rel="stylesheet" href="./assets/compiled/css/table-datatable.css" />
    <link href="css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Dropdown Menu Style */
        .sidebar-item.has-dropdown > .sidebar-link {
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .sidebar-item.has-dropdown > .sidebar-link::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            transition: transform 0.3s ease;
            margin-left: auto;
        }
        
        .sidebar-item.has-dropdown.active > .sidebar-link::after {
            transform: rotate(180deg);
        }
        
        .sidebar-dropdown {
            display: none;
            padding-left: 0;
            list-style: none;
            background: rgba(0,0,0,0.05);
            margin: 0;
        }
        
        .sidebar-dropdown.show {
            display: block;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .sidebar-dropdown .sidebar-item {
            padding-left: 0;
        }
        
        .sidebar-dropdown .sidebar-link {
            padding: 0.625rem 1.625rem 0.625rem 3.5rem;
            font-size: 0.875rem;
        }
        
        .sidebar-dropdown .sidebar-link:hover {
            background: rgba(0,0,0,0.08);
        }
        
        .sidebar-dropdown .sidebar-item.active .sidebar-link {
            background: rgba(0,0,0,0.1);
            font-weight: 600;
        }
        
        /* Notification Badge */
        .indicator {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        /* Avatar Style */
        .avatar {
            width: 32px;
            height: 32px;
            object-fit: cover;
        }
        
        /* Alert Custom */
        .alert {
            border-radius: 8px;
            border-left: 4px solid;
        }
        
        .alert-warning {
            border-left-color: #ffc107;
        }
        
        .alert-danger {
            border-left-color: #dc3545;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <nav id="sidebar" class="sidebar js-sidebar">
            <div class="sidebar-content js-simplebar">
                <a class="sidebar-brand" href="?">
                    <span class="align-middle">Ekstrakurikuler</span>
                </a>

                <ul class="sidebar-nav">
                    <?php
                    $last_header = '';
                    foreach ($current_menu as $page => $config) {
                        // Tampilkan header
                        if (isset($config['header']) && $config['header'] !== $last_header) {
                            echo '<li class="sidebar-header">' . htmlspecialchars($config['header']) . '</li>';
                            $last_header = $config['header'];
                        }
                        
                        // Cek dropdown menu
                        if (isset($config['type']) && $config['type'] === 'dropdown') {
                            $is_submenu_active = false;
                            foreach ($config['submenu'] as $subpage => $subconfig) {
                                if ($current_page == $subpage) {
                                    $is_submenu_active = true;
                                    break;
                                }
                            }
                            $dropdown_active = $is_submenu_active ? 'active' : '';
                            
                            echo '<li class="sidebar-item has-dropdown ' . $dropdown_active . '">
                                    <a class="sidebar-link" onclick="toggleDropdown(this)">
                                        <i class="align-middle" data-feather="' . htmlspecialchars($config['icon']) . '"></i> 
                                        <span class="align-middle">' . htmlspecialchars($config['label']) . '</span>
                                    </a>
                                    <ul class="sidebar-dropdown ' . ($is_submenu_active ? 'show' : '') . '">';
                            
                            foreach ($config['submenu'] as $subpage => $subconfig) {
                                $is_active = ($current_page == $subpage) ? 'active' : '';
                                echo '<li class="sidebar-item ' . $is_active . '">
                                        <a class="sidebar-link" href="?page=' . htmlspecialchars($subpage) . '">
                                            <span class="align-middle">' . htmlspecialchars($subconfig['label']) . '</span>
                                        </a>
                                      </li>';
                            }
                            
                            echo '    </ul>
                                  </li>';
                        } else {
                            // Menu biasa
                            $is_active = ($current_page == $page || ($current_page == '' && $page == 'dashboard')) ? 'active' : '';
                            
                            echo '<li class="sidebar-item ' . $is_active . '">
                                    <a class="sidebar-link" href="?page=' . htmlspecialchars($page) . '">
                                        <i class="align-middle" data-feather="' . htmlspecialchars($config['icon']) . '"></i> 
                                        <span class="align-middle">' . htmlspecialchars($config['label']) . '</span>
                                    </a>
                                  </li>';
                        }
                    }
                    ?>
                    
                    
                </ul>
            </div>
        </nav>

        <div class="main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <a class="sidebar-toggle js-sidebar-toggle">
                    <i class="hamburger align-self-center"></i>
                </a>

                <div class="navbar-collapse collapse">
                    <ul class="navbar-nav navbar-align">
                        <?php if ($user_role == 'siswa'): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-icon dropdown-toggle" href="#" id="alertsDropdown" data-bs-toggle="dropdown">
                                <div class="position-relative">
                                    <i class="align-middle" data-feather="bell"></i>
                                    <span class="indicator">2</span>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0" aria-labelledby="alertsDropdown">
                                <div class="dropdown-menu-header">2 Notifikasi Baru</div>
                                <div class="list-group">
                                    <a href="#" class="list-group-item">
                                        <div class="row g-0 align-items-center">
                                            <div class="col-2">
                                                <i class="text-info" data-feather="calendar"></i>
                                            </div>
                                            <div class="col-10">
                                                <div class="text-dark">Jadwal latihan baru</div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="dropdown-menu-footer">
                                    <a href="?page=pengumuman" class="text-muted">Lihat semua notifikasi</a>
                                </div>
                            </div>
                        </li>
                        <?php endif; ?>

                        <li class="nav-item">
    <a class="nav-link text-danger" href="logout.php" onclick="return confirm('Apakah Anda yakin ingin logout?')">
        <i class="align-middle" data-feather="log-out"></i> Logout
    </a>
</li>

                    </ul>
                </div>
            </nav>

            <main class="content">
                <div class="container-fluid p-0">
                    <?php
                    $page = $_GET['page'] ?? 'dashboard';
                    
                    if (in_array($page, $user_allowed_pages)) {
                        $file_path = $page . '.php';
                        if (file_exists($file_path)) {
                            include $file_path;
                        } else {
                            echo "<div class='alert alert-warning'>
                                    <h5><i class='fas fa-exclamation-triangle'></i> Halaman Sedang Dikembangkan</h5>
                                    <p class='mb-0'>Halaman <strong>" . htmlspecialchars($page) . ".php</strong> tidak ditemukan atau sedang dalam tahap pengembangan.</p>
                                  </div>";
                        }
                    } else {
                        echo "<div class='alert alert-danger'>
                                <h5><i class='fas fa-ban'></i> Akses Ditolak</h5>
                                <p class='mb-0'>Anda tidak memiliki akses ke halaman ini. Role Anda: <strong>" . htmlspecialchars(getRoleDisplay($user_role)) . "</strong></p>
                              </div>";
                    }
                    ?>
                </div>
            </main>

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row text-muted">
                        <div class="col-6 text-start">
                            <p class="mb-0">
                                <a class="text-muted" href="#" target="_blank">
                                    <strong><?php echo ($user_role == 'siswa') ? 'Portal Siswa' : 'Admin Panel'; ?> - Sistem Ekstrakurikuler</strong>
                                </a> &copy; <?php echo date('Y'); ?>
                            </p>
                        </div>
                        <div class="col-6 text-end">
                            <p class="mb-0">
                                Selamat datang, <strong><?php echo htmlspecialchars($user['nama_siswa'] ?? $user['username']); ?></strong> 
                                <span class="badge bg-<?php echo getRoleBadge($user_role); ?>">
                                    <?php echo getRoleDisplay($user_role); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    function toggleDropdown(element) {
        const parentLi = element.closest('.sidebar-item');
        const dropdown = parentLi.querySelector('.sidebar-dropdown');
        
        parentLi.classList.toggle('active');
        dropdown.classList.toggle('show');
    }

    let inactivityTime = function () {
        let time;
        const timeout = <?php echo ($user_role == 'siswa') ? '2700000' : '1800000'; ?>;
        
        function resetTimer() {
            clearTimeout(time);
            time = setTimeout(logout, timeout);
        }
        
        function logout() {
            Swal.fire({
                title: 'Sesi Berakhir',
                text: 'Sesi Anda telah berakhir karena tidak aktif.',
                icon: 'warning',
                confirmButtonText: 'Login Kembali',
                allowOutsideClick: false
            }).then(() => {
                window.location.href = 'logout.php';
            });
        }
        
        window.onload = resetTimer;
        document.onmousemove = resetTimer;
        document.onkeypress = resetTimer;
        document.onclick = resetTimer;
        document.onscroll = resetTimer;
    };

    inactivityTime();
    </script>
</body>
</html>