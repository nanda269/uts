<?php
require_once __DIR__ . '/../config/database.php';

$page_title = 'Dashboard Admin - Nanda Handphone';
require_once __DIR__ . '/../includes/header.php';

// Statistik Dashboard Khusus Produk (CRUD)
$total_produk = 0;
$total_stok = 0;
$produk_baru = 0;
$produk_bekas = 0;
$produk_terbaru = [];

if ($db_connected) {
    // 1. Hitung total produk & total stok
    $res_prod = mysqli_query($koneksi, "SELECT COUNT(*) AS total, SUM(stok) AS total_stok FROM produk");
    if ($row_p = mysqli_fetch_assoc($res_prod)) {
        $total_produk = (int)$row_p['total'];
        $total_stok = (int)$row_p['total_stok'];
    }

    // 2. Hitung produk baru vs bekas
    $res_kondisi = mysqli_query($koneksi, "SELECT kondisi, COUNT(*) as jml FROM produk GROUP BY kondisi");
    if ($res_kondisi) {
        while ($rk = mysqli_fetch_assoc($res_kondisi)) {
            if ($rk['kondisi'] === 'Baru') $produk_baru = (int)$rk['jml'];
            if ($rk['kondisi'] === 'Bekas') $produk_bekas = (int)$rk['jml'];
        }
    }

    // 3. Ambil 5 Produk Terbaru
    $res_latest = mysqli_query($koneksi, "SELECT p.*, k.nama_kategori FROM produk p JOIN kategori k ON p.id_kategori = k.id_kategori ORDER BY p.id_produk DESC LIMIT 5");
    if ($res_latest) {
        while ($p = mysqli_fetch_assoc($res_latest)) {
            $produk_terbaru[] = $p;
        }
    }
}
?>

<div class="container my-4">
  <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
      <h2 class="fw-bold mb-1"><i class="bi bi-speedometer2 text-primary me-2"></i> Dashboard Admin</h2>
      <p class="text-muted small mb-0">Panel Pengelolaan Produk & Inventori Toko "Nanda Handphone"</p>
    </div>
    <div class="mt-2 mt-md-0 d-flex gap-2">
      <a href="<?= base_url('admin/produk-tambah.php') ?>" class="btn btn-primary rounded-pill btn-sm px-3 fw-semibold">
        <i class="bi bi-plus-lg me-1"></i> Tambah Produk Baru
      </a>
      <a href="<?= base_url('admin/produk.php') ?>" class="btn btn-outline-dark rounded-pill btn-sm px-3 fw-semibold">
        <i class="bi bi-box-seam me-1"></i> Kelola Semua Produk
      </a>
    </div>
  </div>

  <!-- Statistik Cards (Fokus Produk & Stok) -->
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
      <div class="stat-card stat-card-blue">
        <div>
          <div class="text-white-50 small fw-semibold text-uppercase">Total Produk</div>
          <div class="display-6 fw-bold mt-1"><?= $total_produk ?></div>
          <div class="small opacity-75 mt-1">Item Handphone & Aksesoris</div>
        </div>
        <div class="fs-1 opacity-50"><i class="bi bi-phone"></i></div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="stat-card stat-card-purple">
        <div>
          <div class="text-white-50 small fw-semibold text-uppercase">Sisa Stok Total</div>
          <div class="display-6 fw-bold mt-1"><?= $total_stok ?></div>
          <div class="small opacity-75 mt-1">Unit Tersedia di Toko</div>
        </div>
        <div class="fs-1 opacity-50"><i class="bi bi-boxes"></i></div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="stat-card stat-card-green">
        <div>
          <div class="text-white-50 small fw-semibold text-uppercase">Produk Baru</div>
          <div class="display-6 fw-bold mt-1"><?= $produk_baru ?></div>
          <div class="small opacity-75 mt-1">Kondisi Baru (Segel Resmi)</div>
        </div>
        <div class="fs-1 opacity-50"><i class="bi bi-patch-check"></i></div>
      </div>
    </div>

    <div class="col-sm-6 col-lg-3">
      <div class="stat-card stat-card-amber">
        <div>
          <div class="text-white-50 small fw-semibold text-uppercase">Produk Second</div>
          <div class="display-6 fw-bold mt-1"><?= $produk_bekas ?></div>
          <div class="small opacity-75 mt-1">Kondisi Bekas Bergaransi</div>
        </div>
        <div class="fs-1 opacity-50"><i class="bi bi-recycle"></i></div>
      </div>
    </div>
  </div>

  <!-- Tabel Inventori Produk Terbaru -->
  <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
      <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-box-seam me-2 text-primary"></i> 5 Produk Handphone Terbaru</h5>
      <a href="<?= base_url('admin/produk.php') ?>" class="btn btn-outline-primary btn-sm rounded-pill">
        Kelola Semua Produk (CRUD) <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>

    <?php if (empty($produk_terbaru)): ?>
      <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-inbox fs-1 mb-2 d-block"></i>
        Belum ada data produk di database.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4" style="width: 70px;">Foto</th>
              <th>Nama Produk</th>
              <th>Merk / Kategori</th>
              <th>Harga</th>
              <th class="text-center">Stok</th>
              <th>Kondisi</th>
              <th class="text-end pe-4" style="width: 150px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($produk_terbaru as $p): 
              $img_src = base_url('assets/uploads/' . ($p['gambar'] ?: 'default-phone.png'));
            ?>
              <tr>
                <td class="ps-4">
                  <img src="<?= $img_src ?>" alt="" class="rounded-3 border" style="width: 45px; height: 45px; object-fit: cover;" onerror="this.onerror=null; this.src='<?= base_url('assets/uploads/default-phone.png') ?>';">
                </td>
                <td class="fw-bold text-dark"><?= htmlspecialchars($p['nama_produk']) ?></td>
                <td>
                  <span class="badge bg-light text-primary border"><?= htmlspecialchars($p['merk']) ?></span>
                  <span class="small text-muted ms-1"><?= htmlspecialchars($p['nama_kategori']) ?></span>
                </td>
                <td class="fw-bold text-primary"><?= rupiah($p['harga']) ?></td>
                <td class="text-center">
                  <?php if ($p['stok'] > 5): ?>
                    <span class="badge bg-success-subtle text-success"><?= $p['stok'] ?> unit</span>
                  <?php elseif ($p['stok'] > 0): ?>
                    <span class="badge bg-warning-subtle text-warning"><?= $p['stok'] ?> unit</span>
                  <?php else: ?>
                    <span class="badge bg-danger-subtle text-danger">Habis</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge <?= ($p['kondisi'] === 'Baru') ? 'bg-success' : 'bg-warning text-dark' ?>">
                    <?= htmlspecialchars($p['kondisi']) ?>
                  </span>
                </td>
                <td class="text-end pe-4">
                  <a href="<?= base_url('admin/produk-edit.php?id=' . $p['id_produk']) ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                    <i class="bi bi-pencil-square me-1"></i> Edit
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- Akses Cepat Menu Admin -->
  <div class="row g-3">
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
        <div class="d-flex align-items-center gap-3">
          <div class="bg-primary-subtle text-primary p-3 rounded-4 fs-2"><i class="bi bi-plus-circle"></i></div>
          <div>
            <h5 class="fw-bold mb-1">Tambah Produk Baru</h5>
            <p class="small text-muted mb-3">Input handphone atau aksesoris baru ke etalase toko beserta foto dan harga.</p>
            <a href="<?= base_url('admin/produk-tambah.php') ?>" class="btn btn-primary rounded-pill btn-sm px-3 fw-bold">
              <i class="bi bi-plus-lg me-1"></i> Tambah Produk Sekarang
            </a>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
        <div class="d-flex align-items-center gap-3">
          <div class="bg-success-subtle text-success p-3 rounded-4 fs-2"><i class="bi bi-box-seam"></i></div>
          <div>
            <h5 class="fw-bold mb-1">Kelola Seluruh Produk (CRUD)</h5>
            <p class="small text-muted mb-3">Lihat tabel inventori lengkap, perbarui data harga dan stok, atau hapus produk.</p>
            <a href="<?= base_url('admin/produk.php') ?>" class="btn btn-success rounded-pill btn-sm px-3 fw-bold">
              <i class="bi bi-pencil-square me-1"></i> Buka Manajemen Produk
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
