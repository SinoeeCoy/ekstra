<?php
include "koneksi.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// PERBAIKAN: Ambil role dari array session
$user_role = '';
if (isset($_SESSION['user']['role'])) {
    // Format login baru (array)
    $user_role = $_SESSION['user']['role'];
} elseif (isset($_SESSION['role'])) {
    // Format login lama (string) - fallback
    $user_role = $_SESSION['role'];
}

// Konversi ke lowercase untuk perbandingan
$user_role_lower = strtolower(trim($user_role));

// Daftar role yang diizinkan
$allowed_roles = ['admin', 'administrator', 'pembina', 'superadmin'];

// Cek akses
if (!in_array($user_role_lower, $allowed_roles)) {
    $username = isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : 'Unknown';
    echo "<div class='container mt-5'>
            <div class='alert alert-danger'>
                <h4>❌ Akses Ditolak</h4>
                <p>Anda tidak memiliki akses ke halaman ini.</p>
                <p>User: <strong>" . htmlspecialchars($username) . "</strong></p>
                <p>Role: <strong>" . htmlspecialchars($user_role ? $user_role : 'Tidak diketahui') . "</strong></p>
                <hr>
                <p><small>✅ Role yang diizinkan: <strong>admin, pembina</strong></small></p>
                <a href='?page=dashboard' class='btn btn-primary mt-3'>
                    <i class='fa fa-arrow-left'></i> Kembali ke Dashboard
                </a>
            </div>
          </div>";
    exit;
}

$id = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';
if (empty($id)) {
    echo "<script>alert('ID tidak ditemukan!'); window.location='?page=history_jurnal';</script>";
    exit;
}

// Ambil data berdasarkan ID
$query = mysqli_query($koneksi, "SELECT * FROM absen WHERE id = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='?page=history_jurnal';</script>";
    exit;
}

// Proses update data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $nama_siswa = mysqli_real_escape_string($koneksi, $_POST['nama_siswa']);
    $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas']);
    $jurusan = mysqli_real_escape_string($koneksi, $_POST['jurusan']);
    $nama_ekstra = mysqli_real_escape_string($koneksi, $_POST['nama_ekstra']);
    $materi = mysqli_real_escape_string($koneksi, $_POST['materi']);
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    $nilai = mysqli_real_escape_string($koneksi, $_POST['nilai']);
    
    // Upload foto baru (opsional)
    $foto_baru = $_FILES['foto']['name'];
    if ($foto_baru != '') {
        $ext = pathinfo($foto_baru, PATHINFO_EXTENSION);
        $nama_foto_baru = time() . '_' . rand(100, 999) . '.' . $ext;
        $upload_path = 'uploads/kegiatan/' . $nama_foto_baru;

        // Buat folder jika belum ada
        if (!file_exists('uploads/kegiatan/')) {
            mkdir('uploads/kegiatan/', 0777, true);
        }

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
            // Hapus foto lama
            if (!empty($data['foto']) && file_exists('uploads/kegiatan/' . $data['foto'])) {
                unlink('uploads/kegiatan/' . $data['foto']);
            }
            $update_foto = ", foto = '$nama_foto_baru'";
        } else {
            $update_foto = "";
        }
    } else {
        $update_foto = "";
    }

    // Update ke database
    $update = mysqli_query($koneksi, "
        UPDATE absen SET 
            tanggal = '$tanggal',
            nama_siswa = '$nama_siswa',
            kelas = '$kelas',
            jurusan = '$jurusan',
            nama_ekstra = '$nama_ekstra',
            materi = '$materi',
            keterangan = '$keterangan',
            nilai = '$nilai'
            $update_foto
        WHERE id = '$id'
    ");

    if ($update) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data berhasil diperbarui',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location = '?page=history_jurnal';
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Data gagal diperbarui'
            });
        </script>";
    }
}

// Ambil info user untuk ditampilkan
$username = isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : 'Unknown';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><i class="fa fa-edit"></i> Edit Data Jurnal</h3>
        <div class="alert alert-info mb-0 py-2 px-3">
            <i class="fa fa-user"></i> <strong><?= htmlspecialchars($username); ?></strong> 
            <span class="badge bg-primary"><?= htmlspecialchars($user_role); ?></span>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" class="mt-3">
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="<?= $data['tanggal']; ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nama Siswa</label>
                <input type="text" name="nama_siswa" class="form-control" value="<?= htmlspecialchars($data['nama_siswa']); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Kelas</label>
                <input type="text" name="kelas" class="form-control" value="<?= htmlspecialchars($data['kelas']); ?>" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Jurusan</label>
                <input type="text" name="jurusan" class="form-control" value="<?= htmlspecialchars($data['jurusan']); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Ekstrakurikuler</label>
                <input type="text" name="nama_ekstra" class="form-control" value="<?= htmlspecialchars($data['nama_ekstra']); ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Materi</label>
                <input type="text" name="materi" class="form-control" value="<?= htmlspecialchars($data['materi']); ?>">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Keterangan</label>
                <select name="keterangan" class="form-select" required>
                    <option value="Hadir" <?= $data['keterangan']=='Hadir'?'selected':''; ?>>Hadir</option>
                    <option value="Izin" <?= $data['keterangan']=='Izin'?'selected':''; ?>>Izin</option>
                    <option value="Alfa" <?= $data['keterangan']=='Alfa'?'selected':''; ?>>Alfa</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nilai</label>
                <input type="number" name="nilai" class="form-control" value="<?= htmlspecialchars($data['nilai']); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Foto (Opsional)</label><br>
                <?php if (!empty($data['foto'])): ?>
                    <img src="uploads/kegiatan/<?= htmlspecialchars($data['foto']); ?>" width="80" class="mb-2 rounded">
                <?php endif; ?>
                <input type="file" name="foto" class="form-control" accept="image/*">
            </div>
        </div>

        <div class="text-end mt-4">
            <a href="?page=history_jurnal" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>