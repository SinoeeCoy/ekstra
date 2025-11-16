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
$filter_gender = isset($_GET['filter_gender']) ? $_GET['filter_gender'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$where_clause = "";
$conditions = [];

if (!empty($filter_ekstra)) {
    $filter_ekstra_escaped = mysqli_real_escape_string($koneksi, $filter_ekstra);
    // Cari di kolom ekstra_id dengan FIND_IN_SET untuk mencocokkan ID yang dipisah koma
    $conditions[] = "FIND_IN_SET('$filter_ekstra_escaped', ds.ekstra_id) > 0";
}

if (!empty($filter_jurusan)) {
    $filter_jurusan_escaped = mysqli_real_escape_string($koneksi, $filter_jurusan);
    $conditions[] = "ds.jurusan = '$filter_jurusan_escaped'";
}

if (!empty($filter_gender)) {
    $filter_gender_escaped = mysqli_real_escape_string($koneksi, $filter_gender);
    $conditions[] = "ds.jenis_kelamin = '$filter_gender_escaped'";
}

if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($koneksi, $search);
    $conditions[] = "(ds.nama_siswa LIKE '%$search_escaped%' OR ds.nis LIKE '%$search_escaped%')";
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
        
        // Ekstra terpopuler - hitung dari ekstra_id yang paling sering muncul
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
                                   placeholder="Cari nama atau NIS..." 
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
                        <label for="filterEkstra" class="form-label">Filter Ekstrakurikuler:</label>
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
                    
                    <!-- Filter Jurusan -->
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label for="filterJurusan" class="form-label">Filter Jurusan:</label>
                        <select id="filterJurusan" class="form-select" onchange="filterData()">
                            <option value="">Semua Jurusan</option>
                            <option value="PPLG" <?php echo ($filter_jurusan == 'PPLG') ? 'selected' : ''; ?>>PPLG</option>
                            <option value="TJKT" <?php echo ($filter_jurusan == 'TJKT') ? 'selected' : ''; ?>>TJKT</option>
                            <option value="BUSANA" <?php echo ($filter_jurusan == 'BUSANA') ? 'selected' : ''; ?>>BUSANA</option>
                        </select>
                    </div>
                    
                    <!-- Filter Gender -->
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label for="filterGender" class="form-label">Filter Jenis Kelamin:</label>
                        <select id="filterGender" class="form-select" onchange="filterData()">
                            <option value="">Semua</option>
                            <option value="Laki-laki" <?php echo ($filter_gender == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="Perempuan" <?php echo ($filter_gender == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                    
                    <!-- Export & Reset -->
                    <div class="col-lg-3 col-md-12 mb-2">
                        <label class="form-label d-none d-lg-block">&nbsp;</label>
                        <div class="d-flex gap-1">
                            <?php if (!empty($filter_ekstra) || !empty($filter_jurusan) || !empty($filter_gender) || !empty($search)): ?>
                            <button class="btn btn-secondary btn-sm" onclick="resetFilter()" title="Reset semua filter">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tabel Data Siswa -->
                <div class="table-responsive">
                    <table id="siswaTable" class="table table-striped table-bordered table-hover">
                        <thead class="table-blue">
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Jenis Kelamin</th>
                                <th>Kelas</th>
                                <th>Jurusan</th>
                                <th>Ekstrakurikuler</th>
                                <th>No. HP</th>
                                <th>Tanggal Daftar</th>
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
                                echo '<tr><td colspan="10" class="text-center text-muted py-4">';
                                echo '<i class="fas fa-inbox fa-3x mb-3 d-block"></i>';
                                echo '<h5>Tidak ada data siswa</h5>';
                                echo '</td></tr>';
                            }
                            
                            while ($data = mysqli_fetch_array($query)) {
                                // Badge warna jurusan
                                $badge_jurusan_color = 'secondary';
                                if ($data['jurusan'] == 'PPLG') $badge_jurusan_color = 'primary';
                                elseif ($data['jurusan'] == 'TJKT') $badge_jurusan_color = 'success';
                                elseif ($data['jurusan'] == 'BUSANA') $badge_jurusan_color = 'warning';
                                
                                // Ambil nama ekstra dari ID yang tersimpan
                                $ekstra_names = [];
                                if (!empty($data['ekstra_id'])) {
                                    $ekstra_ids = explode(',', $data['ekstra_id']);
                                    foreach ($ekstra_ids as $eid) {
                                        $eid = trim($eid);
                                        $ekstra_detail = mysqli_query($koneksi, "SELECT nama_ekstra FROM ekstra WHERE id = '$eid'");
                                        if ($ekstra_row = mysqli_fetch_array($ekstra_detail)) {
                                            $ekstra_names[] = $ekstra_row['nama_ekstra'];
                                        }
                                    }
                                }
                            ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><strong><?php echo htmlspecialchars($data['nis']); ?></strong></td>
                                <td><?php echo htmlspecialchars($data['nama_siswa']); ?></td>
                                <td class="text-center">
                                    <?php if ($data['jenis_kelamin'] == 'Laki-laki'): ?>
                                        <i class="fas fa-mars gender-icon text-primary"></i>
                                        <span class="d-none d-md-inline">Laki-laki</span>
                                    <?php else: ?>
                                        <i class="fas fa-venus gender-icon text-danger"></i>
                                        <span class="d-none d-md-inline">Perempuan</span>
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
                                <td>
                                    <?php foreach ($ekstra_names as $nama_ekstra): ?>
                                        <span class="badge badge-ekstra bg-info text-dark">
                                            <?php echo htmlspecialchars($nama_ekstra); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <a href="tel:<?php echo htmlspecialchars($data['no_hp']); ?>" class="text-decoration-none">
                                        <i class="fas fa-phone text-success"></i> <?php echo htmlspecialchars($data['no_hp']); ?>
                                    </a>
                                </td>
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
                                                data-bs-toggle="modal" 
                                                data-bs-target="#detailModal<?php echo $data['id']; ?>"
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
    var jurusan = document.getElementById('filterJurusan').value;
    var gender = document.getElementById('filterGender').value;
    var search = document.getElementById('searchInput').value;
    
    var url = '?page=siswa';
    if (ekstra) url += '&filter_ekstra=' + encodeURIComponent(ekstra);
    if (jurusan) url += '&filter_jurusan=' + encodeURIComponent(jurusan);
    if (gender) url += '&filter_gender=' + encodeURIComponent(gender);
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

document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        doSearch();
    }
});
</script>

<!-- Modal Detail Siswa -->
<?php
mysqli_data_seek($query, 0);
while ($data = mysqli_fetch_array($query)) {
    // Ambil detail ekstra
    $ekstra_details = [];
    if (!empty($data['ekstra_id'])) {
        $ekstra_ids = explode(',', $data['ekstra_id']);
        foreach ($ekstra_ids as $eid) {
            $eid = trim($eid);
            $ekstra_q = mysqli_query($koneksi, "SELECT * FROM ekstra WHERE id = '$eid'");
            if ($ekstra_r = mysqli_fetch_array($ekstra_q)) {
                $ekstra_details[] = $ekstra_r;
            }
        }
    }
?>
<div class="modal fade" id="detailModal<?php echo $data['id']; ?>" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user"></i> Detail Siswa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2"><i class="fas fa-id-card text-primary"></i> Data Pribadi</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%"><strong>Nama</strong></td>
                                <td>: <?php echo htmlspecialchars($data['nama_siswa']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>NIS</strong></td>
                                <td>: <?php echo htmlspecialchars($data['nis']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Jenis Kelamin</strong></td>
                                <td>: <?php 
                                if ($data['jenis_kelamin'] == 'Laki-laki') {
                                    echo '<i class="fas fa-mars text-primary"></i> Laki-laki';
                                } else {
                                    echo '<i class="fas fa-venus text-danger"></i> Perempuan';
                                }
                                ?></td>
                            </tr>
                            <tr>
                                <td><strong>Kelas</strong></td>
                                <td>: <span class="badge bg-primary"><?php echo htmlspecialchars($data['kelas']); ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>Jurusan</strong></td>
                                <td>: <?php 
                                $badge_color = 'secondary';
                                if ($data['jurusan'] == 'PPLG') $badge_color = 'primary';
                                elseif ($data['jurusan'] == 'TJKT') $badge_color = 'success';
                                elseif ($data['jurusan'] == 'BUSANA') $badge_color = 'warning';
                                echo '<span class="badge bg-' . $badge_color . '">' . htmlspecialchars($data['jurusan']) . '</span>';
                                ?></td>
                            </tr>
                            <tr>
                                <td><strong>No. HP</strong></td>
                                <td>: <?php echo htmlspecialchars($data['no_hp']); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2"><i class="fas fa-running text-success"></i> Data Ekstrakurikuler</h6>
                        <?php foreach ($ekstra_details as $ekstra_det): ?>
                        <div class="mb-3">
                            <strong><span class="badge bg-info text-dark"><?php echo htmlspecialchars($ekstra_det['nama_ekstra']); ?></span></strong>
                            <ul class="list-unstyled ms-3 mt-1">
                                <li><small><i class="fas fa-user text-muted"></i> <?php echo htmlspecialchars($ekstra_det['pembina']); ?></small></li>
                                <li><small><i class="fas fa-calendar text-muted"></i> <?php echo htmlspecialchars($ekstra_det['hari']); ?>, <?php echo htmlspecialchars($ekstra_det['waktu']); ?></small></li>
                                <li><small><i class="fas fa-map-marker-alt text-muted"></i> <?php echo htmlspecialchars($ekstra_det['lokasi']); ?></small></li>
                            </ul>
                        </div>
                        <?php endforeach; ?>
                        <div class="mt-3">
                            <small><strong>Tanggal Daftar:</strong> <?php echo date('d F Y, H:i', strtotime($data['tanggal_daftar'])); ?></small>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <h6 class="border-bottom pb-2"><i class="fas fa-map-marked-alt text-danger"></i> Alamat Rumah</h6>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($data['alamat'])); ?></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>
<?php } ?>