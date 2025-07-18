-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 17 Jul 2025 pada 17.28
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
-- Database: `db_kms`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_categories`
--

CREATE TABLE `tbl_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `create_time` datetime DEFAULT current_timestamp(),
  `update_time` datetime DEFAULT NULL,
  `create_id` int(11) DEFAULT NULL,
  `update_id` int(11) DEFAULT NULL,
  `archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_categories`
--

INSERT INTO `tbl_categories` (`id`, `name`, `create_time`, `update_time`, `create_id`, `update_id`, `archived`) VALUES
(1, 'Keuangan', '2025-07-17 15:51:30', NULL, 1, NULL, 0),
(2, 'Marketing', '2025-07-17 15:51:30', NULL, 1, NULL, 0),
(3, 'Teknologi', '2025-07-17 15:51:30', NULL, 1, NULL, 0),
(4, 'HRD', '2025-07-17 15:51:30', NULL, 1, NULL, 0),
(5, 'Test 2', '2025-07-17 16:34:33', '2025-07-17 11:34:54', 5, 5, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_feedbacks`
--

CREATE TABLE `tbl_feedbacks` (
  `id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `material_rating` int(1) DEFAULT NULL,
  `feedback_text` text NOT NULL,
  `assessment_score` int(1) DEFAULT NULL,
  `assessment_notes` text DEFAULT NULL,
  `assessed_by` int(11) DEFAULT NULL,
  `create_time` datetime DEFAULT current_timestamp(),
  `update_time` datetime DEFAULT NULL,
  `create_id` int(11) DEFAULT NULL,
  `update_id` int(11) DEFAULT NULL,
  `archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_feedbacks`
--

INSERT INTO `tbl_feedbacks` (`id`, `material_id`, `user_id`, `material_rating`, `feedback_text`, `assessment_score`, `assessment_notes`, `assessed_by`, `create_time`, `update_time`, `create_id`, `update_id`, `archived`) VALUES
(1, 1, 3, NULL, 'Panduannya sangat jelas dan mudah diikuti, terima kasih!', 5, NULL, 1, '2025-07-17 15:51:30', NULL, 3, NULL, 0),
(2, 3, 3, NULL, 'Apakah VPN ini bisa digunakan di lebih dari satu perangkat?', NULL, NULL, NULL, '2025-07-17 15:51:30', NULL, 3, NULL, 0),
(3, 2, 4, NULL, 'Desainnya bagus, tapi apakah ada versi dengan skema warna lain?', 4, NULL, 1, '2025-07-17 15:51:30', NULL, 4, NULL, 0),
(4, 1, 7, 4, 'test', 4, 'test', 5, '2025-07-17 11:38:59', '2025-07-17 11:40:25', 7, 5, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_materials`
--

CREATE TABLE `tbl_materials` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('PDF','Image','Link') NOT NULL,
  `content` varchar(255) NOT NULL,
  `create_time` datetime DEFAULT current_timestamp(),
  `update_time` datetime DEFAULT NULL,
  `create_id` int(11) DEFAULT NULL,
  `update_id` int(11) DEFAULT NULL,
  `archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_materials`
--

INSERT INTO `tbl_materials` (`id`, `title`, `description`, `type`, `content`, `create_time`, `update_time`, `create_id`, `update_id`, `archived`) VALUES
(1, 'Panduan Reimbursement Medis', 'SOP lengkap untuk proses reimbursement biaya kesehatan karyawan.', 'PDF', '/uploads/materials/20250717110500-Jurnal SPK.docx.pdf', '2025-07-17 15:51:30', '2025-07-17 11:05:00', 1, 5, 0),
(2, 'Desain Banner Promosi Q3', 'Aset gambar untuk kampanye promosi kuartal ketiga.', 'Image', '/uploads/materials/dummy.png', '2025-07-17 15:51:30', NULL, 2, NULL, 0),
(3, 'Tutorial Setup VPN Kantor', 'Video tutorial cara instalasi dan penggunaan VPN untuk kerja remote.', 'Link', 'https://www.youtube.com', '2025-07-17 15:51:30', NULL, 1, NULL, 0),
(4, 'Kebijakan Cuti Tahunan 2025', 'Update terbaru mengenai kebijakan cuti tahunan untuk semua karyawan.', 'PDF', '/uploads/materials/dummy.pdf', '2025-07-17 15:51:30', NULL, 1, NULL, 0),
(5, 'Update Aplikasi Internal Versi 2.1', 'Penjelasan fitur-fitur baru pada aplikasi internal perusahaan.', 'PDF', '/uploads/materials/dummy.pdf', '2025-07-17 15:51:30', NULL, 2, NULL, 0),
(6, 'Uji Coba', 'Test', 'PDF', '/uploads/materials/20250717111109-Materi-Paparan Sosialiasi Program Magang Berdampak 2025-WHA.pdf', '2025-07-17 16:10:44', '2025-07-17 11:11:09', 6, 6, 0),
(7, 'Test', 'test', 'Link', 'https://meet.google.com/jau-wnje-smw', '2025-07-17 16:11:45', '2025-07-17 11:11:58', 6, 6, 1),
(8, 'Test 2', 'test 2', 'Link', 'https://meet.google.com/jau-wnje-smw', '2025-07-17 16:36:44', '2025-07-17 11:37:52', 5, 5, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_material_assignments`
--

CREATE TABLE `tbl_material_assignments` (
  `id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `create_time` datetime DEFAULT current_timestamp(),
  `update_time` datetime DEFAULT NULL,
  `create_id` int(11) DEFAULT NULL,
  `update_id` int(11) DEFAULT NULL,
  `archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_material_assignments`
--

INSERT INTO `tbl_material_assignments` (`id`, `material_id`, `user_id`, `create_time`, `update_time`, `create_id`, `update_id`, `archived`) VALUES
(3, 4, 3, '2025-07-17 15:51:30', NULL, 1, NULL, 0),
(5, 4, 4, '2025-07-17 15:51:30', NULL, 1, NULL, 0),
(6, 1, 7, '2025-07-17 16:04:21', NULL, 5, NULL, 0),
(7, 3, 7, '2025-07-17 16:04:29', NULL, 5, NULL, 0),
(8, 2, 7, '2025-07-17 16:04:38', NULL, 5, NULL, 0),
(9, 8, 7, '2025-07-17 16:37:14', NULL, 5, NULL, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_material_categories`
--

CREATE TABLE `tbl_material_categories` (
  `id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `create_time` datetime DEFAULT current_timestamp(),
  `update_time` datetime DEFAULT NULL,
  `create_id` int(11) DEFAULT NULL,
  `update_id` int(11) DEFAULT NULL,
  `archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_material_categories`
--

INSERT INTO `tbl_material_categories` (`id`, `material_id`, `category_id`, `create_time`, `update_time`, `create_id`, `update_id`, `archived`) VALUES
(2, 2, 2, '2025-07-17 15:51:30', NULL, NULL, NULL, 0),
(3, 3, 3, '2025-07-17 15:51:30', NULL, NULL, NULL, 0),
(4, 4, 4, '2025-07-17 15:51:30', NULL, NULL, NULL, 0),
(5, 5, 3, '2025-07-17 15:51:30', NULL, NULL, NULL, 0),
(6, 1, 1, '2025-07-17 16:05:00', NULL, NULL, NULL, 0),
(9, 6, 2, '2025-07-17 16:11:09', NULL, NULL, NULL, 0),
(10, 6, 3, '2025-07-17 16:11:09', NULL, NULL, NULL, 0),
(11, 7, 1, '2025-07-17 16:11:45', NULL, NULL, NULL, 0),
(12, 7, 2, '2025-07-17 16:11:45', NULL, NULL, NULL, 0),
(15, 8, 1, '2025-07-17 16:37:40', NULL, NULL, NULL, 0),
(16, 8, 2, '2025-07-17 16:37:40', NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_material_tags`
--

CREATE TABLE `tbl_material_tags` (
  `id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `create_time` datetime DEFAULT current_timestamp(),
  `update_time` datetime DEFAULT NULL,
  `create_id` int(11) DEFAULT NULL,
  `update_id` int(11) DEFAULT NULL,
  `archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_material_tags`
--

INSERT INTO `tbl_material_tags` (`id`, `material_id`, `tag_id`, `create_time`, `update_time`, `create_id`, `update_id`, `archived`) VALUES
(3, 2, 3, '2025-07-17 15:51:30', NULL, NULL, NULL, 0),
(4, 3, 2, '2025-07-17 15:51:30', NULL, NULL, NULL, 0),
(5, 4, 4, '2025-07-17 15:51:30', NULL, NULL, NULL, 0),
(6, 5, 5, '2025-07-17 15:51:30', NULL, NULL, NULL, 0),
(7, 1, 1, '2025-07-17 16:05:00', NULL, NULL, NULL, 0),
(8, 1, 4, '2025-07-17 16:05:00', NULL, NULL, NULL, 0),
(11, 8, 2, '2025-07-17 16:37:40', NULL, NULL, NULL, 0),
(12, 8, 4, '2025-07-17 16:37:40', NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_tags`
--

CREATE TABLE `tbl_tags` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `create_time` datetime DEFAULT current_timestamp(),
  `update_time` datetime DEFAULT NULL,
  `create_id` int(11) DEFAULT NULL,
  `update_id` int(11) DEFAULT NULL,
  `archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_tags`
--

INSERT INTO `tbl_tags` (`id`, `name`, `create_time`, `update_time`, `create_id`, `update_id`, `archived`) VALUES
(1, 'SOP', '2025-07-17 15:51:30', NULL, 1, NULL, 0),
(2, 'Tutorial', '2025-07-17 15:51:30', NULL, 1, NULL, 0),
(3, 'Produk Baru', '2025-07-17 15:51:30', NULL, 1, NULL, 0),
(4, 'Kebijakan', '2025-07-17 15:51:30', NULL, 1, NULL, 0),
(5, 'Update', '2025-07-17 15:51:30', NULL, 1, NULL, 0),
(6, 'test 2', '2025-07-17 16:35:07', '2025-07-17 11:35:19', 5, 5, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Kontributor','Pengguna Umum') NOT NULL DEFAULT 'Pengguna Umum',
  `create_time` datetime DEFAULT current_timestamp(),
  `update_time` datetime DEFAULT NULL,
  `create_id` int(11) DEFAULT NULL,
  `update_id` int(11) DEFAULT NULL,
  `archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_users`
--

INSERT INTO `tbl_users` (`id`, `name`, `email`, `password`, `role`, `create_time`, `update_time`, `create_id`, `update_id`, `archived`) VALUES
(1, 'Admin Utama', 'admin@kms.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcqtOdVszg.RKTrGR4dS', 'Admin', '2025-07-17 15:51:30', NULL, NULL, NULL, 1),
(2, 'Kontributor Handal', 'kontributor@kms.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcqtOdVszg.RKTrGR4dS', 'Kontributor', '2025-07-17 15:51:30', NULL, NULL, NULL, 1),
(3, 'Budi Karyawan', 'karyawan1@kms.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcqtOdVszg.RKTrGR4dS', 'Pengguna Umum', '2025-07-17 15:51:30', NULL, NULL, NULL, 1),
(4, 'Siti Karyawati', 'karyawan2@kms.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcqtOdVszg.RKTrGR4dS', 'Pengguna Umum', '2025-07-17 15:51:30', NULL, NULL, NULL, 1),
(5, 'Admin KMS', 'admin@gmail.com', '$2y$10$LItLD2noZtQ5rYWx90wx4.Mqp.xngXCPnpKe3corGZHlL0835STAy', 'Admin', '2025-07-17 15:53:52', NULL, NULL, NULL, 0),
(6, 'Kontributor', 'kontributor@gmail.com', '$2y$10$FMhgsUjMTgCOaS21My1uRelQzQFR9TUueR53D5WguBgNyg3CbcMqS', 'Kontributor', '2025-07-17 10:56:15', '2025-07-17 10:56:15', NULL, NULL, 0),
(7, 'Pengguna', 'pengguna@gmail.com', '$2y$10$lpwQYV7XtKqxJjFj3NGAOewBrEFU90NUU7SQp6LLN2600PF0jzQHm', 'Pengguna Umum', '2025-07-17 10:56:41', '2025-07-17 10:56:41', NULL, NULL, 0),
(8, 'Rizal Rio Andrian 2', 'rizalrio1357@gmail.com', '$2y$10$wAQ72PMHr0eY12Z4vlR3/eE1NSRLwVS3O.P/RzEJ95yO6gTmMjDku', 'Admin', '2025-07-17 11:41:01', '2025-07-17 11:41:09', NULL, 5, 1);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `tbl_categories`
--
ALTER TABLE `tbl_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeks untuk tabel `tbl_feedbacks`
--
ALTER TABLE `tbl_feedbacks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `material_fk` (`material_id`),
  ADD KEY `user_fk` (`user_id`),
  ADD KEY `assessor_fk` (`assessed_by`);

--
-- Indeks untuk tabel `tbl_materials`
--
ALTER TABLE `tbl_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creator_fk` (`create_id`);

--
-- Indeks untuk tabel `tbl_material_assignments`
--
ALTER TABLE `tbl_material_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `assignment_unique` (`material_id`,`user_id`),
  ADD KEY `assignments_user_fk` (`user_id`);

--
-- Indeks untuk tabel `tbl_material_categories`
--
ALTER TABLE `tbl_material_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `material_category_unique` (`material_id`,`category_id`),
  ADD KEY `matcat_category_fk` (`category_id`);

--
-- Indeks untuk tabel `tbl_material_tags`
--
ALTER TABLE `tbl_material_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `material_tag_unique` (`material_id`,`tag_id`),
  ADD KEY `mattag_tag_fk` (`tag_id`);

--
-- Indeks untuk tabel `tbl_tags`
--
ALTER TABLE `tbl_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeks untuk tabel `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `tbl_categories`
--
ALTER TABLE `tbl_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `tbl_feedbacks`
--
ALTER TABLE `tbl_feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `tbl_materials`
--
ALTER TABLE `tbl_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `tbl_material_assignments`
--
ALTER TABLE `tbl_material_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `tbl_material_categories`
--
ALTER TABLE `tbl_material_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `tbl_material_tags`
--
ALTER TABLE `tbl_material_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `tbl_tags`
--
ALTER TABLE `tbl_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `tbl_feedbacks`
--
ALTER TABLE `tbl_feedbacks`
  ADD CONSTRAINT `feedbacks_assessor_fk` FOREIGN KEY (`assessed_by`) REFERENCES `tbl_users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `feedbacks_material_fk` FOREIGN KEY (`material_id`) REFERENCES `tbl_materials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedbacks_user_fk` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_materials`
--
ALTER TABLE `tbl_materials`
  ADD CONSTRAINT `materials_creator_fk` FOREIGN KEY (`create_id`) REFERENCES `tbl_users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `tbl_material_assignments`
--
ALTER TABLE `tbl_material_assignments`
  ADD CONSTRAINT `assignments_material_fk` FOREIGN KEY (`material_id`) REFERENCES `tbl_materials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignments_user_fk` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_material_categories`
--
ALTER TABLE `tbl_material_categories`
  ADD CONSTRAINT `matcat_category_fk` FOREIGN KEY (`category_id`) REFERENCES `tbl_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matcat_material_fk` FOREIGN KEY (`material_id`) REFERENCES `tbl_materials` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_material_tags`
--
ALTER TABLE `tbl_material_tags`
  ADD CONSTRAINT `mattag_material_fk` FOREIGN KEY (`material_id`) REFERENCES `tbl_materials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mattag_tag_fk` FOREIGN KEY (`tag_id`) REFERENCES `tbl_tags` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
