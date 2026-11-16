<?php
$page_title = 'Lacak Status Pesanan - Nanda Handphone';
require_once __DIR__ . '/includes/header.php';

$keyword = isset($_GET['keyword']) ? bersihkan($koneksi, $_GET['keyword']) : '';
$hasil_pesanan = [];

if (!empty($keyword) && $db_connected) {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM pesanan WHERE kode_pesanan = ? OR no_hp = ? ORDER BY id_pesanan DESC");
    mysqli_stmt_bind_param($stmt, "ss", $keyword, $keyword);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $hasil_pesanan[] = $row;
    }
}
?>

<div class="container my-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      
      <div class="text-center mb-4">
        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-bold mb-2">
          <i class="bi bi-truck me-1"></i> Pelacakan Transaksi
        </span>
        <h2 class="fw-bold">Lacak Status Pesanan Anda</h2>
        <p class="text-muted">Masukkan Kode Pesanan (contoh: <code>NHP-20261101-001</code>) atau Nomor WhatsApp yang Anda gunakan saat pemesanan.</p>
      </div>

      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <form action="<?= base_url('lacak-pesanan.php') ?>" method="GET">
          <div class="input-group input-group-lg">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" name="keyword" class="form-control border-start-0" placeholder="Ketik Kode Pesanan atau No. WhatsApp..." value="<?= htmlspecialchars($keyword) ?>" required>
            <button class="btn btn-primary px-4 fw-bold" type="submit">
              Cari Pesanan
            </button>
          </div>
        </form>
      </div>

      <?php if (!empty($keyword)): ?>
        <?php if (empty($hasil_pesanan)): ?>
          <div class="alert alert-warning text-center rounded-4 p-4 shadow-sm">
            <i class="bi bi-exclamation-triangle fs-3 text-warning d-block mb-2"></i>
            <h5 class="fw-bold">Pesanan Tidak Ditemukan</h5>
            <p class="text-muted mb-0">Tidak ada transaksi yang cocok dengan kata kunci "<strong><?= htmlspecialchars($keyword) ?></strong>". Pastikan kode pesanan atau nomor telepon yang dimasukkan sudah sesuai.</p>
          </div>
        <?php else: ?>
          <h5 class="fw-bold mb-3">Ditemukan <?= count($hasil_pesanan) ?> Transaksi:</h5>
          <div class="d-flex flex-column gap-3 mb-4">
            <?php foreach ($hasil_pesanan as $row): 
              $badge_color = 'bg-secondary';
              if ($row['status_pesanan'] === 'Menunggu Konfirmasi') $badge_color = 'bg-warning text-dark';
              elseif ($row['status_pesanan'] === 'Diproses') $badge_color = 'bg-info text-dark';
              elseif ($row['status_pesanan'] === 'Dikirim') $badge_color = 'bg-primary';
              elseif ($row['status_pesanan'] === 'Selesai') $badge_color = 'bg-success';
              elseif ($row['status_pesanan'] === 'Dibatalkan') $badge_color = 'bg-danger';
            ?>
              <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                  <div>
                    <span class="fw-bold font-monospace text-primary fs-6"><?= htmlspecialchars($row['kode_pesanan']) ?></span>
                    <span class="text-muted small ms-2">&bull; <?= date('d M Y, H:i', strtotime($row['tanggal_pesanan'])) ?> WIB</span>
                  </div>
                  <div>
                    <span class="badge <?= $badge_color ?> px-3 py-1 rounded-pill">
                      <?= htmlspecialchars($row['status_pesanan']) ?>
                    </span>
                  </div>
                </div>

                <div class="row g-2 small text-secondary">
                  <div class="col-md-6">
                    <div>Penerima: <strong><?= htmlspecialchars($row['nama_pembeli']) ?></strong> (<?= htmlspecialchars($row['no_hp']) ?>)</div>
                    <div>Metode: <?= htmlspecialchars($row['metode_pembayaran']) ?></div>
                  </div>
                  <div class="col-md-6 text-md-end">
                    <div class="fs-6 fw-bold text-dark">Total: <?= rupiah($row['total_pembayaran']) ?></div>
                    <div class="mt-2">
                      <a href="<?= base_url('transaksi-sukses.php?id=' . $row['id_pesanan']) ?>" class="btn btn-outline-primary btn-sm rounded-pill">
                        <i class="bi bi-receipt me-1"></i> Buka Nota Transaksi
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
