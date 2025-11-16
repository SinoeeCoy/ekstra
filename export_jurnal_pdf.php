<?php
// File: export_jurnal_pdf.php
// Export History Jurnal ke PDF menggunakan FPDF (bukan TCPDF)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// Include library FPDF (sama seperti export siswa)
if (file_exists(__DIR__ . '/lib/fpdf/fpdf.php')) {
    require_once(__DIR__ . '/lib/fpdf/fpdf.php');
} elseif (file_exists('lib/fpdf/fpdf.php')) {
    require_once('lib/fpdf/fpdf.php');
} elseif (file_exists('../lib/fpdf/fpdf.php')) {
    require_once('../lib/fpdf/fpdf.php');
} elseif (file_exists('fpdf/fpdf.php')) {
    require_once('fpdf/fpdf.php');
} else {
    die("ERROR: File fpdf.php tidak ditemukan!<br><br>
         <strong>Solusi:</strong><br>
         1. Download FPDF dari: <a href='http://www.fpdf.org' target='_blank'>http://www.fpdf.org</a><br>
         2. Extract file zip yang didownload<br>
         3. Copy file fpdf.php ke folder: <code>lib/fpdf/</code><br>
         4. Refresh halaman ini");
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

// ============================================
// CUSTOM PDF CLASS
// ============================================
class PDF extends FPDF {
    private $filterExstra = '';
    private $filterDari = '';
    private $filterSampai = '';
    
    function setFilter($ekstra, $dari, $sampai) {
        $this->filterExstra = $ekstra;
        $this->filterDari = $dari;
        $this->filterSampai = $sampai;
    }
    
    // Header
    function Header() {
        // Judul
        $this->SetFont('Arial', 'B', 18);
        $this->Cell(0, 10, 'HISTORY JURNAL LATIHAN EKSTRAKURIKULER', 0, 1, 'C');
        
        // Sub judul
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 6, 'SISTEM INFORMASI EKSTRAKURIKULER', 0, 1, 'C');
        
        // Tanggal Export
        $this->SetFont('Arial', 'I', 9);
        $this->Cell(0, 5, 'Tanggal Export: ' . date('d F Y, H:i:s'), 0, 1, 'C');
        
        // Filter (jika ada)
        if (!empty($this->filterExstra) || !empty($this->filterDari) || !empty($this->filterSampai)) {
            $this->SetFont('Arial', 'B', 9);
            $this->SetTextColor(220, 53, 69); // Merah
            $filterStr = 'Filter: ';
            if (!empty($this->filterExstra)) {
                $filterStr .= 'Ekskul: ' . $this->filterExstra;
            }
            if (!empty($this->filterDari)) {
                if ($filterStr != 'Filter: ') $filterStr .= ' | ';
                $filterStr .= 'Dari: ' . date('d/m/Y', strtotime($this->filterDari));
            }
            if (!empty($this->filterSampai)) {
                $filterStr .= ' - ' . date('d/m/Y', strtotime($this->filterSampai));
            }
            $this->Cell(0, 5, $filterStr, 0, 1, 'C');
            $this->SetTextColor(0, 0, 0);
        }
        
        $this->Ln(3);
        
        // Header Tabel
        $this->SetFont('Arial', 'B', 7);
        $this->SetFillColor(30, 64, 175);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(30, 64, 175);
        
        $this->Cell(10, 8, 'No', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Tanggal', 1, 0, 'C', true);
        $this->Cell(45, 8, 'Nama Siswa', 1, 0, 'C', true);
        $this->Cell(20, 8, 'Kelas', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Jurusan', 1, 0, 'C', true);
        $this->Cell(40, 8, 'Ekstrakurikuler', 1, 0, 'C', true);
        $this->Cell(45, 8, 'Materi', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Keterangan', 1, 0, 'C', true);
        $this->Cell(15, 8, 'Nilai', 1, 1, 'C', true);
        
        $this->SetTextColor(0, 0, 0);
        $this->SetDrawColor(200, 200, 200);
    }
    
    // Footer
    function Footer() {
        $this->SetY(-15);
        $this->SetDrawColor(30, 64, 175);
        $this->Cell(0, 0, '', 'T', 1);
        $this->Ln(2);
        
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, 'Halaman ' . $this->PageNo() . ' dari {nb}', 0, 0, 'C');
        $this->SetTextColor(0, 0, 0);
    }
}

// Inisialisasi PDF
$pdf = new PDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 7);
$pdf->SetAutoPageBreak(true, 20);

// Set filter
$pdf->setFilter($filter_ekstra, $filter_tanggal_dari, $filter_tanggal_sampai);

// Isi data
$no = 1;
$total_data = 0;
$stats = ['Hadir' => 0, 'Izin' => 0, 'Alfa' => 0];

while ($data = mysqli_fetch_array($query)) {
    // Cek page break
    if ($pdf->GetY() > 180) {
        $pdf->AddPage();
    }
    
    // Potong text panjang
    $nama_siswa = strlen($data['nama_siswa']) > 25 ? 
                  substr($data['nama_siswa'], 0, 22) . '...' : 
                  $data['nama_siswa'];
    
    $nama_ekstra = strlen($data['nama_ekstra']) > 22 ? 
                   substr($data['nama_ekstra'], 0, 19) . '...' : 
                   $data['nama_ekstra'];
    
    $materi = !empty($data['materi']) ? 
              (strlen($data['materi']) > 30 ? substr($data['materi'], 0, 27) . '...' : $data['materi']) : 
              '-';
    
    $jurusan = !empty($data['jurusan']) ? $data['jurusan'] : '-';
    $nilai = !empty($data['nilai']) ? $data['nilai'] : '-';
    
    // Hitung statistik
    $stats[$data['keterangan']]++;
    
    // Zebra striping
    $fill = ($no % 2 == 0);
    if ($fill) {
        $pdf->SetFillColor(245, 245, 245);
    }
    
    // Isi tabel
    $pdf->Cell(10, 6, $no, 1, 0, 'C', $fill);
    $pdf->Cell(25, 6, date('d/m/Y', strtotime($data['tanggal'])), 1, 0, 'C', $fill);
    $pdf->Cell(45, 6, $nama_siswa, 1, 0, 'L', $fill);
    $pdf->Cell(20, 6, $data['kelas'], 1, 0, 'C', $fill);
    $pdf->Cell(25, 6, $jurusan, 1, 0, 'C', $fill);
    $pdf->Cell(40, 6, $nama_ekstra, 1, 0, 'L', $fill);
    $pdf->Cell(45, 6, $materi, 1, 0, 'L', $fill);
    $pdf->Cell(25, 6, $data['keterangan'], 1, 0, 'C', $fill);
    $pdf->Cell(15, 6, $nilai, 1, 1, 'C', $fill);
    
    $no++;
    $total_data++;
}

// Jika tidak ada data
if ($total_data == 0) {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->Cell(0, 20, 'Tidak ada data yang ditemukan', 0, 1, 'C');
}

// Footer dokumen
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, 'Ringkasan Data:', 0, 1, 'L');

$pdf->SetFont('Arial', '', 9);
$pdf->Cell(50, 6, 'Total Record: ' . $total_data, 0, 1, 'L');
$pdf->Cell(50, 6, 'Total Hadir: ' . $stats['Hadir'], 0, 1, 'L');
$pdf->Cell(50, 6, 'Total Izin: ' . $stats['Izin'], 0, 1, 'L');
$pdf->Cell(50, 6, 'Total Alfa: ' . $stats['Alfa'], 0, 1, 'L');

$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 5, 'Dicetak oleh: ' . $_SESSION['user']['username'] . ' pada ' . date('d/m/Y H:i:s'), 0, 1, 'L');

// Output
$filename = 'History_Jurnal_' . date('YmdHis') . '.pdf';
$pdf->Output('D', $filename); // D = Download, I = Inline browser
?>