-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 16 Nov 2025 pada 06.32
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ekstra`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `absen`
--

CREATE TABLE `absen` (
  `id` int(11) NOT NULL,
  `nama_siswa` varchar(255) NOT NULL,
  `tanggal` date NOT NULL,
  `kelas` varchar(20) NOT NULL,
  `jurusan` varchar(20) NOT NULL,
  `nama_ekstra` varchar(50) NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `materi` varchar(255) NOT NULL,
  `nilai` varchar(255) DEFAULT NULL,
  `foto` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_prestasi`
--

CREATE TABLE `data_prestasi` (
  `id_prestasi` int(11) NOT NULL,
  `nama_ekstra` varchar(100) NOT NULL,
  `nama_siswa` varchar(100) NOT NULL,
  `prestasi` varchar(255) NOT NULL,
  `tingkat` enum('Sekolah','Kecamatan','Kabupaten','Provinsi','Nasional','Internasional') NOT NULL,
  `tahun` year(4) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `tanggal_input` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `data_siswa`
--

CREATE TABLE `data_siswa` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama_siswa` varchar(100) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `kelas` varchar(10) NOT NULL,
  `jenis_kelamin` varchar(20) NOT NULL,
  `jurusan` varchar(50) NOT NULL,
  `no_hp` varchar(15) NOT NULL,
  `alamat` text NOT NULL,
  `ekstra_id` varchar(100) DEFAULT NULL,
  `tanggal_daftar` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `data_siswa`
--

INSERT INTO `data_siswa` (`id`, `user_id`, `nama_siswa`, `nis`, `kelas`, `jenis_kelamin`, `jurusan`, `no_hp`, `alamat`, `ekstra_id`, `tanggal_daftar`) VALUES
(4, 3, 'bahrudin', '12345', 'XII', 'Laki-laki', 'PPLG', '08123456789', 'Blora', '1', '2025-11-11 07:40:16'),
(5, 17, 'laila yunita', '299929999', 'XI', 'Perempuan', 'TJKT', '085135945428', 'celuwak', '1', '2025-11-13 13:17:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ekstra`
--

CREATE TABLE `ekstra` (
  `id` int(11) NOT NULL,
  `nama_ekstra` varchar(100) NOT NULL,
  `pembina` varchar(100) DEFAULT NULL,
  `hari` varchar(50) DEFAULT NULL,
  `waktu` varchar(50) DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `ekstra`
--

INSERT INTO `ekstra` (`id`, `nama_ekstra`, `pembina`, `hari`, `waktu`, `lokasi`) VALUES
(1, 'Kesenian (padus & angklung)', 'Ahsin. A, Md.', 'Kamis', '14:00:00', 'Laboratorium Seni'),
(2, 'Teater', 'Angga Widhi Pratama, S.Pd', 'Selasa', '14:30:00', 'Laboratorium Seni'),
(3, 'Rebana', 'Much. Nur Faqih Usman, S.Kom.I', 'Sabtu', '14:00:00', 'Musholla'),
(4, 'Pramuka', 'Cipto Aji Leksono, S.Pd', 'Ahad', '14:00:00', 'Halaman SMK'),
(5, 'Olahraga', 'Mohammad Misbachul Huda, S.Or', 'Rabu', '14:00:00', 'Halaman SMK'),
(6, 'Web Desain', 'Muhammad Fahmi \'Ainunnajib', 'Senin', '14:00:00', 'Laboratorium PPLG'),
(7, 'Pagar Nusa', 'Suhardi', 'Selasa', '15:00:00', 'Halaman SMK');

-- --------------------------------------------------------

--
-- Struktur dari tabel `galeri`
--

CREATE TABLE `galeri` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(255) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `tanggal_upload` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `galeri`
--

INSERT INTO `galeri` (`id`, `judul`, `deskripsi`, `foto`, `kategori`, `tanggal_upload`) VALUES
(1, 'ROBLOXXXXXX', 'takda', '1759023523_Cuplikan layar 2025-07-08 034250.png', 'Lainnya', '2025-09-28 08:38:43'),
(2, 'News', '#BOIKOT TRANS7', '1760597572_PRAY FOR AL KHOZINY.png', 'Lainnya', '2025-10-16 13:52:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `komentar_pengumuman`
--

CREATE TABLE `komentar_pengumuman` (
  `id` int(11) NOT NULL,
  `pengumuman_id` int(11) NOT NULL,
  `komentar` text NOT NULL,
  `nama_pengirim` varchar(100) NOT NULL,
  `role_pengirim` varchar(50) NOT NULL,
  `parent_id` int(11) DEFAULT NULL COMMENT 'ID komentar induk untuk balasan',
  `tanggal_kirim` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `komentar_pengumuman`
--

INSERT INTO `komentar_pengumuman` (`id`, `pengumuman_id`, `komentar`, `nama_pengirim`, `role_pengirim`, `parent_id`, `tanggal_kirim`) VALUES
(25, 9, 'Terima kasih informasinya, siap untuk mengikuti seleksi!', 'Fahmi Ainunnajib', 'Siswa', NULL, '2025-10-22 09:47:20'),
(26, 9, 'Mohon konfirmasi tempat seleksinya di mana?', 'Nur Faqih Usman', 'Pembina', NULL, '2025-10-22 09:47:20');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `prioritas` enum('Sangat Penting','Penting','Sedang') DEFAULT 'Sedang',
  `kategori` varchar(100) DEFAULT NULL COMMENT 'Nama ekstrakurikuler atau kosongkan untuk semua',
  `target_audience` enum('semua','siswa','pembina','admin') DEFAULT 'semua',
  `tanggal_mulai` date NOT NULL,
  `tanggal_berakhir` date NOT NULL,
  `tanggal_dibuat` datetime DEFAULT current_timestamp(),
  `dibuat_oleh` varchar(100) NOT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengumuman`
--

INSERT INTO `pengumuman` (`id`, `judul`, `isi`, `prioritas`, `kategori`, `target_audience`, `tanggal_mulai`, `tanggal_berakhir`, `tanggal_dibuat`, `dibuat_oleh`, `status`) VALUES
(9, 'Lomba Web Desain Nasional 2025', 'Bagi seluruh anggota ekstrakurikuler Web Desain, diharapkan mengikuti seleksi internal untuk lomba tingkat nasional yang akan dilaksanakan pada tanggal 10 November 2025. Persiapkan karya terbaik kalian!', '', 'Ekstrakurikuler', '', '2025-10-25', '2025-11-10', '2025-10-22 09:43:25', 'admin', 'aktif'),
(10, 'Kemungkinan Jadwal Baru', 'Dikarnakan jumlah siswa yang semakin alama semakin naik, kemungkinan akan di buatkan jadwal latiah baru yang bertujuan untuk mengefisienkan jadwal latihan', 'Sangat Penting', '', 'siswa', '2025-10-22', '2025-10-24', '2025-10-22 10:09:10', 'pembina', 'aktif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `profil_pembina`
--

CREATE TABLE `profil_pembina` (
  `id` int(11) NOT NULL,
  `nama_pembina` varchar(100) NOT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `pendidikan` varchar(200) DEFAULT NULL,
  `pengalaman` text DEFAULT NULL,
  `prestasi` text DEFAULT NULL,
  `bidang_keahlian` varchar(200) DEFAULT NULL,
  `pengalaman_mengajar` varchar(50) DEFAULT NULL,
  `ekstrakurikuler_diampu` varchar(200) DEFAULT NULL,
  `motto` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `profil_pembina`
--

INSERT INTO `profil_pembina` (`id`, `nama_pembina`, `tanggal_lahir`, `tempat_lahir`, `alamat`, `no_telp`, `email`, `pendidikan`, `pengalaman`, `prestasi`, `bidang_keahlian`, `pengalaman_mengajar`, `ekstrakurikuler_diampu`, `motto`, `foto`, `created_at`, `updated_at`) VALUES
(10, 'Muhammad Fahmi Ainunnajib', '2004-10-13', 'Pati', 'Ds.Bendokaton Kidul,Rt04,Rw01,Kec.Tayu,Kab.Pati', '0889-8800-1539', 'fahminajibboyo@gmail.com', 'Smk Salafiyah, Kuliah', 'Sekertaris Osis Smk Salafiyah 2021-2022', 'Juara 2 LKS IT Software For Bussines tahun 2022, Juara 1 LKS Web Tech 2025', 'IT Web Technology', '-', 'Web Desain', '-', NULL, '2025-11-11 03:58:00', NULL),
(13, 'Mohammad Misbachul Huda,S.Or.', '1995-12-24', 'Pati', 'Ds. Kajen RT 04 RW 01,Kec.Margoyoso,Kab.Pati', '085228455410', 'mohammad.misbachul.huda24@gmail.com', 'MA Salafiyah,Universitas Negri Semarang', 'UKM baseball/softball,pengurus Federasi Olahraga Petanque Indonesia (FOPI) Kabupaten Pati', 'Juara 3 kejurkab petanque 2021,Harapan 2 kejurprov petanque 2020', 'Olahraga', 'Di Smk Salafiyah dari tahun 2022-Sekarang', 'Olahraga', 'Sukses bukan tentang Siapa yang tercepat,tapi siapa yang tak pernah berhenti.', NULL, '2025-11-11 05:48:46', NULL),
(15, 'Sri Wahyuni', '1994-08-10', 'Pati', 'Tegalharjo 09/04,Trangkil,Pati', '081225498194', 'wahyuniii091@gmail.com', 'S1 Pendidikan Bahasa Inggris UIN Walisongo Semarang.', 'Pembina Osis Periode 24/25 - 25/26', NULL, 'Bahasa Inggris', 'Guru Bahasa Inggris Smk Salafiyah', 'Osis (Organisasi Siswa Intra Sekolah)', '\"Hidup Yang Tidak Diperjuangkan Tidak Akan Dimenangkan\"', NULL, '2025-11-16 01:44:46', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('pembina','admin','siswa') NOT NULL,
  `status` enum('aktif','nonaktif') NOT NULL,
  `nama_siswa` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `status`, `nama_siswa`) VALUES
(3, 'siswa1', '013f0f67779f3b1686c604db150d12ea', 'siswa', 'aktif', '0'),
(5, 'pembina', '377a610343a9812be993e0e755b2e00f', 'pembina', 'aktif', '0'),
(7, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin', 'aktif', '0'),
(16, '03.25.207', 'f8cb8d016586b0931bb99182a983aeeb', 'siswa', 'aktif', 'Ahmad Dawam Maskuri'),
(17, '03.25.208', 'bbb8026f5a6cb335f8c60d1380ab1734', 'siswa', 'aktif', 'Ahmad Nabi\' Syuhadak'),
(18, '03.25.210', '32f05ffbf47111c920caa553c3072e36', 'siswa', 'aktif', 'Eka Tirta Wijaya'),
(19, '03.25.211', '2b13118d0d5c3544a0e1444c91641487', 'siswa', 'aktif', 'Elby Putra Sulur'),
(20, '03.25.212', 'b249dfda3aa2c8eff056e89f2c3187db', 'siswa', 'aktif', 'Fifianka Briliant Tasyantika Imron'),
(21, '03.25.213', '1902e2ef069abbddeb7f3ec9525012f4', 'siswa', 'aktif', 'Muhammad Alvin Ni\'Am'),
(22, '03.25.214', 'e6d490e761366faba7138bdf4204dbf3', 'siswa', 'aktif', 'Muhammad Bagus Kurniawan'),
(23, '03.25.215', 'b2d391583d27ac04301584c8e9d95f1f', 'siswa', 'aktif', 'Muhammad Denny Syah Putra'),
(24, '03.25.216', 'c7951efc459f0ab8cfe86bca00341bce', 'siswa', 'aktif', 'Muhammad Gesta Al Haris'),
(25, '03.25.217', '356adfabdc21d95683c2a9dce9dfae38', 'siswa', 'aktif', 'Muhammad Naja Zainul Wafa'),
(26, '03.25.218', '4671eeb30484202bdbb31e4be396eb35', 'siswa', 'aktif', 'Muhammad Navid Amri Maulana'),
(27, '03.25.219', '13cd823f356cb60f60e4e8268d941736', 'siswa', 'aktif', 'Muhammad Zulfikar Fuadi'),
(28, '03.25.220', 'b6e9154acfc268800932a0997d463442', 'siswa', 'aktif', 'Nadia Dwi Apriliani'),
(29, '03.25.221', 'd1f9becfcd908a02644e28cd22cd494e', 'siswa', 'aktif', 'Nurul Ashfa'),
(30, '03.25.222', 'eb7bde2d7bb9a48ecf59ef6f4ffbce88', 'siswa', 'aktif', 'Rohaf Azzam Miqdad'),
(31, '03.25.223', 'b13ec3c4d6e30354f4687d0190cae91c', 'siswa', 'aktif', 'Tazkia Khairun Nisa');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `absen`
--
ALTER TABLE `absen`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `data_prestasi`
--
ALTER TABLE `data_prestasi`
  ADD PRIMARY KEY (`id_prestasi`),
  ADD KEY `idx_nama_exstra` (`nama_ekstra`),
  ADD KEY `idx_nama_siswa` (`nama_siswa`),
  ADD KEY `idx_tahun` (`tahun`);

--
-- Indeks untuk tabel `data_siswa`
--
ALTER TABLE `data_siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `ekstra`
--
ALTER TABLE `ekstra`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `galeri`
--
ALTER TABLE `galeri`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `komentar_pengumuman`
--
ALTER TABLE `komentar_pengumuman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pengumuman` (`pengumuman_id`),
  ADD KEY `idx_parent` (`parent_id`);

--
-- Indeks untuk tabel `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_tanggal` (`tanggal_mulai`,`tanggal_berakhir`),
  ADD KEY `idx_prioritas` (`prioritas`);

--
-- Indeks untuk tabel `profil_pembina`
--
ALTER TABLE `profil_pembina`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_pembina` (`nama_pembina`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `absen`
--
ALTER TABLE `absen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=709;

--
-- AUTO_INCREMENT untuk tabel `data_prestasi`
--
ALTER TABLE `data_prestasi`
  MODIFY `id_prestasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `data_siswa`
--
ALTER TABLE `data_siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `ekstra`
--
ALTER TABLE `ekstra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `galeri`
--
ALTER TABLE `galeri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `komentar_pengumuman`
--
ALTER TABLE `komentar_pengumuman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT untuk tabel `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `profil_pembina`
--
ALTER TABLE `profil_pembina`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `komentar_pengumuman`
--
ALTER TABLE `komentar_pengumuman`
  ADD CONSTRAINT `komentar_pengumuman_ibfk_1` FOREIGN KEY (`pengumuman_id`) REFERENCES `pengumuman` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `komentar_pengumuman_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `komentar_pengumuman` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
