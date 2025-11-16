<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// ============================================
// EXPORT EXCEL - Data Siswa Ekstrakurikuler
// ============================================

// Include koneksi database
include "koneksi.php";

// ============================================
// 1. CEK AUTENTIKASI & AUTHORIZATION
// ============================================
$user_role = strtolower($user['role'] ?? '');
$can_edit = in_array($user_role, ['pembina', 'admin', 'waka', 'kepala']);

// ============================================
// 2. SET HEADERS UNTUK DOWNLOAD EXCEL
// ============================================
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=Data_Siswa_Ekstrakurikuler_" . date('Y-m-d_His') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Untuk support karakter Indonesia
echo "\xEF\xBB\xBF"; // UTF-8 BOM

// ============================================
// 3. AMBIL PARAMETER FILTER (Jika Ada)
// ============================================
$filter_ekstra = isset($_GET['filter_ekstra']) ? $_GET['filter_ekstra'] : '';
$filter_jurusan = isset($_GET['filter_jurusan']) ? $_GET['filter_jurusan'] : '';
$where_clause = "";
$conditions = [];

if (!empty($filter_ekstra)) {
    $filter_ekstra_escaped = mysqli_real_escape_string($koneksi, $filter_ekstra);
    $conditions[] = "ds.nama_ekstra = '$filter_ekstra_escaped'";
}

if (!empty($filter_jurusan)) {
    $filter_jurusan_escaped = mysqli_real_escape_string($koneksi, $filter_jurusan);
    $conditions[] = "ds.jurusan = '$filter_jurusan_escaped'";
}

if (count($conditions) > 0) {
    $where_clause = "WHERE " . implode(" AND ", $conditions);
}

// ============================================
// 4. QUERY DATA DARI DATABASE
// ============================================
$query = mysqli_query($koneksi, "
    SELECT 
        ds.*,
        de.pembina,
        de.hari,
        de.waktu 
    FROM data_siswa ds 
    LEFT JOIN ekstra de ON ds.nama_ekstra = de.nama_ekstra 
    $where_clause 
    ORDER BY ds.tanggal_daftar DESC
");

// Cek apakah query berhasil
if (!$query) {
    die("Error query: " . mysqli_error($koneksi));
}

$total_data = mysqli_num_rows($query);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Data Siswa Ekstrakurikuler</title>
    <style>
        /* Styling untuk Excel */
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
        
        tr:hover {
            background-color: #e5e7eb;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
        }
        
        .badge-primary {
            background-color: #3b82f6;
            color: white;
        }
        
        .badge-info {
            background-color: #06b6d4;
            color: white;
        }
        
        .badge-success {
            background-color: #10b981;
            color: white;
        }
        
        .badge-warning {
            background-color: #f59e0b;
            color: white;
        }
        
        .footer {
            margin-top: 30px;
            padding: 15px;
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            font-size: 9pt;
            color: #6b7280;
        }
        
        .footer strong {
            color: #1f2937;
        }
    </style>
</head>
<body>
    
    <!-- ========================================
         HEADER DOKUMEN
         ======================================== -->
    <div class="header">
        <h2>DATA SISWA EKSTRAKURIKULER</h2>
        <p>SMK Negeri 1 Contoh</p>
        <p>Tahun Ajaran <?php echo date('Y') . '/' . (date('Y')+1); ?></p>
    </div>
    
    <!-- ========================================
         INFO BOX
         ======================================== -->
    <div class="info-box">
        <strong>Informasi Export:</strong><br>
        Tanggal Export: <?php echo date('d F Y, H:i:s'); ?><br>
        Total Data: <?php echo $total_data; ?> siswa<br>
        Export Oleh: <?php echo htmlspecialchars($user['nama'] ?? 'Administrator'); ?>
        <?php if (!empty($filter_ekstra)): ?>
        <br><strong style="color: #dc2626;">Filter Ekstrakurikuler: <?php echo htmlspecialchars($filter_ekstra); ?></strong>
        <?php endif; ?>
        <?php if (!empty($filter_jurusan)): ?>
        <br><strong style="color: #dc2626;">Filter Jurusan: <?php echo htmlspecialchars($filter_jurusan); ?></strong>
        <?php endif; ?>
    </div>

    <!-- ========================================
         TABEL DATA SISWA
         ======================================== -->
    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="9%">NIS</th>
                <th width="16%">Nama Siswa</th>
                <th width="7%">Kelas</th>
                <th width="8%">Jurusan</th>
                <th width="14%">Ekstrakurikuler</th>
                <th width="11%">Pembina</th>
                <th width="11%">Jadwal</th>
                <th width="10%">No. HP</th>
                <th width="18%">Alamat</th>
                <th width="11%">Tanggal Daftar</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($total_data > 0) {
                $no = 1;
                while ($data = mysqli_fetch_array($query)) {
                    // Format jadwal
                    $jadwal = '-';
                    if (!empty($data['hari']) && !empty($data['waktu'])) {
                        $jadwal = htmlspecialchars($data['hari']) . ', ' . htmlspecialchars($data['waktu']);
                    }
                    
                    // Format tanggal
                    $tanggal = new DateTime($data['tanggal_daftar']);
                    $tanggal_format = $tanggal->format('d/m/Y H:i');
                    
                    // Badge warna jurusan
                    $badge_jurusan_class = 'badge-primary';
                    if ($data['jurusan'] == 'PPLG') $badge_jurusan_class = 'badge-primary';
                    elseif ($data['jurusan'] == 'TJKT') $badge_jurusan_class = 'badge-success';
                    elseif ($data['jurusan'] == 'BUSANA') $badge_jurusan_class = 'badge-warning';
            ?>
            <tr>
                <td class="text-center"><?php echo $no; ?></td>
                <td class="text-center"><strong><?php echo htmlspecialchars($data['nis']); ?></strong></td>
                <td><?php echo htmlspecialchars($data['nama_siswa']); ?></td>
                <td class="text-center">
                    <span class="badge badge-primary"><?php echo htmlspecialchars($data['kelas']); ?></span>
                </td>
                <td class="text-center">
                    <?php if (!empty($data['jurusan'])): ?>
                        <span class="badge <?php echo $badge_jurusan_class; ?>">
                            <?php echo htmlspecialchars($data['jurusan']); ?>
                        </span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge badge-info"><?php echo htmlspecialchars($data['nama_ekstra']); ?></span>
                </td>
                <td><?php echo htmlspecialchars($data['pembina'] ?? '-'); ?></td>
                <td><?php echo $jadwal; ?></td>
                <td class="text-center">
                    <?php echo !empty($data['no_hp']) ? htmlspecialchars($data['no_hp']) : '-'; ?>
                </td>
                <td><?php echo !empty($data['alamat']) ? htmlspecialchars($data['alamat']) : '-'; ?></td>
                <td class="text-center"><?php echo $tanggal_format; ?></td>
            </tr>
            <?php 
                    $no++;
                }
            } else {
            ?>
            <tr>
                <td colspan="11" class="text-center" style="padding: 20px; color: #9ca3af; font-style: italic;">
                    Tidak ada data yang ditemukan
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- ========================================
         FOOTER DOKUMEN
         ======================================== -->
    <div class="footer">
        <strong>Catatan:</strong><br>
        <ul style="margin: 5px 0; padding-left: 20px;">
            <li>Data ini adalah hasil export otomatis dari sistem informasi ekstrakurikuler</li>
            <li>Mohon periksa kembali data sebelum digunakan untuk keperluan resmi</li>
            <li>Untuk pertanyaan hubungi administrator sistem</li>
        </ul>
        
        <hr style="margin: 10px 0; border: 0; border-top: 1px solid #d1d5db;">
        
        <table width="100%" style="border: none;">
            <tr>
                <td width="50%" style="border: none; padding: 5px 0;">
                    <strong>Total Data:</strong> <?php echo $total_data; ?> siswa
                </td>
                <td width="50%" style="border: none; padding: 5px 0; text-align: right;">
                    <strong>Waktu Export:</strong> <?php echo date('d F Y, H:i:s'); ?>
                </td>
            </tr>
            <tr>
                <td style="border: none; padding: 5px 0;">
                    <strong>Export Oleh:</strong> <?php echo htmlspecialchars($user['nama'] ?? 'Administrator'); ?>
                </td>
                <td style="border: none; padding: 5px 0; text-align: right;">
                    <strong>Role:</strong> <?php echo ucfirst($user_role); ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- ========================================
         STATISTIK TAMBAHAN (OPSIONAL)
         ======================================== -->
    <?php
    // Hitung statistik per ekstrakurikuler dan jurusan
    mysqli_data_seek($query, 0); // Reset pointer
    $stats_ekstra = [];
    $stats_jurusan = [];
    
    while ($data = mysqli_fetch_array($query)) {
        $ekstra = $data['nama_ekstra'];
        $jurusan = $data['jurusan'] ?? 'Belum Set';
        
        if (!isset($stats_ekstra[$ekstra])) {
            $stats_ekstra[$ekstra] = 0;
        }
        $stats_ekstra[$ekstra]++;
        
        if (!isset($stats_jurusan[$jurusan])) {
            $stats_jurusan[$jurusan] = 0;
        }
        $stats_jurusan[$jurusan]++;
    }
    
    if (count($stats_ekstra) > 0) {
    ?>
    <div style="margin-top: 30px; page-break-before: always;">
        <h3 style="color: #1e40af; border-bottom: 2px solid #1e40af; padding-bottom: 5px;">
            📊 STATISTIK PER EKSTRAKURIKULER
        </h3>
        
        <table style="width: 60%; margin-top: 15px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Ekstrakurikuler</th>
                    <th>Jumlah Siswa</th>
                    <th>Persentase</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stat_no = 1;
                arsort($stats_ekstra); // Urutkan dari terbanyak
                foreach ($stats_ekstra as $ekstra => $jumlah) {
                    $persentase = ($jumlah / $total_data) * 100;
                ?>
                <tr>
                    <td class="text-center"><?php echo $stat_no; ?></td>
                    <td><?php echo htmlspecialchars($ekstra); ?></td>
                    <td class="text-center"><strong><?php echo $jumlah; ?></strong></td>
                    <td class="text-center"><?php echo number_format($persentase, 1); ?>%</td>
                </tr>
                <?php
                    $stat_no++;
                }
                ?>
            </tbody>
        </table>
        
        <h3 style="color: #1e40af; border-bottom: 2px solid #1e40af; padding-bottom: 5px; margin-top: 30px;">
            📚 STATISTIK PER JURUSAN
        </h3>
        
        <table style="width: 60%; margin-top: 15px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Jurusan</th>
                    <th>Jumlah Siswa</th>
                    <th>Persentase</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stat_no = 1;
                arsort($stats_jurusan); // Urutkan dari terbanyak
                foreach ($stats_jurusan as $jurusan => $jumlah) {
                    $persentase = ($jumlah / $total_data) * 100;
                    
                    // Badge warna
                    $badge_class = 'badge-primary';
                    if ($jurusan == 'PPLG') $badge_class = 'badge-primary';
                    elseif ($jurusan == 'TJKT') $badge_class = 'badge-success';
                    elseif ($jurusan == 'BUSANA') $badge_class = 'badge-warning';
                ?>
                <tr>
                    <td class="text-center"><?php echo $stat_no; ?></td>
                    <td>
                        <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($jurusan); ?></span>
                    </td>
                    <td class="text-center"><strong><?php echo $jumlah; ?></strong></td>
                    <td class="text-center"><?php echo number_format($persentase, 1); ?>%</td>
                </tr>
                <?php
                    $stat_no++;
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php } ?>

</body>
</html>
<?php
// Close koneksi database
mysqli_close($koneksi);
?>