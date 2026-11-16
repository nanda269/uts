<?php
require_once __DIR__ . '/../config/database.php';

// Hitung total item di keranjang belanja
$total_cart_items = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total_cart_items += ($item['qty'] ?? 1);
    }
}

$page_title = $page_title ?? 'Nanda Handphone - Marketplace Handphone & Gadget Terpercaya';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title) ?></title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📱</text></svg>">
  
  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

<?php require_once __DIR__ . '/navbar.php'; ?>

<!-- Flash Message Notifications -->
<?php if (isset($_SESSION['flash_success'])): ?>
  <div class="container mt-3">
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
      <i class="bi bi-check-circle-fill fs-5 me-2"></i>
      <div><?= $_SESSION['flash_success'] ?></div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
  <div class="container mt-3">
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
      <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
      <div><?= $_SESSION['flash_error'] ?></div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_info'])): ?>
  <div class="container mt-3">
    <div class="alert alert-info alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
      <i class="bi bi-info-circle-fill fs-5 me-2"></i>
      <div><?= $_SESSION['flash_info'] ?></div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
  <?php unset($_SESSION['flash_info']); ?>
<?php endif; ?>

<!-- Database Offline Banner if not connected -->
<?php if (!$db_connected): ?>
  <div class="container mt-3">
    <div class="alert alert-warning shadow-sm" role="alert">
      <h5 class="alert-heading"><i class="bi bi-database-exclamation me-2"></i> Koneksi Database Belum Aktif</h5>
      <p class="mb-1">Aplikasi belum terhubung ke database MySQL. Pastikan MySQL sudah aktif di XAMPP Control Panel dan database <code>uts_nanda_hp</code> sudah di-import melalui phpMyAdmin.</p>
      <hr>
      <p class="mb-0 small text-muted">Error: <?= htmlspecialchars($db_error ?? 'Unknown error') ?> | Silakan jalankan file <code>database.sql</code> di phpMyAdmin.</p>
    </div>
  </div>
<?php endif; ?>

<main class="py-4">
