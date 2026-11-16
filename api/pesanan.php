<?php
/**
 * REST API Endpoint: Transaksi Pemesanan Produk (Checkout API)
 * 
 * Endpoints:
 * - POST /api/pesanan.php                -> Membuat pesanan baru (Payload JSON / Form-Data)
 * - GET  /api/pesanan.php?id=1           -> Melihat rincian pesanan berdasarkan ID
 * - GET  /api/pesanan.php?kode=NHP-...   -> Melihat rincian pesanan berdasarkan Kode Pesanan
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/api_helper.php';

// Pastikan koneksi database aktif
check_db_connection($db_connected, $db_error ?? '');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// =========================================================================
// HANDLER: GET /api/pesanan.php?id={id} atau ?kode={kode} (Pelacakan Pesanan)
// =========================================================================
if ($method === 'GET') {
    if ((!isset($_GET['id']) || trim($_GET['id']) === '') && (!isset($_GET['kode']) || trim($_GET['kode']) === '')) {
        api_response(
            405,
            'error',
            'Endpoint ini dikhususkan untuk pembuatan pesanan menggunakan metode POST. Untuk melihat detail pesanan tertentu, sertakan parameter ?id=... atau ?kode=...',
            null,
            ['allowed_methods' => ['POST'], 'example_get' => '/api/pesanan.php?kode=NHP-20261101-001']
        );
    }

    if (isset($_GET['id']) && trim($_GET['id']) !== '') {
        $id_raw = trim($_GET['id']);
        if (!ctype_digit($id_raw) || (int)$id_raw <= 0) {
            api_response(400, 'error', 'Parameter ID pesanan harus berupa bilangan bulat positif.');
        }
        $where = "p.id_pesanan = ?";
        $param = (int)$id_raw;
        $type  = "i";
    } else {
        $where = "p.kode_pesanan = ?";
        $param = trim($_GET['kode']);
        $type  = "s";
    }

    $sql_pesanan = "SELECT p.*, u.nama_lengkap AS akun_terdaftar
                    FROM pesanan p
                    LEFT JOIN pengguna u ON p.id_pembeli = u.id_pengguna
                    WHERE {$where}
                    LIMIT 1";

    $stmt = mysqli_prepare($koneksi, $sql_pesanan);
    if (!$stmt) {
        api_response(500, 'error', 'Query database error: ' . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt, $type, $param);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $pesanan = mysqli_fetch_assoc($res);

    if (!$pesanan) {
        api_response(404, 'error', 'Data pesanan tidak ditemukan.');
    }

    // Ambil Rincian Item Produk dari detail_pesanan
    $id_pesanan = (int)$pesanan['id_pesanan'];
    $sql_detail = "SELECT dp.*, pr.nama_produk, pr.merk, pr.gambar
                   FROM detail_pesanan dp
                   JOIN produk pr ON dp.id_produk = pr.id_produk
                   WHERE dp.id_pesanan = ?
                   ORDER BY dp.id_detail ASC";

    $stmt_detail = mysqli_prepare($koneksi, $sql_detail);
    mysqli_stmt_bind_param($stmt_detail, "i", $id_pesanan);
    mysqli_stmt_execute($stmt_detail);
    $res_detail = mysqli_stmt_get_result($stmt_detail);

    $items = [];
    while ($item = mysqli_fetch_assoc($res_detail)) {
        $items[] = [
            'id_detail'           => (int)$item['id_detail'],
            'id_produk'           => (int)$item['id_produk'],
            'nama_produk'         => $item['nama_produk'],
            'merk'                => $item['merk'],
            'harga_satuan'        => (float)$item['harga_satuan'],
            'harga_satuan_format' => rupiah($item['harga_satuan']),
            'jumlah'              => (int)$item['jumlah'],
            'subtotal'            => (float)$item['subtotal'],
            'subtotal_format'     => rupiah($item['subtotal']),
            'url_gambar'          => base_url('assets/uploads/' . $item['gambar'])
        ];
    }

    $data_pesanan = [
        'id_pesanan'              => (int)$pesanan['id_pesanan'],
        'kode_pesanan'            => $pesanan['kode_pesanan'],
        'tanggal_pesanan'         => $pesanan['tanggal_pesanan'],
        'nama_pembeli'            => $pesanan['nama_pembeli'],
        'no_hp'                   => $pesanan['no_hp'],
        'alamat_pengiriman'       => $pesanan['alamat_pengiriman'],
        'metode_pembayaran'       => $pesanan['metode_pembayaran'],
        'status_pesanan'          => $pesanan['status_pesanan'],
        'catatan'                 => $pesanan['catatan'] ?? '',
        'total_pembayaran'        => (float)$pesanan['total_pembayaran'],
        'total_pembayaran_format' => rupiah($pesanan['total_pembayaran']),
        'total_item'              => count($items),
        'items'                   => $items
    ];

    api_response(200, 'success', 'Detail pesanan berhasil diambil.', $data_pesanan);
}

// =========================================================================
// HANDLER: POST /api/pesanan.php -> Membuat Pesanan Baru
// =========================================================================
require_method('POST');

// Ambil data payload request (JSON Body atau $_POST)
$input = get_request_data();

if ($input === null) {
    api_response(400, 'error', 'Format payload tidak valid. Mohon kirimkan format JSON yang valid (contoh header: Content-Type: application/json).');
}

if (empty($input)) {
    api_response(400, 'error', 'Data pesanan kosong. Mohon sertakan field nama_pembeli, no_hp, alamat_pengiriman, dan items.');
}

// Ekstraksi data
$nama_pembeli      = trim($input['nama_pembeli'] ?? '');
$no_hp             = trim($input['no_hp'] ?? '');
$alamat_pengiriman = trim($input['alamat_pengiriman'] ?? '');
$metode_pembayaran = trim($input['metode_pembayaran'] ?? 'Transfer Bank BCA');
$catatan           = trim($input['catatan'] ?? '');
$id_pembeli        = null;

if (!empty($input['id_pembeli']) && is_numeric($input['id_pembeli'])) {
    $id_pembeli = (int)$input['id_pembeli'];
} elseif (isset($_SESSION['user_id'])) {
    $id_pembeli = (int)$_SESSION['user_id'];
}

// Ekstraksi Items (fleksibel: menerima 'items', 'produk', atau 'detail')
$raw_items = $input['items'] ?? $input['produk'] ?? $input['detail'] ?? null;

// Jika user mengirim single item di root payload: {"id_produk": 1, "jumlah": 2}
if ($raw_items === null && isset($input['id_produk'])) {
    $raw_items = [[
        'id_produk' => $input['id_produk'],
        'jumlah'    => $input['jumlah'] ?? $input['qty'] ?? 1
    ]];
}

// 1. Validasi Kolom Wajib
$errors = [];
if (empty($nama_pembeli)) {
    $errors['nama_pembeli'] = 'Nama pembeli wajib diisi.';
}
if (empty($no_hp)) {
    $errors['no_hp'] = 'Nomor HP/WhatsApp pembeli wajib diisi.';
}
if (empty($alamat_pengiriman)) {
    $errors['alamat_pengiriman'] = 'Alamat pengiriman lengkap wajib diisi.';
}
if (empty($raw_items) || !is_array($raw_items)) {
    $errors['items'] = 'Daftar item produk pesanan wajib diisi (minimal 1 item) dalam bentuk array.';
}

if (!empty($errors)) {
    api_response(400, 'error', 'Validasi form pesanan gagal. Mohon lengkapi field yang diperlukan.', null, ['errors' => $errors]);
}

// 2. Validasi & Kalkulasi Item dari Database
$total_bayar     = 0;
$items_to_save   = [];

foreach ($raw_items as $idx => $item) {
    $id_produk = $item['id_produk'] ?? $item['produk_id'] ?? $item['id'] ?? null;
    $jumlah    = $item['jumlah'] ?? $item['qty'] ?? $item['quantity'] ?? null;

    if (!is_numeric($id_produk) || (int)$id_produk <= 0) {
        api_response(400, 'error', "ID produk pada item index #{$idx} tidak valid.");
    }
    if (!is_numeric($jumlah) || (int)$jumlah <= 0) {
        api_response(400, 'error', "Jumlah (qty) untuk produk ID {$id_produk} harus minimal 1 unit.");
    }

    $id_produk = (int)$id_produk;
    $jumlah    = (int)$jumlah;

    // Cek ketersediaan dan stok produk di database
    $stmt_check = mysqli_prepare($koneksi, "SELECT id_produk, nama_produk, merk, harga, stok FROM produk WHERE id_produk = ?");
    if (!$stmt_check) {
        api_response(500, 'error', 'Query database error: ' . mysqli_error($koneksi));
    }
    mysqli_stmt_bind_param($stmt_check, "i", $id_produk);
    mysqli_stmt_execute($stmt_check);
    $res_check = mysqli_stmt_get_result($stmt_check);
    $prod_db = mysqli_fetch_assoc($res_check);

    if (!$prod_db) {
        api_response(404, 'error', "Produk dengan ID {$id_produk} tidak ditemukan di database katalog.");
    }

    if ((int)$prod_db['stok'] < $jumlah) {
        api_response(
            422,
            'error',
            "Stok produk '{$prod_db['nama_produk']}' tidak mencukupi (tersedia: {$prod_db['stok']} unit, diminta: {$jumlah} unit).",
            null,
            [
                'id_produk'       => $id_produk,
                'nama_produk'     => $prod_db['nama_produk'],
                'stok_tersedia'   => (int)$prod_db['stok'],
                'jumlah_diminta'  => $jumlah
            ]
        );
    }

    $subtotal = (float)$prod_db['harga'] * $jumlah;
    $total_bayar += $subtotal;

    $items_to_save[] = [
        'id_produk'           => $prod_db['id_produk'],
        'nama_produk'         => $prod_db['nama_produk'],
        'merk'                => $prod_db['merk'],
        'harga_satuan'        => (float)$prod_db['harga'],
        'harga_satuan_format' => rupiah($prod_db['harga']),
        'jumlah'              => $jumlah,
        'subtotal'            => $subtotal,
        'subtotal_format'     => rupiah($subtotal),
        'sisa_stok'           => (int)$prod_db['stok'] - $jumlah
    ];
}

// 3. Eksekusi Database Transaction (ACID)
mysqli_begin_transaction($koneksi);

try {
    // Generate Nomor Invoice Unik: NHP-YYYYMMDD-XXXX
    $kode_pesanan = 'NHP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

    // A. Simpan ke tabel pesanan
    $sql_pesanan = "INSERT INTO `pesanan` 
                    (`kode_pesanan`, `id_pembeli`, `nama_pembeli`, `no_hp`, `alamat_pengiriman`, `metode_pembayaran`, `total_pembayaran`, `status_pesanan`, `catatan`, `tanggal_pesanan`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'Menunggu Konfirmasi', ?, NOW())";

    $stmt_pesanan = mysqli_prepare($koneksi, $sql_pesanan);
    if (!$stmt_pesanan) {
        throw new Exception("Gagal prepare statement pesanan: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param(
        $stmt_pesanan,
        "sissssds",
        $kode_pesanan,
        $id_pembeli,
        $nama_pembeli,
        $no_hp,
        $alamat_pengiriman,
        $metode_pembayaran,
        $total_bayar,
        $catatan
    );

    if (!mysqli_stmt_execute($stmt_pesanan)) {
        throw new Exception("Gagal menyimpan data pesanan induk: " . mysqli_stmt_error($stmt_pesanan));
    }

    $id_pesanan_baru = mysqli_insert_id($koneksi);

    // B. Simpan ke tabel detail_pesanan & Potong Stok Produk
    $sql_detail = "INSERT INTO `detail_pesanan` (`id_pesanan`, `id_produk`, `jumlah`, `harga_satuan`, `subtotal`) VALUES (?, ?, ?, ?, ?)";
    $stmt_detail = mysqli_prepare($koneksi, $sql_detail);
    if (!$stmt_detail) {
        throw new Exception("Gagal prepare statement detail pesanan: " . mysqli_error($koneksi));
    }

    $sql_update_stok = "UPDATE `produk` SET `stok` = `stok` - ? WHERE `id_produk` = ?";
    $stmt_stok = mysqli_prepare($koneksi, $sql_update_stok);
    if (!$stmt_stok) {
        throw new Exception("Gagal prepare statement update stok: " . mysqli_error($koneksi));
    }

    foreach ($items_to_save as $item) {
        // Simpan Detail
        mysqli_stmt_bind_param(
            $stmt_detail,
            "iiidd",
            $id_pesanan_baru,
            $item['id_produk'],
            $item['jumlah'],
            $item['harga_satuan'],
            $item['subtotal']
        );
        if (!mysqli_stmt_execute($stmt_detail)) {
            throw new Exception("Gagal menyimpan detail pesanan produk {$item['nama_produk']}: " . mysqli_stmt_error($stmt_detail));
        }

        // Potong Stok
        mysqli_stmt_bind_param($stmt_stok, "ii", $item['jumlah'], $item['id_produk']);
        if (!mysqli_stmt_execute($stmt_stok)) {
            throw new Exception("Gagal memotong stok produk {$item['nama_produk']}: " . mysqli_stmt_error($stmt_stok));
        }
    }

    // Commit transaksi jika seluruh tahapan sukses
    mysqli_commit($koneksi);

    // Ambil tanggal pesanan aktual
    $res_tgl = mysqli_query($koneksi, "SELECT tanggal_pesanan FROM `pesanan` WHERE id_pesanan = {$id_pesanan_baru}");
    $row_tgl = mysqli_fetch_assoc($res_tgl);
    $tanggal_pesanan = $row_tgl['tanggal_pesanan'] ?? date('Y-m-d H:i:s');

    // Susun response data
    $response_data = [
        'id_pesanan'              => $id_pesanan_baru,
        'kode_pesanan'            => $kode_pesanan,
        'tanggal_pesanan'         => $tanggal_pesanan,
        'nama_pembeli'            => $nama_pembeli,
        'no_hp'                   => $no_hp,
        'alamat_pengiriman'       => $alamat_pengiriman,
        'metode_pembayaran'       => $metode_pembayaran,
        'catatan'                 => $catatan,
        'status_pesanan'          => 'Menunggu Konfirmasi',
        'total_pembayaran'        => (float)$total_bayar,
        'total_pembayaran_format' => rupiah($total_bayar),
        'items'                   => $items_to_save
    ];

    api_response(201, 'success', 'Pesanan baru berhasil dibuat dan stok produk telah diperbarui.', $response_data);

} catch (Exception $e) {
    mysqli_rollback($koneksi);
    api_response(500, 'error', 'Terjadi kesalahan sistem saat memproses transaksi pesanan: ' . $e->getMessage());
}
