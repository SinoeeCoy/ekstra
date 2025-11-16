<?php
include "koneksi.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

if (isset($_GET['hapus']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    

    $get_file = mysqli_query($koneksi, "SELECT foto FROM galeri WHERE id = '$id'");
    $file_data = mysqli_fetch_array($get_file);
    
    if ($file_data && file_exists("uploads/galeri/" . $file_data['foto'])) {
        unlink("uploads/galeri/" . $file_data['foto']); // Hapus file
    }
    
    $delete_query = mysqli_query($koneksi, "DELETE FROM galeri WHERE id = '$id'");
    
    if ($delete_query) {
        $pesan = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    Foto berhasil dihapus!
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                  </div>";
    } else {
        $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    Gagal menghapus foto!
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                  </div>";
    }
}


if (isset($_POST['tambah_foto'])) {
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    
    
    $foto = $_FILES['foto']['name'];
    $foto_tmp = $_FILES['foto']['tmp_name'];
    $foto_size = $_FILES['foto']['size'];
    $foto_error = $_FILES['foto']['error'];
    
    if ($foto_error === 0) {
        $foto_ext = strtolower(pathinfo($foto, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($foto_ext, $allowed_ext)) {
            if ($foto_size <= 5000000) { 
                $foto_name = time() . '_' . $foto;
                $foto_destination = 'uploads/galeri/' . $foto_name;
                
                
                if (!file_exists('uploads/galeri/')) {
                    mkdir('uploads/galeri/', 0777, true);
                }
                
                if (move_uploaded_file($foto_tmp, $foto_destination)) {
                    $tanggal = date('Y-m-d H:i:s');
                    $insert_query = mysqli_query($koneksi, "INSERT INTO galeri (judul, deskripsi, foto, kategori, tanggal_upload) VALUES ('$judul', '$deskripsi', '$foto_name', '$kategori', '$tanggal')");
                    
                    if ($insert_query) {
                        $pesan = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                                    Foto berhasil ditambahkan!
                                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                                  </div>";
                    } else {
                        $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                                    Gagal menyimpan data foto!
                                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                                  </div>";
                    }
                } else {
                    $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                                Gagal mengupload foto!
                                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                              </div>";
                }
            } else {
                $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                            Ukuran foto terlalu besar! Maksimal 5MB.
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                          </div>";
            }
        } else {
            $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        Format foto tidak didukung! Gunakan JPG, JPEG, PNG, atau GIF.
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                      </div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Ekstrakurikuler</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- Lightbox CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet">
    
    <style>
        .gallery-item {
            margin-bottom: 30px;
        }
        .gallery-img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 10px;
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .gallery-img:hover {
            transform: scale(1.05);
        }
        .gallery-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .gallery-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .gallery-card:hover .gallery-overlay {
            opacity: 1;
        }
        .category-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 2;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid mt-4">
        
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h3 class="mb-0">
                                    <i class=""></i> Galeri Ekstrakurikuler
                                </h3>
                                <p class="text-muted mb-0">Dokumentasi kegiatan ekstrakurikuler sekolah</p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <?php if ($user['role'] == 'pembina'): ?>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahFotoModal">
                                    <i class="fas fa-plus"></i> Tambah Foto
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pesan -->
        <?php if (isset($pesan)) echo $pesan; ?>

        <!-- Statistics -->
        <div class="row mb-4">
            <?php
            $total_foto = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM galeri"));
            $foto_bulan_ini = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM galeri WHERE MONTH(tanggal_upload) = MONTH(CURDATE()) AND YEAR(tanggal_upload) = YEAR(CURDATE())"));
            $kategori_query = mysqli_query($koneksi, "SELECT kategori, COUNT(*) as jumlah FROM galeri GROUP BY kategori ORDER BY jumlah DESC LIMIT 1");
            $kategori_terpopuler = mysqli_fetch_array($kategori_query);
            $kategori_popular = $kategori_terpopuler ? $kategori_terpopuler['kategori'] . " (" . $kategori_terpopuler['jumlah'] . ")" : "Belum ada data";
            ?>
            
            <div class="col-md-4 mb-3">
                <div class="card stats-card border-0 h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-images fa-3x mb-3"></i>
                        <h3><?php echo $total_foto; ?></h3>
                        <p class="mb-0">Total Foto</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card stats-card border-0 h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar fa-3x mb-3"></i>
                        <h3><?php echo $foto_bulan_ini; ?></h3>
                        <p class="mb-0">Foto Bulan Ini</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card stats-card border-0 h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-trophy fa-3x mb-3"></i>
                        <h6><?php echo $kategori_popular; ?></h6>
                        <p class="mb-0">Kategori Terpopuler</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="row mb-4">
            <div class="col-md-6">
                <select id="filterKategori" class="form-select" onchange="filterGaleri()">
                    <option value="">Semua Kategori</option>
                    <?php
                    $kategori_list = mysqli_query($koneksi, "SELECT DISTINCT kategori FROM galeri ORDER BY kategori");
                    while ($kat = mysqli_fetch_array($kategori_list)) {
                        echo "<option value='" . $kat['kategori'] . "'>" . $kat['kategori'] . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- Galeri -->
        <div class="row" id="galeriContainer">
            <?php
            $query = mysqli_query($koneksi, "SELECT * FROM galeri ORDER BY tanggal_upload DESC");
            
            if (mysqli_num_rows($query) > 0) {
                while ($data = mysqli_fetch_array($query)) {
            ?>
            <div class="col-lg-4 col-md-6 gallery-item" data-kategori="<?php echo $data['kategori']; ?>">
                <div class="gallery-card position-relative">
                    <div class="category-badge">
                        <span class="badge bg-primary"><?php echo $data['kategori']; ?></span>
                    </div>
                    
                    <div class="position-relative">
                        <img src="uploads/galeri/<?php echo $data['foto']; ?>" 
                             class="gallery-img" 
                             alt="<?php echo htmlspecialchars($data['judul']); ?>">
                        
                        <div class="gallery-overlay">
                            <div class="text-center">
                                <a href="uploads/galeri/<?php echo $data['foto']; ?>" 
                                   data-lightbox="gallery" 
                                   data-title="<?php echo htmlspecialchars($data['judul']); ?>"
                                   class="btn btn-light me-2">
                                    <i class="fas fa-search-plus"></i>
                                </a>
                                <?php if ($user['role'] == 'pembina'): ?>
                                <button class="btn btn-warning me-2" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editModal" 
                                        onclick="editFoto(<?php echo $data['id']; ?>, '<?php echo addslashes($data['judul']); ?>', '<?php echo addslashes($data['deskripsi']); ?>', '<?php echo $data['kategori']; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="?page=history&hapus=true&id=<?php echo $data['id']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Yakin ingin menghapus foto ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-3">
                        <h5 class="mb-2"><?php echo htmlspecialchars($data['judul']); ?></h5>
                        <p class="text-muted small mb-2">
                            <?php echo strlen($data['deskripsi']) > 100 ? substr(htmlspecialchars($data['deskripsi']), 0, 100) . '...' : htmlspecialchars($data['deskripsi']); ?>
                        </p>
                        <small class="text-muted">
                            <i class="fas fa-calendar"></i> 
                            <?php echo date('d/m/Y H:i', strtotime($data['tanggal_upload'])); ?>
                        </small>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
                echo "<div class='col-12'><div class='alert alert-info text-center'>Belum ada foto di galeri.</div></div>";
            }
            ?>
        </div>
    </div>

    <!-- Modal Tambah Foto -->
    <?php if ($user['role'] == 'pembina'): ?>
    <div class="modal fade" id="tambahFotoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Judul Foto</label>
                            <input type="text" class="form-control" name="judul" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-control" name="kategori" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Olahraga">Olahraga</option>
                                <option value="Seni">Seni</option>
                                <option value="Akademik">Akademik</option>
                                <option value="Kompetisi">Kompetisi</option>
                                <option value="Event Sekolah">Event Sekolah</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Upload Foto</label>
                            <input type="file" class="form-control" name="foto" accept="image/*" required>
                            <small class="text-muted">Format: JPG, JPEG, PNG, GIF. Maksimal 5MB.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah_foto" class="btn btn-primary">Upload Foto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Foto -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editForm">
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Judul Foto</label>
                            <input type="text" class="form-control" name="edit_judul" id="edit_judul" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="edit_deskripsi" id="edit_deskripsi" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-control" name="edit_kategori" id="edit_kategori" required>
                                <option value="Olahraga">Olahraga</option>
                                <option value="Seni">Seni</option>
                                <option value="Akademik">Akademik</option>
                                <option value="Kompetisi">Kompetisi</option>
                                <option value="Event Sekolah">Event Sekolah</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_foto" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
    
    <script>
    
    function filterGaleri() {
        const filter = document.getElementById('filterKategori').value;
        const items = document.querySelectorAll('.gallery-item');
        
        items.forEach(item => {
            if (filter === '' || item.dataset.kategori === filter) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    
    function editFoto(id, judul, deskripsi, kategori) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_judul').value = judul;
        document.getElementById('edit_deskripsi').value = deskripsi;
        document.getElementById('edit_kategori').value = kategori;
    }

    
    lightbox.option({
        'resizeDuration': 200,
        'wrapAround': true,
        'showImageNumberLabel': false
    });
    </script>

    <?php
    
    if (isset($_POST['edit_foto'])) {
        $id = $_POST['edit_id'];
        $judul = mysqli_real_escape_string($koneksi, $_POST['edit_judul']);
        $deskripsi = mysqli_real_escape_string($koneksi, $_POST['edit_deskripsi']);
        $kategori = mysqli_real_escape_string($koneksi, $_POST['edit_kategori']);
        
        $update_query = mysqli_query($koneksi, "UPDATE galeri SET judul = '$judul', deskripsi = '$deskripsi', kategori = '$kategori' WHERE id = '$id'");
        
        if ($update_query) {
            echo "<script>
                alert('Foto berhasil diupdate!');
                window.location.href = '?page=history';
            </script>";
        } else {
            echo "<script>alert('Gagal mengupdate foto!');</script>";
        }
    }
    ?>
</body>
</html>