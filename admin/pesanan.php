<?php
require_once __DIR__ . '/../config/database.php';

$page_title = 'Kelola Pesanan Masuk - Nanda Handphone';
require_once __DIR__ . '/../includes/header.php';

$status_filter = isset($_GET['status']) ? bersihkan($koneksi, $_GET['status']) : '';
$pesanan_list = [];

if ($db_connected) {
    $sql = "SELECT * FROM pesanan WHERE 1=1";
    if (!empty($status_filter)) {
        $sql .= " AND status_pesanan = '$status_filter'";
    }
    $sql .= " ORDER BY id_pesanan DESC";
    $res = mysqli_query($koneksi, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $pesanan_list[] = $row;
        }
    }
}
?>

<div class="container my-4">
  <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
      <h2 class="fw-bold mb-1"><i class="bi bi-receipt text-primary me-2"></i> Kelola Pesanan Masuk</h2>
      <p class="text-muted small mb-0">Daftar pemesanan handphone dari pembeli dan pengelolaan status transaksi.</p>
    </div>
    <div class="mt-2 mt-md-0">
      <a href="<?= base_url('admin/index.php') ?>" class="btn btn-outline-secondary rounded-pill btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
      </a>
    </div>
  </div>

  <!-- Filter Status Nav Pills -->
  <div class="filter-pills d-flex flex-wrap align-items-center mb-4">
    <a href="<?= base_url('admin/pesanan.php') ?>" class="nav-link <?= empty($status_filter) ? 'active' : '' ?>">
      Semua Pesanan
    </a>
    <a href="<?= base_url('admin/pesanan.php?status=' . urlencode('Menunggu Konfirmasi')) ?>" class="nav-link <?= ($status_filter === 'Menunggu Konfirmasi') ? 'active' : '' ?>">
      Menunggu Konfirmasi
    </a>
    <a href="<?= base_url('admin/pesanan.php?status=' . urlencode('Diproses')) ?>" class="nav-link <?= ($status_filter === 'Diproses') ? 'active' : '' ?>">
      Diproses
    </a>
    <a href="<?= base_url('admin/pesanan.php?status=' . urlencode('Dikirim')) ?>" class="nav-link <?= ($status_filter === 'Dikirim') ? 'active' : '' ?>">
      Dikirim
    </a>
    <a href="<?= base_url('admin/pesanan.php?status=' . urlencode('Selesai')) ?>" class="nav-link <?= ($status_filter === 'Selesai') ? 'active' : '' ?>">
      Selesai
    </a>
    <a href="<?= base_url('admin/pesanan.php?status=' . urlencode('Dibatalkan')) ?>" class="nav-link <?= ($status_filter === 'Dibatalkan') ? 'active' : '' ?>">
      Dibatalkan
    </a>
  </div>

  <!-- Tabel Pesanan -->
  <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center">
      <span class="fw-bold text-dark">Total: <?= count($pesanan_list) ?> Pesanan</span>
    </div>

    <?php if (empty($pesanan_list)): ?>
      <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-inbox fs-1 mb-2 d-block"></i>
        Tidak ada data pesanan pada kategori filter ini.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4">No. Invoice</th>
              <th>Tanggal</th>
              <th>Pembeli</th>
              <th>No. WhatsApp</th>
              <th>Total Pembayaran</th>
              <th>Metode Pembayaran</th>
              <th>Status</th>
              <th class="text-end pe-4" style="width: 130px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pesanan_list as $row): 
              $badge_color = 'bg-secondary';
              if ($row['status_pesanan'] === 'Menunggu Konfirmasi') $badge_color = 'bg-warning text-dark';
              elseif ($row['status_pesanan'] === 'Diproses') $badge_color = 'bg-info text-dark';
              elseif ($row['status_pesanan'] === 'Dikirim') $badge_color = 'bg-primary';
              elseif ($row['status_pesanan'] === 'Selesai') $badge_color = 'bg-success';
              elseif ($row['status_pesanan'] === 'Dibatalkan') $badge_color = 'bg-danger';
            ?>
              <tr>
                <td class="ps-4 fw-bold font-monospace text-primary"><?= htmlspecialchars($row['kode_pesanan']) ?></td>
                <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($row['tanggal_pesanan'])) ?></td>
                <td class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_pembeli']) ?></td>
                <td>
                  <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $row['no_hp']) ?>" target="_blank" class="text-decoration-none text-success small">
                    <i class="bi bi-whatsapp me-1"></i><?= htmlspecialchars($row['no_hp']) ?>
                  </a>
                </td>
                <td class="fw-bold text-dark"><?= rupiah($row['total_pembayaran']) ?></td>
                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['metode_pembayaran']) ?></span></td>
                <td><span class="badge <?= $badge_color ?>"><?= htmlspecialchars($row['status_pesanan']) ?></span></td>
                <td class="text-end pe-4">
                  <a href="<?= base_url('admin/pesanan-detail.php?id=' . $row['id_pesanan']) ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                    Kelola <i class="bi bi-chevron-right"></i>
                  </a>
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
