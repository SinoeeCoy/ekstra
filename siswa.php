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

// Cek role user
$user_role = strtolower($user['role'] ?? '');
$is_siswa = ($user_role === 'siswa');
$can_edit = in_array($user_role, ['pembina', 'admin', 'waka', 'kepala']);

// Jika siswa, ambil user_id untuk filter data
$user_id = $user['id'] ?? 0;

// Proses hapus (hanya untuk yang memiliki akses)
if ($can_edit && isset($_GET['hapus']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $delete_query = mysqli_query($koneksi, "DELETE FROM data_siswa WHERE id = '$id'");
    
    if ($delete_query) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data siswa berhasil dihapus',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = '?page=siswa';
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Gagal menghapus data siswa'
            });
        </script>";
    }
}

// Ambil parameter filter dan search
$filter_ekstra = isset($_GET['filter_ekstra']) ? $_GET['filter_ekstra'] : '';
$filter_jurusan = isset($_GET['filter_jurusan']) ? $_GET['filter_jurusan'] : '';
$filter_kelas = isset($_GET['filter_kelas']) ? $_GET['filter_kelas'] : '';
$filter_gender = isset($_GET['filter_gender']) ? $_GET['filter_gender'] : '';
$filter_agama = isset($_GET['filter_agama']) ? $_GET['filter_agama'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$where_clause = "";
$conditions = [];

if (!empty($filter_ekstra)) {
    $filter_ekstra_escaped = mysqli_real_escape_string($koneksi, $filter_ekstra);
    $conditions[] = "FIND_IN_SET('$filter_ekstra_escaped', ds.ekstra_id) > 0";
}

if (!empty($filter_jurusan)) {
    $filter_jurusan_escaped = mysqli_real_escape_string($koneksi, $filter_jurusan);
    $conditions[] = "ds.jurusan = '$filter_jurusan_escaped'";
}

if (!empty($filter_kelas)) {
    $filter_kelas_escaped = mysqli_real_escape_string($koneksi, $filter_kelas);
    $conditions[] = "ds.kelas = '$filter_kelas_escaped'";
}

if (!empty($filter_gender)) {
    $filter_gender_escaped = mysqli_real_escape_string($koneksi, $filter_gender);
    $conditions[] = "ds.jenis_kelamin = '$filter_gender_escaped'";
}

if (!empty($filter_agama)) {
    $filter_agama_escaped = mysqli_real_escape_string($koneksi, $filter_agama);
    $conditions[] = "ds.agama = '$filter_agama_escaped'";
}

if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($koneksi, $search);
    // FIXED: Hapus pencarian 'nis', hanya gunakan 'nisn' dan 'nama_siswa'
    $conditions[] = "(ds.nama_siswa LIKE '%$search_escaped%' OR ds.nisn LIKE '%$search_escaped%')";
}

if (count($conditions) > 0) {
    $where_clause = "WHERE " . implode(" AND ", $conditions);
}

?>

<style>
    .stats-card {
        border-left: 4px solid #007bff;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    .table-container {
        background: white;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        padding: 20px;
    }
    .badge-ekstra {
        font-size: 0.85em;
        margin: 2px;
    }
    .badge-jurusan {
        font-size: 0.85em;
        font-weight: 600;
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
    .gender-icon {
        font-size: 1.1em;
    }
    
    /* Styling untuk tombol aksi */
    .btn-group .btn {
        transition: all 0.3s ease;
    }
    .btn-group .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
</style>

<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h3 class="mb-0">
                                <i class="fas fa-users text-primary"></i> Data Siswa Ekstrakurikuler
                            </h3>
                            <p class="text-muted mb-0">
                                <?php echo $is_siswa ? 'Lihat data siswa yang terdaftar dalam ekstrakurikuler' : 'Kelola data siswa yang terdaftar dalam ekstrakurikuler'; ?>
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <?php if ($can_edit): ?>
                            <a href="home.php?page=siswa_tambah_manual" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Siswa Manual
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <?php
        $total_siswa = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM data_siswa"));
        $total_ekstra = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM ekstra"));
        $siswa_hari_ini = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM data_siswa WHERE DATE(tanggal_daftar) = CURDATE()"));
        
        // Statistik jenis kelamin
        $total_laki = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM data_siswa WHERE jenis_kelamin = 'Laki-laki'"));
        $total_perempuan = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM data_siswa WHERE jenis_kelamin = 'Perempuan'"));
        
        // Ekstra terpopuler
        $popular_query = mysqli_query($koneksi, "
            SELECT e.id, e.nama_ekstra, COUNT(*) as jumlah 
            FROM data_siswa ds
            JOIN ekstra e ON FIND_IN_SET(e.id, ds.ekstra_id) > 0
            GROUP BY e.id, e.nama_ekstra
            ORDER BY jumlah DESC 
            LIMIT 1
        ");
        $popular_data = mysqli_fetch_array($popular_query);
        $ekstra_popular = $popular_data ? $popular_data['nama_ekstra'] . " (" . $popular_data['jumlah'] . ")" : "Belum ada data";
        ?>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stats-card border-0 h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <p class="text-muted mb-1">Total Siswa</p>
                            <h3 class="mb-0"><?php echo $total_siswa; ?></h3>
                            <small class="text-muted">
                                <i class="fas fa-mars text-primary"></i> <?php echo $total_laki; ?> | 
                                <i class="fas fa-venus text-danger"></i> <?php echo $total_perempuan; ?>
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-primary"></i>
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
                            <p class="text-muted mb-1">Total Ekstrakurikuler</p>
                            <h3 class="mb-0"><?php echo $total_ekstra; ?></h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-success"></i>
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
                            <p class="text-muted mb-1">Pendaftar Hari Ini</p>
                            <h3 class="mb-0"><?php echo $siswa_hari_ini; ?></h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-warning"></i>
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
                            <p class="text-muted mb-1">Ekstra Terpopuler</p>
                            <small class="mb-0"><?php echo $ekstra_popular; ?></small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter dan Tabel -->
    <div class="row">
        <div class="col-12">
            <div class="table-container">
                <!-- Search Box dan Filter -->
                <div class="row mb-3 align-items-end">
                    <!-- Search Box -->
                    <div class="col-lg-3 col-md-12 mb-2">
                        <label for="searchInput" class="form-label">Cari Siswa:</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" 
                                   id="searchInput" 
                                   class="form-control" 
                                   placeholder="Cari nama atau NISN..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" type="button" onclick="doSearch()">
                                Cari
                            </button>
                            <?php if (!empty($search)): ?>
                            <button class="btn btn-secondary" type="button" onclick="clearSearch()" title="Hapus pencarian">
                                <i class="fas fa-times"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Filter Ekstrakurikuler -->
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label for="filterEkstra" class="form-label">Filter Ekstra:</label>
                        <select id="filterEkstra" class="form-select" onchange="filterData()">
                            <option value="">Semua Ekstra</option>
                            <?php
                            $ekstra_query = mysqli_query($koneksi, "SELECT id, nama_ekstra FROM ekstra ORDER BY nama_ekstra");
                            while ($ekstra = mysqli_fetch_array($ekstra_query)) {
                                $selected = ($filter_ekstra == $ekstra['id']) ? 'selected' : '';
                                echo "<option value='" . $ekstra['id'] . "' $selected>" . htmlspecialchars($ekstra['nama_ekstra']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <!-- Filter Kelas -->
                    <div class="col-lg-1 col-md-4 mb-2">
                        <label for="filterKelas" class="form-label">Kelas:</label>
                        <select id="filterKelas" class="form-select" onchange="filterData()">
                            <option value="">Semua</option>
                            <option value="X" <?php echo ($filter_kelas == 'X') ? 'selected' : ''; ?>>X</option>
                            <option value="XI" <?php echo ($filter_kelas == 'XI') ? 'selected' : ''; ?>>XI</option>
                            <option value="XII" <?php echo ($filter_kelas == 'XII') ? 'selected' : ''; ?>>XII</option>
                        </select>
                    </div>
                    
                    <!-- Filter Jurusan -->
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label for="filterJurusan" class="form-label">Jurusan:</label>
                        <select id="filterJurusan" class="form-select" onchange="filterData()">
                            <option value="">Semua</option>
                            <option value="PPLG" <?php echo ($filter_jurusan == 'PPLG') ? 'selected' : ''; ?>>PPLG</option>
                            <option value="TJKT" <?php echo ($filter_jurusan == 'TJKT') ? 'selected' : ''; ?>>TJKT</option>
                            <option value="BUSANA" <?php echo ($filter_jurusan == 'BUSANA') ? 'selected' : ''; ?>>BUSANA</option>
                        </select>
                    </div>
                    
                    <!-- Filter Gender -->
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label for="filterGender" class="form-label">Gender:</label>
                        <select id="filterGender" class="form-select" onchange="filterData()">
                            <option value="">Semua</option>
                            <option value="Laki-laki" <?php echo ($filter_gender == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="Perempuan" <?php echo ($filter_gender == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                    
                    <!-- Filter Agama -->
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label for="filterAgama" class="form-label">Agama:</label>
                        <select id="filterAgama" class="form-select" onchange="filterData()">
                            <option value="">Semua</option>
                            <option value="Islam" <?php echo ($filter_agama == 'Islam') ? 'selected' : ''; ?>>Islam</option>
                            <option value="Kristen" <?php echo ($filter_agama == 'Kristen') ? 'selected' : ''; ?>>Kristen</option>
                            <option value="Katolik" <?php echo ($filter_agama == 'Katolik') ? 'selected' : ''; ?>>Katolik</option>
                            <option value="Hindu" <?php echo ($filter_agama == 'Hindu') ? 'selected' : ''; ?>>Hindu</option>
                            <option value="Buddha" <?php echo ($filter_agama == 'Buddha') ? 'selected' : ''; ?>>Buddha</option>
                            <option value="Konghucu" <?php echo ($filter_agama == 'Konghucu') ? 'selected' : ''; ?>>Konghucu</option>
                        </select>
                    </div>
                </div>

                <!-- Reset Button -->
                <?php if (!empty($filter_ekstra) || !empty($filter_jurusan) || !empty($filter_kelas) || !empty($filter_gender) || !empty($filter_agama) || !empty($search)): ?>
                <div class="row mb-3">
                    <div class="col-12">
                        <button class="btn btn-secondary btn-sm" onclick="resetFilter()">
                            <i class="fas fa-redo"></i> Reset Semua Filter
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tabel Data Siswa -->
                <div class="table-responsive">
                    <table id="siswaTable" class="table table-striped table-bordered table-hover">
                        <thead class="table-blue">
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>NISN</th>
                                <th>TTL</th>
                                <th>JK</th>
                                <th>Kelas</th>
                                <th>Jurusan</th>
                                <th>Agama</th>
                                <th>Ekstrakurikuler</th>
                                <?php if ($can_edit): ?>
                                <th>No. HP</th>
                                <?php endif; ?>
                                <th>Tgl Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            $query = mysqli_query($koneksi, "
                                SELECT ds.* 
                                FROM data_siswa ds 
                                $where_clause 
                                ORDER BY ds.tanggal_daftar DESC
                            ");
                            
                            if (mysqli_num_rows($query) == 0) {
                                $colspan = $can_edit ? 12 : 11;
                                echo '<tr><td colspan="' . $colspan . '" class="text-center text-muted py-4">';
                                echo '<i class="fas fa-inbox fa-3x mb-3 d-block"></i>';
                                if ($is_siswa) {
                                    echo '<h5>Anda belum terdaftar dalam ekstrakurikuler</h5>';
                                } else {
                                    echo '<h5>Tidak ada data siswa</h5>';
                                }
                                echo '</td></tr>';
                            }
                            
                            while ($data = mysqli_fetch_array($query)) {
                                // Badge warna jurusan
                                $badge_jurusan_color = 'secondary';
                                if ($data['jurusan'] == 'PPLG') $badge_jurusan_color = 'primary';
                                elseif ($data['jurusan'] == 'TJKT') $badge_jurusan_color = 'success';
                                elseif ($data['jurusan'] == 'BUSANA') $badge_jurusan_color = 'warning';
                                
                                // Format TTL
                                $ttl = '';
                                if (!empty($data['tempat_lahir']) && !empty($data['tanggal_lahir'])) {
                                    $tgl = new DateTime($data['tanggal_lahir']);
                                    $ttl = htmlspecialchars($data['tempat_lahir']) . ', ' . $tgl->format('d/m/Y');
                                }
                                
                                // Ambil detail ekstra
                                $ekstra_names = [];
                                $ekstra_json = [];
                                if (!empty($data['ekstra_id'])) {
                                    $ekstra_ids = explode(',', $data['ekstra_id']);
                                    foreach ($ekstra_ids as $eid) {
                                        $eid = trim($eid);
                                        $ekstra_q = mysqli_query($koneksi, "SELECT * FROM ekstra WHERE id = '$eid'");
                                        if ($ekstra_r = mysqli_fetch_array($ekstra_q)) {
                                            $ekstra_names[] = $ekstra_r['nama_ekstra'];
                                            $ekstra_json[] = [
                                                'nama' => $ekstra_r['nama_ekstra'],
                                                'pembina' => $ekstra_r['pembina'],
                                                'hari' => $ekstra_r['hari'],
                                                'waktu' => $ekstra_r['waktu'],
                                                'lokasi' => $ekstra_r['lokasi']
                                            ];
                                        }
                                    }
                                }
                                $ekstra_json_string = htmlspecialchars(json_encode($ekstra_json), ENT_QUOTES, 'UTF-8');
                                $tanggal_format = date('d F Y, H:i', strtotime($data['tanggal_daftar']));
                            ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><?php echo htmlspecialchars($data['nama_siswa']); ?></td>
                                <td><strong><?php echo htmlspecialchars($data['nisn'] ?? '-'); ?></strong></td>
                                <td><small><?php echo $ttl; ?></small></td>
                                <td class="text-center">
                                    <?php if ($data['jenis_kelamin'] == 'Laki-laki'): ?>
                                        <i class="fas fa-mars gender-icon text-primary" title="Laki-laki"></i>
                                    <?php else: ?>
                                        <i class="fas fa-venus gender-icon text-danger" title="Perempuan"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($data['kelas']); ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-jurusan bg-<?php echo $badge_jurusan_color; ?>">
                                        <?php echo htmlspecialchars($data['jurusan']); ?>
                                    </span>
                                </td>
                                <td><small><?php echo htmlspecialchars($data['agama'] ?? '-'); ?></small></td>
                                <td>
                                    <?php foreach ($ekstra_names as $nama_ekstra): ?>
                                        <span class="badge badge-ekstra bg-info text-dark">
                                            <?php echo htmlspecialchars($nama_ekstra); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </td>
                                <?php if ($can_edit): ?>
                                <td>
                                    <?php if (!empty($data['no_hp'])): ?>
                                    <a href="tel:<?php echo htmlspecialchars($data['no_hp']); ?>" class="text-decoration-none">
                                        <i class="fas fa-phone text-success"></i> 
                                        <small><?php echo htmlspecialchars($data['no_hp']); ?></small>
                                    </a>
                                    <?php else: ?>
                                    <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                                <td>
                                    <small>
                                        <?php 
                                        $tanggal = new DateTime($data['tanggal_daftar']);
                                        echo $tanggal->format('d/m/Y H:i');
                                        ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-info btn-sm" 
                                                onclick='showDetail(<?php echo json_encode([
                                                    'id' => $data['id'],
                                                    'nama' => $data['nama_siswa'],
                                                    'nisn' => $data['nisn'] ?? '',
                                                    'gender' => $data['jenis_kelamin'],
                                                    'tempat_lahir' => $data['tempat_lahir'] ?? '',
                                                    'tanggal_lahir' => $data['tanggal_lahir'] ?? '',
                                                    'agama' => $data['agama'] ?? '',
                                                    'kelas' => $data['kelas'],
                                                    'jurusan' => $data['jurusan'],
                                                    'no_hp' => $data['no_hp'] ?? '',
                                                    'tanggal_daftar' => $tanggal_format,
                                                    'ekstra' => $ekstra_json,
                                                    'is_siswa' => $is_siswa,
                                                    'can_edit' => $can_edit
                                                ]); ?>)'
                                                title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if ($can_edit): ?>
                                        <a href="?page=siswa_edit&id=<?php echo $data['id']; ?>" 
                                           class="btn btn-warning btn-sm"
                                           title="Edit Data">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                onclick="confirmDelete(<?php echo $data['id']; ?>, '<?php echo addslashes($data['nama_siswa']); ?>')"
                                                title="Hapus Data">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                $i++;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterData() {
    var ekstra = document.getElementById('filterEkstra').value;
    var kelas = document.getElementById('filterKelas').value;
    var jurusan = document.getElementById('filterJurusan').value;
    var gender = document.getElementById('filterGender').value;
    var agama = document.getElementById('filterAgama').value;
    var search = document.getElementById('searchInput').value;
    
    var url = '?page=siswa';
    if (ekstra) url += '&filter_ekstra=' + encodeURIComponent(ekstra);
    if (kelas) url += '&filter_kelas=' + encodeURIComponent(kelas);
    if (jurusan) url += '&filter_jurusan=' + encodeURIComponent(jurusan);
    if (gender) url += '&filter_gender=' + encodeURIComponent(gender);
    if (agama) url += '&filter_agama=' + encodeURIComponent(agama);
    if (search) url += '&search=' + encodeURIComponent(search);
    
    window.location.href = url;
}

function doSearch() {
    filterData();
}

function resetFilter() {
    window.location.href = '?page=siswa';
}

function clearSearch() {
    document.getElementById('searchInput').value = '';
    filterData();
}

function confirmDelete(id, nama) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        html: `Apakah Anda yakin ingin menghapus data siswa:<br><strong>${nama}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
        cancelButtonText: '<i class="fas fa-times"></i> Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '?page=siswa&hapus=true&id=' + id;
        }
    });
}

function showDetail(data) {
    // Icon gender
    const genderIcon = data.gender === 'Laki-laki' 
        ? '<i class="fas fa-mars text-primary"></i>' 
        : '<i class="fas fa-venus text-danger"></i>';
    
    // Badge jurusan color
    let badgeColor = 'secondary';
    if (data.jurusan === 'PPLG') badgeColor = 'primary';
    else if (data.jurusan === 'TJKT') badgeColor = 'success';
    else if (data.jurusan === 'BUSANA') badgeColor = 'warning';
    
    // Format TTL - FIXED
    let ttl = '-';
    let tempatLahir = data.tempat_lahir || '';
    let tanggalLahir = data.tanggal_lahir || '';
    
    if (tempatLahir && tanggalLahir) {
        try {
            const tgl = new Date(tanggalLahir);
            if (!isNaN(tgl.getTime())) {
                const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                const hari = tgl.getDate();
                const bulanNama = bulan[tgl.getMonth()];
                const tahun = tgl.getFullYear();
                ttl = `${tempatLahir}, ${hari} ${bulanNama} ${tahun}`;
            }
        } catch (e) {
            console.error('Error parsing date:', e);
        }
    }
    
    // Format ekstrakurikuler - hanya nama ekstra saja
    let ekstraHTML = '';
    if (data.ekstra && data.ekstra.length > 0) {
        ekstraHTML = '<div class="d-flex flex-wrap gap-2">' + data.ekstra.map(e => `
            <span class="badge bg-info text-dark px-3 py-2" style="font-size: 0.9rem;">${e.nama}</span>
        `).join('') + '</div>';
    } else {
        ekstraHTML = '<div class="alert alert-light text-center mb-0"><i class="fas fa-info-circle me-2"></i>Belum terdaftar ekstrakurikuler</div>';
    }
    
    // Build detail HTML dengan layout yang lebih baik
    let detailHTML = `
        <div class="container-fluid px-4">
            <!-- Header Info -->
            <div class="text-center mb-4 pb-3 border-bottom">
                <h4 class="mb-1 text-primary">${data.nama}</h4>
                <p class="text-muted mb-0">NISN: <strong>${data.nisn || '-'}</strong></p>
            </div>
            
            <div class="row g-4">
                <!-- Kolom Kiri: Data Pribadi -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary bg-gradient text-white">
                            <h6 class="mb-0"><i class="fas fa-id-card me-2"></i>Data Pribadi</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td width="45%" class="text-muted">Nama Lengkap</td>
                                        <td width="5%">:</td>
                                        <td><strong>${data.nama}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">NISN</td>
                                        <td>:</td>
                                        <td><strong class="text-primary">${data.nisn || '-'}</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Tempat, Tgl Lahir</td>
                                        <td>:</td>
                                        <td>${ttl}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Jenis Kelamin</td>
                                        <td>:</td>
                                        <td>${genderIcon} <span>${data.gender}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Agama</td>
                                        <td>:</td>
                                        <td>${data.agama || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Kelas</td>
                                        <td>:</td>
                                        <td><span class="badge bg-primary">${data.kelas}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Jurusan</td>
                                        <td>:</td>
                                        <td><span class="badge bg-${badgeColor}">${data.jurusan}</span></td>
                                    </tr>
                                    ${data.can_edit && data.no_hp ? `
                                    <tr>
                                        <td class="text-muted">No. HP/WA</td>
                                        <td>:</td>
                                        <td>
                                            <a href="https://wa.me/${data.no_hp.replace(/^0/, '62')}" target="_blank" class="text-decoration-none text-success">
                                                <i class="fab fa-whatsapp me-1"></i>${data.no_hp}
                                            </a>
                                        </td>
                                    </tr>
                                    ` : ''}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Kolom Kanan: Ekstrakurikuler -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-success bg-gradient text-white">
                            <h6 class="mb-0"><i class="fas fa-running me-2"></i>Ekstrakurikuler</h6>
                        </div>
                        <div class="card-body">
                            ${ekstraHTML}
                            
                            <div class="mt-4 pt-3 border-top">
                                <div class="d-flex align-items-center text-muted">
                                    <i class="far fa-calendar-check me-2"></i>
                                    <small>Tanggal Pendaftaran:</small>
                                </div>
                                <div class="mt-1">
                                    <strong class="text-dark">${data.tanggal_daftar}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    Swal.fire({
        html: detailHTML,
        width: '900px',
        showCloseButton: true,
        showConfirmButton: true,
        confirmButtonText: '<i class="fas fa-times me-2"></i>Tutup',
        confirmButtonColor: '#6c757d',
        customClass: {
            popup: 'rounded-4',
            htmlContainer: 'p-0'
        },
        didOpen: () => {
            const swalPopup = document.querySelector('.swal2-popup');
            if (swalPopup) {
                swalPopup.style.padding = '25px';
            }
        }
    });
}

document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        doSearch();
    }
});
</script>