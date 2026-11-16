<?php
/**
 * REST API Endpoint: Data Kategori Produk
 * 
 * Endpoints:
 * - GET /api/kategori.php        -> Mengambil seluruh kategori beserta jumlah produknya
 * - GET /api/kategori.php?id=1   -> Mengambil 1 kategori spesifik berdasarkan ID
 * - GET /api/kategori.php?slug=android -> Mengambil 1 kategori spesifik berdasarkan slug
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/api_helper.php';

// Validasi Metode HTTP: Hanya izinkan GET
require_method('GET');

// Pastikan koneksi database aktif
check_db_connection($db_connected, $db_error ?? '');

// =========================================================================
// KASUS 1: GET /api/kategori.php?id={id} atau ?slug={slug} -> Kategori Tunggal
// =========================================================================
if ((isset($_GET['id']) && trim($_GET['id']) !== '') || (isset($_GET['slug']) && trim($_GET['slug']) !== '')) {
    if (isset($_GET['id']) && trim($_GET['id']) !== '') {
        $id_raw = trim($_GET['id']);
        if (!ctype_digit($id_raw) || (int)$id_raw <= 0) {
            api_response(400, 'error', 'Parameter ID kategori tidak valid. Harus berupa bilangan bulat positif (contoh: ?id=1).');
        }
        $where = "k.id_kategori = ?";
        $param = (int)$id_raw;
        $type  = "i";
    } else {
        $where = "k.slug = ?";
        $param = trim($_GET['slug']);
        $type  = "s";
    }

    $query = "SELECT k.id_kategori, k.nama_kategori, k.slug, k.deskripsi,
                     COUNT(p.id_produk) AS total_produk
              FROM kategori k
              LEFT JOIN produk p ON k.id_kategori = p.id_kategori
              WHERE {$where}
              GROUP BY k.id_kategori, k.nama_kategori, k.slug, k.deskripsi
              LIMIT 1";

    $stmt = mysqli_prepare($koneksi, $query);
    if (!$stmt) {
        api_response(500, 'error', 'Query database error: ' . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt, $type, $param);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if (!$row) {
        api_response(404, 'error', 'Kategori tidak ditemukan.', null);
    }

    $detail_kategori = [
        'id_kategori'   => (int)$row['id_kategori'],
        'nama_kategori' => $row['nama_kategori'],
        'slug'          => $row['slug'],
        'deskripsi'     => $row['deskripsi'],
        'total_produk'  => (int)$row['total_produk']
    ];

    api_response(200, 'success', 'Detail kategori berhasil diambil.', $detail_kategori);
}

// =========================================================================
// KASUS 2: GET /api/kategori.php -> Daftar Seluruh Kategori
// =========================================================================
$query = "SELECT k.id_kategori, k.nama_kategori, k.slug, k.deskripsi,
                 COUNT(p.id_produk) AS total_produk
          FROM kategori k
          LEFT JOIN produk p ON k.id_kategori = p.id_kategori
          GROUP BY k.id_kategori, k.nama_kategori, k.slug, k.deskripsi
          ORDER BY k.id_kategori ASC";

$result = mysqli_query($koneksi, $query);
if (!$result) {
    api_response(500, 'error', 'Gagal mengambil data kategori: ' . mysqli_error($koneksi));
}

$kategori_list = [];
while ($row = mysqli_fetch_assoc($result)) {
    $kategori_list[] = [
        'id_kategori'   => (int)$row['id_kategori'],
        'nama_kategori' => $row['nama_kategori'],
        'slug'          => $row['slug'],
        'deskripsi'     => $row['deskripsi'],
        'total_produk'  => (int)$row['total_produk']
    ];
}

api_response(200, 'success', 'Daftar kategori berhasil diambil.', $kategori_list, ['total' => count($kategori_list)]);
