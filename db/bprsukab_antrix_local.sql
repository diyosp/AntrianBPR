-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 10, 2025 at 01:19 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bprsukab_antrix`
--

-- --------------------------------------------------------

--
-- Table structure for table `cabang`
--

CREATE TABLE `cabang` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cabang`
--

INSERT INTO `cabang` (`id`, `nama`, `alamat`, `created_at`, `updated_at`) VALUES
(300, 'Kantor Pusat Non Operasional', 'Jl. Suryakencana No. 51 Sukabumi, Jawa Barat', '2024-10-08 11:37:21', '2024-10-08 11:37:21'),
(301, 'Kantor Pusat Operasional', 'Jl. Suryakencana No. 51 Sukabumi, Jawa Barat', '2024-10-08 11:04:36', '2024-10-08 11:04:36'),
(302, 'Kantor Cabang Sukaraja', 'Jl. Sukaraja No. 120 Sukabumi, Jawa Barat', '2024-10-08 11:38:24', '2024-10-08 11:38:24'),
(303, 'Kantor Cabang Baros', 'Jl. Raya Baros KM. 5 No. 302 Sukabumi, Jawa Barat', '2024-10-08 11:38:24', '2024-10-08 11:38:24'),
(304, 'Kantor Cabang Cisaat', 'Jl. Pasar Baru Cisaat, Sukabumi, Jawa Barat', '2024-10-08 11:55:27', '2024-10-08 11:55:31'),
(305, 'Kantor Cabang Cibadak', 'Jl. Siliwangi No. 108 Komp. Kewedanan Cibadak', '2024-10-08 11:55:36', '2024-10-08 11:55:36'),
(306, 'Kantor Cabang Cicurug', 'Jl. Siliwangi Komp. Kecamatan Cicurug', '2024-10-08 11:56:18', '2024-10-08 11:56:18'),
(307, 'Kantor Cabang Cisolok', 'Jl. Raya Cisolok No.1 KM. 1 - Pelabuhan Ratu', '2024-10-08 11:56:48', '2024-10-08 11:56:48'),
(308, 'Kantor Cabang Sagaranten', 'Jl. Raya Baros Sagaranten, Sukabumi', '2024-10-08 11:57:14', '2024-10-08 11:57:14'),
(310, 'Kantor Cabang Jampangkulon', 'Jl. Raya Jampang Kulon Sukabumi', '2024-10-08 11:57:57', '2024-10-08 11:57:57'),
(311, 'Kantor Cabang Kalapanunggal', 'Komplek Kecamatan Parakansalak, Sukabumi', '2024-10-08 11:59:41', '2024-10-08 11:59:45'),
(312, 'Kantor Cabang Cikembar', 'Jl. Pelabuhan II, Cikembar, Kec. Cikembar, Kabupaten Sukabumi', '2024-10-08 11:58:49', '2024-10-08 11:58:49'),
(313, 'Kantor Cabang Parungkuda', 'Jl. Siliwangi Kp. Leuwi Orok No.Rt. 010/004, Sundawenang', '2024-10-08 11:59:17', '2024-10-08 11:59:17');

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `role_id` int UNSIGNED NOT NULL,
  `nama` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`role_id`, `nama`, `keterangan`) VALUES
(1, 'super_admin', 'untuk team ti dan laporan keseluruhan'),
(2, 'pinca', 'untuk pimpinan cabang'),
(3, 'kasie', 'untuk kasie operasional'),
(4, 'teller', 'untuk teller'),
(5, 'customer_service', 'untuk cs');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_antrian`
--

CREATE TABLE `tbl_antrian` (
  `id` bigint NOT NULL,
  `cabang_id` bigint UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `no_antrian` smallint NOT NULL,
  `status` enum('0','1','2') NOT NULL DEFAULT '0',
  `updated_date` datetime DEFAULT NULL,
  `waktu_mulai` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `durasi` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_antrian`
--

INSERT INTO `tbl_antrian` (`id`, `cabang_id`, `tanggal`, `no_antrian`, `status`, `updated_date`, `waktu_mulai`, `waktu_selesai`, `durasi`) VALUES
(1, 312, '2025-11-06', 1, '2', '2025-11-06 14:32:07', '2025-11-06 14:29:16', '2025-11-06 14:32:07', 171),
(2, 312, '2025-11-06', 2, '2', '2025-11-06 14:32:27', '2025-11-06 14:32:15', '2025-11-06 14:32:27', 12),
(3, 312, '2025-11-10', 1, '2', '2025-11-10 13:32:43', '2025-11-10 13:32:08', '2025-11-10 13:32:43', 35),
(4, 312, '2025-11-10', 2, '2', '2025-11-10 18:00:39', '2025-11-10 17:59:20', '2025-11-10 18:00:39', 79),
(5, 312, '2025-11-12', 1, '2', '2025-11-12 08:44:55', '2025-11-12 08:44:17', '2025-11-12 08:44:55', 38),
(6, 312, '2025-11-12', 2, '1', '2025-11-12 08:45:15', '2025-11-12 08:44:59', NULL, NULL),
(7, 306, '2025-11-13', 1, '2', '2025-11-13 17:34:48', '2025-11-13 17:33:59', '2025-11-13 17:34:48', 49),
(8, 306, '2025-11-13', 2, '2', '2025-11-13 17:42:03', '2025-11-13 17:34:51', '2025-11-13 17:42:03', 432);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_antrian_kredit`
--

CREATE TABLE `tbl_antrian_kredit` (
  `id_kredit` bigint NOT NULL,
  `cabang_id` bigint UNSIGNED NOT NULL,
  `tanggal_kredit` date NOT NULL,
  `no_antrian_kredit` smallint NOT NULL,
  `status_kredit` enum('0','1','2') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '0',
  `bagian` varchar(100) DEFAULT NULL,
  `updated_date_kredit` datetime DEFAULT NULL,
  `waktu_mulai` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `durasi` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_antrian_kredit`
--

INSERT INTO `tbl_antrian_kredit` (`id_kredit`, `cabang_id`, `tanggal_kredit`, `no_antrian_kredit`, `status_kredit`, `bagian`, `updated_date_kredit`, `waktu_mulai`, `waktu_selesai`, `durasi`) VALUES
(1, 312, '2025-11-10', 1, '2', NULL, '2025-11-10 18:01:26', '2025-11-10 18:00:01', '2025-11-10 18:01:26', 85),
(2, 312, '2025-11-10', 2, '2', NULL, '2025-11-10 18:02:47', '2025-11-10 18:01:51', '2025-11-10 18:02:47', 56),
(3, 312, '2025-11-10', 3, '2', NULL, '2025-11-10 18:03:07', '2025-11-10 18:02:49', '2025-11-10 18:03:07', 18),
(4, 312, '2025-11-10', 4, '2', NULL, '2025-11-10 18:03:24', '2025-11-10 18:03:09', '2025-11-10 18:03:24', 15),
(5, 312, '2025-11-10', 5, '2', NULL, '2025-11-10 18:03:40', '2025-11-10 18:03:25', '2025-11-10 18:03:40', 15),
(6, 312, '2025-11-12', 1, '2', NULL, '2025-11-12 08:47:17', '2025-11-12 08:46:04', '2025-11-12 08:47:17', 73),
(7, 312, '2025-11-12', 2, '0', NULL, NULL, NULL, NULL, NULL),
(8, 306, '2025-11-13', 1, '2', NULL, '2025-11-13 17:42:51', '2025-11-13 17:42:39', '2025-11-13 17:42:51', 12),
(9, 306, '2025-11-13', 2, '2', NULL, '2025-11-13 17:43:01', '2025-11-13 17:42:52', '2025-11-13 17:43:01', 9),
(10, 306, '2025-11-13', 3, '2', NULL, '2025-11-13 17:43:11', '2025-11-13 17:43:02', '2025-11-13 17:43:11', 9);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_antrian_teller`
--

CREATE TABLE `tbl_antrian_teller` (
  `id_teller` bigint NOT NULL,
  `cabang_id` bigint UNSIGNED NOT NULL,
  `tanggal_teller` date NOT NULL,
  `no_antrian_teller` smallint NOT NULL,
  `status_teller` enum('0','1','2') NOT NULL DEFAULT '0',
  `bagian` varchar(100) DEFAULT NULL,
  `updated_date_teller` datetime DEFAULT NULL,
  `waktu_mulai` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `durasi` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tbl_antrian_teller`
--

INSERT INTO `tbl_antrian_teller` (`id_teller`, `cabang_id`, `tanggal_teller`, `no_antrian_teller`, `status_teller`, `bagian`, `updated_date_teller`, `waktu_mulai`, `waktu_selesai`, `durasi`) VALUES
(1, 312, '2025-11-10', 1, '2', '1', '2025-11-10 18:01:09', '2025-11-10 17:59:31', '2025-11-10 18:01:09', 98),
(2, 312, '2025-11-10', 2, '2', '2', '2025-11-10 18:01:16', '2025-11-10 17:59:49', '2025-11-10 18:01:16', 87),
(3, 312, '2025-11-10', 3, '2', '2', '2025-11-10 18:02:06', '2025-11-10 18:01:21', '2025-11-10 18:02:06', 45),
(4, 312, '2025-11-10', 4, '2', '2', '2025-11-10 18:24:15', '2025-11-10 18:23:17', '2025-11-10 18:24:15', 58),
(5, 312, '2025-11-10', 5, '2', '1', '2025-11-10 18:24:20', '2025-11-10 18:24:02', '2025-11-10 18:24:20', 18),
(6, 312, '2025-11-10', 6, '2', '1', '2025-11-10 18:24:47', '2025-11-10 18:24:21', '2025-11-10 18:24:47', 26),
(7, 312, '2025-11-10', 7, '2', '2', '2025-11-10 18:24:50', '2025-11-10 18:24:24', '2025-11-10 18:24:50', 26),
(8, 312, '2025-11-10', 8, '2', '1', '2025-11-10 18:27:51', '2025-11-10 18:27:40', '2025-11-10 18:27:51', 11),
(9, 312, '2025-11-10', 9, '2', '2', '2025-11-10 18:28:03', '2025-11-10 18:27:53', '2025-11-10 18:28:03', 10),
(10, 306, '2025-11-11', 1, '2', NULL, '2025-11-11 09:45:09', '2025-11-11 09:44:13', '2025-11-11 09:45:09', 56),
(11, 306, '2025-11-11', 2, '2', NULL, '2025-11-11 09:46:07', '2025-11-11 09:45:44', '2025-11-11 09:46:07', 23),
(12, 312, '2025-11-12', 1, '2', '1', '2025-11-12 08:47:13', '2025-11-12 08:45:30', '2025-11-12 08:47:13', 103),
(13, 312, '2025-11-12', 2, '2', '2', '2025-11-12 08:47:15', '2025-11-12 08:45:53', '2025-11-12 08:47:15', 82),
(14, 312, '2025-11-12', 3, '0', NULL, NULL, NULL, NULL, NULL),
(15, 312, '2025-11-12', 4, '0', NULL, NULL, NULL, NULL, NULL),
(16, 312, '2025-11-12', 5, '0', NULL, NULL, NULL, NULL, NULL),
(17, 306, '2025-11-13', 1, '2', NULL, '2025-11-13 17:42:25', '2025-11-13 17:42:15', '2025-11-13 17:42:25', 10),
(18, 306, '2025-11-13', 2, '2', NULL, '2025-11-13 17:42:35', '2025-11-13 17:42:26', '2025-11-13 17:42:35', 9),
(19, 312, '2025-11-13', 1, '2', '1', '2025-11-13 17:48:37', '2025-11-13 17:47:35', '2025-11-13 17:48:37', 62),
(20, 312, '2025-11-13', 2, '2', '2', '2025-11-13 17:48:54', '2025-11-13 17:48:30', '2025-11-13 17:48:54', 24),
(21, 312, '2025-11-13', 3, '2', '2', '2025-11-13 17:54:04', '2025-11-13 17:50:03', '2025-11-13 17:54:04', 241),
(22, 312, '2025-11-13', 4, '2', '1', '2025-11-13 17:54:02', '2025-11-13 17:53:40', '2025-11-13 17:54:02', 22),
(23, 312, '2025-11-13', 5, '2', '1', '2025-11-13 18:29:32', '2025-11-13 18:29:20', '2025-11-13 18:29:32', 12),
(24, 312, '2025-11-13', 6, '2', '1', '2025-11-13 18:29:50', '2025-11-13 18:29:38', '2025-11-13 18:29:50', 12),
(25, 312, '2025-11-13', 7, '2', '2', '2025-11-13 18:29:55', '2025-11-13 18:29:40', '2025-11-13 18:29:55', 15),
(26, 312, '2025-11-13', 8, '2', '2', '2025-11-13 18:30:02', '2025-11-13 18:29:52', '2025-11-13 18:30:02', 10);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `id_pegawai` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cabang_id` bigint UNSIGNED NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `id_pegawai`, `username`, `cabang_id`, `status`, `last_login`, `password`, `role_id`, `created_at`, `updated_at`) VALUES
(1, '73.004.156', '312pinca', 312, 'active', '2025-11-10 17:56:37', '$2y$10$apESvl/bLRKWR/ii7lFMj.IklO57faelcWITiBG2K4NZqA8T53uEa', 2, '2025-01-26 11:04:08', '2025-11-10 10:56:37'),
(2, '79.098.021', '312kasie', 312, 'active', '2025-11-10 17:56:15', '$2y$10$qdgqz1n/E6f8H2Y/JCKc2OWWhIVYE99nYGKiokBzsjMjeJ67WIBHi', 3, '2025-01-26 11:11:32', '2025-11-10 10:56:15'),
(3, '91.015.307', '312teller', 312, 'active', '2025-11-12 08:27:16', '$2y$10$ch.iCoJaYY10vA2iaJ0SYOMGNbfIrppv5KlTgeHpKUmGpxF2XOqeu', 4, '2025-01-26 11:12:04', '2025-11-12 01:27:16'),
(4, NULL, '312cs', 312, 'active', NULL, '$2y$10$.YALxxbU/DjOD3VlvvW1IugS/Q9YxXh/ls4KbqJGIi/zAETtlmske', 5, '2025-01-26 11:13:23', '2025-01-26 11:13:23'),
(5, NULL, '312super', 312, 'active', '2025-12-10 08:16:07', '$2y$10$C0z7tEdnzFKgTbMYWkg5WeOEdSo1ZnbdB6V/pEKxH1On0KbVySLDW', 1, '2025-01-26 11:13:57', '2025-12-10 01:16:07'),
(6, NULL, '311kasie', 311, 'active', NULL, '$2y$10$dMAZ6Dwp2Pk0J4Td366kqec97dbwqiIxXYMJPiPvgd0mfWMj4oQsS', 3, '2025-01-26 13:28:42', '2025-01-26 14:58:53'),
(8, NULL, '311cs', 311, 'active', NULL, '$2y$10$GLyyLoLUNEVOVgjzfv012esmBnS.s8JraxVH2nzaw5r/3FaMbmoxm', 5, '2025-01-26 14:59:18', '2025-01-26 14:59:57'),
(9, NULL, '305super', 305, 'active', NULL, '$2y$10$C0z7tEdnzFKgTbMYWkg5WeOEdSo1ZnbdB6V/pEKxH1On0KbVySLDW', 1, '2025-01-27 07:56:42', '2025-05-15 03:38:55'),
(10, '78.098.018', '305pinca', 305, 'active', '2025-11-10 17:47:51', '$2y$10$yxKKlTPGX84IZTsgdWrKD.Yh5MQqC08tXpgLG3gERoDs604oghTNC', 2, '2025-01-27 07:59:31', '2025-11-10 10:47:51'),
(11, NULL, '305kasie', 305, 'active', NULL, '$2y$10$.Od0NCvSDSS8d/aFeuIRh.0xhqY2QKjrUiyX3rz37TC9jImFBtZja', 3, '2025-01-30 02:55:48', '2025-01-30 02:55:48'),
(12, NULL, '305teller', 305, 'active', NULL, '$2y$10$w7bXWf0NFDRsHg1erdBA1uQm7NLYWm0BKg9GQZ1atAeePjXv0VDhi', 4, '2025-01-30 02:56:00', '2025-01-30 02:56:00'),
(13, NULL, '305cs', 305, 'active', NULL, '$2y$10$Q/asuakbUmPU2kduLvZUaelZAIODO9RJXTjgcYGqHwyLsJtiBwala', 5, '2025-01-30 02:56:10', '2025-01-30 02:56:10'),
(14, '95.018.324', '301super', 301, 'active', '2025-11-13 17:21:38', '$2y$10$K3RDcg2GKX2DNANQq25MJ.3qAc0mlVJdfDh.YyVUM16PFT.KOPNnm', 1, '2025-02-03 09:30:06', '2025-11-13 10:21:38'),
(15, NULL, '301kasie', 301, 'active', NULL, '$2y$10$8UT28CsCGpFPWNOCY/8BGOZBrR7O2sedQ2AVioqx5li8hXfhbGnsa', 3, '2025-02-03 09:30:25', '2025-02-03 09:30:25'),
(16, '94.018.327', '301teller', 301, 'active', '2025-11-10 17:56:59', '$2y$10$v/mZAzkna9GU4Cmyd1VJiut6ISbbXhx1sNg2Y4ilxxlgDtGeQGwCK', 4, '2025-02-03 09:30:38', '2025-11-10 10:56:59'),
(17, 'TKK005', '301cs', 301, 'active', '2025-11-10 17:57:16', '$2y$10$LO50aPx2M76KK2.6CtdOe.OdTvsHx6A4kftVoXcyhimE4zqp.BjOG', 5, '2025-02-03 09:30:48', '2025-11-10 10:57:16'),
(18, '77.000.137', '301pinca', 301, 'active', '2025-11-10 17:48:21', '$2y$10$gLD3rFNAYkBQ6Zpda85uDe7oiOkhzgxWeDxMhPet0GERcS9J1wzlu', 2, '2025-02-03 09:30:56', '2025-11-10 10:48:21'),
(19, '95.018.324', '306super', 306, 'active', '2025-11-13 17:33:30', '$2y$10$3KTd9lrJmNHcRFVwxCEtsOnwAy5UVkof4e3Leal7DJoIkhbImFvoC', 1, '2025-04-09 07:14:28', '2025-11-13 10:33:30'),
(20, '76.098.113', '306pinca', 306, 'active', '2025-11-10 17:48:53', '$2y$10$LRP82zg9gBps2FaGB5xMK.AofcPcbGb5Y4yPs3F4LyJrx9O75tdL.', 2, '2025-04-09 07:14:59', '2025-11-10 10:48:53'),
(21, NULL, '306teller', 306, 'active', NULL, '$2y$10$l23G9AGQnSICN5H/Cn/4je82sDNDtpPL9pCKuyF9xb/PSZ69fbtFW', 4, '2025-04-09 07:15:15', '2025-04-09 07:15:15'),
(22, '89.013.252', '306cs', 306, 'active', '2025-11-11 09:44:04', '$2y$10$xLTey4aqWNCDSh0LAHPTdOb.9FlJ7WMBZQJK21X.lbO7y5suwT/EC', 5, '2025-04-09 07:15:37', '2025-11-11 02:44:04'),
(23, '97.024.366', '304cs', 304, 'active', '2025-11-11 10:21:32', '$2y$10$t89Hgyh7H10ortjq2aeJM.8hYCQwn0voNMUycviMDi2sRNwwrAk2W', 5, '2025-11-11 03:19:46', '2025-11-11 03:21:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cabang`
--
ALTER TABLE `cabang`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `tbl_antrian`
--
ALTER TABLE `tbl_antrian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tbl_antrian_cabang` (`cabang_id`);

--
-- Indexes for table `tbl_antrian_kredit`
--
ALTER TABLE `tbl_antrian_kredit`
  ADD PRIMARY KEY (`id_kredit`),
  ADD KEY `fk_tbl_antrian_kredit_cabang` (`cabang_id`) USING BTREE;

--
-- Indexes for table `tbl_antrian_teller`
--
ALTER TABLE `tbl_antrian_teller`
  ADD PRIMARY KEY (`id_teller`),
  ADD KEY `fk_tbl_antrian_teller_cabang` (`cabang_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `cabang_id` (`cabang_id`),
  ADD KEY `fk_users_role` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cabang`
--
ALTER TABLE `cabang`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=314;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_antrian`
--
ALTER TABLE `tbl_antrian`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_antrian_kredit`
--
ALTER TABLE `tbl_antrian_kredit`
  MODIFY `id_kredit` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_antrian_teller`
--
ALTER TABLE `tbl_antrian_teller`
  MODIFY `id_teller` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_antrian`
--
ALTER TABLE `tbl_antrian`
  ADD CONSTRAINT `fk_tbl_antrian_cabang` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_antrian_teller`
--
ALTER TABLE `tbl_antrian_teller`
  ADD CONSTRAINT `fk_tbl_antrian_teller_cabang` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
