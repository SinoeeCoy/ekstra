<?php
// File: export_jurnal_excel.php
// Export History Jurnal ke Excel

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

include "koneksi.php";

// Ambil filter dari POST
$filter_ekstra = isset($_POST['filter_ekstra']) ? $_POST['filter_ekstra'] : '';
$filter_tanggal_dari = isset($_POST['tanggal_dari']) ? $_POST['tanggal_dari'] : '';
$filter_tanggal_sampai = isset($_POST['tanggal_sampai']) ? $_POST['tanggal_sampai'] : '';

// Build WHERE clause
$where_conditions = [];
if ($filter_ekstra != '') {
    $where_conditions[] = "nama_ekstra = '" . mysqli_real_escape_string($koneksi, $filter_ekstra) . "'";
}
if ($filter_tanggal_dari != '') {
    $where_conditions[] = "tanggal >= '" . mysqli_real_escape_string($koneksi, $filter_tanggal_dari) . "'";
}
if ($filter_tanggal_sampai != '') {
    $where_conditions[] = "tanggal <= '" . mysqli_real_escape_string($koneksi, $filter_tanggal_sampai) . "'";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Query data
$query = mysqli_query($koneksi, "
    SELECT * FROM absen 
    $where_clause 
    ORDER BY tanggal DESC, nama_siswa ASC
");

$total_data = mysqli_num_rows($query);

// Set headers untuk download Excel
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=History_Jurnal_" . date('Ymd_His') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// UTF-8 BOM untuk support karakter Indonesia
echo "\xEF\xBB\xBF";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>History Jurnal Latihan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #1e40af;
            color: white;
        }
        
        .header h2 {
            margin: 5px 0;
            font-size: 18pt;
        }
        
        .header p {
            margin: 3px 0;
            font-size: 10pt;
        }
        
        .info-box {
            background-color: #e3f2fd;
            padding: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #1e40af;
        }
        
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
        }
        
        th {
            background-color: #1e40af;
            color: white;
            font-weight: bold;
            border: 1px solid #1e3a8a;
            padding: 10px 8px;
            text-align: center;
            font-size: 10pt;
        }
        
        td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            font-size: 10pt;
        }
        
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .text-center {
            text-align: center;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
        }
        
        .badge-hadir {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .badge-izin {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .badge-alfa {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .footer {
            margin-top: 30px;
            padding: 15px;
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            font-size: 9pt;
            color: #6b7280;
        }
    </style>
</head>
<body>
    
    <!-- HEADER -->
    <div class="header">
        <h2>HISTORY JURNAL LATIHAN EKSTRAKURIKULER</h2>
        <p>SISTEM INFORMASI EKSTRAKURIKULER</p>
        <p>Tahun Ajaran <?php echo date('Y') . '/' . (date('Y')+1); ?></p>
    </div>
    
    <!-- INFO BOX -->
    <div class="info-box">
        <strong>Informasi Export:</strong><br>
        Tanggal Export: <?php echo date('d F Y, H:i:s'); ?><br>
        Total Data: <?php echo $total_data; ?> record<br>
        Export Oleh: <?php echo htmlspecialchars($_SESSION['user']['username']); ?>
        <?php if (!empty($filter_ekstra)): ?>
        <br><strong style="color: #dc2626;">Filter Ekstrakurikuler: <?php echo htmlspecialchars($filter_ekstra); ?></strong>
        <?php endif; ?>
        <?php if (!empty($filter_tanggal_dari)): ?>
        <br><strong style="color: #dc2626;">Periode: <?php echo date('d/m/Y', strtotime($filter_tanggal_dari)); ?> 
        <?php if (!empty($filter_tanggal_sampai)): ?>
        - <?php echo date('d/m/Y', strtotime($filter_tanggal_sampai)); ?>
        <?php endif; ?>
        </strong>
        <?php endif; ?>
    </div>

    <!-- TABEL DATA -->
    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="10%">Tanggal</th>
                <th width="16%">Nama Siswa</th>
                <th width="8%">Kelas</th>
                <th width="10%">Jurusan</th>
                <th width="14%">Ekstrakurikuler</th>
                <th width="20%">Materi</th>
                <th width="10%">Keterangan</th>
                <th width="8%">Nilai</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($total_data > 0) {
                $no = 1;
                $stats = ['Hadir' => 0, 'Izin' => 0, 'Alfa' => 0];
                
                while ($data = mysqli_fetch_array($query)) {
                    // Hitung statistik
                    $stats[$data['keterangan']]++;
                    
                    // Badge class
                    $badge_class = 'badge-hadir';
                    if ($data['keterangan'] == 'Izin') $badge_class = 'badge-izin';
                    elseif ($data['keterangan'] == 'Alfa') $badge_class = 'badge-alfa';
            ?>
            <tr>
                <td class="text-center"><?php echo $no; ?></td>
                <td class="text-center"><?php echo date('d/m/Y', strtotime($data['tanggal'])); ?></td>
                <td><?php echo htmlspecialchars($data['nama_siswa']); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($data['kelas']); ?></td>
                <td class="text-center">
                    <?php echo !empty($data['jurusan']) ? htmlspecialchars($data['jurusan']) : '-'; ?>
                </td>
                <td><?php echo htmlspecialchars($data['nama_ekstra']); ?></td>
                <td><?php echo !empty($data['materi']) ? htmlspecialchars($data['materi']) : '-'; ?></td>
                <td class="text-center">
                    <span class="<?php echo $badge_class; ?>">
                        <?php echo htmlspecialchars($data['keterangan']); ?>
                    </span>
                </td>
                <td class="text-center">
                    <?php echo !empty($data['nilai']) ? $data['nilai'] : '-'; ?>
                </td>
            </tr>
            <?php 
                    $no++;
                }
            } else {
            ?>
            <tr>
                <td colspan="9" class="text-center" style="padding: 20px; color: #9ca3af; font-style: italic;">
                    Tidak ada data yang ditemukan
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        <strong>Ringkasan Data:</strong><br>
        <table width="100%" style="border: none; margin-top: 10px;">
            <tr>
                <td width="50%" style="border: none; padding: 5px 0;">
                    <strong>Total Record:</strong> <?php echo $total_data; ?>
                </td>
                <td width="50%" style="border: none; padding: 5px 0;">
                    <strong>Total Hadir:</strong> <?php echo isset($stats) ? $stats['Hadir'] : 0; ?>
                </td>
            </tr>
            <tr>
                <td style="border: none; padding: 5px 0;">
                    <strong>Total Izin:</strong> <?php echo isset($stats) ? $stats['Izin'] : 0; ?>
                </td>
                <td style="border: none; padding: 5px 0;">
                    <strong>Total Alfa:</strong> <?php echo isset($stats) ? $stats['Alfa'] : 0; ?>
                </td>
            </tr>
        </table>
        
        <hr style="margin: 15px 0; border: 0; border-top: 1px solid #d1d5db;">
        
        <strong>Catatan:</strong>
        <ul style="margin: 5px 0; padding-left: 20px;">
            <li>Data ini adalah hasil export otomatis dari sistem informasi ekstrakurikuler</li>
            <li>Foto kegiatan tidak disertakan dalam export Excel</li>
            <li>Untuk melihat foto, silakan gunakan export PDF atau lihat di sistem</li>
        </ul>
        
        <hr style="margin: 10px 0; border: 0; border-top: 1px solid #d1d5db;">
        
        <table width="100%" style="border: none;">
            <tr>
                <td width="50%" style="border: none; padding: 5px 0;">
                    <strong>Export Oleh:</strong> <?php echo htmlspecialchars($_SESSION['user']['username']); ?>
                </td>
                <td width="50%" style="border: none; padding: 5px 0; text-align: right;">
                    <strong>Waktu Export:</strong> <?php echo date('d F Y, H:i:s'); ?>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
<?php
mysqli_close($koneksi);
?>