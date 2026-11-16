<?php
require_once __DIR__ . '/config/database.php';

// Hapus data sesi pengguna (pertahankan keranjang belanja jika ada)
$cart_backup = $_SESSION['cart'] ?? [];

unset($_SESSION['user_id']);
unset($_SESSION['user_nama']);
unset($_SESSION['user_email']);
unset($_SESSION['user_role']);
unset($_SESSION['user_hp']);
unset($_SESSION['user_alamat']);

$_SESSION['cart'] = $cart_backup;
$_SESSION['flash_info'] = 'Anda telah berhasil keluar dari akun.';

header('Location: ' . base_url('index.php'));
exit;
