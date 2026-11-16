<?php
/**
 * REST API Endpoint: Data Produk Handphone & Aksesoris
 * 
 * Endpoints:
 * - GET /api/produk.php       -> Mengambil semua data produk (bisa filter ?kategori=1 atau ?q=samsung)
 * - GET /api/produk.php?id=1  -> Mengambil rincian 1 produk spesifik berdasarkan ID
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/api_helper.php';

// Validasi Metode HTTP: Hanya izinkan GET
require_method('GET');

// Pastikan koneksi database aktif
check_db_connection($db_connected, $db_error ?? '');

// =========================================================================
// KASUS 1: GET /api/produk.php?id={id} -> Detail Produk Tunggal
// =========================================================================
if (isset($_GET['id']) && trim($_GET['id']) !== '') {
    $id_raw = trim($_GET['id']);

    // Validasi parameter id harus integer positif
    if (!ctype_digit($id_raw) || (int)$id_raw <= 0) {
        api_response(400, 'error', 'Parameter ID produk tidak valid. Harus berupa bilangan bulat positif (contoh: ?id=1).');
    }

    $id_produk = (int)$id_raw;

    $query = "SELECT p.id_produk, p.id_kategori, k.nama_kategori, k.slug AS slug_kategori,
                     p.id_penjual, u.nama_lengkap AS nama_penjual,
                     p.nama_produk, p.merk, p.harga, p.stok, p.kondisi, p.garansi,
                     p.deskripsi, p.gambar, p.created_at
              FROM produk p
              JOIN kategori k ON p.id_kategori = k.id_kategori
              LEFT JOIN pengguna u ON p.id_penjual = u.id_pengguna
              WHERE p.id_produk = ?
              LIMIT 1";

    $stmt = mysqli_prepare($koneksi, $query);
    if (!$stmt) {
        api_response(500, 'error', 'Query database error: ' . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt, "i", $id_produk);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if (!$row) {
        api_response(404, 'error', "Produk dengan ID {$id_produk} tidak ditemukan.", null);
    }

    $detail_produk = [
        'id_produk'       => (int)$row['id_produk'],
        'nama_produk'     => $row['nama_produk'],
        'merk'            => $row['merk'],
        'harga'           => (float)$row['harga'],
        'harga_format'    => rupiah($row['harga']),
        'stok'            => (int)$row['stok'],
        'kondisi'         => $row['kondisi'],
        'garansi'         => $row['garansi'],
        'deskripsi'       => $row['deskripsi'],
        'kategori'        => [
            'id_kategori'   => (int)$row['id_kategori'],
            'nama_kategori' => $row['nama_kategori'],
            'slug'          => $row['slug_kategori']
        ],
        'penjual'         => [
            'id_penjual'    => $row['id_penjual'] !== null ? (int)$row['id_penjual'] : null,
            'nama_penjual'  => $row['nama_penjual'] ?? 'Nanda Handphone Official'
        ],
        'gambar'          => $row['gambar'],
        'url_gambar'      => base_url('assets/uploads/' . $row['gambar']),
        'created_at'      => $row['created_at']
    ];

    api_response(200, 'success', 'Detail produk berhasil diambil.', $detail_produk);
}

// =========================================================================
// KASUS 2: GET /api/produk.php -> Daftar Seluruh Produk (dengan Filter Opsional)
// =========================================================================
$where_clauses = ["1=1"];
$params = [];
$types  = "";

// Filter Kategori (ID atau Slug)
if (isset($_GET['kategori']) && trim($_GET['kategori']) !== '') {
    $kat = trim($_GET['kategori']);
    if (ctype_digit($kat)) {
        $where_clauses[] = "p.id_kategori = ?";
        $params[] = (int)$kat;
        $types   .= "i";
    } else {
        $where_clauses[] = "k.slug = ?";
        $params[] = $kat;
        $types   .= "s";
    }
}

// Filter Pencarian Keyword (Nama Produk / Merk)
if (isset($_GET['q']) && trim($_GET['q']) !== '') {
    $keyword = '%' . trim($_GET['q']) . '%';
    $where_clauses[] = "(p.nama_produk LIKE ? OR p.merk LIKE ?)";
    $params[] = $keyword;
    $params[] = $keyword;
    $types   .= "ss";
}

// Filter Kondisi (Baru / Bekas)
if (isset($_GET['kondisi']) && in_array(trim($_GET['kondisi']), ['Baru', 'Bekas'])) {
    $where_clauses[] = "p.kondisi = ?";
    $params[] = trim($_GET['kondisi']);
    $types   .= "s";
}

$sql = "SELECT p.id_produk, p.id_kategori, k.nama_kategori, k.slug AS slug_kategori,
               p.id_penjual, u.nama_lengkap AS nama_penjual,
               p.nama_produk, p.merk, p.harga, p.stok, p.kondisi, p.garansi,
               p.deskripsi, p.gambar, p.created_at
        FROM produk p
        JOIN kategori k ON p.id_kategori = k.id_kategori
        LEFT JOIN pengguna u ON p.id_penjual = u.id_pengguna
        WHERE " . implode(" AND ", $where_clauses) . "
        ORDER BY p.id_produk ASC";

$stmt = mysqli_prepare($koneksi, $sql);
if (!$stmt) {
    api_response(500, 'error', 'Query database error: ' . mysqli_error($koneksi));
}

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$produk_list = [];
while ($row = mysqli_fetch_assoc($result)) {
    $produk_list[] = [
        'id_produk'       => (int)$row['id_produk'],
        'nama_produk'     => $row['nama_produk'],
        'merk'            => $row['merk'],
        'harga'           => (float)$row['harga'],
        'harga_format'    => rupiah($row['harga']),
        'stok'            => (int)$row['stok'],
        'kondisi'         => $row['kondisi'],
        'garansi'         => $row['garansi'],
        'deskripsi'       => $row['deskripsi'],
        'kategori'        => [
            'id_kategori'   => (int)$row['id_kategori'],
            'nama_kategori' => $row['nama_kategori'],
            'slug'          => $row['slug_kategori']
        ],
        'penjual'         => [
            'id_penjual'    => $row['id_penjual'] !== null ? (int)$row['id_penjual'] : null,
            'nama_penjual'  => $row['nama_penjual'] ?? 'Nanda Handphone Official'
        ],
        'gambar'          => $row['gambar'],
        'url_gambar'      => base_url('assets/uploads/' . $row['gambar']),
        'created_at'      => $row['created_at']
    ];
}

api_response(200, 'success', 'Daftar produk berhasil diambil.', $produk_list, ['total' => count($produk_list)]);
