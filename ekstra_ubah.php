<?php
include 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

if (isset($_GET['nama_ekstra']) && !empty($_GET['nama_ekstra'])) {
    $nama_ekstra = $_GET['nama_ekstra'];
} elseif (isset($_GET['id']) && !empty($_GET['id'])) {
    $nama_ekstra = $_GET['id'];
} else {
    echo '<script>alert("Data tidak valid!"); location.href="home.php?page=ekstra";</script>';
    exit;
}


$stmt = $koneksi->prepare("SELECT * FROM ekstra WHERE nama_ekstra = ?");
$stmt->bind_param("s", $nama_ekstra);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo '<script>alert("Data tidak ditemukan!"); location.href="home.php?page=ekstra";</script>';
    exit;
}

$data = $result->fetch_assoc();
$stmt->close();


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (
        empty($_POST['nama_ekstra_baru']) || 
        empty($_POST['pembina']) || 
        empty($_POST['hari']) || 
        empty($_POST['waktu'])
    ) {
        echo '<script>alert("Semua field harus diisi!");</script>';
    } else {
        $nama_ekstra_baru = trim($_POST['nama_ekstra_baru']);
        $pembina = trim($_POST['pembina']);
        $hari = trim($_POST['hari']);
        $waktu = trim($_POST['waktu']);

        $update_stmt = $koneksi->prepare("
            UPDATE ekstra 
            SET nama_ekstra = ?, pembina = ?, hari = ?, waktu = ? 
            WHERE nama_ekstra = ?
        ");
        $update_stmt->bind_param("sssss", $nama_ekstra_baru, $pembina, $hari, $waktu, $nama_ekstra);

        if ($update_stmt->execute()) {
            if ($update_stmt->affected_rows > 0) {
                echo '<script>alert("Data berhasil diubah!"); location.href="home.php?page=ekstra";</script>';
            } else {
                echo '<script>alert("Tidak ada perubahan data!");</script>';
            }
        } else {
            echo '<script>alert("Gagal mengubah data: '.$update_stmt->error.'");</script>';
        }
        $update_stmt->close();
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Ubah Data Ekstrakurikuler</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Ubah Data Ekstrakurikuler</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="nama_ekstra_baru" class="form-label">Nama Ekstrakurikuler</label>
                            <input type="text" class="form-control" id="nama_ekstra_baru" name="nama_ekstra_baru" 
                                   value="<?php echo htmlspecialchars($data['nama_ekstra']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="pembina" class="form-label">Nama Pembina</label>
                            <input type="text" class="form-control" id="pembina" name="pembina" 
                                   value="<?php echo htmlspecialchars($data['pembina']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="hari" class="form-label">Hari</label>
                            <select class="form-control" id="hari" name="hari" required>
                                <option value="">Pilih Hari</option>
                                <option value="Senin" <?php echo ($data['hari'] == 'Senin') ? 'selected' : ''; ?>>Senin</option>
                                <option value="Selasa" <?php echo ($data['hari'] == 'Selasa') ? 'selected' : ''; ?>>Selasa</option>
                                <option value="Rabu" <?php echo ($data['hari'] == 'Rabu') ? 'selected' : ''; ?>>Rabu</option>
                                <option value="Kamis" <?php echo ($data['hari'] == 'Kamis') ? 'selected' : ''; ?>>Kamis</option>
                                <option value="Jumat" <?php echo ($data['hari'] == 'Jumat') ? 'selected' : ''; ?>>Jumat</option>
                                <option value="Sabtu" <?php echo ($data['hari'] == 'Sabtu') ? 'selected' : ''; ?>>Sabtu</option>
                                <option value="Minggu" <?php echo ($data['hari'] == 'Minggu') ? 'selected' : ''; ?>>Minggu</option>
                            </select>
                        </div>
                        
                       
                        
                        <div class="mb-3">
                            <label for="waktu" class="form-label">Waktu</label>
                            <input type="time" class="form-control" id="waktu" name="waktu" 
                                   value="<?php echo $data['waktu']; ?>" required>
                        </div>
                        
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <a href="home.php?page=ekstra" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $koneksi->close(); ?>