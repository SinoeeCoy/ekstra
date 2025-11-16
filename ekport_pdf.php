<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

// ============================================
// EXPORT PDF - Data Siswa Ekstrakurikuler
// ============================================

// Include library FPDF
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

// Include koneksi database
include "koneksi.php";

// ============================================
// 1. CEK AUTENTIKASI & AUTHORIZATION
// ============================================
$user_role = strtolower($user['role'] ?? '');
$can_edit = in_array($user_role, ['pembina', 'admin', 'waka', 'kepala']);

// ============================================
// 2. AMBIL PARAMETER FILTER (Jika Ada)
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
// 3. QUERY DATA DARI DATABASE
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

// ============================================
// 4. BUAT CLASS PDF CUSTOM (Extends FPDF)
// ============================================
class PDF extends FPDF {
    
    // Global variable untuk filter
    private $filterText = '';
    private $filterJurusan = '';
    
    // Method untuk set filter text
    function setFilter($text, $jurusan = '') {
        $this->filterText = $text;
        $this->filterJurusan = $jurusan;
    }
    
    // Header - Akan muncul di setiap halaman
    function Header() {
        // Judul
        $this->SetFont('Arial', 'B', 18);
        $this->Cell(0, 10, 'DATA SISWA EKSTRAKURIKULER', 0, 1, 'C');
        
        // Sub judul
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 6, 'SMK Negeri 1 Contoh', 0, 1, 'C');
        
        // Tanggal Export
        $this->SetFont('Arial', 'I', 9);
        $this->Cell(0, 5, 'Tanggal Export: ' . date('d F Y, H:i:s'), 0, 1, 'C');
        
        // Filter (jika ada)
        if (!empty($this->filterText) || !empty($this->filterJurusan)) {
            $this->SetFont('Arial', 'B', 9);
            $this->SetTextColor(220, 53, 69); // Merah
            $filterStr = '';
            if (!empty($this->filterText)) {
                $filterStr .= 'Ekstrakurikuler: ' . $this->filterText;
            }
            if (!empty($this->filterJurusan)) {
                if ($filterStr) $filterStr .= ' | ';
                $filterStr .= 'Jurusan: ' . $this->filterJurusan;
            }
            $this->Cell(0, 5, 'Filter: ' . $filterStr, 0, 1, 'C');
            $this->SetTextColor(0, 0, 0); // Kembali hitam
        }
        
        $this->Ln(3);
        
        // ========================================
        // HEADER TABEL
        // ========================================
        $this->SetFont('Arial', 'B', 7);
        $this->SetFillColor(30, 64, 175); // Biru tua
        $this->SetTextColor(255, 255, 255); // Putih
        $this->SetDrawColor(30, 64, 175); // Border biru
        
        // Kolom header
        $this->Cell(8, 8, 'No', 1, 0, 'C', true);
        $this->Cell(22, 8, 'NIS', 1, 0, 'C', true);
        $this->Cell(40, 8, 'Nama Siswa', 1, 0, 'C', true);
        $this->Cell(15, 8, 'Kelas', 1, 0, 'C', true);
        $this->Cell(18, 8, 'Jurusan', 1, 0, 'C', true);
        $this->Cell(35, 8, 'Ekstrakurikuler', 1, 0, 'C', true);
        $this->Cell(30, 8, 'Pembina', 1, 0, 'C', true);
        $this->Cell(25, 8, 'No. HP', 1, 0, 'C', true);
        $this->Cell(30, 8, 'Tgl Daftar', 1, 1, 'C', true);
        
        // Reset warna untuk isi tabel
        $this->SetTextColor(0, 0, 0);
        $this->SetDrawColor(200, 200, 200); // Border abu-abu
    }
    
    // Footer - Akan muncul di setiap halaman
    function Footer() {
        // Posisi 15 mm dari bawah
        $this->SetY(-15);
        
        // Garis horizontal
        $this->SetDrawColor(30, 64, 175);
        $this->Cell(0, 0, '', 'T', 1);
        
        $this->Ln(2);
        
        // Informasi footer
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        
        // Halaman
        $this->Cell(0, 5, 'Halaman ' . $this->PageNo() . ' dari {nb}', 0, 0, 'C');
        
        // Kembali ke warna hitam
        $this->SetTextColor(0, 0, 0);
    }
}

// ============================================
// 5. INISIALISASI PDF
// ============================================
$pdf = new PDF('L', 'mm', 'A4'); // L = Landscape (horizontal)
$pdf->AliasNbPages(); // Untuk total halaman {nb}
$pdf->AddPage();
$pdf->SetFont('Arial', '', 7);
$pdf->SetAutoPageBreak(true, 20); // Auto page break 20mm dari bawah

// Set filter text jika ada
$pdf->setFilter($filter_ekstra, $filter_jurusan);

// ============================================
// 6. ISI TABEL DENGAN DATA
// ============================================
$no = 1;
$total_data = 0;
$stats_jurusan = [];

while ($data = mysqli_fetch_array($query)) {
    // Cek jika perlu halaman baru
    if ($pdf->GetY() > 180) {
        $pdf->AddPage();
    }
    
    // Format tanggal
    $tanggal = new DateTime($data['tanggal_daftar']);
    $tanggal_format = $tanggal->format('d/m/Y H:i');
    
    // Potong text yang terlalu panjang
    $nama_siswa = strlen($data['nama_siswa']) > 25 ? 
                  substr($data['nama_siswa'], 0, 22) . '...' : 
                  $data['nama_siswa'];
    
    $nama_ekstra = strlen($data['nama_ekstra']) > 22 ? 
                   substr($data['nama_ekstra'], 0, 19) . '...' : 
                   $data['nama_ekstra'];
    
    $pembina = strlen($data['pembina'] ?? '-') > 20 ? 
               substr($data['pembina'] ?? '-', 0, 17) . '...' : 
               ($data['pembina'] ?? '-');
    
    $no_hp = $data['no_hp'] ?? '-';
    $jurusan = $data['jurusan'] ?? '-';
    
    // Hitung statistik jurusan
    if (!isset($stats_jurusan[$jurusan])) {
        $stats_jurusan[$jurusan] = 0;
    }
    $stats_jurusan[$jurusan]++;
    
    // Baris zebra (warna bergantian)
    if ($no % 2 == 0) {
        $pdf->SetFillColor(245, 245, 245); // Abu-abu muda
        $fill = true;
    } else {
        $fill = false;
    }
    
    // Isi tabel
    $pdf->Cell(8, 6, $no, 1, 0, 'C', $fill);
    $pdf->Cell(22, 6, $data['nis'], 1, 0, 'L', $fill);
    $pdf->Cell(40, 6, $nama_siswa, 1, 0, 'L', $fill);
    $pdf->Cell(15, 6, $data['kelas'], 1, 0, 'C', $fill);
    $pdf->Cell(18, 6, $jurusan, 1, 0, 'C', $fill);
    $pdf->Cell(35, 6, $nama_ekstra, 1, 0, 'L', $fill);
    $pdf->Cell(30, 6, $pembina, 1, 0, 'L', $fill);
    $pdf->Cell(25, 6, $no_hp, 1, 0, 'L', $fill);
    $pdf->Cell(30, 6, $tanggal_format, 1, 1, 'C', $fill);
    
    $no++;
    $total_data++;
}

// Jika tidak ada data
if ($total_data == 0) {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->Cell(0, 20, 'Tidak ada data yang ditemukan', 0, 1, 'C');
}

// ============================================
// 7. INFORMASI FOOTER DOKUMEN
// ============================================
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, 'Ringkasan Data:', 0, 1, 'L');

$pdf->SetFont('Arial', '', 9);
$pdf->Cell(50, 6, 'Total Siswa: ' . $total_data, 0, 1, 'L');

// Tampilkan statistik per jurusan
foreach ($stats_jurusan as $jur => $jml) {
    $pdf->Cell(50, 6, '- Jurusan ' . $jur . ': ' . $jml . ' siswa', 0, 1, 'L');
}

// ============================================
// 9. OUTPUT PDF
// ============================================
$filename = 'Data_Siswa_Ekstrakurikuler_' . date('Ymd_His') . '.pdf';

// Output ke browser (force download)
$pdf->Output('D', $filename);

// Jika ingin langsung tampil di browser, gunakan:
// $pdf->Output('I', $filename);