-- ==============================================================
-- DATABASE MARKETPLACE TRADISIONAL "NANDA HANDPHONE"
-- Tugas Soal 12 Pengembangan Web Service / REST API Marketplace
-- Dosen Pengampu: Pak Hary
-- Basis Data: MySQL / MariaDB (XAMPP Localhost & InfinityFree)
-- ==============================================================

-- Buat database jika di localhost
CREATE DATABASE IF NOT EXISTS `uts_nanda_hp` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `uts_nanda_hp`;

-- Nonaktifkan foreign key checks sementara saat reset / migrasi
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- 1. TABEL: PENGGUNA (Users & Pelanggan)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `detail_pesanan`;
DROP TABLE IF EXISTS `pesanan`;
DROP TABLE IF EXISTS `produk`;
DROP TABLE IF EXISTS `kategori`;
DROP TABLE IF EXISTS `pengguna`;

CREATE TABLE `pengguna` (
  `id_pengguna` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `no_hp` VARCHAR(20) NOT NULL,
  `alamat` TEXT NULL,
  `role` ENUM('admin', 'penjual', 'pembeli') NOT NULL DEFAULT 'pembeli',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pengguna`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. TABEL: KATEGORI (Kategori Produk Handphone & Gadget)
-- --------------------------------------------------------
CREATE TABLE `kategori` (
  `id_kategori` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` VARCHAR(50) NOT NULL,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `deskripsi` TEXT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 3. TABEL: PRODUK (Katalog Handphone & Aksesoris)
-- --------------------------------------------------------
CREATE TABLE `produk` (
  `id_produk` INT(11) NOT NULL AUTO_INCREMENT,
  `id_kategori` INT(11) NOT NULL,
  `id_penjual` INT(11) NULL,
  `nama_produk` VARCHAR(150) NOT NULL,
  `merk` VARCHAR(50) NOT NULL,
  `harga` DECIMAL(12,2) NOT NULL,
  `stok` INT(11) NOT NULL DEFAULT 0,
  `kondisi` ENUM('Baru', 'Bekas') NOT NULL DEFAULT 'Baru',
  `garansi` VARCHAR(50) DEFAULT '1 Tahun Garansi Resmi',
  `deskripsi` TEXT NOT NULL,
  `gambar` VARCHAR(255) NOT NULL DEFAULT 'default-phone.png',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_produk`),
  KEY `fk_produk_kategori` (`id_kategori`),
  KEY `fk_produk_penjual` (`id_penjual`),
  CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_produk_penjual` FOREIGN KEY (`id_penjual`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 4. TABEL: PESANAN (Header Transaksi Pemesanan)
-- --------------------------------------------------------
CREATE TABLE `pesanan` (
  `id_pesanan` INT(11) NOT NULL AUTO_INCREMENT,
  `kode_pesanan` VARCHAR(50) NOT NULL UNIQUE,
  `id_pembeli` INT(11) NULL,
  `nama_pembeli` VARCHAR(100) NOT NULL,
  `no_hp` VARCHAR(20) NOT NULL,
  `alamat_pengiriman` TEXT NOT NULL,
  `metode_pembayaran` VARCHAR(50) NOT NULL,
  `total_pembayaran` DECIMAL(12,2) NOT NULL,
  `status_pesanan` ENUM('Menunggu Konfirmasi', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan') NOT NULL DEFAULT 'Menunggu Konfirmasi',
  `catatan` TEXT NULL,
  `tanggal_pesanan` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pesanan`),
  KEY `fk_pesanan_pembeli` (`id_pembeli`),
  CONSTRAINT `fk_pesanan_pembeli` FOREIGN KEY (`id_pembeli`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 5. TABEL: DETAIL_PESANAN (Rincian Produk Tiap Transaksi)
-- --------------------------------------------------------
CREATE TABLE `detail_pesanan` (
  `id_detail` INT(11) NOT NULL AUTO_INCREMENT,
  `id_pesanan` INT(11) NOT NULL,
  `id_produk` INT(11) NOT NULL,
  `jumlah` INT(11) NOT NULL,
  `harga_satuan` DECIMAL(12,2) NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `fk_detail_pesanan` (`id_pesanan`),
  KEY `fk_detail_produk` (`id_produk`),
  CONSTRAINT `fk_detail_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detail_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================
-- DUMP DATA SEED AWAL (Master Data & Sampel Transaksi)
-- ==============================================================

-- 1. Data Pengguna (Password default: 'admin123' dengan hash bcrypt resmi)
INSERT INTO `pengguna` (`id_pengguna`, `nama_lengkap`, `email`, `password`, `no_hp`, `alamat`, `role`) VALUES
(1, 'Nanda Owner Handphone', 'admin@nandahp.com', '$2y$10$Ju2G5mcTo/MD0lZ9orth2./ucsUqzEl/8OzylwCaLaoc2zMYToOcq', '081234567890', 'Jl. Pemuda No. 45, Semarang Tengah', 'admin'),
(2, 'Penjual Mitra Handphone', 'penjual@nandahp.com', '$2y$10$Ju2G5mcTo/MD0lZ9orth2./ucsUqzEl/8OzylwCaLaoc2zMYToOcq', '085712345678', 'Jl. Pandanaran No. 12, Semarang', 'penjual'),
(3, 'Budi Santoso', 'budi@gmail.com', '$2y$10$Ju2G5mcTo/MD0lZ9orth2./ucsUqzEl/8OzylwCaLaoc2zMYToOcq', '081987654321', 'Jl. Gajah Mada No. 88, Semarang', 'pembeli');

-- 2. Data Kategori
INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `slug`, `deskripsi`) VALUES
(1, 'Smartphone Android', 'android', 'Koleksi smartphone sistem operasi Android dari brand ternama seperti Samsung, Xiaomi, Vivo, Oppo, Infinix'),
(2, 'Apple iPhone (iOS)', 'iphone', 'iPhone resmi iBox & internasional garansi terjamin dengan performa iOS prima'),
(3, 'Handphone Second Bergaransi', 'second', 'Handphone bekas berkualitas tinggi, fungsi 100% normal dan bergaransi toko 1 bulan'),
(4, 'Aksesoris & Gadget', 'aksesoris', 'Charger original, kabel data type-C, TWS earphone, tempered glass, dan casing pelindung');

-- 3. Data Produk Handphone & Aksesoris
INSERT INTO `produk` (`id_produk`, `id_kategori`, `id_penjual`, `nama_produk`, `merk`, `harga`, `stok`, `kondisi`, `garansi`, `deskripsi`, `gambar`) VALUES
(1, 1, 1, 'Samsung Galaxy A55 5G (8GB/256GB)', 'Samsung', 5799000.00, 15, 'Baru', '1 Tahun Resmi SEIN', 'Samsung Galaxy A55 5G hadir dengan layar Super AMOLED 120Hz 6.6 inci, prosesor Exynos 1480 kencang, kamera 50MP OIS jernih, baterai 5000 mAh dengan 25W fast charging, bodi kaca premium dengan sertifikasi tahan air IP67.', 'prod_1794155540_6af0a4140f895.jpg'),
(2, 2, 1, 'Apple iPhone 13 (128GB) Midnight', 'Apple', 9999000.00, 8, 'Baru', '1 Tahun Garansi Resmi iBox', 'Apple iPhone 13 dengan layar Super Retina XDR OLED 6.1 inci, Chipset A15 Bionic super kencang dan efisien, sistem kamera ganda 12MP dengan Cinematic Mode, baterai lebih awet hingga 19 jam video playback, jaringan 5G kencang.', 'prod_1794155444_6af0a3b4980df.jpg'),
(3, 1, 1, 'Xiaomi Redmi Note 13 Pro 4G (8GB/256GB)', 'Xiaomi', 3499000.00, 20, 'Baru', '15 Bulan Resmi Xiaomi', 'Redmi Note 13 Pro dibekali kamera flagship 200MP OIS, layar 120Hz AMOLED super tajam, prosesor MediaTek Helio G99 Ultra, turbo charging 67W dengan baterai 5000mAh. Pilihan terbaik di kelas menengah!', 'prod_1794155391_6af0a37f5e7ea.jpg'),
(4, 1, 2, 'Infinix Note 40 (8GB+8GB/256GB)', 'Infinix', 2699000.00, 12, 'Baru', '1 Tahun Resmi Infinix', 'Infinix Note 40 menawarkan teknologi Wireless MagCharge 20W + 45W All-Round FastCharge 2.0, layar AMOLED 120Hz bezelless, kamera 108MP Super Zoom, dual speaker by JBL, prosesor Helio G99 Ultimate.', 'prod_1794155283_6af0a3137f2a8.jpg'),
(5, 3, 2, 'iPhone 11 (64GB) Black - Second Like New', 'Apple', 4850000.00, 5, 'Bekas', '1 Bulan Garansi Toko Nanda HP', 'Kondisi fisik 97% mulus like new, Battery Health (BH) 87% masih original awet. Face ID aktif, True Tone nyala normal, 3uTools hijau semua, all provider bebas blokir IMEI seumur hidup.', 'prod_1794155235_6af0a2e35b9ca.jpg'),
(6, 1, 1, 'Vivo V30 5G (12GB/512GB) Green', 'Vivo', 5999000.00, 7, 'Baru', '1 Tahun Resmi Vivo', 'Vivo V30 5G dilengkapi Aura Light Portrait 3.0 tercanggih, bodi tipis 3D curved screen 1.5K AMOLED, chipset Snapdragon 7 Gen 3, baterai 5000 mAh dengan 80W FlashCharge, RAM 12GB + 12GB Extended RAM.', 'prod_1794155171_6af0a2a30b0c9.jpg'),
(7, 4, 1, 'Adaptor Fast Charger 33W Original Type-C', 'Baseus', 145000.00, 35, 'Baru', '6 Bulan Garansi Toko', 'Kepala charger fast charging 33W GaN support Power Delivery 3.0 dan Quick Charge 4.0. Kompatibel untuk semua Android dan iPhone, proteksi suhu pintar tidak bikin panas perangkat.', 'prod_1794155033_6af0a21936d74.jpg'),
(8, 4, 2, 'TWS Earphone Bluetooth 5.3 Stereo Bass', 'Anker', 279000.00, 25, 'Baru', '12 Bulan Resmi Anker', 'Earphone TWS Bluetooth 5.3 dengan latensi rendah, suara bass bertenaga khas Anker Soundcore, mikrofon ganda jernih untuk panggilan telepon, baterai tahan hingga 30 jam dengan case pengisian.', 'prod_1794155115_6af0a26b10ed5.webp'),
(9, 4, 1, 'USB Flashdisk 32GB', 'JETE', 195000.00, 50, 'Baru', '1 Tahun Resmi', 'Flashdisk USB 3.0 transfer cepat untuk backup data dan media.', 'prod_1794156381_6af0a75d18f0c.jpg'),
(10, 1, 1, 'vivo v70 FE (8GB/256GB)', 'VIVO', 6499000.00, 10, 'Baru', '1 Tahun Resmi Vivo', 'AMOLED 6.83 inci 120Hz, MediaTek Dimensity 7360-Turbo, Kamera Utama 200MP OIS, Baterai 7000 mAh 90W FlashCharge.', 'prod_1794412589_6af4902db8ede.jpg');

-- 4. Data Sampel Pesanan Awal (Transaksi)
INSERT INTO `pesanan` (`id_pesanan`, `kode_pesanan`, `id_pembeli`, `nama_pembeli`, `no_hp`, `alamat_pengiriman`, `metode_pembayaran`, `total_pembayaran`, `status_pesanan`, `catatan`, `tanggal_pesanan`) VALUES
(1, 'NHP-20261101-001', 3, 'Budi Santoso', '081987654321', 'Jl. Gajah Mada No. 88, Semarang Tengah, Kota Semarang 50134', 'Transfer Bank BCA', 5944000.00, 'Selesai', 'Mohon dipacking bubble wrap tebal warna hitam ya mas Nanda', '2026-11-01 10:30:00'),
(2, 'NHP-20261105-002', NULL, 'Siti Nurhaliza', '085233445566', 'Kecamatan Banyumanik, RT 03/RW 02, Kota Semarang', 'COD (Bayar di Tempat)', 3499000.00, 'Diproses', 'Antar di jam istirahat kantor sekitar pukul 12.00 - 13.00 siang', '2026-11-05 14:15:20');

-- 5. Data Sampel Detail Pesanan
INSERT INTO `detail_pesanan` (`id_detail`, `id_pesanan`, `id_produk`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
(1, 1, 1, 1, 5799000.00, 5799000.00),
(2, 1, 7, 1, 145000.00, 145000.00),
(3, 2, 3, 1, 3499000.00, 3499000.00);
