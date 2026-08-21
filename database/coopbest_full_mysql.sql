-- ============================================================
-- SISTEM PENGURUSAN KOPERASI
-- Corrected MySQL/phpMyAdmin import script
-- Notes: DATE DEFAULT NULL changed for older MySQL/MariaDB compatibility.
-- ============================================================

SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+08:00';
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS coopbestt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE coopbestt;
ALTER DATABASE coopbestt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ============================================================
-- 0.1 LARAVEL FRAMEWORK TABLES
-- Required because .env uses database-backed sessions, cache, and queue.
-- ============================================================

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) NOT NULL PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL DEFAULT NULL,
    ip_address VARCHAR(45) NULL DEFAULT NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX sessions_user_id_index (user_id),
    INDEX sessions_last_activity_index (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cache (
    `key` VARCHAR(255) NOT NULL PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration BIGINT NOT NULL,
    INDEX cache_expiration_index (expiration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cache_locks (
    `key` VARCHAR(255) NOT NULL PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration BIGINT NOT NULL,
    INDEX cache_locks_expiration_index (expiration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS jobs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts SMALLINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL DEFAULT NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    INDEX jobs_queue_index (queue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS job_batches (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options MEDIUMTEXT NULL,
    cancelled_at INT NULL,
    created_at INT NOT NULL,
    finished_at INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS failed_jobs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX failed_jobs_connection_queue_failed_at_index (connection(191), queue(191), failed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ahli_imports (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    imported_count INT UNSIGNED NOT NULL DEFAULT 0,
    failed_count INT UNSIGNED NOT NULL DEFAULT 0,
    errors JSON NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP VIEW IF EXISTS v_status_tempahan;
DROP VIEW IF EXISTS v_top5_pemegang_saham;
DROP VIEW IF EXISTS v_laporan_pembayaran_vendor;
DROP VIEW IF EXISTS v_laporan_saham_ahli;
DROP VIEW IF EXISTS v_laporan_stok_semasa;
DROP VIEW IF EXISTS v_laporan_jualan_harian;
DROP PROCEDURE IF EXISTS kira_bayaran_vendor;
DROP PROCEDURE IF EXISTS import_ahli;
DROP TRIGGER IF EXISTS trg_update_stok_sales;
DROP TRIGGER IF EXISTS trg_update_tempahan_total;
DROP TRIGGER IF EXISTS trg_create_saham_new_ahli;

-- ============================================================
-- SISTEM PENGURUSAN KOPERASI
-- Full Database Script
-- Versi: 2.0
-- Tarikh: 13 Julai 2026
-- ============================================================

-- ============================================================
-- 1.0 DROP EXISTING TABLES (if any)
-- ============================================================

DROP TABLE IF EXISTS log_sistem;
DROP TABLE IF EXISTS admin;
DROP TABLE IF EXISTS tugasan;
DROP TABLE IF EXISTS elaun;
DROP TABLE IF EXISTS kehadiran;
DROP TABLE IF EXISTS jualan;
DROP TABLE IF EXISTS stok;
DROP TABLE IF EXISTS pembayaran_vendor;
DROP TABLE IF EXISTS item_serahan;
DROP TABLE IF EXISTS serahan;
DROP TABLE IF EXISTS vendor;
DROP TABLE IF EXISTS pembayaran_ahli;
DROP TABLE IF EXISTS saham;
DROP TABLE IF EXISTS item_tempahan;
DROP TABLE IF EXISTS item_baju;
DROP TABLE IF EXISTS kategori_baju;
DROP TABLE IF EXISTS tempahan;
DROP TABLE IF EXISTS pekerja;
DROP TABLE IF EXISTS ahli;

-- ============================================================
-- 2.0 CREATE TABLES
-- ============================================================

-- ============================================================
-- 2.1 TABLE: ahli
-- ============================================================
CREATE TABLE ahli (
    id_ahli INT PRIMARY KEY AUTO_INCREMENT,
    no_matrik VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    nric VARCHAR(20) UNIQUE,
    password_hash VARCHAR(255),
    semester VARCHAR(10),
    program VARCHAR(50),
    no_tel VARCHAR(15),
    email VARCHAR(100),
    baki_ewallet DECIMAL(10,2) DEFAULT 0.00,
    tarikh_daftar DATE DEFAULT NULL,
    status_aktif TINYINT(1) DEFAULT 1,
    INDEX idx_no_matrik (no_matrik),
    INDEX idx_semester (semester),
    INDEX idx_status_aktif (status_aktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2.2 TABLE: pekerja
-- ============================================================
CREATE TABLE pekerja (
    id_pekerja INT PRIMARY KEY AUTO_INCREMENT,
    no_pekerja VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    nric VARCHAR(20) UNIQUE,
    password_hash VARCHAR(255),
    jawatan VARCHAR(50),
    no_tel VARCHAR(15),
    email VARCHAR(100),
    kadar_elaun DECIMAL(10,2) DEFAULT 0.00,
    tarikh_mula DATE DEFAULT NULL,
    status_aktif TINYINT(1) DEFAULT 1,
    INDEX idx_no_pekerja (no_pekerja),
    INDEX idx_status_aktif (status_aktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2.3 TABLE: kategori_baju
-- ============================================================
CREATE TABLE kategori_baju (
    id_kategori INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(50) NOT NULL,
    penerangan VARCHAR(200),
    INDEX idx_nama_kategori (nama_kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2.4 TABLE: item_baju
-- ============================================================
CREATE TABLE item_baju (
    id_item INT PRIMARY KEY AUTO_INCREMENT,
    id_kategori INT,
    nama_item VARCHAR(100) NOT NULL,
    saiz VARCHAR(10),
    harga DECIMAL(10,2) NOT NULL,
    stok_tertinggal INT DEFAULT 0,
    FOREIGN KEY (id_kategori) REFERENCES kategori_baju(id_kategori) ON DELETE SET NULL,
    INDEX idx_kategori (id_kategori),
    INDEX idx_nama_item (nama_item)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2.5 TABLE: tempahan
-- ============================================================
CREATE TABLE tempahan (
    id_tempahan INT PRIMARY KEY AUTO_INCREMENT,
    id_ahli INT NOT NULL,
    tarikh_tempahan DATE DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'Pending',
    jumlah_total DECIMAL(10,2) DEFAULT 0.00,
    kaedah_bayaran VARCHAR(20),
    tarikh_siap DATE,
    tarikh_ambil DATE,
    FOREIGN KEY (id_ahli) REFERENCES ahli(id_ahli) ON DELETE CASCADE,
    INDEX idx_ahli (id_ahli),
    INDEX idx_status (status),
    INDEX idx_tarikh_tempahan (tarikh_tempahan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2.6 TABLE: item_tempahan
-- ============================================================
CREATE TABLE item_tempahan (
    id_item_tempahan INT PRIMARY KEY AUTO_INCREMENT,
    id_tempahan INT NOT NULL,
    id_item INT NOT NULL,
    kuantiti INT NOT NULL,
    harga_seunit DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_tempahan) REFERENCES tempahan(id_tempahan) ON DELETE CASCADE,
    FOREIGN KEY (id_item) REFERENCES item_baju(id_item) ON DELETE CASCADE,
    INDEX idx_tempahan (id_tempahan),
    INDEX idx_item (id_item)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2.7 TABLE: saham
-- ============================================================
CREATE TABLE saham (
    id_saham INT PRIMARY KEY AUTO_INCREMENT,
    id_ahli INT NOT NULL,
    syer DECIMAL(10,2) DEFAULT 0.00,
    yuran DECIMAL(10,2) DEFAULT 0.00,
    tarikh_kemaskini DATE DEFAULT NULL,
    FOREIGN KEY (id_ahli) REFERENCES ahli(id_ahli) ON DELETE CASCADE,
    UNIQUE KEY unique_ahli (id_ahli),
    INDEX idx_ahli (id_ahli)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2.8 TABLE: pembayaran_ahli
-- ============================================================
CREATE TABLE pembayaran_ahli (
    id_pembayaran INT PRIMARY KEY AUTO_INCREMENT,
    id_ahli INT NOT NULL,
    id_tempahan INT,
    jumlah DECIMAL(10,2) NOT NULL,
    tarikh_bayar DATE DEFAULT NULL,
    kaedah_bayaran VARCHAR(20),
    status VARCHAR(20) DEFAULT 'Berjaya',
    no_resit VARCHAR(50) UNIQUE,
    FOREIGN KEY (id_ahli) REFERENCES ahli(id_ahli) ON DELETE CASCADE,
    FOREIGN KEY (id_tempahan) REFERENCES tempahan(id_tempahan) ON DELETE SET NULL,
    INDEX idx_ahli (id_ahli),
    INDEX idx_tempahan (id_tempahan),
    INDEX idx_tarikh_bayar (tarikh_bayar),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2.9 TABLE: vendor
-- ============================================================
CREATE TABLE vendor (
    id_vendor INT PRIMARY KEY AUTO_INCREMENT,
    nama_vendor VARCHAR(100) NOT NULL,
    no_akaun VARCHAR(30),
    bank VARCHAR(50),
    no_tel VARCHAR(15),
    email VARCHAR(100),
    alamat TEXT,
    komisen_peratus DECIMAL(5,2) DEFAULT 20.00,
    status_aktif TINYINT(1) DEFAULT 1,
    INDEX idx_nama_vendor (nama_vendor),
    INDEX idx_status_aktif (status_aktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2.10 TABLE: serahan
-- ============================================================
CREATE TABLE serahan (
    id_serahan INT PRIMARY KEY AUTO_INCREMENT,
    id_vendor INT NOT NULL,
    tarikh_serahan DATE DEFAULT NULL,
    jumlah_kasar DECIMAL(10,2) DEFAULT 0.00,
    komisen DECIMAL(10,2) DEFAULT 0.00,
    tuntutan_bersih DECIMAL(10,2) DEFAULT 0.00,
    status VARCHAR(20) DEFAULT 'Pending',
    tarikh_lulus DATE,
    diluluskan_oleh INT,
    FOREIGN KEY (id_vendor) REFERENCES vendor(id_vendor) ON DELETE CASCADE,
    INDEX idx_vendor (id_vendor),
    INDEX idx_status (status),
    INDEX idx_tarikh_serahan (tarikh_serahan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2.11 TABLE: item_serahan
-- ============================================================
CREATE TABLE item_serahan (
    id_item_serahan INT PRIMARY KEY AUTO_INCREMENT,
    id_serahan INT NOT NULL,
    nama_item VARCHAR(100) NOT NULL,
    harga_seunit DECIMAL(10,2) NOT NULL,
    kuantiti INT NOT NULL,
    jumlah DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_serahan) REFERENCES serahan(id_serahan) ON DELETE CASCADE,
    INDEX idx_serahan (id_serahan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2.12 TABLE: pembayaran_vendor
-- ============================================================
CREATE TABLE pembayaran_vendor (
    id_payment INT PRIMARY KEY AUTO_INCREMENT,
    id_vendor INT NOT NULL,
    id_serahan INT,
    jumlah_jualan DECIMAL(10,2) DEFAULT 0.00,
    komisen_dipotong DECIMAL(10,2) DEFAULT 0.00,
    refund DECIMAL(10,2) DEFAULT 0.00,
    jumlah_bayaran DECIMAL(10,2) DEFAULT 0.00,
    tarikh_bayar DATE DEFAULT NULL,
    no_resit VARCHAR(50) UNIQUE,
    status VARCHAR(20) DEFAULT 'Pending',
    FOREIGN KEY (id_vendor) REFERENCES vendor(id_vendor) ON DELETE CASCADE,
    FOREIGN KEY (id_serahan) REFERENCES serahan(id_serahan) ON DELETE SET NULL,
    INDEX idx_vendor (id_vendor),
    INDEX idx_serahan (id_serahan),
    INDEX idx_tarikh_bayar (tarikh_bayar),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2.13 TABLE: stok
-- ============================================================
CREATE TABLE stok (
    id_stok INT PRIMARY KEY AUTO_INCREMENT,
    nama_item VARCHAR(100) NOT NULL,
    kategori VARCHAR(50),
    harga_beli DECIMAL(10,2),
    harga_jual DECIMAL(10,2) NOT NULL,
    kuantiti_semasa INT DEFAULT 0,
    kuantiti_minimum INT DEFAULT 5,
    tarikh_kemaskini DATE DEFAULT NULL,
    INDEX idx_nama_item (nama_item),
    INDEX idx_kategori (kategori),
    INDEX idx_kuantiti_semasa (kuantiti_semasa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2.14 TABLE: jualan
-- ============================================================
CREATE TABLE jualan (
    id_jualan INT PRIMARY KEY AUTO_INCREMENT,
    id_stok INT NOT NULL,
    tarikh_jualan DATE DEFAULT NULL,
    kuantiti INT NOT NULL,
    harga_seunit DECIMAL(10,2) NOT NULL,
    jumlah DECIMAL(10,2) NOT NULL,
    kaedah_bayaran VARCHAR(20),
    id_pekerja INT,
    FOREIGN KEY (id_stok) REFERENCES stok(id_stok) ON DELETE CASCADE,
    FOREIGN KEY (id_pekerja) REFERENCES pekerja(id_pekerja) ON DELETE SET NULL,
    INDEX idx_stok (id_stok),
    INDEX idx_pekerja (id_pekerja),
    INDEX idx_tarikh_jualan (tarikh_jualan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2.15 TABLE: kehadiran
-- ============================================================
CREATE TABLE kehadiran (
    id_kehadiran INT PRIMARY KEY AUTO_INCREMENT,
    id_pekerja INT NOT NULL,
    tarikh DATE DEFAULT NULL,
    masa_masuk TIME,
    masa_keluar TIME,
    jam_kerja DECIMAL(5,2) DEFAULT 0.00,
    status VARCHAR(20) DEFAULT 'Hadir',
    FOREIGN KEY (id_pekerja) REFERENCES pekerja(id_pekerja) ON DELETE CASCADE,
    INDEX idx_pekerja (id_pekerja),
    INDEX idx_tarikh (tarikh),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2.16 TABLE: elaun
-- ============================================================
CREATE TABLE elaun (
    id_elaun INT PRIMARY KEY AUTO_INCREMENT,
    id_pekerja INT NOT NULL,
    id_kehadiran INT NOT NULL,
    jumlah_elaun DECIMAL(10,2) NOT NULL,
    tarikh_tuntutan DATE DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'Pending',
    tarikh_lulus DATE,
    diluluskan_oleh INT,
    FOREIGN KEY (id_pekerja) REFERENCES pekerja(id_pekerja) ON DELETE CASCADE,
    FOREIGN KEY (id_kehadiran) REFERENCES kehadiran(id_kehadiran) ON DELETE CASCADE,
    INDEX idx_pekerja (id_pekerja),
    INDEX idx_kehadiran (id_kehadiran),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2.17 TABLE: tugasan
-- ============================================================
CREATE TABLE tugasan (
    id_tugasan INT PRIMARY KEY AUTO_INCREMENT,
    id_pekerja INT NOT NULL,
    id_ahli INT,
    tajuk VARCHAR(100) NOT NULL,
    penerangan TEXT,
    tarikh_mula DATE DEFAULT NULL,
    tarikh_tamat DATE,
    status VARCHAR(20) DEFAULT 'Baru',
    keutamaan VARCHAR(10) DEFAULT 'Sederhana',
    FOREIGN KEY (id_pekerja) REFERENCES pekerja(id_pekerja) ON DELETE CASCADE,
    FOREIGN KEY (id_ahli) REFERENCES ahli(id_ahli) ON DELETE SET NULL,
    INDEX idx_pekerja (id_pekerja),
    INDEX idx_ahli (id_ahli),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2.18 TABLE: admin
-- ============================================================
CREATE TABLE admin (
    id_admin INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    nric VARCHAR(20) UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    peranan VARCHAR(50) DEFAULT 'Admin',
    status_aktif TINYINT(1) DEFAULT 1,
    INDEX idx_username (username),
    INDEX idx_status_aktif (status_aktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2.19 TABLE: log_sistem
-- ============================================================
CREATE TABLE log_sistem (
    id_log INT PRIMARY KEY AUTO_INCREMENT,
    id_admin INT,
    tindakan VARCHAR(100) NOT NULL,
    butiran TEXT,
    tarikh DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    FOREIGN KEY (id_admin) REFERENCES admin(id_admin) ON DELETE SET NULL,
    INDEX idx_admin (id_admin),
    INDEX idx_tarikh (tarikh)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3.0 INSERT SAMPLE DATA
-- ============================================================

-- ============================================================
-- 3.1 ADMIN
-- ============================================================
INSERT INTO admin (nama, username, password_hash, peranan, status_aktif) VALUES
('Nor Azira Binti Abd Razak', 'azira_admin', '$2y$10$ExampleHashHere1234567890', 'Super Admin', 1),
('Norazlina Bt Abdullah', 'azlina', '$2y$10$ExampleHashHere1234567890', 'Admin', 1),
('Norulmaisura Bt Mohamad', 'maisura', '$2y$10$ExampleHashHere1234567890', 'Pengurus', 1);

-- ============================================================
-- 3.2 KATEGORI_BAJU
-- ============================================================
INSERT INTO kategori_baju (nama_kategori, penerangan) VALUES
('Baju Berkolar', 'Baju berkolari Politeknik dengan logo institusi'),
('Baju Korporat', 'Baju korporat untuk staf dan pengurusan PBT'),
('T-Shirt Sukan', 'T-Shirt untuk aktiviti sukan dan rekreasi');

-- ============================================================
-- 3.3 ITEM_BAJU
-- ============================================================
INSERT INTO item_baju (id_kategori, nama_item, saiz, harga, stok_tertinggal) VALUES
(1, 'Baju Berkolar Poli', 'S', 35.00, 50),
(1, 'Baju Berkolar Poli', 'M', 35.00, 80),
(1, 'Baju Berkolar Poli', 'L', 35.00, 60),
(1, 'Baju Berkolar Poli', 'XL', 35.00, 40),
(1, 'Baju Berkolar Poli', 'XXL', 35.00, 20),
(2, 'Baju Korporat PBT', 'S', 65.00, 30),
(2, 'Baju Korporat PBT', 'M', 65.00, 45),
(2, 'Baju Korporat PBT', 'L', 65.00, 40),
(2, 'Baju Korporat PBT', 'XL', 65.00, 25),
(2, 'Baju Korporat PBT', 'XXL', 65.00, 15);

-- ============================================================
-- 3.4 VENDOR
-- ============================================================
INSERT INTO vendor (nama_vendor, no_akaun, bank, no_tel, komisen_peratus, status_aktif) VALUES
('CHE ISMAIL BIN CHE NAF (SAFWAN)', '1199541100223383', 'BSN', '019-1234567', 20.00, 1),
('ANG Kim Lin Sdn Bhd (PEPSI)', '8006771347', 'CIMB', '019-2345678', 20.00, 1),
('AYUSRI ENTERPRISE (KACANG)', '3820617917', 'PUBLIC BANK', '019-3456789', 20.00, 1),
('NUR IZZATI AKMA BT AZMAN (CUPCAKE FIRA)', '7622015733', 'CIMB', '019-4567890', 20.00, 1),
('DIK MEK KITCHEN (NS LEMOK)', '163064290447', 'MAYBANK', '019-5678901', 20.00, 1),
('FIQ ENTERPRISE (BCP)', '8006786690', 'CIMB', '019-6789012', 20.00, 1),
('NIK ROHAYA BT ABDULLAH (AL FAQEH)', '164070538336', 'MAYBANK', '019-7890123', 20.00, 1),
('NUR ANIEZUL HUDA (SPAGHETI)', '1100541000175596', 'BSN', '019-8901234', 20.00, 1),
('ANR BAKERY', '1111029000323997', 'BSN', '019-9012345', 20.00, 1),
('SURIYANI BINTI HASSAN (ROHAYUDEN/DEEN)', '1111029000012684', 'BSN', '019-0123456', 20.00, 1),
('K.T.KRIM TRADING (AISKRIM)', NULL, NULL, '019-1122334', 20.00, 1),
('AYKN PRINTING', '563046362633', 'MAYBANK', '019-2233445', 20.00, 1),
('AINNUR MILLENNIUM RESOURCES (AIR MINERAL)', '1111841100082695', 'BSN', '019-3344556', 20.00, 1),
('IMIEMAJU ENTERPRISE (CHOCOJAR)', '13030001518716', 'BANK MUAMALAT', '019-4455667', 12.50, 1),
('ZABANA MOHAMMAD (KULIT POPIA)', '13030012756728', 'BANK MUAMALAT', '019-5566778', 24.00, 1),
('NAJWA (KEK BATIK)', '7653040458', 'CIMB', '019-6677889', 22.86, 1),
('ROTI SAMUDRA', '6925188719', 'PUBLIC BANK', '019-7788990', 20.00, 1);

-- ============================================================
-- 3.5 AHLI (SAMPLE STUDENTS)
-- ============================================================
INSERT INTO ahli (no_matrik, nama, semester, program, no_tel, email, baki_ewallet, status_aktif) VALUES
('13DDT22F1001', 'Ayman Bin Zaid', 'Sem 4', 'JTMK', '011-1234567', 'ayman.zaid@example.com', 150.00, 1),
('13DDT22F1099', 'Muhammad Farhan', 'Sem 4', 'JTMK', '011-2345678', 'farhan@example.com', 250.00, 1),
('13DDT23F2005', 'Siti Nurhaliza', 'Sem 2', 'JTMK', '011-3456789', 'siti.nur@example.com', 80.00, 1),
('13DDT24F1002', 'Nurul Izzah', 'Sem 1', 'JTMK', '011-4567890', 'nurul.izzah@example.com', 50.00, 1),
('13DDT22F1010', 'Amirul Hakim', 'Sem 4', 'JTMK', '011-5678901', 'amirul@example.com', 300.00, 1),
('13DDT23F2015', 'Ahmad Zaki', 'Sem 2', 'JTMK', '011-6789012', 'ahmad.zaki@example.com', 120.00, 1),
('13DDT24F1025', 'Farhana Ali', 'Sem 1', 'JTMK', '011-7890123', 'farhana@example.com', 90.00, 1),
('13DDT22F1035', 'Mohammad Azri', 'Sem 4', 'JTMK', '011-8901234', 'azri@example.com', 200.00, 0);

-- ============================================================
-- 3.6 PEKERJA
-- ============================================================
INSERT INTO pekerja (no_pekerja, nama, jawatan, no_tel, email, kadar_elaun, status_aktif) VALUES
('PBT-001', 'Nor Azira Binti Abd Razak', 'Pengurus Kedai', '012-1234567', 'azira.staff@example.com', 15.00, 1),
('PBT-8821', 'Norazlina Bt Abdullah', 'Bendahari 1', '012-2345678', 'azlina.staff@example.com', 12.00, 1),
('PBT-003', 'Norulmaisura Bt Mohamad', 'Pengerusi Koperasi', '012-3456789', 'maisura.staff@example.com', 18.00, 1),
('PBT-004', 'Suriyani Binti Hassan', 'Ketua Stok', '012-4567890', 'suriyani.staff@example.com', 14.00, 1),
('PBT-005', 'Rohani Ramli', 'Pembantu Kedai', '012-5678901', 'rohani.staff@example.com', 10.00, 1),
('PBT-006', 'Rugayah Bt Mat Ail', 'Pembantu Kedai', '012-6789012', 'rugayah.staff@example.com', 10.00, 1);

-- ============================================================
-- 3.7 SAHAM
-- ============================================================
INSERT INTO saham (id_ahli, syer, yuran, tarikh_kemaskini) VALUES
(1, 50.00, 200.00, CURRENT_DATE),
(2, 100.00, 200.00, CURRENT_DATE),
(3, 30.00, 150.00, CURRENT_DATE),
(4, 20.00, 100.00, CURRENT_DATE),
(5, 80.00, 250.00, CURRENT_DATE),
(6, 40.00, 180.00, CURRENT_DATE),
(7, 25.00, 120.00, CURRENT_DATE);

-- ============================================================
-- 3.8 STOK
-- ============================================================
INSERT INTO stok (nama_item, kategori, harga_beli, harga_jual, kuantiti_semasa, kuantiti_minimum) VALUES
('Nasi Lemak', 'Makanan', 2.50, 3.00, 50, 10),
('Ramen', 'Makanan', 4.50, 6.00, 30, 5),
('Sandwich Telur', 'Makanan', 2.00, 3.00, 25, 5),
('Kacang Goreng', 'Snek', 1.50, 2.50, 40, 10),
('Air Mineral 500ml', 'Minuman', 0.80, 1.50, 100, 20),
('Pepsi 330ml', 'Minuman', 1.20, 2.00, 60, 15);

-- ============================================================
-- 3.9 SERAHAN (Vendor Submissions)
-- ============================================================
INSERT INTO serahan (id_vendor, tarikh_serahan, jumlah_kasar, komisen, tuntutan_bersih, status, tarikh_lulus) VALUES
(5, '2025-11-15', 1127.00, 225.40, 901.60, 'Selesai', '2025-11-21'),
(14, '2025-11-15', 64.00, 8.00, 56.00, 'Selesai', '2025-11-21'),
(15, '2025-11-15', 247.80, 59.60, 188.20, 'Selesai', '2025-11-21'),
(16, '2025-11-15', 105.00, 24.00, 81.00, 'Selesai', '2025-11-21');

-- ============================================================
-- 3.10 ITEM_SERAHAN
-- ============================================================
INSERT INTO item_serahan (id_serahan, nama_item, harga_seunit, kuantiti, jumlah) VALUES
(1, 'Nasi Lemak', 3.00, 200, 600.00),
(1, 'Ramen', 6.00, 50, 300.00),
(1, 'Sandwich Telur', 3.00, 75, 225.00),
(2, 'Chocojar', 8.00, 8, 64.00),
(3, 'Kulit Popia', 4.00, 30, 120.00),
(3, 'Refund Minggu Lepas', 0.00, 0, 0.00),
(4, 'Kek Batik', 15.00, 7, 105.00);

-- ============================================================
-- 3.11 PEMBAYARAN_VENDOR
-- ============================================================
INSERT INTO pembayaran_vendor (id_vendor, id_serahan, jumlah_jualan, komisen_dipotong, refund, jumlah_bayaran, tarikh_bayar, no_resit, status) VALUES
(5, 1, 1127.00, 225.40, 0.00, 901.60, '2025-11-21', 'PV-20251121-001', 'Selesai'),
(14, 2, 64.00, 8.00, 0.00, 56.00, '2025-11-21', 'PV-20251121-002', 'Selesai'),
(15, 3, 247.80, 59.60, 50.20, 188.20, '2025-11-21', 'PV-20251121-003', 'Selesai'),
(16, 4, 105.00, 24.00, 15.00, 81.00, '2025-11-21', 'PV-20251121-004', 'Selesai');

-- ============================================================
-- 3.12 JUALAN (Sample Daily Sales)
-- ============================================================
INSERT INTO jualan (id_stok, tarikh_jualan, kuantiti, harga_seunit, jumlah, kaedah_bayaran, id_pekerja) VALUES
(1, '2025-11-15', 15, 3.00, 45.00, 'Tunai', 1),
(2, '2025-11-15', 10, 6.00, 60.00, 'E-Wallet', 2),
(3, '2025-11-15', 12, 3.00, 36.00, 'Tunai', 1),
(1, '2025-11-16', 20, 3.00, 60.00, 'Tunai', 3),
(2, '2025-11-16', 8, 6.00, 48.00, 'E-Wallet', 2),
(4, '2025-11-16', 15, 2.50, 37.50, 'Tunai', 4);

-- ============================================================
-- 3.13 KEHADIRAN
-- ============================================================
INSERT INTO kehadiran (id_pekerja, tarikh, masa_masuk, masa_keluar, jam_kerja, status) VALUES
(1, '2025-11-15', '08:00:00', '17:00:00', 9.00, 'Hadir'),
(2, '2025-11-15', '08:15:00', '17:15:00', 9.00, 'Hadir'),
(3, '2025-11-15', '08:30:00', '17:30:00', 9.00, 'Hadir'),
(4, '2025-11-15', '09:00:00', '18:00:00', 9.00, 'Hadir'),
(5, '2025-11-15', '08:00:00', '16:00:00', 8.00, 'Hadir'),
(1, '2025-11-16', '08:00:00', '17:00:00', 9.00, 'Hadir'),
(2, '2025-11-16', '08:20:00', '17:20:00', 9.00, 'Hadir');

-- ============================================================
-- 3.14 TEMPAHAN BAJU
-- ============================================================
INSERT INTO tempahan (id_ahli, tarikh_tempahan, status, jumlah_total, kaedah_bayaran, tarikh_siap, tarikh_ambil) VALUES
(1, '2025-11-10', 'Siap Diambil', 100.00, 'Tunai', '2025-11-12', '2025-11-14'),
(3, '2025-11-11', 'Siap', 70.00, 'E-Wallet', '2025-11-13', NULL),
(5, '2025-11-12', 'Dalam Proses', 130.00, 'Tunai', NULL, NULL),
(7, '2025-11-13', 'Pending', 35.00, 'Online', NULL, NULL);

-- ============================================================
-- 3.15 ITEM_TEMPAHAN
-- ============================================================
INSERT INTO item_tempahan (id_tempahan, id_item, kuantiti, harga_seunit, subtotal) VALUES
(1, 1, 2, 35.00, 70.00),
(1, 2, 1, 30.00, 30.00),
(2, 6, 1, 65.00, 65.00),
(3, 2, 2, 35.00, 70.00),
(3, 8, 1, 60.00, 60.00);

-- ============================================================
-- 3.16 PEMBAYARAN_AHLI
-- ============================================================
INSERT INTO pembayaran_ahli (id_ahli, id_tempahan, jumlah, tarikh_bayar, kaedah_bayaran, status, no_resit) VALUES
(1, 1, 100.00, '2025-11-10', 'Tunai', 'Berjaya', 'PA-20251110-001'),
(3, 2, 70.00, '2025-11-11', 'E-Wallet', 'Berjaya', 'PA-20251111-001'),
(5, 3, 130.00, '2025-11-12', 'Tunai', 'Berjaya', 'PA-20251112-001');

-- ============================================================
-- 3.17 ELAUN
-- ============================================================
INSERT INTO elaun (id_pekerja, id_kehadiran, jumlah_elaun, tarikh_tuntutan, status, tarikh_lulus, diluluskan_oleh) VALUES
(1, 1, 135.00, '2025-11-15', 'Diluluskan', '2025-11-16', 1),
(2, 2, 108.00, '2025-11-15', 'Diluluskan', '2025-11-16', 1),
(3, 3, 162.00, '2025-11-15', 'Diluluskan', '2025-11-16', 1),
(4, 4, 126.00, '2025-11-15', 'Diluluskan', '2025-11-16', 1),
(5, 5, 80.00, '2025-11-15', 'Diluluskan', '2025-11-16', 1);

-- ============================================================
-- 3.18 TUGASAN
-- ============================================================
INSERT INTO tugasan (id_pekerja, id_ahli, tajuk, penerangan, tarikh_mula, tarikh_tamat, status, keutamaan) VALUES
(1, NULL, 'Proses Tempahan Baju', 'Memproses tempahan baju pelajar yang pending', '2025-11-15', '2025-11-20', 'Dalam Proses', 'Tinggi'),
(2, NULL, 'Kemaskini Stok', 'Mengemaskini stok barang di kedai', '2025-11-16', '2025-11-17', 'Baru', 'Sederhana'),
(3, NULL, 'Laporan Jualan', 'Menyediakan laporan jualan mingguan', '2025-11-18', '2025-11-20', 'Baru', 'Tinggi');

-- ============================================================
-- 3.19 LOG_SISTEM
-- ============================================================
INSERT INTO log_sistem (id_admin, tindakan, butiran, tarikh, ip_address) VALUES
(1, 'Import Data Ahli', 'Berjaya mengimport 7 rekod ahli baru', '2025-11-15 10:30:00', '192.168.1.100'),
(1, 'Pembayaran Vendor', 'Pembayaran kepada DIK MEK KITCHEN sebanyak RM901.60', '2025-11-21 15:00:00', '192.168.1.100'),
(2, 'Kemaskini Stok', 'Stok Nasi Lemak dikemaskini: 50 unit', '2025-11-15 09:00:00', '192.168.1.101');

-- ============================================================
-- 3.20 AUTH DEFAULT CREDENTIALS
-- Student login: No Matrik + password NRIC.
-- Staff login: NRIC + password staff12345.
-- Admin login: NRIC + password aadmin12345.
-- ============================================================

UPDATE ahli SET nric = '010101010001', password_hash = '$2y$10$pOH2rHcotR4XYkeZRYmRDO4sIBBKBHaI8DE5XnyxU0.DSUa.eeiKm' WHERE no_matrik = '13DDT22F1001';
UPDATE ahli SET nric = '010101010002', password_hash = '$2y$10$Ct9QtKfmLmpGZNPWnGRmuuBYznGJVzPct1p04OxTGrIm3q4oPxqW6' WHERE no_matrik = '13DDT22F1099';
UPDATE ahli SET nric = '010101010003', password_hash = '$2y$10$qK7twMdPoDsFP2JsD2X6nOoHZS0yEuJXXNZG9TmoHkpXBUnbVdDWS' WHERE no_matrik = '13DDT23F2005';
UPDATE ahli SET nric = '010101010004', password_hash = '$2y$10$5CwUmIHcRvpEQJG.BhF5n.7HcPejJp06ixlts3A9pdQvo7ghfulvK' WHERE no_matrik = '13DDT24F1002';
UPDATE ahli SET nric = '010101010005', password_hash = '$2y$10$RS4L0oupoIvhYSEWTk9ZUOB6kLu6jwwpmT3WhbGNg.LTIEPzU61aq' WHERE no_matrik = '13DDT22F1010';
UPDATE ahli SET nric = '010101010006', password_hash = '$2y$10$yInq3L0aXQXfORM0BYouMOEYFn7oa6k29.rhwJsUMdFl.NxHxeBT.' WHERE no_matrik = '13DDT23F2015';
UPDATE ahli SET nric = '010101010007', password_hash = '$2y$10$jzJ4Frmsas35cd.sxI7AE.t5eDrbOcFhlMxNuN.Vy7en0RzyrDZK.' WHERE no_matrik = '13DDT24F1025';
UPDATE ahli SET nric = '010101010008', password_hash = '$2y$10$CY1Bb5tb7/18te0kK.YqCe5p4c/1Capst1w5sF3SjJMfmKOsdgqQO' WHERE no_matrik = '13DDT22F1035';

UPDATE pekerja SET nric = CONCAT('82010101', LPAD(id_pekerja, 4, '0')), password_hash = '$2y$10$GtWR1uhIc3j5YTlkX6gg5.vdHW3g50mDbCdT83rzftCcKLGsOW4ci';

UPDATE admin SET nric = CONCAT('80010101', LPAD(id_admin, 4, '0')), password_hash = '$2y$10$SZkbPgcyZNG2YWOBh2zQOem1StFiroJGaPTytSAwsubG7/kEqTc8y';

-- ============================================================
-- 4.0 STORED PROCEDURES
-- ============================================================

-- ============================================================
-- 4.1 PROCEDURE: Kira Bayaran Vendor
-- ============================================================
DELIMITER //

CREATE PROCEDURE kira_bayaran_vendor(
    IN p_id_vendor INT,
    IN p_jumlah_jualan DECIMAL(10,2),
    IN p_refund DECIMAL(10,2)
)
BEGIN
    DECLARE v_komisen_peratus DECIMAL(5,2);
    DECLARE v_komisen DECIMAL(10,2);
    DECLARE v_jumlah_bayaran DECIMAL(10,2);
    DECLARE v_no_resit VARCHAR(50);
    
    -- Get vendor commission percentage
    SELECT komisen_peratus INTO v_komisen_peratus
    FROM vendor
    WHERE id_vendor = p_id_vendor;
    
    -- Calculate commission
    SET v_komisen = p_jumlah_jualan * (v_komisen_peratus / 100);
    
    -- Calculate final payment
    SET v_jumlah_bayaran = p_jumlah_jualan - v_komisen - p_refund;
    
    -- Generate receipt number
    SET v_no_resit = CONCAT('PV-', DATE_FORMAT(CURRENT_DATE, '%Y%m%d'), '-', LPAD(FLOOR(RAND() * 1000), 3, '0'));
    
    -- Insert payment record
    INSERT INTO pembayaran_vendor (
        id_vendor, 
        jumlah_jualan, 
        komisen_dipotong, 
        refund, 
        jumlah_bayaran, 
        tarikh_bayar, 
        no_resit, 
        status
    ) VALUES (
        p_id_vendor,
        p_jumlah_jualan,
        v_komisen,
        p_refund,
        v_jumlah_bayaran,
        CURRENT_DATE,
        v_no_resit,
        'Selesai'
    );
    
    -- Log the action
    INSERT INTO log_sistem (id_admin, tindakan, butiran, tarikh) 
    VALUES (1, 'Pembayaran Vendor', CONCAT('Bayaran kepada vendor ID ', p_id_vendor, ' sebanyak RM', v_jumlah_bayaran), NOW());
    
END //

DELIMITER ;

-- ============================================================
-- 4.3 PROCEDURE: Import Data Ahli
-- ============================================================
DELIMITER //

CREATE PROCEDURE import_ahli(
    IN p_no_matrik VARCHAR(20),
    IN p_nama VARCHAR(100),
    IN p_semester VARCHAR(10),
    IN p_program VARCHAR(50)
)
BEGIN
    -- Check if matric number already exists
    IF EXISTS (SELECT 1 FROM ahli WHERE no_matrik = p_no_matrik) THEN
        SELECT CONCAT('Warning: No Matrik ', p_no_matrik, ' sudah wujud. Data dilangkau.') AS status;
    ELSE
        INSERT INTO ahli (no_matrik, nama, semester, program, tarikh_daftar, status_aktif)
        VALUES (p_no_matrik, p_nama, p_semester, p_program, CURRENT_DATE, 1);
        
        SELECT CONCAT('Berjaya mengimport: ', p_nama, ' (', p_no_matrik, ')') AS status;
    END IF;
END //

DELIMITER ;

-- ============================================================
-- 5.0 VIEWS
-- ============================================================

-- ============================================================
-- 5.1 VIEW: Laporan Jualan Harian
-- ============================================================
CREATE VIEW v_laporan_jualan_harian AS
SELECT 
    DATE(tarikh_jualan) AS tarikh,
    COUNT(*) AS bil_transaksi,
    SUM(kuantiti) AS unit_terjual,
    SUM(jumlah) AS jumlah_jualan
FROM jualan
GROUP BY DATE(tarikh_jualan)
ORDER BY DATE(tarikh_jualan) DESC;

-- ============================================================
-- 5.2 VIEW: Laporan Stok Semasa
-- ============================================================
CREATE VIEW v_laporan_stok_semasa AS
SELECT 
    nama_item,
    kategori,
    kuantiti_semasa,
    kuantiti_minimum,
    CASE 
        WHEN kuantiti_semasa <= kuantiti_minimum THEN 'PERLU TEMPAH'
        ELSE 'MENCUKUPI'
    END AS status_stok
FROM stok
ORDER BY kuantiti_semasa ASC;

-- ============================================================
-- 5.3 VIEW: Laporan Saham Ahli
-- ============================================================
CREATE VIEW v_laporan_saham_ahli AS
SELECT 
    a.nama,
    a.no_matrik,
    a.semester,
    s.syer,
    s.yuran,
    (s.syer + s.yuran) AS jumlah_pelaburan
FROM ahli a
JOIN saham s ON a.id_ahli = s.id_ahli
WHERE a.status_aktif = 1
ORDER BY (s.syer + s.yuran) DESC;

-- ============================================================
-- 5.4 VIEW: Laporan Pembayaran Vendor
-- ============================================================
CREATE VIEW v_laporan_pembayaran_vendor AS
SELECT 
    v.nama_vendor,
    v.no_akaun,
    v.bank,
    pv.jumlah_jualan,
    pv.komisen_dipotong,
    pv.refund,
    pv.jumlah_bayaran,
    pv.tarikh_bayar,
    pv.no_resit,
    pv.status
FROM pembayaran_vendor pv
JOIN vendor v ON pv.id_vendor = v.id_vendor
ORDER BY pv.tarikh_bayar DESC;

-- ============================================================
-- 5.5 VIEW: Top 5 Pemegang Saham
-- ============================================================
CREATE VIEW v_top5_pemegang_saham AS
SELECT 
    a.nama,
    a.no_matrik,
    (s.syer + s.yuran) AS jumlah_pelaburan
FROM ahli a
JOIN saham s ON a.id_ahli = s.id_ahli
WHERE a.status_aktif = 1
ORDER BY (s.syer + s.yuran) DESC
LIMIT 5;

-- ============================================================
-- 5.6 VIEW: Status Tempahan
-- ============================================================
CREATE VIEW v_status_tempahan AS
SELECT 
    t.id_tempahan,
    a.nama AS nama_ahli,
    a.no_matrik,
    t.tarikh_tempahan,
    t.status,
    t.jumlah_total,
    t.kaedah_bayaran,
    t.tarikh_siap,
    t.tarikh_ambil,
    DATEDIFF(CURRENT_DATE, t.tarikh_tempahan) AS hari_menunggu
FROM tempahan t
JOIN ahli a ON t.id_ahli = a.id_ahli
ORDER BY t.tarikh_tempahan DESC;

-- ============================================================
-- 6.0 TRIGGERS
-- ============================================================

-- ============================================================
-- 6.1 TRIGGER: Auto Update Stok After Sales
-- ============================================================
DELIMITER //

CREATE TRIGGER trg_update_stok_sales
AFTER INSERT ON jualan
FOR EACH ROW
BEGIN
    UPDATE stok 
    SET kuantiti_semasa = kuantiti_semasa - NEW.kuantiti,
        tarikh_kemaskini = CURRENT_DATE
    WHERE id_stok = NEW.id_stok;
END //

DELIMITER ;

-- ============================================================
-- 6.2 TRIGGER: Auto Update Total Tempahan
-- ============================================================
DELIMITER //

CREATE TRIGGER trg_update_tempahan_total
AFTER INSERT ON item_tempahan
FOR EACH ROW
BEGIN
    UPDATE tempahan 
    SET jumlah_total = (
        SELECT SUM(subtotal) 
        FROM item_tempahan 
        WHERE id_tempahan = NEW.id_tempahan
    )
    WHERE id_tempahan = NEW.id_tempahan;
END //

DELIMITER ;

-- ============================================================
-- 6.3 TRIGGER: Auto Create Saham for New Ahli
-- ============================================================
DELIMITER //

CREATE TRIGGER trg_create_saham_new_ahli
AFTER INSERT ON ahli
FOR EACH ROW
BEGIN
    INSERT INTO saham (id_ahli, syer, yuran, tarikh_kemaskini)
    VALUES (NEW.id_ahli, 0.00, 0.00, CURRENT_DATE);
END //

DELIMITER ;

-- ============================================================
-- 7.0 INDEXES (Additional Performance Indexes)
-- ============================================================

CREATE INDEX idx_jualan_tarikh ON jualan(tarikh_jualan);
CREATE INDEX idx_tempahan_status ON tempahan(status);
CREATE INDEX idx_pembayaran_vendor_tarikh ON pembayaran_vendor(tarikh_bayar);
CREATE INDEX idx_serahan_tarikh ON serahan(tarikh_serahan);

-- ============================================================
-- 8.0 GRANT PERMISSIONS (Optional - Adjust as needed)
-- ============================================================

-- Create user if not exists (for MySQL)
-- CREATE USER IF NOT EXISTS 'koperasi_user'@'localhost' IDENTIFIED BY 'secure_password';

-- Grant privileges
-- GRANT SELECT, INSERT, UPDATE, DELETE ON koperasi_db.* TO 'koperasi_user'@'localhost';
-- FLUSH PRIVILEGES;

-- ============================================================
-- 9.0 SAMPLE QUERIES FOR TESTING
-- ============================================================

-- 9.1 Check all top shareholders
-- SELECT * FROM v_top5_pemegang_saham;

-- 9.2 Check daily sales report
-- SELECT * FROM v_laporan_jualan_harian;

-- 9.3 Check vendor payments
-- SELECT * FROM v_laporan_pembayaran_vendor;

-- 9.4 Check stock status
-- SELECT * FROM v_laporan_stok_semasa;

-- 9.5 Process vendor payment
-- CALL kira_bayaran_vendor(5, 1127.00, 0.00);

-- 9.6 Import new student
-- CALL import_ahli('13DDT25F3001', 'Ahmad Bin Ismail', 'Sem 1', 'JTMK');

-- ============================================================
-- Re-enable foreign key checks
-- ============================================================
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- END OF SCRIPT
-- ============================================================
