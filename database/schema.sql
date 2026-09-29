-- Schema Database Campus Fair 2027 Management System
-- MariaDB / MySQL Compatible

CREATE DATABASE IF NOT EXISTS `campusfair` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `campusfair`;

-- 1. Tabel users (Admin & Pewawancara)
DROP TABLE IF EXISTS `seleksi`;
DROP TABLE IF EXISTS `wawancara`;
DROP TABLE IF EXISTS `pendaftar`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'pewawancara') NOT NULL DEFAULT 'pewawancara',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel pendaftar
CREATE TABLE `pendaftar` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_lengkap` VARCHAR(255) NOT NULL,
  `tahun_lulus` VARCHAR(50) NOT NULL,
  `asal_kampus_jurusan` TEXT NOT NULL,
  `no_whatsapp` VARCHAR(50) NOT NULL,
  `alasan_mendaftar` TEXT NOT NULL,
  `pilihan_1` ENUM(
    'Humas',
    'Acara',
    'Konsumsi',
    'Sponsorship',
    'Perlengkapan',
    'Keamanan',
    'Publikasi, Dekorasi, dan Dokumentasi'
  ) NOT NULL,
  `alasan_pilihan_1` TEXT NOT NULL,
  `pengalaman_pilihan_1` TEXT NOT NULL,
  `pilihan_2` ENUM(
    'Humas',
    'Acara',
    'Konsumsi',
    'Sponsorship',
    'Perlengkapan',
    'Keamanan',
    'Publikasi, Dekorasi, dan Dokumentasi'
  ) NOT NULL,
  `alasan_pilihan_2` TEXT NOT NULL,
  `pengalaman_pilihan_2` TEXT NOT NULL,
  `kesibukan_okt_jan` TEXT NOT NULL,
  `skor_komitmen` INT NOT NULL CHECK (`skor_komitmen` BETWEEN 1 AND 10),
  `skor_prioritas` INT NOT NULL CHECK (`skor_prioritas` BETWEEN 1 AND 10),
  `skor_keyakinan` INT NOT NULL CHECK (`skor_keyakinan` BETWEEN 1 AND 10),
  `cv_link` VARCHAR(500) NOT NULL,
  `portofolio_link` VARCHAR(500) DEFAULT NULL,
  `input_by` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_pendaftar_input_by` FOREIGN KEY (`input_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel wawancara
CREATE TABLE `wawancara` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pendaftar_id` INT NOT NULL UNIQUE,
  `pewawancara_id` INT DEFAULT NULL,
  `status_wawancara` ENUM('belum', 'selesai') DEFAULT 'belum',
  `komentar` TEXT DEFAULT NULL,
  `komentar_updated_at` DATETIME DEFAULT NULL,
  CONSTRAINT `fk_wawancara_pendaftar` FOREIGN KEY (`pendaftar_id`) REFERENCES `pendaftar` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wawancara_pewawancara` FOREIGN KEY (`pewawancara_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel seleksi
CREATE TABLE `seleksi` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pendaftar_id` INT NOT NULL UNIQUE,
  `status_diterima` ENUM('pending', 'diterima', 'tidak_diterima') DEFAULT 'pending',
  `divisi_ditempatkan` ENUM(
    'Humas',
    'Acara',
    'Konsumsi',
    'Sponsorship',
    'Perlengkapan',
    'Keamanan',
    'Publikasi, Dekorasi, dan Dokumentasi'
  ) DEFAULT NULL,
  `decided_by` INT DEFAULT NULL,
  `decided_at` DATETIME DEFAULT NULL,
  CONSTRAINT `fk_seleksi_pendaftar` FOREIGN KEY (`pendaftar_id`) REFERENCES `pendaftar` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_seleksi_decided_by` FOREIGN KEY (`decided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Data Akun Utama (Password default: password123)
-- Hash BCRYPT untuk 'password123': $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO `users` (`nama`, `email`, `password_hash`, `role`) VALUES
('Admin Utama', 'admin@campusfair.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Pewawancara 1', 'interviewer1@campusfair.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pewawancara'),
('Pewawancara 2', 'interviewer2@campusfair.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pewawancara'),
('Pewawancara 3', 'interviewer3@campusfair.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pewawancara'),
('Pewawancara 4', 'interviewer4@campusfair.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pewawancara');
