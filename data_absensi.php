<?php 
include "koneksi.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$result = $koneksi->query("SELECT * FROM absen ORDER BY tanggal DESC");
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Data Absensi</title>
  <style>
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #000; padding: 8px; }
    @media print {
      .no-print { display: none; }
    }
  </style>
</head>
<body>
  <h2>Data Absensi / Jurnal Latihan</h2>
  <div class="no-print">
    <a href="absen.php">Input Baru</a> | 
    <button onclick="window.print()">Cetak</button>
  </div>
  <br>
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Nama</th>
        <th>Tanggal</th>
        <th>Kelas</th>
        <th>Jurusan</th>
        <th>Nama Ekstra</th>
        <th>Keterangan</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      $no = 1;
      while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= htmlspecialchars($row['nama']) ?></td>
        <td><?= htmlspecialchars($row['tanggal']) ?></td>
        <td><?= htmlspecialchars($row['kelas']) ?></td>
        <td><?= htmlspecialchars($row['jurusan']) ?></td>
        <td><?= htmlspecialchars($row['ekstra']) ?></td>
        <td><?= htmlspecialchars($row['keterangan']) ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</body>
</html>
