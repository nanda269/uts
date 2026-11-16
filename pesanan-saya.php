<?php
require_once __DIR__ . '/config/database.php';

// Pastikan pembeli sudah login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_info'] = 'Silakan login terlebih dahulu untuk melihat riwayat pesanan Anda.';
    header('Location: ' . base_url('login.php?redirect=pesanan-saya.php'));
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$user_hp = $_SESSION['user_hp'] ?? '';
$daftar_pesanan = [];

if ($db_connected) {
    // Ambil pesanan yang dimiliki user ini (berdasarkan id_pembeli atau no_hp)
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM pesanan WHERE id_pembeli = ? OR no_hp = ? ORDER BY id_pesanan DESC");
    mysqli_stmt_bind_param($stmt, "is", $user_id, $user_hp);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $daftar_pesanan[] = $row;
    }
}

$page_title = 'Riwayat Pesanan Saya - Nanda Handphone';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-4">
  <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
      <h2 class="fw-bold mb-1"><i class="bi bi-bag-check text-primary me-2"></i> Riwayat Pesanan Daring Saya</h2>
      <p class="text-muted small mb-0">Daftar seluruh transaksi pembelian handphone yang telah Anda lakukan di Nanda HP.</p>
    </div>
    <div class="mt-2 mt-md-0">
      <a href="<?= base_url('index.php#katalog') ?>" class="btn btn-outline-primary rounded-pill btn-sm">
        <i class="bi bi-cart-plus me-1"></i> Beli Handphone Lainnya
      </a>
    </div>
  </div>

  <?php if (empty($daftar_pesanan)): ?>
    <div class="card border-0 shadow-sm rounded-4 text-center py-5 my-4">
      <div class="card-body">
        <i class="bi bi-receipt text-muted" style="font-size: 4rem;"></i>
        <h4 class="fw-bold mt-3">Belum Ada Riwayat Pesanan</h4>
        <p class="text-muted mb-4">Anda belum pernah melakukan pembelian secara daring. Jelajahi katalog smartphone terbaik kami sekarang!</p>
        <a href="<?= base_url('index.php#katalog') ?>" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
          <i class="bi bi-shop me-1"></i> Mulai Belanja Daring
        </a>
      </div>
    </div>
  <?php else: ?>
    <div class="d-flex flex-column gap-3 mb-5">
      <?php foreach ($daftar_pesanan as $row): 
        $badge_color = 'bg-secondary';
        if ($row['status_pesanan'] === 'Menunggu Konfirmasi') $badge_color = 'bg-warning text-dark';
        elseif ($row['status_pesanan'] === 'Diproses') $badge_color = 'bg-info text-dark';
        elseif ($row['status_pesanan'] === 'Dikirim') $badge_color = 'bg-primary';
        elseif ($row['status_pesanan'] === 'Selesai') $badge_color = 'bg-success';
        elseif ($row['status_pesanan'] === 'Dibatalkan') $badge_color = 'bg-danger';

        // Ambil item ringkas untuk pesanan ini
        $items_str = [];
        $stmt_i = mysqli_prepare($koneksi, "SELECT p.nama_produk, d.jumlah FROM detail_pesanan d JOIN produk p ON d.id_produk = p.id_produk WHERE d.id_pesanan = ?");
        mysqli_stmt_bind_param($stmt_i, "i", $row['id_pesanan']);
        mysqli_stmt_execute($stmt_i);
        $res_i = mysqli_stmt_get_result($stmt_i);
        while ($item_row = mysqli_fetch_assoc($res_i)) {
            $items_str[] = htmlspecialchars($item_row['nama_produk']) . ' (' . $item_row['jumlah'] . ' unit)';
        }
      ?>
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-3 mb-3">
            <div>
              <span class="fw-bold font-monospace text-primary fs-6"><?= htmlspecialchars($row['kode_pesanan']) ?></span>
              <span class="text-muted small ms-2">&bull; <?= date('d M Y, H:i', strtotime($row['tanggal_pesanan'])) ?> WIB</span>
            </div>
            <div>
              <span class="badge <?= $badge_color ?> px-3 py-2 rounded-pill fs-6">
                <?= htmlspecialchars($row['status_pesanan']) ?>
              </span>
            </div>
          </div>

          <div class="row g-3 align-items-center">
            <div class="col-lg-7">
              <div class="small text-muted mb-1">Produk yang Dipesan:</div>
              <div class="fw-semibold text-dark mb-2">
                <?= implode(' &bull; ', $items_str) ?>
              </div>
              <div class="small text-secondary">
                <i class="bi bi-geo-alt me-1 text-danger"></i> Tujuan: <?= htmlspecialchars($row['alamat_pengiriman']) ?>
              </div>
              <div class="small text-secondary mt-1">
                <i class="bi bi-credit-card me-1 text-info"></i> Pembayaran: <?= htmlspecialchars($row['metode_pembayaran']) ?>
              </div>
            </div>

            <div class="col-lg-5 text-lg-end">
              <div class="text-muted small">Total Pembayaran:</div>
              <div class="fs-4 fw-extrabold text-primary fw-bold mb-3"><?= rupiah($row['total_pembayaran']) ?></div>

              <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                <a href="<?= base_url('transaksi-sukses.php?id=' . $row['id_pesanan']) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                  <i class="bi bi-receipt me-1"></i> Buka Nota / Invoice
                </a>
                <?php 
                  $wa_msg = "Halo Nanda HP, saya mau tanya pesanan daring nomor " . $row['kode_pesanan'] . " atas nama " . $row['nama_pembeli'];
                ?>
                <a href="https://wa.me/6281234567890?text=<?= urlencode($wa_msg) ?>" target="_blank" class="btn btn-outline-success btn-sm rounded-pill px-3">
                  <i class="bi bi-whatsapp me-1"></i> Chat Toko
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
