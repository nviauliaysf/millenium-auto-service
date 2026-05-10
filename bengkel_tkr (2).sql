-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 10, 2026 at 06:21 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bengkel_tkr`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `username` varchar(35) NOT NULL,
  `password` varchar(35) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`username`, `password`) VALUES
('milleniumbengkeltkr', 'milleniumdantoyota'),
('milleniumbengkeltkr', 'milleniumdantoyota'),
('milleniumbengkeltkr', 'milleniumdantoyota');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `model_kendaraan` varchar(100) NOT NULL,
  `tahun_kendaraan` int(4) DEFAULT NULL,
  `no_polisi` varchar(20) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `foto_stnk` varchar(255) NOT NULL,
  `foto_ktp` varchar(255) NOT NULL,
  `permintaan_servis` text NOT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `status` varchar(20) DEFAULT 'menunggu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `nama`, `alamat`, `model_kendaraan`, `tahun_kendaraan`, `no_polisi`, `no_hp`, `foto_stnk`, `foto_ktp`, `permintaan_servis`, `tanggal`, `jam`, `harga`, `status`, `created_at`) VALUES
(28, 12, 'Novi Aulia Yusuf', 'Kp.Curug', 'Agya', 2021, 'F 7417 Ail', '089639159107', '1778237670_stnk_1.png', '1778237670_ktp_1.png', 'Paket Hemat: Ganti Oli Mesin & Filter Oli (TMO 10w-40), Servis Rem (Keamanan Utama!), Pemeriksaan Menyeluruh (Cek kesehatan mobil).', '2026-05-13', '00:00:09', 350000.00, 'selesai', '2026-05-08 10:54:30'),
(29, 13, 'Novi Aulia Yusuf', 'Kp.Curug', 'Avanza', 2020, 'F 7417 Ail', '089234567898', '1778247408_stnk_13.png', '1778247408_ktp_13.png', 'Paket Hemat: Ganti Oli Mesin & Filter Oli (TMO 10w-40), Servis Rem (Keamanan Utama!), Pemeriksaan Menyeluruh (Cek kesehatan mobil).', '2026-05-12', '00:00:09', 350000.00, 'selesai', '2026-05-08 13:36:48'),
(30, 12, 'Naila Izzati Syafira', 'Bojong Gede', 'Rush', 2021, 'b 0809 ana', '08952722600', '1778248139_stnk_13.png', '1778248139_ktp_21.png', 'Paket Hemat: Ganti Oli Mesin & Filter Oli (TMO 10w-40), Servis Rem (Keamanan Utama!), Pemeriksaan Menyeluruh (Cek kesehatan mobil).', '2026-05-11', '00:00:09', 350000.00, 'selesai', '2026-05-08 13:48:59');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `isi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `nama`, `isi`) VALUES
(1, 'tentang', 'Mendorong generasi muda berkarya lewat aksi nyata'),
(2, 'alamat', 'Jl. Raya Cibinong No.123, Bogor'),
(3, 'telepon', '(021) 1234567'),
(4, 'email', 'bengkel.tkr@smkn1cibinong.sch.id'),
(5, 'hero_title', 'Servis Mobil Anda, Sambil Dukung Siswa Teknik Kendaraan Ringan Berkarya'),
(6, 'card1_title', 'Praktik Siswa'),
(7, 'card1_text', 'Siswa TKR langsung praktik di bengkel bersertifikasi Toyota.'),
(8, 'card2_title', 'Service mobil'),
(9, 'card2_text', 'Layanan lengkap mulai dari servis rutin sampai perbaikan mesin.'),
(10, 'card3_title', 'Kerja Sama Toyota'),
(11, 'card3_text', 'Teknologi & standar servis resmi dari Toyota.'),
(20, 'visi', 'Terwujudnya Program Keahlian yang unggul di SMKN 1 Cibinong yang menghasilkan lulusan terampil dibidang otomotif yang berjiwa wirausaha yang berlandaskan iman dan taqwa.'),
(21, 'misi', 'Menghasilkan Murid Teknik Otomotif SMKN 1 Cibinong, yang:\r\n1. Berahklak Mulia, Beriman dan Bertaqwa kepada Tuhan Yang Maha Esa.\r\n2. Memiliki Kompetensi dan Wawasan yang luas\r\n3. Siap Bekerja, Melanjutkan dan Berwirausaha'),
(32, 'tentang_profil', 'Website ini merupakan media informasi dan pelayanan resmi Bengkel Teaching Factory Millenium Auto Service, milik jurusan Teknik Kendaraan Ringan (TKR) di SMKN 1 Cibinong yang bekerja sama dengan Toyota Auto 2000 Cibinong.'),
(33, 'tentang_visi', 'Terwujudnya Program Keahlian yang unggul di SMKN 1 Cibinong yang menghasilkan lulusan terampil dibidang otomotif yang berjiwa wirausaha yang berlandaskan iman dan taqwa.'),
(34, 'tentang_misi', 'Menghasilkan Murid Teknik Otomotif SMKN 1 Cibinong, yang:\r\n1. Berahklak Mulia, Beriman dan Bertaqwa kepada Tuhan Yang Maha Esa.\r\n2. Memiliki Kompetensi dan Wawasan yang luas\r\n3. Siap Bekerja, Melanjutkan dan Berwirausaha'),
(35, 'tentang_keunggulan', '1. Standar Industri Resmi: Didukung kerja sama dengan Toyota Auto 2000 Cibinong sehingga proses servis mengikuti standar industri otomotif.\r\n\r\n2. Teknisi Terlatih & Dibimbing Guru Profesional: Pengerjaan dilakukan oleh siswa kompeten di bawah pengawasan guru berpengalaman.\r\n\r\n3. Peralatan Modern: Menggunakan peralatan praktik sesuai standar bengkel profesional.\r\n\r\n4. Harga Terjangkau & Transparan: Biaya servis jelas tanpa biaya tersembunyi.\r\n\r\n5. Lokasi Strategis: Berada di lingkungan SMKN 1 Cibinong dan mudah dijangkau masyarakat sekitar.\r\n\r\n6. Sambil Mendukung Pendidikan: Setiap servis yang dilakukan turut mendukung proses pembelajaran siswa.'),
(36, 'tentang_kerjasama', 'Toyota Auto 2000 Cibinong'),
(37, 'tentang_dok_1_judul', 'Praktek'),
(38, 'tentang_dok_1_isi', 'Didampingi langsung oleh pihak Toyota'),
(39, 'tentang_dok_2_judul', 'Service'),
(40, 'tentang_dok_2_isi', 'Melakukan service mobil pelanggan'),
(41, 'tentang_dok_3_judul', ''),
(42, 'tentang_dok_3_isi', ''),
(65, 'kontak_alamat', 'Jl. Raya Karadenan No.7, Karadenan, Kec. Cibinong, Kabupaten Bogor, Jawa Barat 16111'),
(66, 'kontak_telepon', '085781434137'),
(67, 'kontak_email', 'gumarangkusma1986@gmail.com'),
(68, 'kontak_jam', 'Senin - Jumat, 08.00-16.00 WIB'),
(70, 'hero_desc', ''),
(96, 'tentang_dok_3_foto', '1772590752_dok3_Vector-1.png'),
(108, 'tentang_dok_1_img', '1776088472_dok_1_kegiatan1.jpeg'),
(109, 'tentang_dok_2_img', '1776088472_dok_2_kegiatan2.jpeg'),
(123, 'layanan_judul_halaman', 'Layanan Yang Tersedia'),
(124, 'layanan_judul1', 'Ganti Oli Mesin & Filter Oli '),
(125, 'layanan_isi1', 'TMO 10w-40'),
(126, 'layanan_judul2', 'Service Rem '),
(127, 'layanan_isi2', 'Keamanan Utama!'),
(128, 'layanan_judul3', 'Pemeriksaan Menyeluruh '),
(129, 'layanan_isi3', 'Cek Kesehatan Mobil');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` varchar(20) DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `role`) VALUES
(1, 'nviauliaysf', 'empiaull@gmail.com', '$2y$10$denoD/wD0R8zQ4xG9sUYues0DNrVSiA.tVRINnFSNb0OO/oROmEX.', '2026-02-13 02:47:41', 'customer'),
(2, 'awulll', 'noviyusuf130@gmail.com', '$2y$10$iVyGnCBqEA40P2pHZV52SuIhJXKAMfAMY/5t2vYTKKyF.Q2Kj8xxC', '2026-02-13 02:49:04', 'customer'),
(3, 'alya', 'arsyakayladahayu677@gmail.com', '$2y$10$K8K7APpyhYyyCASspBotSu4F4ASuMJZwqddUYwcBpiM4iIknDX7we', '2026-03-04 01:18:21', 'customer'),
(4, 'kaila', 'kailamusyafa@gmail.com', '$2y$10$eKGbRhtNurNm4zDn7V6DzOaWBmZTp197SnhaxsqVasZX4g/UNn6r6', '2026-03-04 01:56:01', 'customer'),
(5, 'opang123', 'arippr8@gmail.con', '$2y$10$VDJDR89Aw5fCwCKQcIqLuOuyKuabW7HtZyT70CyTq8V0UEQwrsB1e', '2026-03-04 03:48:23', 'customer'),
(6, 'naufal de santos', 'dimanaanindya@gmail.com', '$2y$10$4aXop.k3p3qlgIJJ1xQJ7.vHpdyiW5LLp/tn4KQCxDJxpDoTcVDie', '2026-04-17 01:09:37', 'customer'),
(7, 'manustest', 'manus@test.com', '$2y$10$JwYAtTxF3xENrsAsaRbrvuB7NTjf7CSPVP8AsfuhV21fgcmpj1thS', '2026-04-17 01:32:47', 'customer'),
(8, 'daffadin', 'daffadin@gmail.com', '$2y$10$Ikl7O6S/VSss/HjGPG4Q0O/tPG3sfwLRaH1ORkXb4zqQfo8HOLawS', '2026-04-29 23:15:47', 'customer'),
(9, 'useclient', 'pembelimp@gmail.com', '$2y$10$0FT95zOQ3h8bqC2KCkhqS.Eimyrd5gOLM6KrF7fib5xBpGt5C59y6', '2026-04-29 23:33:01', 'customer'),
(10, 'aulia', 'pelanggan@gmail.com', '$2y$10$iZk1.wVFszFZ1lqvHTzMRuz7kxhntt5CvSjrwTALaN/Nh/SK7UP1u', '2026-04-30 03:30:35', 'customer'),
(11, 'alifiah', 'alif@gamil.com', '$2y$10$zymChNy7G7AJZbTDSYoSwuFLrmCktDP6glaJpvW7VzIyqBjirb5Gq', '2026-05-01 08:11:59', 'customer'),
(12, 'auliaa', 'aulia21@gmail.com', '$2y$10$4W1lfIlN/izn9/yVzzPvZOvDpS9A4Se2PQAtoKWzMsbVmO28B1CZ.', '2026-05-08 10:52:40', 'customer'),
(13, 'Auliaaa', 'aulia06@gmail.com', '$2y$10$WXFfliZv.xLr1WN05XkKOO0uDqi2E6q7qwlKBof.yj.krP6CysneW', '2026-05-08 13:33:10', 'customer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_active_booking` (`user_id`,`tanggal`,`status`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama` (`nama`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=182;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
