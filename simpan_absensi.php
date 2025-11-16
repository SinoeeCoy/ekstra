<?php
// File: simpan_absensi.php
// Proses simpan absensi dengan tanggal, materi, foto global

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

include "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $filter_ekstra = isset($_POST['filter_ekstra']) ? $_POST['filter_ekstra'] : '';
    
    // Ambil data global (tanggal, materi, foto)
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $materi = mysqli_real_escape_string($koneksi, $_POST['materi']);
    
    // Upload foto kegiatan (jika ada)
    $foto_name = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $upload_dir = 'uploads/kegiatan/';
        
        // Buat folder jika belum ada
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $foto_name = 'kegiatan_' . date('YmdHis') . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $foto_name;
            
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                $foto_name = ''; // Reset jika upload gagal
            }
        }
    }
    
    // Proses data absensi
    $absen_data = isset($_POST['absen']) ? $_POST['absen'] : [];
    $success_count = 0;
    $error_count = 0;
    
    if (!empty($absen_data)) {
        foreach ($absen_data as $siswa_id => $data) {
            // Skip jika keterangan kosong
            if (empty($data['keterangan']) || $data['keterangan'] == '') {
                continue;
            }
            
            // Ambil data siswa
            $query_siswa = mysqli_query($koneksi, "SELECT * FROM data_siswa WHERE id = '$siswa_id'");
            $siswa = mysqli_fetch_array($query_siswa);
            
            if ($siswa) {
                $nama_siswa = mysqli_real_escape_string($koneksi, $siswa['nama_siswa']);
                $kelas = mysqli_real_escape_string($koneksi, $siswa['kelas']);
                $jurusan = mysqli_real_escape_string($koneksi, $siswa['jurusan']);
                $nama_ekstra = mysqli_real_escape_string($koneksi, $siswa['nama_ekstra']);
                $keterangan = mysqli_real_escape_string($koneksi, $data['keterangan']);
                
                // FIX: Use empty string instead of NULL for nilai
                $nilai = !empty($data['nilai']) ? mysqli_real_escape_string($koneksi, $data['nilai']) : '';
                
                // Insert ke database
                $query_insert = "INSERT INTO absen (
                    tanggal, 
                    nama_siswa, 
                    kelas, 
                    jurusan, 
                    nama_ekstra, 
                    materi, 
                    keterangan, 
                    nilai, 
                    foto
                ) VALUES (
                    '$tanggal',
                    '$nama_siswa',
                    '$kelas',
                    '$jurusan',
                    '$nama_ekstra',
                    '$materi',
                    '$keterangan',
                    '$nilai',
                    '$foto_name'
                )";
                
                if (mysqli_query($koneksi, $query_insert)) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            }
        }
        
        // Redirect dengan status
        if ($success_count > 0 && $error_count == 0) {
            header("Location: home.php?page=absensi&filter_ekstra=$filter_ekstra&status=success&count=$success_count");
        } elseif ($success_count > 0 && $error_count > 0) {
            header("Location: home.php?page=absensi&filter_ekstra=$filter_ekstra&status=error&success=$success_count&error=$error_count");
        } else {
            header("Location: home.php?page=absensi&filter_ekstra=$filter_ekstra&status=empty");
        }
    } else {
        header("Location: home.php?page=absensi&filter_ekstra=$filter_ekstra&status=empty");
    }
} else {
    header("Location: home.php?page=absensi");
}
exit;
?>