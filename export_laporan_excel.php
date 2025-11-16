<?php
include "koneksi.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// Filter
$filter_ekstra = isset($_GET['filter_ekstra']) ? $_GET['filter_ekstra'] : '';
$filter_tahun = isset($_GET['filter_tahun']) ? $_GET['filter_tahun'] : date('Y');

// Build WHERE clause
$where_clause = "WHERE 1=1";
if (!empty($filter_ekstra)) {
    $filter_ekstra_escaped = mysqli_real_escape_string($koneksi, $filter_ekstra);
    $where_clause .= " AND nama_ekstra = '$filter_ekstra_escaped'";
}
if (!empty($filter_tahun)) {
    $where_clause .= " AND YEAR(tanggal) = '$filter_tahun'";
}

$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Nama file export
$filename = "laporan_ringkasan_" . ($filter_ekstra ?: "semua") . "_" . $filter_tahun . "_" . date('Ymd') . ".xls";

// Set header untuk Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

echo "<h2 align='center'>LAPORAN RINGKASAN EKSTRAKURIKULER</h2>";
echo "<p align='center'>";
if (!empty($filter_ekstra)) echo "Ekstrakurikuler: " . htmlspecialchars($filter_ekstra) . " | ";
echo "Tahun: " . $filter_tahun;
echo "</p>";
echo "<p align='center'>Dicetak pada: " . date('d/m/Y H:i') . "</p>";
echo "<br>";

// Query ringkasan
$query_ringkasan = "
    SELECT 
        nama_ekstra,
        COUNT(DISTINCT tanggal) AS total_pertemuan,
        COUNT(*) AS total_kehadiran
    FROM absen
    $where_clause
    GROUP BY nama_ekstra
    ORDER BY total_pertemuan DESC, nama_ekstra ASC
";
$result_ringkasan = mysqli_query($koneksi, $query_ringkasan);

// TABEL RINGKASAN PER EKSTRAKURIKULER
echo "<h3>RINGKASAN PER EKSTRAKURIKULER</h3>";
echo "<table border='1'>";
echo "<tr style='background-color:#1e40af; color:white; font-weight:bold;'>
        <th>No</th>
        <th>Ekstrakurikuler</th>
        <th>Total Pertemuan</th>
        <th>Total Kehadiran</th>
        <th>Rata-rata Kehadiran</th>
      </tr>";

$no = 1;
while ($row = mysqli_fetch_assoc($result_ringkasan)) {
    $rata_rata = $row['total_pertemuan'] > 0 ? round($row['total_kehadiran'] / $row['total_pertemuan'], 1) : 0;
    
    echo "<tr>
            <td align='center'>$no</td>
            <td>" . htmlspecialchars($row['nama_ekstra']) . "</td>
            <td align='center'>{$row['total_pertemuan']} kali</td>
            <td align='center'>{$row['total_kehadiran']} siswa</td>
            <td align='center'>{$rata_rata} siswa/pertemuan</td>
          </tr>";
    $no++;
}
echo "</table>";

echo "<br><br>";

// Reset query untuk detail per bulan
mysqli_data_seek($result_ringkasan, 0);

// DETAIL PER BULAN UNTUK SETIAP EKSTRAKURIKULER
echo "<h3>DETAIL PER BULAN</h3>";

$result_ringkasan = mysqli_query($koneksi, $query_ringkasan);
while ($row = mysqli_fetch_assoc($result_ringkasan)) {
    echo "<br>";
    echo "<h4 style='background-color:#1e40af; color:white; padding:5px;'>" . htmlspecialchars($row['nama_ekstra']) . " - Tahun $filter_tahun</h4>";
    
    echo "<table border='1'>";
    echo "<tr style='background-color:#6c757d; color:white; font-weight:bold;'>
            <th>No</th>
            <th>Bulan</th>
            <th>Jumlah Pertemuan</th>
            <th>Total Kehadiran</th>
            <th>Rata-rata Siswa</th>
          </tr>";
    
    // Query detail per bulan
    $ekstra_escaped = mysqli_real_escape_string($koneksi, $row['nama_ekstra']);
    $query_detail = "
        SELECT 
            MONTH(tanggal) AS bulan,
            COUNT(DISTINCT tanggal) AS jumlah_pertemuan,
            COUNT(*) AS total_kehadiran
        FROM absen
        WHERE nama_ekstra = '$ekstra_escaped' AND YEAR(tanggal) = '$filter_tahun'
        GROUP BY MONTH(tanggal)
        ORDER BY bulan ASC
    ";
    $result_detail = mysqli_query($koneksi, $query_detail);
    
    if (mysqli_num_rows($result_detail) > 0) {
        $no_detail = 1;
        while ($detail = mysqli_fetch_assoc($result_detail)) {
            $rata_detail = $detail['jumlah_pertemuan'] > 0 ? round($detail['total_kehadiran'] / $detail['jumlah_pertemuan'], 1) : 0;
            
            echo "<tr>
                    <td align='center'>$no_detail</td>
                    <td>{$nama_bulan[$detail['bulan']]}</td>
                    <td align='center'>{$detail['jumlah_pertemuan']} kali</td>
                    <td align='center'>{$detail['total_kehadiran']} siswa</td>
                    <td align='center'>{$rata_detail} siswa/pertemuan</td>
                  </tr>";
            $no_detail++;
        }
    } else {
        echo "<tr><td colspan='5' align='center'>Tidak ada data untuk tahun ini</td></tr>";
    }
    
    echo "</table>";
}

exit;
?>