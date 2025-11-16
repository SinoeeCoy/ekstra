<?php
require('lib/fpdf/fpdf.php');
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

// Custom PDF Class
class PDF extends FPDF {
    private $filter_info = '';
    
    function setFilterInfo($info) {
        $this->filter_info = $info;
    }
    
    function Header() {
        $this->SetFont('Arial','B',16);
        $this->Cell(0,10,'LAPORAN RINGKASAN EKSTRAKURIKULER',0,1,'C');
        
        if (!empty($this->filter_info)) {
            $this->SetFont('Arial','',11);
            $this->Cell(0,6,$this->filter_info,0,1,'C');
        }
        
        $this->SetFont('Arial','I',9);
        $this->Cell(0,6,'Dicetak pada '.date('d/m/Y H:i'),0,1,'C');
        $this->Ln(5);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo().' dari {nb}',0,0,'C');
    }
    
    function SectionTitle($title) {
        $this->SetFont('Arial','B',12);
        $this->SetFillColor(233,236,239);
        $this->Cell(0,8,$title,0,1,'L',true);
        $this->Ln(2);
    }
    
    function SubSectionTitle($title) {
        $this->SetFont('Arial','B',11);
        $this->SetFillColor(30,64,175);
        $this->SetTextColor(255,255,255);
        $this->Cell(0,7,$title,1,1,'L',true);
        $this->SetTextColor(0,0,0);
        $this->Ln(1);
    }
}

$pdf = new PDF('P','mm','A4');
$pdf->AliasNbPages();

// Set filter info
$filter_info = '';
if (!empty($filter_ekstra)) $filter_info .= "Ekstrakurikuler: " . $filter_ekstra . " | ";
$filter_info .= "Tahun: " . $filter_tahun;
$pdf->setFilterInfo($filter_info);

$pdf->AddPage();

// TABEL RINGKASAN PER EKSTRAKURIKULER
$pdf->SectionTitle('RINGKASAN PER EKSTRAKURIKULER');

// Header tabel ringkasan
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(30,64,175);
$pdf->SetTextColor(255,255,255);
$pdf->Cell(10,8,'No',1,0,'C',true);
$pdf->Cell(70,8,'Ekstrakurikuler',1,0,'C',true);
$pdf->Cell(35,8,'Total Pertemuan',1,0,'C',true);
$pdf->Cell(35,8,'Total Kehadiran',1,0,'C',true);
$pdf->Cell(40,8,'Rata-rata',1,1,'C',true);
$pdf->SetTextColor(0,0,0);

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

$pdf->SetFont('Arial','',9);
$no = 1;

while ($row = mysqli_fetch_assoc($result_ringkasan)) {
    $rata_rata = $row['total_pertemuan'] > 0 ? round($row['total_kehadiran'] / $row['total_pertemuan'], 1) : 0;
    
    $pdf->Cell(10,7,$no,1,0,'C');
    $pdf->Cell(70,7,$row['nama_ekstra'],1,0);
    $pdf->Cell(35,7,$row['total_pertemuan'].' kali',1,0,'C');
    $pdf->Cell(35,7,$row['total_kehadiran'].' siswa',1,0,'C');
    $pdf->Cell(40,7,$rata_rata.' siswa/pertemuan',1,1,'C');
    
    $no++;
}

// DETAIL PER BULAN
$pdf->Ln(10);
$pdf->SectionTitle('DETAIL PER BULAN');

// Reset query
$result_ringkasan = mysqli_query($koneksi, $query_ringkasan);

while ($row = mysqli_fetch_assoc($result_ringkasan)) {
    // Cek apakah perlu halaman baru
    if ($pdf->GetY() > 230) {
        $pdf->AddPage();
    }
    
    $pdf->Ln(3);
    $pdf->SubSectionTitle($row['nama_ekstra'] . ' - Tahun ' . $filter_tahun);
    
    // Header tabel detail
    $pdf->SetFont('Arial','B',9);
    $pdf->SetFillColor(108,117,125);
    $pdf->SetTextColor(255,255,255);
    $pdf->Cell(10,7,'No',1,0,'C',true);
    $pdf->Cell(50,7,'Bulan',1,0,'C',true);
    $pdf->Cell(40,7,'Jumlah Pertemuan',1,0,'C',true);
    $pdf->Cell(40,7,'Total Kehadiran',1,0,'C',true);
    $pdf->Cell(50,7,'Rata-rata Siswa',1,1,'C',true);
    $pdf->SetTextColor(0,0,0);
    
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
    
    $pdf->SetFont('Arial','',9);
    
    if (mysqli_num_rows($result_detail) > 0) {
        $no_detail = 1;
        while ($detail = mysqli_fetch_assoc($result_detail)) {
            // Cek apakah perlu halaman baru
            if ($pdf->GetY() > 270) {
                $pdf->AddPage();
                // Ulangi header
                $pdf->SubSectionTitle($row['nama_ekstra'] . ' - Tahun ' . $filter_tahun . ' (lanjutan)');
                $pdf->SetFont('Arial','B',9);
                $pdf->SetFillColor(108,117,125);
                $pdf->SetTextColor(255,255,255);
                $pdf->Cell(10,7,'No',1,0,'C',true);
                $pdf->Cell(50,7,'Bulan',1,0,'C',true);
                $pdf->Cell(40,7,'Jumlah Pertemuan',1,0,'C',true);
                $pdf->Cell(40,7,'Total Kehadiran',1,0,'C',true);
                $pdf->Cell(50,7,'Rata-rata Siswa',1,1,'C',true);
                $pdf->SetTextColor(0,0,0);
                $pdf->SetFont('Arial','',9);
            }
            
            $rata_detail = $detail['jumlah_pertemuan'] > 0 ? round($detail['total_kehadiran'] / $detail['jumlah_pertemuan'], 1) : 0;
            
            $pdf->Cell(10,6,$no_detail,1,0,'C');
            $pdf->Cell(50,6,$nama_bulan[$detail['bulan']],1,0);
            $pdf->Cell(40,6,$detail['jumlah_pertemuan'].' kali',1,0,'C');
            $pdf->Cell(40,6,$detail['total_kehadiran'].' siswa',1,0,'C');
            $pdf->Cell(50,6,$rata_detail.' siswa/pertemuan',1,1,'C');
            
            $no_detail++;
        }
    } else {
        $pdf->SetFont('Arial','I',9);
        $pdf->Cell(190,6,'Tidak ada data untuk tahun ini',1,1,'C');
    }
}

$pdf->Output('D', 'laporan_ringkasan_' . ($filter_ekstra ?: 'semua') . '_' . $filter_tahun . '_' . date('Ymd') . '.pdf');
exit;
?>