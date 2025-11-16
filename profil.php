<?php
// File: profil_pembina_edit.php
include "koneksi.php"; // koneksi ke database

$nama_pembina_edit = mysqli_real_escape_string($koneksi, $_GET['edit'] ?? '');

// 🔧 Pastikan kolom-kolom tambahan tersedia di tabel
$columns_to_check = [
    'pengalaman' => 'TEXT AFTER pendidikan',
    'prestasi'   => 'TEXT AFTER pengalaman',
    'motto'      => 'TEXT AFTER prestasi',
    'foto'       => 'VARCHAR(255) AFTER motto',
    'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
    'updated_at' => 'DATETIME NULL DEFAULT NULL AFTER created_at'
];

foreach ($columns_to_check as $column => $definition) {
    $check = mysqli_query($koneksi, "SHOW COLUMNS FROM profil_pembina LIKE '$column'");
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($koneksi, "ALTER TABLE profil_pembina ADD COLUMN $column $definition");
    }
}

// 🔹 Ambil data lama
$check_query = mysqli_query($koneksi, "SELECT * FROM profil_pembina WHERE nama_pembina = '$nama_pembina_edit'");
$existing_data = mysqli_fetch_array($check_query);
$is_edit = ($existing_data) ? true : false;

// 🔹 Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pembina = mysqli_real_escape_string($koneksi, $_POST['nama_pembina']);
    $tanggal_lahir = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']);
    $tempat_lahir = mysqli_real_escape_string($koneksi, $_POST['tempat_lahir']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_telp = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $pendidikan = mysqli_real_escape_string($koneksi, $_POST['pendidikan']);
    $pengalaman = mysqli_real_escape_string($koneksi, $_POST['pengalaman'] ?? '');
    $prestasi = mysqli_real_escape_string($koneksi, $_POST['prestasi'] ?? '');
    $motto = mysqli_real_escape_string($koneksi, $_POST['motto'] ?? '');

    // 🔹 Upload foto
    $foto_name = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed_types)) {
            $foto_name = 'pembina_' . time() . '_' . uniqid() . '.' . $ext;
            $upload_path = 'uploads/pembina/' . $foto_name;

            if (!file_exists('uploads/pembina/')) {
                mkdir('uploads/pembina/', 0777, true);
            }

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                if ($is_edit && !empty($existing_data['foto']) && file_exists('uploads/pembina/' . $existing_data['foto'])) {
                    unlink('uploads/pembina/' . $existing_data['foto']);
                }
            } else {
                $foto_name = '';
            }
        }
    } else {
        $foto_name = $is_edit ? $existing_data['foto'] : '';
    }

    // 🔹 Simpan data
    if ($is_edit) {
        $foto_sql = $foto_name ? ", foto = '$foto_name'" : "";
        $update_query = "UPDATE profil_pembina SET 
                            tanggal_lahir = '$tanggal_lahir',
                            tempat_lahir = '$tempat_lahir',
                            alamat = '$alamat',
                            no_telp = '$no_telp',
                            pendidikan = '$pendidikan',
                            pengalaman = '$pengalaman',
                            prestasi = '$prestasi',
                            motto = '$motto'
                            $foto_sql,
                            updated_at = NOW()
                         WHERE nama_pembina = '$nama_pembina_edit'";
    } else {
        $update_query = "INSERT INTO profil_pembina 
                        (nama_pembina, tanggal_lahir, tempat_lahir, alamat, no_telp, pendidikan, pengalaman, prestasi, motto, foto, created_at)
                        VALUES 
                        ('$nama_pembina', '$tanggal_lahir', '$tempat_lahir', '$alamat', '$no_telp', '$pendidikan', '$pengalaman', '$prestasi', '$motto', '$foto_name', NOW())";
    }

    if (mysqli_query($koneksi, $update_query)) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Profil berhasil disimpan',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = '?page=sistemprofil_pembina';
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Error: " . mysqli_error($koneksi) . "'
            });
        </script>";
    }
}
?>



<style>
    .profile-card {
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .form-container {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    .photo-preview {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .upload-btn {
        position: relative;
        overflow: hidden;
        display: inline-block;
    }
    .upload-btn input[type=file] {
        position: absolute;
        left: -9999px;
    }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="profile-card p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-0">
                            <i class="fas fa-user-edit"></i> 
                            <?= $is_edit ? 'Edit Profil Pembina' : 'Buat Profil Pembina' ?>
                        </h2>
                        <p class="mb-0 opacity-75">
                            <?php if ($is_edit): ?>
                                Perbarui informasi profil pembina: <?= htmlspecialchars($nama_pembina_edit) ?>
                            <?php else: ?>
                                Buat profil baru untuk: <?= htmlspecialchars($nama_pembina_edit) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="?page=sistemprofil_pembina" class="btn btn-light">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Form Section -->
        <div class="col-lg-8">
            <div class="form-container p-4">
                <form method="POST" enctype="multipart/form-data">
                    
                    <!-- Data Pribadi -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-user text-primary"></i> Data Pribadi
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_pembina" 
                                       value="<?= $is_edit ? htmlspecialchars($existing_data['nama_pembina']) : $nama_pembina_edit ?>" 
                                       <?= $is_pembina ? 'readonly' : '' ?> required>
                                <?php if ($is_pembina): ?>
                                <small class="text-muted">Nama tidak dapat diubah</small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tempat Lahir</label>
                                <input type="text" class="form-control" name="tempat_lahir" 
                                       value="<?= $is_edit ? htmlspecialchars($existing_data['tempat_lahir']) : '' ?>" placeholder="Jakarta">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" name="tanggal_lahir" 
                                       value="<?= $is_edit ? $existing_data['tanggal_lahir'] : '' ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Alamat Rumah</label>
                            <textarea class="form-control" name="alamat" rows="3" 
                                      placeholder="Masukkan alamat lengkap"><?= $is_edit ? htmlspecialchars($existing_data['alamat']) : '' ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">No. Telepon/HP</label>
                            <input type="tel" class="form-control" name="no_telp" 
                                   value="<?= $is_edit ? htmlspecialchars($existing_data['no_telp']) : '' ?>" 
                                   placeholder="08123456789">
                        </div>
                    </div>

                    <!-- Data Akademik & Profesional -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-graduation-cap text-success"></i> Data Akademik & Profesional
                        </h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Pendidikan Terakhir</label>
                            <input type="text" class="form-control" name="pendidikan" 
                                   value="<?= $is_edit ? htmlspecialchars($existing_data['pendidikan']) : '' ?>" 
                                   placeholder="S1 Pendidikan Olahraga - Universitas ABC">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Pengalaman Mengajar/Membina</label>
                            <textarea class="form-control" name="pengalaman" rows="4" 
                                      placeholder="Deskripsikan pengalaman Anda dalam mengajar atau membina ekstrakurikuler"><?= $is_edit ? htmlspecialchars($existing_data['pengalaman']) : '' ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Prestasi & Penghargaan</label>
                            <textarea class="form-control" name="prestasi" rows="4" 
                                      placeholder="Tuliskan prestasi, sertifikat, atau penghargaan yang pernah diraih"><?= $is_edit ? htmlspecialchars($existing_data['prestasi']) : '' ?></textarea>
                        </div>
                    </div>

                    <!-- Data Tambahan -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-heart text-danger"></i> Data Tambahan
                        </h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Motto/Filosofi Mengajar</label>
                            <textarea class="form-control" name="motto" rows="3" 
                                      placeholder="Tuliskan motto atau filosofi Anda dalam mengajar dan membina siswa"><?= $is_edit ? htmlspecialchars($existing_data['motto']) : '' ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Foto Profil</label>
                            <div class="upload-btn">
                                <label for="foto" class="btn btn-outline-primary">
                                    <i class="fas fa-camera"></i> Pilih Foto
                                </label>
                                <input type="file" id="foto" name="foto" accept="image/*" onchange="previewImage(this)">
                            </div>
                            <small class="text-muted d-block mt-1">Format: JPG, PNG, GIF. Maksimal 2MB</small>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="?page=sistemprofil_pembina" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?= $is_edit ? 'Update Profil' : 'Simpan Profil' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview Section -->
        <div class="col-lg-4">
            <div class="form-container p-4">
                <h6 class="mb-3"><i class="fas fa-eye text-info"></i> Preview Foto</h6>
                
                <div class="text-center mb-3">
                    <?php if ($is_edit && !empty($existing_data['foto'])): ?>
                        <img src="uploads/pembina/<?= $existing_data['foto'] ?>" 
                             alt="Foto Profil" class="photo-preview" id="imagePreview">
                    <?php else: ?>
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($nama_pembina_edit) ?>&size=150&background=667eea&color=fff&bold=true" 
                             alt="Preview" class="photo-preview" id="imagePreview">
                    <?php endif; ?>
                </div>
                
                <!-- Tips -->
                <div class="mt-4">
                    <h6><i class="fas fa-lightbulb text-warning"></i> Tips:</h6>
                    <ul class="small text-muted">
                        <li>Gunakan foto dengan pencahayaan yang baik</li>
                        <li>Pastikan wajah terlihat jelas</li>
                        <li>Foto formal lebih disarankan</li>
                        <li>Hindari foto yang buram atau gelap</li>
                    </ul>
                </div>

                <!-- Quick Stats -->
                <?php if ($is_edit): ?>
                <div class="mt-4 p-3 bg-light rounded">
                    <h6>Profil Statistics:</h6>
                    <small class="text-muted">
                        <i class="fas fa-calendar"></i> Dibuat: <?= date('d/m/Y', strtotime($existing_data['created_at'])) ?>
                    </small><br>
                    <small class="text-muted">
                        <i class="fas fa-edit"></i> Terakhir update: 
                        <?= isset($existing_data['updated_at']) && $existing_data['updated_at'] ? date('d/m/Y', strtotime($existing_data['updated_at'])) : 'Belum pernah' ?>
                    </small>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Preview image function
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const required = document.querySelector('input[name="nama_pembina"]');
        if (!required.value.trim()) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: 'Nama pembina harus diisi!'
            });
            required.focus();
        }
    });

    // Phone number formatting
    document.querySelector('input[name="no_telp"]').addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9+]/g, '');
    });
</script>