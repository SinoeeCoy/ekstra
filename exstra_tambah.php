<?php
include 'koneksi.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_ekstra = trim($_POST['nama_ekstra']);
    $pembina = trim($_POST['pembina']);
    $hari = trim($_POST['hari']);
    $waktu = trim($_POST['waktu']);

    if (empty($nama_ekstra) || empty($pembina) || empty($hari) || empty($waktu)) {
        echo "<script>alert('Semua field harus diisi!');</script>";
    } else {
        $stmt = $koneksi->prepare("INSERT INTO ekstra (nama_ekstra, pembina, hari, waktu) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama_ekstra, $pembina, $hari, $waktu);

       if ($stmt->execute()) {
    echo "<script>
        alert('Data berhasil disimpan!');
        window.location.href='home.php?page=ekstra&pesan=input';
    </script>";
    exit;

        } else {
            echo "Error: " . $stmt->error;
            $stmt->close();
        }
    }
}
?>

<h1 class="h3 mb-3">Tambah Data Ekstra</h1>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <a href="home.php?page=ekstra" class="btn btn-primary">Kembali</a>
                <hr>
                <form method="POST" action="">
                    <table class="table">
                        <tr>
                            <td width="200">Nama Ekstra</td>
                            <td width="1">:</td>
                            <td><input class="form-control" type="text" name="nama_ekstra" required></td>
                        </tr>
                        <tr>
                            <td>Pembina</td>
                            <td>:</td>
                            <td><input class="form-control" type="text" name="pembina" required></td>
                        </tr>
                        <tr>
                            <td>Hari</td>
                            <td>:</td>
                            <td><input class="form-control" type="text" name="hari" required></td>
                        </tr>
                        <tr>
                            <td>Waktu</td>
                            <td>:</td>
                            <td><input class="form-control" type="time" name="waktu" required></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td><button class="btn btn-success" type="submit">Simpan</button></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>

