-- Add jumlah_transaksi column to tbl_antrian table
-- This column stores the number of transactions for each queue entry
-- to help supervisors understand why queues might be long

ALTER TABLE `tbl_antrian` 
ADD COLUMN `jumlah_transaksi` INT NULL DEFAULT NULL 
COMMENT 'Jumlah transaksi yang akan dilakukan oleh nasabah'
AFTER `durasi`;

-- Add jumlah_transaksi column to tbl_antrian_teller table
ALTER TABLE `tbl_antrian_teller` 
ADD COLUMN `jumlah_transaksi` INT NULL DEFAULT NULL 
COMMENT 'Jumlah transaksi yang akan dilakukan oleh nasabah'
AFTER `durasi`;

-- Add jumlah_transaksi column to tbl_antrian_kredit table
ALTER TABLE `tbl_antrian_kredit` 
ADD COLUMN `jumlah_transaksi` INT NULL DEFAULT NULL 
COMMENT 'Jumlah transaksi yang akan dilakukan oleh nasabah'
AFTER `durasi`;
