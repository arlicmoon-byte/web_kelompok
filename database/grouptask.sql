-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20261112.f2beb4ceee
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 23, 2026 at 05:05 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `grouptask`
--

-- --------------------------------------------------------

--
-- Table structure for table `anggota`
--
CREATE TABLE `anggota` (
  `id` int NOT NULL,
  `kelompok_id` int NOT NULL,
  `nama_anggota` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `anggota`
--

INSERT INTO `anggota` (`id`, `kelompok_id`, `nama_anggota`) VALUES
(1, 1, 'Arya Malik'),
(2, 1, 'jendra'),
(4, 2, 'Rustam kece'),
(6, 3, 'TES');

-- --------------------------------------------------------

--
-- Table structure for table `jobdesk`
--
CREATE TABLE `jobdesk` (
  `id` int NOT NULL,
  `kelompok_id` int NOT NULL,
  `anggota_id` int DEFAULT NULL,
  `nama_tugas` varchar(150) NOT NULL,
  `deskripsi` text,
  `deadline` date DEFAULT NULL,
  `status` enum('Belum dikerjakan','Sedang dikerjakan','Selesai') DEFAULT 'Belum dikerjakan',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `jobdesk`
--

INSERT INTO `jobdesk` (`id`, `kelompok_id`, `anggota_id`, `nama_tugas`, `deskripsi`, `deadline`, `status`, `created_at`) VALUES
(8, 3, 6, 'wa', 'wadaawda', '2026-12-12', 'Selesai', '2026-11-23 12:56:27');

-- --------------------------------------------------------

--
-- Table structure for table `kelompok`
--
CREATE TABLE `kelompok` (
  `id` int NOT NULL,
  `nama_kelompok` varchar(100) NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kelompok`
--

INSERT INTO `kelompok` (`id`, `nama_kelompok`, `created_by`, `created_at`) VALUES
(1, 'Kelompok 1', 2, '2026-11-13 14:53:16'),
(2, 'Kelompok 2', 2, '2026-11-22 13:11:09'),
(3, 'kelompok tes', 2, '2026-11-23 11:54:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('murid','guru') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'guru', 'guru01', '$2y$10$15AbNifq7MtvJdOnCI9hz.nhr7Z2D2u61N03h/5RUTdEFIGgObJOG', 'guru', '2026-11-13 13:26:44'),
(2, 'murid', 'murid01', '$2y$10$JTJfIdQC7Opo2WokP1sJ/u3pk0t8houLh4vJTOai8faeYdReWlbWu', 'murid', '2026-11-13 14:44:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kelompok_id` (`kelompok_id`);

--
-- Indexes for table `jobdesk`
--
ALTER TABLE `jobdesk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kelompok_id` (`kelompok_id`),
  ADD KEY `anggota_id` (`anggota_id`);

--
-- Indexes for table `kelompok`
--
ALTER TABLE `kelompok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `jobdesk`
--
ALTER TABLE `jobdesk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `kelompok`
--
ALTER TABLE `kelompok`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `anggota`
--
ALTER TABLE `anggota`
  ADD CONSTRAINT `anggota_ibfk_1` FOREIGN KEY (`kelompok_id`) REFERENCES `kelompok` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jobdesk`
--
ALTER TABLE `jobdesk`
  ADD CONSTRAINT `jobdesk_ibfk_1` FOREIGN KEY (`kelompok_id`) REFERENCES `kelompok` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jobdesk_ibfk_2` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `kelompok`
--
ALTER TABLE `kelompok`
  ADD CONSTRAINT `kelompok_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
