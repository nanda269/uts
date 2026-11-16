<?php
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('keranjang.php'));
    exit;
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['flash_error'] = 'Keranjang belanja kosong. Silakan pilih produk terlebih dahulu.';
    header('Location: ' . base_url('index.php'));
    exit;
}

if (!$db_connected) {
    $_SESSION['flash_error'] = 'Gagal memproses pesanan: Database MySQL belum terhubung.';
    header('Location: ' . base_url('checkout.php'));
    exit;
}

// Ambil & Sanitasi Data Form
$nama_pembeli       = bersihkan($koneksi, $_POST['nama_pembeli'] ?? '');
$no_hp              = bersihkan($koneksi, $_POST['no_hp'] ?? '');
$alamat_pengiriman  = bersihkan($koneksi, $_POST['alamat_pengiriman'] ?? '');
$metode_pembayaran  = bersihkan($koneksi, $_POST['metode_pembayaran'] ?? 'Transfer Bank BCA');
$catatan            = bersihkan($koneksi, $_POST['catatan'] ?? '');

// Validasi Form Wajib
if (empty($nama_pembeli) || empty($no_hp) || empty($alamat_pengiriman)) {
    $_SESSION['flash_error'] = 'Mohon lengkapi seluruh kolom yang wajib diisi (*).';
    header('Location: ' . base_url('checkout.php'));
    exit;
}

// 1. Validasi Stok Terkini dari Database
$total_bayar = 0;
$items_to_save = [];

foreach ($_SESSION['cart'] as $id_produk => $item) {
    $id_prod = (int)$id_produk;
    $qty_order = (int)$item['qty'];

    $stmt_check = mysqli_prepare($koneksi, "SELECT id_produk, nama_produk, harga, stok FROM produk WHERE id_produk = ? FOR UPDATE");
    mysqli_stmt_bind_param($stmt_check, "i", $id_prod);
    mysqli_stmt_execute($stmt_check);
    $res_check = mysqli_stmt_get_result($stmt_check);
    $prod_db = mysqli_fetch_assoc($res_check);

    if (!$prod_db) {
        $_SESSION['flash_error'] = 'Salah satu produk di keranjang tidak lagi tersedia.';
        header('Location: ' . base_url('keranjang.php'));
        exit;
    }

    if ($prod_db['stok'] < $qty_order) {
        $_SESSION['flash_error'] = 'Maaf, stok produk <strong>' . htmlspecialchars($prod_db['nama_produk']) . '</strong> tidak mencukupi (sisa: ' . $prod_db['stok'] . ' unit).';
        header('Location: ' . base_url('keranjang.php'));
        exit;
    }

    $subtotal = (float)$prod_db['harga'] * $qty_order;
    $total_bayar += $subtotal;

    $items_to_save[] = [
        'id_produk'    => $prod_db['id_produk'],
        'nama_produk'  => $prod_db['nama_produk'],
        'harga_satuan' => (float)$prod_db['harga'],
        'jumlah'       => $qty_order,
        'subtotal'     => $subtotal
    ];
}

// 2. Buat Nomor Invoice Unik: NHP-YYYYMMDD-XXXX
$kode_pesanan = 'NHP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
$id_pembeli = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

// 3. Mulai Database Transaction (ACID)
mysqli_begin_transaction($koneksi);

try {
    // A. Simpan ke Tabel `pesanan`
    $sql_pesanan = "INSERT INTO `pesanan` 
                    (`kode_pesanan`, `id_pembeli`, `nama_pembeli`, `no_hp`, `alamat_pengiriman`, `metode_pembayaran`, `total_pembayaran`, `status_pesanan`, `catatan`, `tanggal_pesanan`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'Menunggu Konfirmasi', ?, NOW())";
    
    $stmt_pesanan = mysqli_prepare($koneksi, $sql_pesanan);
    mysqli_stmt_bind_param($stmt_pesanan, "sissssds", $kode_pesanan, $id_pembeli, $nama_pembeli, $no_hp, $alamat_pengiriman, $metode_pembayaran, $total_bayar, $catatan);
    $exec_pesanan = mysqli_stmt_execute($stmt_pesanan);

    if (!$exec_pesanan) {
        throw new Exception("Gagal menyimpan data pesanan induk: " . mysqli_error($koneksi));
    }

    $id_pesanan_baru = mysqli_insert_id($koneksi);

    // B. Simpan ke Tabel `detail_pesanan` & Potong Stok di Tabel `produk`
    $sql_detail = "INSERT INTO `detail_pesanan` (`id_pesanan`, `id_produk`, `jumlah`, `harga_satuan`, `subtotal`) VALUES (?, ?, ?, ?, ?)";
    $stmt_detail = mysqli_prepare($koneksi, $sql_detail);

    $sql_update_stok = "UPDATE `produk` SET `stok` = `stok` - ? WHERE `id_produk` = ?";
    $stmt_stok = mysqli_prepare($koneksi, $sql_update_stok);

    foreach ($items_to_save as $item) {
        // Simpan Detail
        mysqli_stmt_bind_param($stmt_detail, "iiidd", $id_pesanan_baru, $item['id_produk'], $item['jumlah'], $item['harga_satuan'], $item['subtotal']);
        $exec_det = mysqli_stmt_execute($stmt_detail);
        if (!$exec_det) {
            throw new Exception("Gagal menyimpan rincian pesanan: " . mysqli_error($koneksi));
        }

        // Potong Stok
        mysqli_stmt_bind_param($stmt_stok, "ii", $item['jumlah'], $item['id_produk']);
        $exec_stok = mysqli_stmt_execute($stmt_stok);
        if (!$exec_stok) {
            throw new Exception("Gagal memperbarui stok produk: " . mysqli_error($koneksi));
        }
    }

    // Commit Transaksi
    mysqli_commit($koneksi);

    // Kosongkan Keranjang Belanja
    $_SESSION['cart'] = [];

    // Redirect ke Halaman Sukses
    header('Location: ' . base_url('transaksi-sukses.php?id=' . $id_pesanan_baru));
    exit;

} catch (Exception $e) {
    mysqli_rollback($koneksi);
    $_SESSION['flash_error'] = 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage();
    header('Location: ' . base_url('checkout.php'));
    exit;
}
