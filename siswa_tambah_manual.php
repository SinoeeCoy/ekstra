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
$user_role = strtolower($user['role'] ?? '');
$can_edit = in_array($user_role, ['pembina', 'admin']);

if (!$can_edit) {
    header("Location: home.php?page=siswa");
    exit;
}

// Handle POST request untuk menyimpan data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $nama_siswa = mysqli_real_escape_string($koneksi, trim($_POST['nama_siswa'] ?? ''));
    $nis = mysqli_real_escape_string($koneksi, trim($_POST['nis'] ?? ''));
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin'] ?? '');
    $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas'] ?? '');
    $jurusan = mysqli_real_escape_string($koneksi, $_POST['jurusan'] ?? '');
    $nama_ekstra = mysqli_real_escape_string($koneksi, $_POST['nama_ekstra'] ?? '');
    $no_hp = mysqli_real_escape_string($koneksi, trim($_POST['no_hp'] ?? ''));
    $alamat = mysqli_real_escape_string($koneksi, trim($_POST['alamat'] ?? ''));
    
    // Validasi data
    if (empty($nama_siswa) || empty($nis) || empty($jenis_kelamin) || empty($kelas) || empty($jurusan) || empty($nama_ekstra)) {
        echo json_encode(['status' => 'error', 'message' => 'Semua field wajib diisi!']);
        exit;
    }
    
    if (strlen($nis) < 4) {
        echo json_encode(['status' => 'error', 'message' => 'NIS minimal 4 digit!']);
        exit;
    }
    
    if (!empty($no_hp) && (strlen($no_hp) < 10 || strlen($no_hp) > 13)) {
        echo json_encode(['status' => 'error', 'message' => 'Nomor HP harus 10-13 digit!']);
        exit;
    }
    
    // Cek duplikasi NIS dan ekstrakurikuler
    $check_query = "SELECT * FROM data_siswa WHERE nis = '$nis' AND nama_ekstra = '$nama_ekstra'";
    $check_result = mysqli_query($koneksi, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'NIS sudah terdaftar di ekstrakurikuler yang sama!']);
        exit;
    }
    
    // Insert data
    $insert_query = "INSERT INTO data_siswa (nama_siswa, nis, jenis_kelamin, kelas, jurusan, nama_ekstra, no_hp, alamat, tanggal_daftar) 
                     VALUES ('$nama_siswa', '$nis', '$jenis_kelamin', '$kelas', '$jurusan', '$nama_ekstra', '$no_hp', '$alamat', NOW())";
    
    if (mysqli_query($koneksi, $insert_query)) {
        echo json_encode(['status' => 'success', 'message' => 'Data siswa berhasil ditambahkan!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan data: ' . mysqli_error($koneksi)]);
    }
    exit;
}

// Ambil data ekstrakurikuler
$ekstra_query = mysqli_query($koneksi, "SELECT * FROM ekstra ORDER BY nama_ekstra");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa - Sistem Ekstrakurikuler</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        .required {
            color: #dc3545;
        }
        .info-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
            font-weight: 600;
        }
        .ekstra-list {
            max-height: 400px;
            overflow-y: auto;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 10px;
            background: #f8f9fa;
        }
        .ekstra-list::-webkit-scrollbar {
            width: 8px;
        }
        .ekstra-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .ekstra-list::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 10px;
        }
        .ekstra-item {
            padding: 12px;
            margin-bottom: 10px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        .ekstra-item:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.2);
        }
        .ekstra-item input[type="radio"]:checked ~ label {
            color: #667eea;
            font-weight: 600;
        }
        .ekstra-item:has(input[type="radio"]:checked) {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-color: #667eea;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
        }
        .sidebar-info {
            position: sticky;
            top: 20px;
        }
        .info-item {
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .badge-custom {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container mt-4 mb-5">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="info-box">
                    <h3 class="mb-3"><i class="fas fa-user-plus"></i> Tambah Siswa Baru</h3>
                    <p class="mb-0"><i class="fas fa-info-circle"></i> Lengkapi semua field yang wajib diisi (bertanda <span class="text-warning fw-bold">*</span>) untuk mendaftarkan siswa baru ke sistem ekstrakurikuler.</p>
                </div>
            </div>
        </div>

        <!-- Form Tambah Siswa -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-edit"></i> Formulir Data Siswa</h5>
                    </div>
                    <div class="card-body">
                        <form id="formTambahSiswa" method="POST">
                            <!-- Data Pribadi -->
                            <div class="mb-4">
                                <h6 class="text-primary mb-3"><i class="fas fa-id-card"></i> Data Pribadi</h6>
                                
                                <div class="mb-3">
                                    <label for="nama_siswa" class="form-label">Nama Lengkap Siswa <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="nama_siswa" name="nama_siswa" 
                                           placeholder="Masukkan nama lengkap siswa" required>
                                </div>

                                <div class="mb-3">
                                    <label for="nis" class="form-label">NIS (Nomor Induk Siswa) <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="nis" name="nis" 
                                           placeholder="Contoh: 12345" maxlength="20" required>
                                    <small class="text-muted"><i class="fas fa-info-circle"></i> Minimal 4 digit angka</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Jenis Kelamin <span class="required">*</span></label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="jenis_kelamin" 
                                                    id="laki" value="Laki-laki" required>
                                                <label class="form-check-label" for="laki">
                                                    <i class="fas fa-mars text-primary"></i> Laki-laki
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="jenis_kelamin" 
                                                    id="perempuan" value="Perempuan" required>
                                                <label class="form-check-label" for="perempuan">
                                                    <i class="fas fa-venus text-danger"></i> Perempuan
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Data Kelas -->
                            <div class="mb-4">
                                <h6 class="text-primary mb-3"><i class="fas fa-school"></i> Kelas & Jurusan</h6>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="kelas" class="form-label">Kelas <span class="required">*</span></label>
                                            <select class="form-select" id="kelas" name="kelas" required>
                                                <option value="">-- Pilih Kelas --</option>
                                                <option value="X">X (Sepuluh)</option>
                                                <option value="XI">XI (Sebelas)</option>
                                                <option value="XII">XII (Dua Belas)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="jurusan" class="form-label">Jurusan <span class="required">*</span></label>
                                            <select class="form-select" id="jurusan" name="jurusan" required>
                                                <option value="">-- Pilih Jurusan --</option>
                                                <option value="PPLG">PPLG (Pengembangan Perangkat Lunak dan Gim)</option>
                                                <option value="TJKT">TJKT (Teknik Jaringan Komputer dan Telekomunikasi)</option>
                                                <option value="BUSANA">BUSANA (Tata Busana)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Ekstrakurikuler -->
                            <div class="mb-4">
                                <h6 class="text-primary mb-3"><i class="fas fa-running"></i> Pilih Ekstrakurikuler</h6>

                                <div class="mb-3">
                                    <label class="form-label">Ekstrakurikuler <span class="required">*</span></label>
                                    <input type="text" class="form-control mb-2" id="searchEkstra" 
                                           placeholder="🔍 Cari ekstrakurikuler...">
                                    
                                    <div class="ekstra-list" id="ekstraContainer">
                                        <?php if (mysqli_num_rows($ekstra_query) > 0): ?>
                                            <?php while ($ekstra = mysqli_fetch_array($ekstra_query)): ?>
                                            <div class="ekstra-item" data-name="<?php echo strtolower(htmlspecialchars($ekstra['nama_ekstra'])); ?>">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="nama_ekstra" 
                                                           id="ekstra_<?php echo htmlspecialchars($ekstra['id']); ?>" 
                                                           value="<?php echo htmlspecialchars($ekstra['nama_ekstra']); ?>" required>
                                                    <label class="form-check-label w-100" for="ekstra_<?php echo htmlspecialchars($ekstra['id']); ?>">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div>
                                                                <strong class="d-block mb-1"><?php echo htmlspecialchars($ekstra['nama_ekstra']); ?></strong>
                                                                <small class="text-muted d-block">
                                                                    <i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($ekstra['pembina']); ?>
                                                                </small>
                                                                <small class="text-muted d-block">
                                                                    <i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($ekstra['hari']); ?> | 
                                                                    <i class="fas fa-clock"></i> <?php echo htmlspecialchars($ekstra['waktu']); ?>
                                                                    <?php if (!empty($ekstra['lokasi'])): ?>
                                                                        | <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($ekstra['lokasi']); ?>
                                                                    <?php endif; ?>
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <div class="alert alert-warning mb-0">
                                                <i class="fas fa-exclamation-triangle"></i> Belum ada data ekstrakurikuler tersedia.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Kontak -->
                            <div class="mb-4">
                                <h6 class="text-primary mb-3"><i class="fas fa-address-book"></i> Informasi Kontak (Opsional)</h6>

                                <div class="mb-3">
                                    <label for="no_hp" class="form-label">No. HP/WhatsApp</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input type="text" class="form-control" id="no_hp" name="no_hp" 
                                               placeholder="Contoh: 08123456789" maxlength="13">
                                    </div>
                                    <small class="text-muted"><i class="fas fa-info-circle"></i> Format: 08xxxxxxxxxx (10-13 digit)</small>
                                </div>

                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat Lengkap</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="3" 
                                              placeholder="Masukkan alamat rumah dan pondok (jika ada)"></textarea>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="home.php?page=siswa" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Simpan Data Siswa
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="col-lg-4">
                <div class="sidebar-info">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-info-circle"></i> Panduan Pengisian</h6>
                        </div>
                        <div class="card-body">
                            <div class="info-item">
                                <i class="fas fa-check-circle text-success"></i> Pastikan data yang dimasukkan benar
                            </div>
                            <div class="info-item">
                                <i class="fas fa-check-circle text-success"></i> NIS harus sesuai dengan data sekolah
                            </div>
                            <div class="info-item">
                                <i class="fas fa-check-circle text-success"></i> NIS minimal 4 digit angka
                            </div>
                            <div class="info-item">
                                <i class="fas fa-check-circle text-success"></i> Pilih ekstrakurikuler yang sesuai
                            </div>
                            <div class="info-item">
                                <i class="fas fa-check-circle text-success"></i> No. HP untuk komunikasi (opsional)
                            </div>
                            <div class="info-item">
                                <i class="fas fa-check-circle text-success"></i> Field bertanda <span class="required">*</span> wajib diisi
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-body bg-warning bg-opacity-10">
                            <h6 class="text-warning"><i class="fas fa-exclamation-triangle"></i> Perhatian</h6>
                            <p class="mb-0 small">NIS tidak boleh duplikat dengan siswa lain yang sudah terdaftar di ekstrakurikuler yang sama.</p>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-body bg-info bg-opacity-10">
                            <h6 class="text-info"><i class="fas fa-lightbulb"></i> Tips</h6>
                            <p class="mb-0 small">Gunakan fitur pencarian untuk menemukan ekstrakurikuler dengan cepat.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Search ekstrakurikuler dengan highlight
        document.getElementById('searchEkstra').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const ekstraItems = document.querySelectorAll('.ekstra-item');
            let foundCount = 0;
            
            ekstraItems.forEach(item => {
                const text = item.getAttribute('data-name');
                if (text.includes(searchTerm)) {
                    item.style.display = 'block';
                    foundCount++;
                } else {
                    item.style.display = 'none';
                }
            });
        });
        
        // Validasi hanya angka untuk NIS dan No HP
        document.getElementById('nis').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        document.getElementById('no_hp').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        // Handle form submit
        document.getElementById('formTambahSiswa').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Ambil nilai form
            const nama_siswa = document.getElementById('nama_siswa').value.trim();
            const nis = document.getElementById('nis').value.trim();
            const jenis_kelamin = document.querySelector('input[name="jenis_kelamin"]:checked')?.value;
            const kelas = document.getElementById('kelas').value;
            const jurusan = document.getElementById('jurusan').value;
            const nama_ekstra = document.querySelector('input[name="nama_ekstra"]:checked')?.value;
            const no_hp = document.getElementById('no_hp').value.trim();
            
            // Validasi
            if (!nama_siswa || !nis || !jenis_kelamin || !kelas || !jurusan || !nama_ekstra) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: 'Mohon lengkapi semua field yang wajib diisi (bertanda *)',
                    confirmButtonColor: '#667eea'
                });
                return;
            }
            
            if (nis.length < 4) {
                Swal.fire({
                    icon: 'warning',
                    title: 'NIS Tidak Valid!',
                    text: 'NIS minimal 4 digit angka',
                    confirmButtonColor: '#667eea'
                });
                return;
            }
            
            if (no_hp && (no_hp.length < 10 || no_hp.length > 13)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nomor HP Tidak Valid!',
                    text: 'Nomor HP harus 10-13 digit',
                    confirmButtonColor: '#667eea'
                });
                return;
            }
            
            const jenisKelaminIcon = jenis_kelamin === 'Laki-laki' ? '♂️' : '♀️';
            
            // Konfirmasi sebelum simpan
            Swal.fire({
                title: 'Konfirmasi Data',
                html: `
                    <div class="text-start">
                        <p class="mb-3"><strong>Apakah data berikut sudah benar?</strong></p>
                        <table class="table table-sm table-bordered">
                            <tr><td class="fw-bold">Nama</td><td>${nama_siswa}</td></tr>
                            <tr><td class="fw-bold">NIS</td><td>${nis}</td></tr>
                            <tr><td class="fw-bold">Jenis Kelamin</td><td>${jenisKelaminIcon} ${jenis_kelamin}</td></tr>
                            <tr><td class="fw-bold">Kelas</td><td>${kelas}</td></tr>
                            <tr><td class="fw-bold">Jurusan</td><td>${jurusan}</td></tr>
                            <tr><td class="fw-bold">Ekstrakurikuler</td><td>${nama_ekstra}</td></tr>
                            ${no_hp ? `<tr><td class="fw-bold">No. HP</td><td>${no_hp}</td></tr>` : ''}
                        </table>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#667eea',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check"></i> Ya, Simpan Data!',
                cancelButtonText: '<i class="fas fa-times"></i> Periksa Lagi',
                width: '600px'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tampilkan loading
                    Swal.fire({
                        title: 'Menyimpan Data...',
                        html: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3">Mohon tunggu sebentar</p>',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false
                    });
                    
                    // Kirim data dengan fetch API
                    const formData = new FormData(document.getElementById('formTambahSiswa'));
                    
                    fetch('siswa_tambah_manual.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                confirmButtonColor: '#667eea',
                                timer: 2000,
                                timerProgressBar: true
                            }).then(() => {
                                window.location.href = 'home.php?page=siswa';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message,
                                confirmButtonColor: '#667eea'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan sistem: ' + error,
                            confirmButtonColor: '#667eea'
                        });
                    });
                }
            });
        });
    </script>
</body>
</html