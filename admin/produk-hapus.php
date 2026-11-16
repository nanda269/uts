<?php
require_once __DIR__ . '/../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($db_connected && $id > 0) {
    // Ambil data produk terlebih dahulu untuk mengecek nama file gambar
    $stmt = mysqli_prepare($koneksi, "SELECT id_produk, nama_produk, gambar FROM produk WHERE id_produk = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $prod = mysqli_fetch_assoc($res);

    if ($prod) {
        // Hapus record dari database
        $stmt_del = mysqli_prepare($koneksi, "DELETE FROM produk WHERE id_produk = ?");
        mysqli_stmt_bind_param($stmt_del, "i", $id);
        
        if (mysqli_stmt_execute($stmt_del)) {
            // Hapus file gambar jika foto unggahan kustom
            $gambar = $prod['gambar'];
            if ($gambar && strpos($gambar, 'prod_') === 0) {
                $file_path = __DIR__ . '/../assets/uploads/' . $gambar;
                if (file_exists($file_path)) {
                    @unlink($file_path);
                }
            }

            $_SESSION['flash_success'] = 'Produk <strong>' . htmlspecialchars($prod['nama_produk']) . '</strong> berhasil dihapus!';
        } else {
            $_SESSION['flash_error'] = 'Gagal menghapus produk: ' . mysqli_error($koneksi);
        }
    } else {
        $_SESSION['flash_error'] = 'Produk tidak ditemukan.';
    }
}

header('Location: ' . base_url('admin/produk.php'));
exit;
