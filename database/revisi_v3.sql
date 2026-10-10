
USE `campusfair`;

INSERT INTO `wawancara` (`pendaftar_id`, `status_wawancara`)
SELECT p.id, 'belum'
FROM `pendaftar` p
LEFT JOIN `wawancara` w ON p.id = w.pendaftar_id
WHERE w.id IS NULL;

INSERT INTO `seleksi` (`pendaftar_id`, `status_diterima`)
SELECT p.id, 'pending'
FROM `pendaftar` p
LEFT JOIN `seleksi` s ON p.id = s.pendaftar_id
WHERE s.id IS NULL;

DROP TRIGGER IF EXISTS `trg_pendaftar_after_insert`;

DELIMITER $$

CREATE TRIGGER `trg_pendaftar_after_insert`
AFTER INSERT ON `pendaftar`
FOR EACH ROW
BEGIN
    INSERT INTO `wawancara` (`pendaftar_id`, `status_wawancara`)
    VALUES (NEW.id, 'belum');
    
    INSERT INTO `seleksi` (`pendaftar_id`, `status_diterima`)
    VALUES (NEW.id, 'pending');
END$$

DELIMITER ;

ALTER TABLE `seleksi`
ADD COLUMN `jabatan` ENUM('Anggota', 'Kadiv') NOT NULL DEFAULT 'Anggota'
AFTER `divisi_ditempatkan`;


