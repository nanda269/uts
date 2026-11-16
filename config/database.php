<?php
/**
 * Konfigurasi Database - Nanda Handphone Marketplace
 * Mendukung Localhost (XAMPP) & Hosting Gratis InfinityFree secara otomatis
 */

// Konfigurasi Environment
$is_infinityfree = (isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'infinityfree') !== false) 
                || (isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'epizy.com') !== false);

if ($is_infinityfree) {
    // Kredensial Hosting InfinityFree (Sesuaikan dengan data dari cPanel InfinityFree Anda)
    define('DB_HOST', 'sqlxxx.infinityfree.com'); // Ganti dengan MySQL Hostname InfinityFree Anda (misal: sql102.infinityfree.com)
    define('DB_USER', 'if0_xxxxxxx');            // Ganti dengan MySQL Username InfinityFree Anda
    define('DB_PASS', 'your_vpanel_password');   // Ganti dengan MySQL Password akun InfinityFree Anda
    define('DB_NAME', 'if0_xxxxxxx_uts_nanda');  // Ganti dengan Nama Database di InfinityFree
} else {
    // Kredensial Localhost (XAMPP default)
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'uts_nanda_hp');
}

// Inisialisasi Koneksi MySQLi
mysqli_report(MYSQLI_REPORT_OFF);
try {
    $koneksi = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$koneksi) {
        // Jangan hentikan total jika database belum diimport agar tampilan instruksi ramah pengguna bisa muncul
        $db_connected = false;
        $db_error = mysqli_connect_error();
    } else {
        $db_connected = true;
        mysqli_set_charset($koneksi, "utf8mb4");
    }
} catch (Throwable $e) {
    $koneksi = false;
    $db_connected = false;
    $db_error = $e->getMessage();
}

// Mulai Session jika belum aktif
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}

// Helper Function: Format Rupiah
if (!function_exists('rupiah')) {
    function rupiah($angka) {
        return 'Rp ' . number_format((float)$angka, 0, ',', '.');
    }
}

// Helper Function: Sanitasi Input
if (!function_exists('bersihkan')) {
    function bersihkan($koneksi, $data) {
        if (!$koneksi) return htmlspecialchars(trim($data));
        return mysqli_real_escape_string($koneksi, htmlspecialchars(trim($data)));
    }
}

// Helper Function: URL Base Path
if (!function_exists('base_url')) {
    function base_url($path = '') {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script_dir = dirname($_SERVER['SCRIPT_NAME']);
        
        // Deteksi jika berada di dalam subfolder admin atau api
        if (basename($script_dir) === 'admin' || basename($script_dir) === 'api') {
            $root_dir = dirname($script_dir);
        } else {
            $root_dir = $script_dir;
        }
        
        $root_dir = rtrim(str_replace('\\', '/', $root_dir), '/');
        return $protocol . $host . $root_dir . '/' . ltrim($path, '/');
    }
}
