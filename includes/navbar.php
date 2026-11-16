<?php
$is_admin_area = (basename(dirname($_SERVER['PHP_SELF'])) === 'admin');
$current_page  = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
  <div class="container">
    
    <?php if ($is_admin_area): ?>
      <!-- ================= BRAND: ADMIN VIEW ================= -->
      <a class="navbar-brand navbar-brand-logo" href="<?= base_url('admin/index.php') ?>">
        <span class="fs-4 text-warning"><i class="bi bi-shield-lock-fill"></i></span>
        <span class="text-white">Nanda<span class="text-info">HP</span></span>
        <span class="brand-badge bg-warning text-dark">Admin</span>
      </a>
    <?php else: ?>
      <!-- ================= BRAND: CUSTOMER / PUBLIC VIEW ================= -->
      <a class="navbar-brand navbar-brand-logo" href="<?= base_url('index.php') ?>">
        <span class="fs-4 text-warning"><i class="bi bi-phone"></i></span>
        <span class="text-white">Nanda<span class="text-info">HP</span></span>
        <span class="brand-badge">Marketplace</span>
      </a>
    <?php endif; ?>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">

      <?php if ($is_admin_area): ?>
        <!-- ======================================================== -->
        <!-- NAVIGATION KHUSUS ADMIN (Tanpa Lacak Pesanan, Keranjang, Pesanan) -->
        <!-- ======================================================== -->
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3 gap-1">
          <li class="nav-item">
            <a class="nav-link <?= ($current_page == 'index.php') ? 'active fw-bold' : '' ?>" href="<?= base_url('admin/index.php') ?>">
              <i class="bi bi-speedometer2 me-1"></i> Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= ($current_page == 'produk.php') ? 'active fw-bold' : '' ?>" href="<?= base_url('admin/produk.php') ?>">
              <i class="bi bi-box-seam me-1"></i> Kelola Produk (CRUD)
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= ($current_page == 'produk-tambah.php') ? 'active fw-bold' : '' ?>" href="<?= base_url('admin/produk-tambah.php') ?>">
              <i class="bi bi-plus-circle me-1"></i> Tambah Produk
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= base_url('index.php') ?>" target="_blank">
              <i class="bi bi-box-arrow-up-right me-1"></i> Lihat Toko
            </a>
          </li>
        </ul>

        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
          <li class="nav-item dropdown">
            <a class="btn btn-primary rounded-pill px-3 py-1 dropdown-toggle fw-semibold d-inline-flex align-items-center gap-1" href="#" id="adminProfileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle fs-5"></i>
              <span><?= htmlspecialchars($_SESSION['user_nama'] ?? 'Admin Nanda') ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="adminProfileDropdown">
              <li><h6 class="dropdown-header text-uppercase small">Administrator Toko</h6></li>
              <li><a class="dropdown-item" href="<?= base_url('admin/produk.php') ?>"><i class="bi bi-box-seam text-primary me-2"></i> Kelola Produk</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= base_url('logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i> Keluar (Logout)</a></li>
            </ul>
          </li>
        </ul>

      <?php else: ?>
        <!-- ======================================================== -->
        <!-- NAVIGATION KHUSUS PEMBELI / GUEST (Tanpa Portal Penjual & Lacak Pesanan) -->
        <!-- ======================================================== -->
        
        <!-- Search Bar -->
        <form class="d-flex mx-lg-auto my-2 my-lg-0 w-100" style="max-width: 440px;" action="<?= base_url('index.php') ?>" method="GET">
          <div class="input-group">
            <input class="form-control border-0 rounded-start-pill ps-3" type="search" name="q" placeholder="Cari iPhone, Samsung, Aksesoris..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" aria-label="Search">
            <button class="btn btn-warning rounded-end-pill px-3" type="submit">
              <i class="bi bi-search text-dark"></i>
            </button>
          </div>
        </form>

        <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-1">
          <!-- Menu Katalog -->
          <li class="nav-item">
            <a class="nav-link <?= ($current_page == 'index.php' && empty($_GET['kategori'])) ? 'active' : '' ?>" href="<?= base_url('index.php') ?>">
              <i class="bi bi-shop me-1"></i> Katalog
            </a>
          </li>

          <!-- Keranjang Belanja -->
          <li class="nav-item me-lg-2">
            <a class="btn btn-outline-light rounded-pill px-3 py-1 position-relative d-inline-flex align-items-center" href="<?= base_url('keranjang.php') ?>">
              <i class="bi bi-cart3 me-1 fs-5"></i> Keranjang
              <?php if ($total_cart_items > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light">
                  <?= $total_cart_items ?>
                </span>
              <?php endif; ?>
            </a>
          </li>

          <!-- Akses Pelanggan -->
          <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Pelanggan Sudah Login -->
            <li class="nav-item dropdown ms-lg-1 mt-2 mt-lg-0">
              <a class="btn btn-primary rounded-pill px-3 py-1 dropdown-toggle fw-semibold d-inline-flex align-items-center gap-1" href="#" id="customerUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle fs-5"></i>
                <span class="text-truncate" style="max-width: 120px;"><?= htmlspecialchars($_SESSION['user_nama']) ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="customerUserDropdown">
                <li><h6 class="dropdown-header text-uppercase small">Akun Pelanggan</h6></li>
                <li><a class="dropdown-item" href="<?= base_url('pesanan-saya.php') ?>"><i class="bi bi-bag-check text-primary me-2"></i> Pesanan Saya</a></li>
                
                <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'penjual')): ?>
                  <li><hr class="dropdown-divider"></li>
                  <li><h6 class="dropdown-header text-uppercase small">Panel Pengelola</h6></li>
                  <li><a class="dropdown-item" href="<?= base_url('admin/index.php') ?>"><i class="bi bi-speedometer2 text-warning me-2"></i> Dashboard Admin</a></li>
                  <li><a class="dropdown-item" href="<?= base_url('admin/produk.php') ?>"><i class="bi bi-box-seam text-success me-2"></i> Kelola Produk</a></li>
                <?php endif; ?>

                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?= base_url('logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i> Keluar (Logout)</a></li>
              </ul>
            </li>
          <?php else: ?>
            <!-- Pelanggan Belum Login -->
            <li class="nav-item ms-lg-1 mt-2 mt-lg-0 d-flex gap-2">
              <a class="btn btn-outline-info rounded-pill px-3 py-1 text-white fw-semibold small" href="<?= base_url('login.php') ?>">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
              </a>
              <a class="btn btn-warning rounded-pill px-3 py-1 text-dark fw-bold small" href="<?= base_url('register.php') ?>">
                <i class="bi bi-person-plus me-1"></i> Daftar
              </a>
            </li>
          <?php endif; ?>

        </ul>
      <?php endif; ?>

    </div>
  </div>
</nav>
