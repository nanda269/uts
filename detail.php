<?php
require_once __DIR__ . '/config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$produk = null;

if ($db_connected && $id > 0) {
    $stmt = mysqli_prepare($koneksi, "SELECT p.*, k.nama_kategori, k.slug, u.nama_lengkap AS nama_penjual, u.no_hp AS no_penjual 
                                      FROM produk p 
                                      JOIN kategori k ON p.id_kategori = k.id_kategori 
                                      LEFT JOIN pengguna u ON p.id_penjual = u.id_pengguna 
                                      WHERE p.id_produk = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $produk = mysqli_fetch_assoc($result);
}

if (!$produk) {
    $_SESSION['flash_error'] = 'Produk tidak ditemukan atau telah dihapus.';
    header('Location: ' . base_url('index.php'));
    exit;
}

$page_title = htmlspecialchars($produk['nama_produk']) . ' - Nanda Handphone';
require_once __DIR__ . '/includes/header.php';

// Ambil produk terkait dalam kategori yang sama
$related_products = [];
if ($db_connected) {
    $stmt_rel = mysqli_prepare($koneksi, "SELECT * FROM produk WHERE id_kategori = ? AND id_produk != ? ORDER BY id_produk DESC LIMIT 4");
    mysqli_stmt_bind_param($stmt_rel, "ii", $produk['id_kategori'], $id);
    mysqli_stmt_execute($stmt_rel);
    $res_rel = mysqli_stmt_get_result($stmt_rel);
    while ($r = mysqli_fetch_assoc($res_rel)) {
        $related_products[] = $r;
    }
}

$img_src = base_url('assets/uploads/' . ($produk['gambar'] ?: 'default-phone.png'));
?>

<div class="container">
  <!-- Breadcrumbs -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>" class="text-decoration-none">Beranda</a></li>
      <li class="breadcrumb-item"><a href="<?= base_url('index.php?kategori=' . urlencode($produk['slug'])) ?>" class="text-decoration-none"><?= htmlspecialchars($produk['nama_kategori']) ?></a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($produk['nama_produk']) ?></li>
    </ol>
  </nav>

  <div class="row g-4 mb-5">
    <!-- Gambar Produk -->
    <div class="col-lg-5 col-md-6">
      <div class="detail-img-box p-3 text-center position-relative">
        <span class="badge <?= ($produk['kondisi'] === 'Baru') ? 'bg-success' : 'bg-warning text-dark' ?> position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill fw-bold">
          Kondisi: <?= htmlspecialchars($produk['kondisi']) ?>
        </span>
        <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($produk['nama_produk']) ?>" class="img-fluid rounded-3" style="max-height: 420px; width: 100%; object-fit: contain;" onerror="this.onerror=null; this.src='<?= base_url('assets/uploads/default-phone.png') ?>';">
      </div>
      
      <!-- Box Keunggulan Toko Nanda Handphone -->
      <div class="card border-0 bg-light rounded-4 mt-3 p-3">
        <div class="row text-center g-2">
          <div class="col-4">
            <i class="bi bi-shield-lock-fill text-primary fs-3"></i>
            <div class="small fw-bold mt-1">Garansi Terjamin</div>
          </div>
          <div class="col-4">
            <i class="bi bi-patch-check-fill text-success fs-3"></i>
            <div class="small fw-bold mt-1">100% Original</div>
          </div>
          <div class="col-4">
            <i class="bi bi-box-seam-fill text-warning fs-3"></i>
            <div class="small fw-bold mt-1">Packing Aman</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Informasi & Form Pembelian -->
    <div class="col-lg-7 col-md-6">
      <div class="ps-lg-3">
        <div class="d-flex align-items-center gap-2 mb-2">
          <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase px-3 py-1 rounded-pill">
            <?= htmlspecialchars($produk['merk']) ?>
          </span>
          <span class="text-muted small">Kategori: <strong><?= htmlspecialchars($produk['nama_kategori']) ?></strong></span>
        </div>

        <h1 class="h2 fw-bold text-dark mb-2"><?= htmlspecialchars($produk['nama_produk']) ?></h1>

        <!-- Harga & Status Stok -->
        <div class="d-flex align-items-baseline gap-3 my-3">
          <div class="display-6 fw-extrabold text-primary fw-bold">
            <?= rupiah($produk['harga']) ?>
          </div>
          <?php if ($produk['stok'] > 0): ?>
            <span class="badge bg-success-subtle text-success fs-6 px-3 py-2 rounded-pill">
              <i class="bi bi-check-circle-fill me-1"></i> Stok Tersedia: <?= $produk['stok'] ?> unit
            </span>
          <?php else: ?>
            <span class="badge bg-danger-subtle text-danger fs-6 px-3 py-2 rounded-pill">
              <i class="bi bi-x-circle-fill me-1"></i> Stok Habis
            </span>
          <?php endif; ?>
        </div>

        <hr class="my-4">

        <!-- Form Tambah ke Keranjang / Checkout Langsung -->
        <?php if ($produk['stok'] > 0): ?>
          <form action="<?= base_url('keranjang.php?action=add') ?>" method="POST" class="mb-4">
            <input type="hidden" name="id_produk" value="<?= $produk['id_produk'] ?>">
            
            <div class="row align-items-center g-3 mb-4">
              <div class="col-auto">
                <label for="inputQty" class="col-form-label fw-bold">Jumlah Beli:</label>
              </div>
              <div class="col-auto">
                <div class="qty-control">
                  <button type="button" class="btn-qty-minus">-</button>
                  <input type="number" id="inputQty" name="qty" value="1" min="1" max="<?= $produk['stok'] ?>" readonly>
                  <button type="button" class="btn-qty-plus">+</button>
                </div>
              </div>
              <div class="col-auto">
                <span class="text-muted small">Maksimal <?= $produk['stok'] ?> unit per transaksi</span>
              </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
              <button type="submit" name="direct_buy" value="0" class="btn btn-outline-primary btn-lg rounded-pill px-4 fw-bold">
                <i class="bi bi-cart-plus me-2"></i> Tambah ke Keranjang
              </button>
              <button type="submit" name="direct_buy" value="1" class="btn btn-primary btn-lg rounded-pill px-4 fw-bold">
                <i class="bi bi-lightning-charge-fill me-2"></i> Beli Sekarang
              </button>
            </div>
          </form>
        <?php else: ?>
          <div class="alert alert-warning rounded-4 mb-4">
            <i class="bi bi-exclamation-circle-fill me-2"></i> Produk ini sedang tidak tersedia saat ini. Silakan hubungi penjual atau pilih produk lainnya.
          </div>
        <?php endif; ?>

        <!-- Hubungi Penjual via WhatsApp -->
        <div class="mb-4">
          <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $produk['no_penjual'] ?? '6281234567890') ?>?text=Halo%20Nanda%20Handphone,%20saya%20tertarik%20dengan%20produk%20<?= urlencode($produk['nama_produk']) ?>" target="_blank" class="btn btn-outline-success rounded-pill btn-sm px-3">
            <i class="bi bi-whatsapp me-1"></i> Tanya Penjual via WhatsApp (Toko Nanda)
          </a>
        </div>

        <!-- Spesifikasi Detail -->
        <h5 class="fw-bold mb-3">Spesifikasi & Informasi Produk</h5>
        <div class="table-responsive mb-4">
          <table class="table table-bordered detail-specs-table">
            <tbody>
              <tr>
                <th>Merk Handphone</th>
                <td><?= htmlspecialchars($produk['merk']) ?></td>
              </tr>
              <tr>
                <th>Kategori</th>
                <td><?= htmlspecialchars($produk['nama_kategori']) ?></td>
              </tr>
              <tr>
                <th>Kondisi Fisik</th>
                <td><span class="badge <?= ($produk['kondisi'] === 'Baru') ? 'bg-success' : 'bg-warning text-dark' ?>"><?= htmlspecialchars($produk['kondisi']) ?></span></td>
              </tr>
              <tr>
                <th>Garansi Unit</th>
                <td><?= htmlspecialchars($produk['garansi'] ?: 'Garansi Resmi') ?></td>
              </tr>
              <tr>
                <th>Penjual / Toko</th>
                <td><i class="bi bi-shop me-1 text-primary"></i> <?= htmlspecialchars($produk['nama_penjual'] ?: 'Nanda Handphone Store') ?></td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Deskripsi Produk -->
        <h5 class="fw-bold mb-2">Deskripsi Produk</h5>
        <div class="card bg-white border-0 p-3 rounded-4 shadow-sm text-secondary" style="white-space: pre-line; line-height: 1.6;">
          <?= htmlspecialchars($produk['deskripsi']) ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Produk Terkait -->
  <?php if (!empty($related_products)): ?>
    <div class="mt-5 pt-4 border-top">
      <h3 class="fw-bold mb-4">Produk Sejenis Lainnya</h3>
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
        <?php foreach ($related_products as $rel): 
          $rel_img = base_url('assets/uploads/' . ($rel['gambar'] ?: 'default-phone.png'));
        ?>
          <div class="col">
            <div class="product-card h-100">
              <div class="product-img-wrapper" style="padding-top: 75%;">
                <img src="<?= $rel_img ?>" alt="<?= htmlspecialchars($rel['nama_produk']) ?>" loading="lazy" onerror="this.onerror=null; this.src='<?= base_url('assets/uploads/default-phone.png') ?>';">
              </div>
              <div class="product-card-body p-3">
                <span class="product-brand"><?= htmlspecialchars($rel['merk']) ?></span>
                <h6 class="product-title mt-1 mb-2">
                  <a href="<?= base_url('detail.php?id=' . $rel['id_produk']) ?>" class="text-decoration-none text-dark">
                    <?= htmlspecialchars($rel['nama_produk']) ?>
                  </a>
                </h6>
                <div class="product-price fs-6 mb-2"><?= rupiah($rel['harga']) ?></div>
                <a href="<?= base_url('detail.php?id=' . $rel['id_produk']) ?>" class="btn btn-outline-primary btn-sm rounded-pill w-100">
                  Lihat Detail
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
