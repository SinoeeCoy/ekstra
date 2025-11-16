<?php
include "koneksi.php";

$filter_ekstra = isset($_GET['filter_ekstra']) ? $_GET['filter_ekstra'] : '';
$filter_tanggal = isset($_GET['filter_tanggal']) ? $_GET['filter_tanggal'] : date('Y-m-d');

$where_clause = "WHERE 1=1";
if ($filter_ekstra) {
    $where_clause .= " AND nama_ekstra='" . mysqli_real_escape_string($koneksi, $filter_ekstra) . "'";
}
if ($filter_tanggal) {
    $where_clause .= " AND tanggal='" . mysqli_real_escape_string($koneksi, $filter_tanggal) . "'";
}

$query = mysqli_query($koneksi, "
    SELECT * FROM absen 
    $where_clause
    ORDER BY nama ASC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Absensi Ekstrakurikuler</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 24px;
        }
        .header h3 {
            margin: 5px 0;
            font-size: 18px;
            font-weight: normal;
        }
        .info {
            margin: 20px 0;
        }
        .info table {
            width: 100%;
        }
        .info td {
            padding: 5px;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table.data th, table.data td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        table.data th {
            background: #e0e0e0;
            text-align: center;
            font-weight: bold;
        }
        table.data td {
            vertical-align: top;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-hadir { background: #10b981; color: #fff; }
        .badge-izin { background: #f59e0b; color: #fff; }
        .badge-alfa { background: #ef4444; color: #fff; }
        .summary {
            margin-top: 20px;
            font-weight: bold;
        }
        .ttd {
            margin-top: 40px;
            text-align: right;
        }
        .ttd-box {
            display: inline-block;
            text-align: center;
            min-width: 200px;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
            @page { margin: 1cm; }
        }
    </style>
</head>
<body>

<button onclick="window.print()" class="no-print" style="padding: 10px 20px; margin-bottom: 20px; cursor: pointer; background: #1e40af; color: white; border: none; border-radius: 5px;">
    <i class="fas fa-print"></i> Cetak / Print
</button>

<div class="header">
    <h2>DAFTAR ABSENSI EKSTRAKURIKULER</h2>
    <h3>TAHUN AJARAN 2024/2025</h3>
</div>

<div class="info">
    <table>
        <tr>
            <td width="150">Tanggal</td>
            <td width="10">:</td>
            <td><strong><?= date('d F Y', strtotime($filter_tanggal)); ?></strong></td>
        </tr>
        <?php if ($filter_ekstra): ?>
        <tr>
            <td>Ekstrakurikuler</td>
            <td>:</td>
            <td><strong><?= htmlspecialchars($filter_ekstra); ?></strong></td>
        </tr>
        <?php endif; ?>
    </table>
</div>

<table class="data">
    <thead>
        <tr>
            <th width="40">No</th>
            <th>Nama Siswa</th>
            <th width="80">Kelas</th>
            <th>Jurusan</th>
            <th width="150">Ekstrakurikuler</th>
            <th width="100">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        $total_hadir = 0;
        $total_izin = 0;
        $total_alfa = 0;
        
        while ($data = mysqli_fetch_array($query)) { 
            if ($data['keterangan'] == 'Hadir') $total_hadir++;
            elseif ($data['keterangan'] == 'Izin') $total_izin++;
            elseif ($data['keterangan'] == 'Alfa') $total_alfa++;
        ?>
            <tr>
                <td class="text-center"><?= $no++; ?></td>
                <td><?= htmlspecialchars($data['nama']); ?></td>
                <td class="text-center"><?= htmlspecialchars($data['kelas']); ?></td>
                <td><?= htmlspecialchars($data['jurusan']); ?></td>
                <td><?= htmlspecialchars($data['nama_ekstra']); ?></td>
                <td class="text-center"><?= htmlspecialchars($data['keterangan']); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<div class="summary">
    <p>Ringkasan:</p>
    <ul>
        <li>Total Hadir: <?= $total_hadir; ?> siswa</li>
        <li>Total Izin: <?= $total_izin; ?> siswa</li>
        <li>Total Alfa: <?= $total_alfa; ?> siswa</li>
        <li>Total Keseluruhan: <?= $total_hadir + $total_izin + $total_alfa; ?> siswa</li>
    </ul>
</div>

<div class="ttd">
    <div class="ttd-box">
        <p>Pembina Ekstrakurikuler</p>
        <br><br><br>
        <p>_________________________</p>
    </div>
</div>

</body>
</html>