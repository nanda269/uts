<?php
$page_title = 'Nanda Handphone - Marketplace Smartphone & Aksesoris';
require_once __DIR__ . '/includes/header.php';

// Parameter Filter & Search
$q = isset($_GET['q']) ? bersihkan($koneksi, $_GET['q']) : '';
$kategori_slug = isset($_GET['kategori']) ? bersihkan($koneksi, $_GET['kategori']) : '';

// Ambil Daftar Kategori untuk Filter Pills
$kategori_list = [];
if ($db_connected) {
    $res_kat = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY id_kategori ASC");
    if ($res_kat) {
        while ($row = mysqli_fetch_assoc($res_kat)) {
            $kategori_list[] = $row;
        }
    }
}

// Query Produk
$produk_list = [];
if ($db_connected) {
    $query = "SELECT p.*, k.nama_kategori, k.slug, u.nama_lengkap AS nama_penjual 
              FROM produk p 
              JOIN kategori k ON p.id_kategori = k.id_kategori 
              LEFT JOIN pengguna u ON p.id_penjual = u.id_pengguna 
              WHERE 1=1";
    
    if (!empty($q)) {
        $query .= " AND (p.nama_produk LIKE '%$q%' OR p.merk LIKE '%$q%' OR p.deskripsi LIKE '%$q%')";
    }
    
    if (!empty($kategori_slug)) {
        $query .= " AND k.slug = '$kategori_slug'";
    }
    
    $query .= " ORDER BY p.id_produk DESC";
    $res_prod = mysqli_query($koneksi, $query);
    if ($res_prod) {
        while ($p = mysqli_fetch_assoc($res_prod)) {
            $produk_list[] = $p;
        }
    }
}
?>

<div class="container">
  <!-- Hero Section -->
  <div class="hero-banner">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill mb-3">
          <i class="bi bi-patch-check-fill me-1"></i> Marketplace Resmi Nanda HP
        </span>
        <h1 class="display-5 fw-bold mb-3">Pusat Belanja Smartphone & Aksesoris Terlengkap</h1>
        <p class="lead mb-4 opacity-90">
          Dapatkan smartphone Android & iPhone resmi bergaransi, unit second mulus teruji, dan aksesoris original dengan harga terbaik.
        </p>
        <div class="d-flex flex-wrap gap-2">
          <a href="#katalog" class="btn btn-warning text-dark fw-bold rounded-pill px-4 py-2">
            <i class="bi bi-bag-check me-1"></i> Jelajahi Katalog Produk
          </a>
        </div>
      </div>
      <div class="col-lg-4 text-center mt-4 mt-lg-0 d-none d-lg-block">
        <div class="bg-white p-3 rounded-4 shadow-lg text-dark text-start" style="max-width: 280px; margin: 0 auto;">
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-success-subtle text-success p-2 rounded-3"><i class="bi bi-shield-check fs-5"></i></span>
            <div>
              <div class="fw-bold small">Garansi Terjamin</div>
              <div class="text-muted" style="font-size: 0.75rem;">100% Original & Aman</div>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-primary-subtle text-primary p-2 rounded-3"><i class="bi bi-truck fs-5"></i></span>
            <div>
              <div class="fw-bold small">Pengiriman Cepat</div>
              <div class="text-muted" style="font-size: 0.75rem;">Support COD & Ekspedisi</div>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning-subtle text-warning p-2 rounded-3"><i class="bi bi-tag-fill fs-5"></i></span>
            <div>
              <div class="fw-bold small">Harga Tradisional</div>
              <div class="text-muted" style="font-size: 0.75rem;">Bisa Nego di Toko Offline</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter & Kategori Section -->
  <div id="katalog" class="mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
      <div>
        <h3 class="fw-bold mb-1">Daftar Produk Handphone</h3>
        <p class="text-muted small mb-0">
          <?php if (!empty($q)): ?>
            Hasil pencarian untuk kata kunci "<strong><?= htmlspecialchars($q) ?></strong>"
          <?php else: ?>
            Menampilkan seluruh koleksi handphone dan aksesoris berkualitas
          <?php endif; ?>
        </p>
      </div>
      <div class="mt-2 mt-md-0">
        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
          Total: <strong><?= count($produk_list) ?></strong> Produk
        </span>
      </div>
    </div>

    <!-- Category Pills -->
    <div class="filter-pills d-flex flex-wrap align-items-center">
      <a href="<?= base_url('index.php#katalog') ?>" class="nav-link <?= empty($kategori_slug) ? 'active' : '' ?>">
        <i class="bi bi-grid-fill me-1"></i> Semua Kategori
      </a>
      <?php foreach ($kategori_list as $kat): ?>
        <a href="<?= base_url('index.php?kategori=' . urlencode($kat['slug']) . '#katalog') ?>" 
           class="nav-link <?= ($kategori_slug === $kat['slug']) ? 'active' : '' ?>">
          <?= htmlspecialchars($kat['nama_kategori']) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Grid Produk -->
  <?php if (empty($produk_list)): ?>
    <div class="card border-0 shadow-sm text-center py-5 my-4">
      <div class="card-body">
        <i class="bi bi-search text-muted" style="font-size: 3.5rem;"></i>
        <h5 class="fw-bold mt-3">Produk Tidak Ditemukan</h5>
        <p class="text-muted">Maaf, tidak ada produk yang cocok dengan pencarian atau filter kategori yang Anda pilih.</p>
        <a href="<?= base_url('index.php#katalog') ?>" class="btn btn-primary rounded-pill px-4">
          <i class="bi bi-arrow-repeat me-1"></i> Reset Pencarian
        </a>
      </div>
    </div>
  <?php else: ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
      <?php foreach ($produk_list as $prod): 
        $img_src = base_url('assets/uploads/' . ($prod['gambar'] ?: 'default-phone.png'));
        $kondisi_badge_class = ($prod['kondisi'] === 'Baru') ? 'badge-baru' : 'badge-bekas';
      ?>
        <div class="col">
          <div class="product-card">
            <!-- Thumbnail Image -->
            <div class="product-img-wrapper">
              <span class="product-badge-condition <?= $kondisi_badge_class ?>">
                <?= htmlspecialchars($prod['kondisi']) ?>
              </span>
              <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($prod['nama_produk']) ?>" loading="lazy" onerror="this.onerror=null; this.src='<?= base_url('assets/uploads/default-phone.png') ?>';">
            </div>

            <!-- Card Body -->
            <div class="product-card-body">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="product-brand"><?= htmlspecialchars($prod['merk']) ?></span>
                <span class="product-stock">
                  <?php if ($prod['stok'] > 0): ?>
                    <i class="bi bi-check-circle-fill text-success"></i> Stok: <?= $prod['stok'] ?>
                  <?php else: ?>
                    <span class="badge bg-danger">Habis</span>
                  <?php endif; ?>
                </span>
              </div>

              <h5 class="product-title" title="<?= htmlspecialchars($prod['nama_produk']) ?>">
                <a href="<?= base_url('detail.php?id=' . $prod['id_produk']) ?>" class="text-decoration-none text-dark">
                  <?= htmlspecialchars($prod['nama_produk']) ?>
                </a>
              </h5>

              <div class="mt-auto">
                <div class="product-price">
                  <?= rupiah($prod['harga']) ?>
                </div>

                <div class="d-grid gap-2">
                  <a href="<?= base_url('detail.php?id=' . $prod['id_produk']) ?>" class="btn btn-outline-primary btn-sm rounded-pill fw-semibold">
                    <i class="bi bi-eye me-1"></i> Detail Produk
                  </a>
                  
                  <?php if ($prod['stok'] > 0): ?>
                    <form action="<?= base_url('keranjang.php?action=add') ?>" method="POST">
                      <input type="hidden" name="id_produk" value="<?= $prod['id_produk'] ?>">
                      <input type="hidden" name="qty" value="1">
                      <button type="submit" class="btn btn-primary btn-sm rounded-pill w-100 fw-semibold">
                        <i class="bi bi-cart-plus me-1"></i> + Keranjang
                      </button>
                    </form>
                  <?php else: ?>
                    <button class="btn btn-secondary btn-sm rounded-pill w-100" disabled>
                      Stok Habis
                    </button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
