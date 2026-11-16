<?php
require_once __DIR__ . '/../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pesanan = null;
$detail_list = [];

if ($db_connected && $id > 0) {
    // Ambil data pesanan
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM pesanan WHERE id_pesanan = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $pesanan = mysqli_fetch_assoc($res);

    if ($pesanan) {
        // Ambil rincian produk yang dibeli
        $stmt_det = mysqli_prepare($koneksi, "SELECT d.*, p.nama_produk, p.merk, p.gambar 
                                              FROM detail_pesanan d 
                                              JOIN produk p ON d.id_produk = p.id_produk 
                                              WHERE d.id_pesanan = ?");
        mysqli_stmt_bind_param($stmt_det, "i", $id);
        mysqli_stmt_execute($stmt_det);
        $res_det = mysqli_stmt_get_result($stmt_det);
        while ($d = mysqli_fetch_assoc($res_det)) {
            $detail_list[] = $d;
        }
    }
}

if (!$pesanan) {
    $_SESSION['flash_error'] = 'Pesanan tidak ditemukan.';
    header('Location: ' . base_url('admin/pesanan.php'));
    exit;
}

// Proses Update Status Pesanan (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $status_baru = bersihkan($koneksi, $_POST['status_pesanan'] ?? '');
    $status_lama = $pesanan['status_pesanan'];
    
    $allowed_status = ['Menunggu Konfirmasi', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];
    if (in_array($status_baru, $allowed_status) && $db_connected) {
        $stmt_up = mysqli_prepare($koneksi, "UPDATE pesanan SET status_pesanan = ? WHERE id_pesanan = ?");
        mysqli_stmt_bind_param($stmt_up, "si", $status_baru, $id);
        
        if (mysqli_stmt_execute($stmt_up)) {
            // Jika status diubah menjadi Dibatalkan dan sebelumnya bukan Dibatalkan, kembalikan stok
            if ($status_baru === 'Dibatalkan' && $status_lama !== 'Dibatalkan') {
                foreach ($detail_list as $item) {
                    $stmt_rstok = mysqli_prepare($koneksi, "UPDATE produk SET stok = stok + ? WHERE id_produk = ?");
                    mysqli_stmt_bind_param($stmt_rstok, "ii", $item['jumlah'], $item['id_produk']);
                    mysqli_stmt_execute($stmt_rstok);
                }
            }

            $_SESSION['flash_success'] = 'Status pesanan berhasil diperbarui menjadi: <strong>' . $status_baru . '</strong>';
            header('Location: ' . base_url('admin/pesanan-detail.php?id=' . $id));
            exit;
        }
    }
}

$page_title = 'Detail Pesanan ' . htmlspecialchars($pesanan['kode_pesanan']) . ' - Nanda Handphone';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <!-- Navigation Header -->
  <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
    <div>
      <h2 class="fw-bold mb-1"><i class="bi bi-receipt text-primary me-2"></i> Detail Transaksi Pesanan</h2>
      <p class="text-muted small mb-0 font-monospace">Nomor Invoice: <strong><?= htmlspecialchars($pesanan['kode_pesanan']) ?></strong></p>
    </div>
    <div class="mt-2 mt-md-0 d-flex gap-2">
      <a href="<?= base_url('admin/pesanan.php') ?>" class="btn btn-outline-secondary rounded-pill btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pesanan
      </a>
      <a href="<?= base_url('transaksi-sukses.php?id=' . $pesanan['id_pesanan']) ?>" target="_blank" class="btn btn-outline-primary rounded-pill btn-sm">
        <i class="bi bi-printer me-1"></i> Cetak Struk
      </a>
    </div>
  </div>

  <div class="row g-4 mb-5">
    <!-- Kolom Rincian Pesanan & Produk -->
    <div class="col-lg-8">
      <!-- Info Pembeli -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">
          <i class="bi bi-person-circle text-primary me-2"></i> Informasi Pembeli & Pengiriman
        </h5>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="text-muted small">Nama Pembeli:</div>
            <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($pesanan['nama_pembeli']) ?></div>
            
            <div class="text-muted small mt-2">Nomor Telepon / WhatsApp:</div>
            <div>
              <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $pesanan['no_hp']) ?>" target="_blank" class="text-success fw-semibold">
                <i class="bi bi-whatsapp me-1"></i><?= htmlspecialchars($pesanan['no_hp']) ?>
              </a>
            </div>
          </div>
          <div class="col-md-6">
            <div class="text-muted small">Tanggal Transaksi:</div>
            <div class="fw-bold text-dark"><?= date('d F Y, H:i', strtotime($pesanan['tanggal_pesanan'])) ?> WIB</div>

            <div class="text-muted small mt-2">Metode Pembayaran:</div>
            <div class="badge bg-light text-dark border"><?= htmlspecialchars($pesanan['metode_pembayaran']) ?></div>
          </div>
          <div class="col-12 mt-3">
            <div class="text-muted small">Alamat Lengkap Pengiriman:</div>
            <div class="p-3 bg-light rounded-3 text-secondary" style="white-space: pre-line;"><?= htmlspecialchars($pesanan['alamat_pengiriman']) ?></div>
          </div>
          <?php if (!empty($pesanan['catatan'])): ?>
            <div class="col-12">
              <div class="text-muted small">Catatan Pembeli:</div>
              <div class="p-2 bg-warning-subtle text-dark rounded-3 small fst-italic">"<?= htmlspecialchars($pesanan['catatan']) ?>"</div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Tabel Produk yang Dipesan -->
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-4">
          <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-box-seam text-success me-2"></i> Item Handphone yang Dipesan</h5>
        </div>
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4">Produk</th>
                <th>Harga Satuan</th>
                <th class="text-center">Jumlah</th>
                <th class="text-end pe-4">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($detail_list as $det): 
                $img_src = base_url('assets/uploads/' . ($det['gambar'] ?: 'default-phone.png'));
              ?>
                <tr>
                  <td class="ps-4">
                    <div class="d-flex align-items-center gap-3">
                      <img src="<?= $img_src ?>" alt="" class="rounded-3 border" style="width: 48px; height: 48px; object-fit: cover;" onerror="this.onerror=null; this.src='<?= base_url('assets/uploads/default-phone.png') ?>';">
                      <div>
                        <div class="fw-bold text-dark"><?= htmlspecialchars($det['nama_produk']) ?></div>
                        <div class="text-muted small">Merk: <?= htmlspecialchars($det['merk']) ?></div>
                      </div>
                    </div>
                  </td>
                  <td><?= rupiah($det['harga_satuan']) ?></td>
                  <td class="text-center fw-semibold"><?= $det['jumlah'] ?> unit</td>
                  <td class="text-end pe-4 fw-bold text-primary"><?= rupiah($det['subtotal']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr class="table-light">
                <th colspan="3" class="text-end">Total Tagihan Transaksi:</th>
                <th class="text-end pe-4 fs-5 text-primary fw-bold"><?= rupiah($pesanan['total_pembayaran']) ?></th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <!-- Kolom Update Status Transaksi -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 90px;">
        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">
          <i class="bi bi-sliders text-warning me-2"></i> Kelola Status Pesanan
        </h5>

        <div class="mb-3">
          <span class="text-muted small d-block">Status Saat Ini:</span>
          <?php 
            $badge_color = 'bg-secondary';
            if ($pesanan['status_pesanan'] === 'Menunggu Konfirmasi') $badge_color = 'bg-warning text-dark';
            elseif ($pesanan['status_pesanan'] === 'Diproses') $badge_color = 'bg-info text-dark';
            elseif ($pesanan['status_pesanan'] === 'Dikirim') $badge_color = 'bg-primary';
            elseif ($pesanan['status_pesanan'] === 'Selesai') $badge_color = 'bg-success';
            elseif ($pesanan['status_pesanan'] === 'Dibatalkan') $badge_color = 'bg-danger';
          ?>
          <span class="badge <?= $badge_color ?> fs-6 px-3 py-2 rounded-pill mt-1">
            <?= htmlspecialchars($pesanan['status_pesanan']) ?>
          </span>
        </div>

        <form action="" method="POST">
          <input type="hidden" name="update_status" value="1">
          
          <div class="mb-3">
            <label for="status_pesanan" class="form-label fw-bold small">Ubah Status Menjadi:</label>
            <select name="status_pesanan" id="status_pesanan" class="form-select">
              <option value="Menunggu Konfirmasi" <?= ($pesanan['status_pesanan'] === 'Menunggu Konfirmasi') ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
              <option value="Diproses" <?= ($pesanan['status_pesanan'] === 'Diproses') ? 'selected' : '' ?>>Diproses (Packing Handphone)</option>
              <option value="Dikirim" <?= ($pesanan['status_pesanan'] === 'Dikirim') ? 'selected' : '' ?>>Dikirim (Menyerahkan ke Kurir)</option>
              <option value="Selesai" <?= ($pesanan['status_pesanan'] === 'Selesai') ? 'selected' : '' ?>>Selesai (Barang Diterima)</option>
              <option value="Dibatalkan" <?= ($pesanan['status_pesanan'] === 'Dibatalkan') ? 'selected' : '' ?>>Dibatalkan (Restock Barang)</option>
            </select>
          </div>

          <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold mb-3">
            <i class="bi bi-check2-circle me-1"></i> Perbarui Status
          </button>
        </form>

        <hr>

        <h6 class="fw-bold small text-muted text-uppercase mb-2">Aksi Cepat Penjual:</h6>
        <div class="d-grid gap-2">
          <?php 
            $chat_msg = "Halo " . $pesanan['nama_pembeli'] . ", pesanan Anda di Nanda Handphone dengan nomor invoice " . $pesanan['kode_pesanan'] . " saat ini berstatus: *" . $pesanan['status_pesanan'] . "*. Terima kasih telah berbelanja!";
            $chat_wa = "https://wa.me/" . preg_replace('/[^0-9]/', '', $pesanan['no_hp']) . "?text=" . urlencode($chat_msg);
          ?>
          <a href="<?= $chat_wa ?>" target="_blank" class="btn btn-outline-success rounded-pill btn-sm">
            <i class="bi bi-whatsapp me-1"></i> Kabari Pembeli via WA
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
