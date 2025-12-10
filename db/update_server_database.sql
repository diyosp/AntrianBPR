-- ========================================
-- SQL Script to Update Server Database
-- From: bprsukab_antrix_server
-- To match: bprsukab_antrix_local
-- Generated: 2025-12-10
-- ========================================

-- ⚠️ IMPORTANT: Backup your database before running these commands!
-- mysqldump -u username -p bprsukab_antrix > backup_before_update.sql

-- ----------------------------------------
-- 1. ALTER tbl_antrian - Add new columns and update status enum
-- ----------------------------------------

-- First, modify the status ENUM to include '2'
ALTER TABLE `tbl_antrian` 
MODIFY COLUMN `status` enum('0','1','2') NOT NULL DEFAULT '0';

-- Add new columns for time tracking
ALTER TABLE `tbl_antrian` 
ADD COLUMN `waktu_mulai` datetime DEFAULT NULL AFTER `updated_date`,
ADD COLUMN `waktu_selesai` datetime DEFAULT NULL AFTER `waktu_mulai`,
ADD COLUMN `durasi` int DEFAULT NULL AFTER `waktu_selesai`;

-- ----------------------------------------
-- 2. CREATE tbl_antrian_kredit (NEW TABLE)
-- ----------------------------------------

CREATE TABLE `tbl_antrian_kredit` (
  `id_kredit` bigint NOT NULL AUTO_INCREMENT,
  `cabang_id` bigint UNSIGNED NOT NULL,
  `tanggal_kredit` date NOT NULL,
  `no_antrian_kredit` smallint NOT NULL,
  `status_kredit` enum('0','1','2') NOT NULL DEFAULT '0',
  `bagian` varchar(100) DEFAULT NULL,
  `updated_date_kredit` datetime DEFAULT NULL,
  `waktu_mulai` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `durasi` int DEFAULT NULL,
  PRIMARY KEY (`id_kredit`),
  KEY `fk_tbl_antrian_kredit_cabang` (`cabang_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Optional: Add foreign key constraint for tbl_antrian_kredit
-- Uncomment the line below if you want to enforce referential integrity
-- ALTER TABLE `tbl_antrian_kredit`
-- ADD CONSTRAINT `fk_tbl_antrian_kredit_cabang` FOREIGN KEY (`cabang_id`) 
-- REFERENCES `cabang` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ----------------------------------------
-- 3. ALTER tbl_antrian_teller - Add new columns
-- ----------------------------------------

ALTER TABLE `tbl_antrian_teller` 
ADD COLUMN `waktu_mulai` datetime DEFAULT NULL AFTER `updated_date_teller`,
ADD COLUMN `waktu_selesai` datetime DEFAULT NULL AFTER `waktu_mulai`,
ADD COLUMN `durasi` int DEFAULT NULL AFTER `waktu_selesai`;

-- ----------------------------------------
-- 4. ALTER users - Add new columns
-- ----------------------------------------

ALTER TABLE `users` 
ADD COLUMN `id_pegawai` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `id`,
ADD COLUMN `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'active' AFTER `cabang_id`,
ADD COLUMN `last_login` datetime DEFAULT NULL AFTER `status`;

-- ========================================
-- END OF SCRIPT
-- ========================================
