<?php
require_once __DIR__ . '/../config/database.php';

$page_title = 'Kelola Data Produk (CRUD) - Nanda Handphone';
require_once __DIR__ . '/../includes/header.php';

// Filter & Pencarian
$q = isset($_GET['q']) ? bersihkan($koneksi, $_GET['q']) : '';
$kategori_filter = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;

$produk_list = [];
$kategori_list = [];

if ($db_connected) {
    // Ambil Kategori untuk dropdown filter
    $res_kat = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
    while ($k = mysqli_fetch_assoc($res_kat)) {
        $kategori_list[] = $k;
    }

    // Query Produk
    $sql = "SELECT p.*, k.nama_kategori, u.nama_lengkap AS nama_penjual 
            FROM produk p 
            JOIN kategori k ON p.id_kategori = k.id_kategori 
            LEFT JOIN pengguna u ON p.id_penjual = u.id_pengguna 
            WHERE 1=1";
    
    if (!empty($q)) {
        $sql .= " AND (p.nama_produk LIKE '%$q%' OR p.merk LIKE '%$q%')";
    }
    if ($kategori_filter > 0) {
        $sql .= " AND p.id_kategori = $kategori_filter";
    }

    $sql .= " ORDER BY p.id_produk DESC";
    $res = mysqli_query($koneksi, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $produk_list[] = $row;
        }
    }
}
?>

<div class="container my-4">
  <!-- Page Header -->
  <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
      <h2 class="fw-bold mb-1"><i class="bi bi-box-seam text-primary me-2"></i> Kelola Data Produk (CRUD)</h2>
      <p class="text-muted small mb-0">Daftar inventori katalog handphone & aksesoris yang dijual di marketplace.</p>
    </div>
    <div class="mt-2 mt-md-0">
      <a href="<?= base_url('admin/produk-tambah.php') ?>" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> Tambah Produk Baru
      </a>
    </div>
  </div>

  <!-- Search & Filter Card -->
  <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
    <form action="<?= base_url('admin/produk.php') ?>" method="GET" class="row g-2 align-items-center">
      <div class="col-md-6">
        <div class="input-group">
          <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
          <input type="text" name="q" class="form-control border-start-0" placeholder="Cari nama produk atau merk..." value="<?= htmlspecialchars($q) ?>">
        </div>
      </div>
      <div class="col-md-4">
        <select name="kategori" class="form-select">
          <option value="">-- Semua Kategori --</option>
          <?php foreach ($kategori_list as $kat): ?>
            <option value="<?= $kat['id_kategori'] ?>" <?= ($kategori_filter == $kat['id_kategori']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($kat['nama_kategori']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-outline-primary fw-semibold">
          <i class="bi bi-funnel me-1"></i> Filter
        </button>
      </div>
    </form>
  </div>

  <!-- Tabel Data Produk -->
  <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
      <span class="fw-bold text-dark">Total: <?= count($produk_list) ?> Produk</span>
      <?php if (!empty($q) || $kategori_filter > 0): ?>
        <a href="<?= base_url('admin/produk.php') ?>" class="btn btn-sm btn-link text-decoration-none">Reset Filter</a>
      <?php endif; ?>
    </div>

    <?php if (empty($produk_list)): ?>
      <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-inbox fs-1 mb-2 d-block"></i>
        Tidak ada data produk yang ditemukan.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4" style="width: 70px;">Foto</th>
              <th>Nama Produk</th>
              <th>Merk / Kategori</th>
              <th>Kondisi</th>
              <th>Harga</th>
              <th class="text-center">Stok</th>
              <th class="text-end pe-4" style="width: 180px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($produk_list as $p): 
              $img_src = base_url('assets/uploads/' . ($p['gambar'] ?: 'default-phone.png'));
            ?>
              <tr>
                <td class="ps-4">
                  <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($p['nama_produk']) ?>" class="rounded-3 border" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.onerror=null; this.src='<?= base_url('assets/uploads/default-phone.png') ?>';">
                </td>
                <td>
                  <div class="fw-bold text-dark"><?= htmlspecialchars($p['nama_produk']) ?></div>
                  <div class="text-muted small">Garansi: <?= htmlspecialchars($p['garansi']) ?></div>
                </td>
                <td>
                  <div class="badge bg-light text-primary border"><?= htmlspecialchars($p['merk']) ?></div>
                  <div class="small text-secondary mt-1"><?= htmlspecialchars($p['nama_kategori']) ?></div>
                </td>
                <td>
                  <span class="badge <?= ($p['kondisi'] === 'Baru') ? 'bg-success' : 'bg-warning text-dark' ?>">
                    <?= htmlspecialchars($p['kondisi']) ?>
                  </span>
                </td>
                <td>
                  <div class="fw-bold text-primary"><?= rupiah($p['harga']) ?></div>
                </td>
                <td class="text-center">
                  <?php if ($p['stok'] > 5): ?>
                    <span class="badge bg-success-subtle text-success"><?= $p['stok'] ?> unit</span>
                  <?php elseif ($p['stok'] > 0): ?>
                    <span class="badge bg-warning-subtle text-warning"><?= $p['stok'] ?> unit</span>
                  <?php else: ?>
                    <span class="badge bg-danger-subtle text-danger">Habis</span>
                  <?php endif; ?>
                </td>
                <td class="text-end pe-4">
                  <div class="btn-group" role="group">
                    <a href="<?= base_url('detail.php?id=' . $p['id_produk']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Lihat Tampilan Publik">
                      <i class="bi bi-eye"></i>
                    </a>
                    <a href="<?= base_url('admin/produk-edit.php?id=' . $p['id_produk']) ?>" class="btn btn-sm btn-outline-primary" title="Edit Produk">
                      <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="<?= base_url('admin/produk-hapus.php?id=' . $p['id_produk']) ?>" class="btn btn-sm btn-outline-danger confirm-delete" data-confirm="Apakah Anda yakin ingin menghapus produk '<?= htmlspecialchars(addslashes($p['nama_produk'])) ?>'?" title="Hapus Produk">
                      <i class="bi bi-trash"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
