<?php
require_once __DIR__ . '/config/database.php';

$id_pesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$kode_pesanan = isset($_GET['kode']) ? bersihkan($koneksi, $_GET['kode']) : '';

$pesanan = null;
$detail_list = [];

if ($db_connected && ($id_pesanan > 0 || !empty($kode_pesanan))) {
    if ($id_pesanan > 0) {
        $stmt = mysqli_prepare($koneksi, "SELECT * FROM pesanan WHERE id_pesanan = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_pesanan);
    } else {
        $stmt = mysqli_prepare($koneksi, "SELECT * FROM pesanan WHERE kode_pesanan = ?");
        mysqli_stmt_bind_param($stmt, "s", $kode_pesanan);
    }

    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $pesanan = mysqli_fetch_assoc($res);

    if ($pesanan) {
        $stmt_det = mysqli_prepare($koneksi, "SELECT d.*, p.nama_produk, p.merk, p.gambar 
                                              FROM detail_pesanan d 
                                              JOIN produk p ON d.id_produk = p.id_produk 
                                              WHERE d.id_pesanan = ?");
        mysqli_stmt_bind_param($stmt_det, "i", $pesanan['id_pesanan']);
        mysqli_stmt_execute($stmt_det);
        $res_det = mysqli_stmt_get_result($stmt_det);
        while ($d = mysqli_fetch_assoc($res_det)) {
            $detail_list[] = $d;
        }
    }
}

if (!$pesanan) {
    $_SESSION['flash_error'] = 'Data transaksi pesanan tidak ditemukan.';
    header('Location: ' . base_url('index.php'));
    exit;
}

$page_title = 'Nota Transaksi ' . htmlspecialchars($pesanan['kode_pesanan']) . ' - Nanda Handphone';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-4">
  <div class="row justify-content-center">
    <div class="col-lg-9">
      
      <!-- Alert Sukses Pemesanan (Hanya tampil di layar, tersembunyi saat cetak) -->
      <div class="alert alert-success d-flex align-items-center rounded-4 shadow-sm p-3 mb-4 no-print">
        <i class="bi bi-check-circle-fill fs-2 me-3 text-success"></i>
        <div>
          <h5 class="alert-heading fw-bold mb-1">Pesanan Anda Berhasil Dibuat!</h5>
          <p class="mb-0 small">Terima kasih atas pesanan Anda. Kami telah mencatat transaksi Anda dengan kode pesanan <strong><?= htmlspecialchars($pesanan['kode_pesanan']) ?></strong>.</p>
        </div>
      </div>

      <!-- Tombol Aksi (Print & WA) -->
      <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="<?= base_url('index.php') ?>" class="btn btn-outline-secondary rounded-pill btn-sm">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
        </a>
        <div class="d-flex gap-2">
          <button onclick="window.print()" class="btn btn-outline-primary rounded-pill btn-sm">
            <i class="bi bi-printer me-1"></i> Cetak Struk / Invoice
          </button>
          <?php 
            $wa_text = "Halo Nanda Handphone, saya ingin konfirmasi pesanan dengan nomor invoice " . $pesanan['kode_pesanan'] . " atas nama " . $pesanan['nama_pembeli'] . " total " . rupiah($pesanan['total_pembayaran']);
            $wa_url = "https://wa.me/6281234567890?text=" . urlencode($wa_text);
          ?>
          <a href="<?= $wa_url ?>" target="_blank" class="btn btn-success rounded-pill btn-sm">
            <i class="bi bi-whatsapp me-1"></i> Konfirmasi ke WhatsApp Toko
          </a>
        </div>
      </div>

      <!-- Kartu Nota / Invoice -->
      <div class="invoice-card mb-5">
        <div class="invoice-header">
          <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
              <span class="badge bg-warning text-dark px-3 py-1 rounded-pill mb-2 fw-bold">INVOICE PEMESANAN RESMI</span>
              <h2 class="fw-bold mb-0 text-white"><i class="bi bi-phone"></i> Nanda Handphone</h2>
              <div class="small opacity-75">Marketplace Smartphone & Aksesoris Terpercaya</div>
              <div class="small opacity-75">Jl. Pemuda No. 45 Semarang &bull; 0812-3456-7890</div>
            </div>
            <div class="col-md-6 text-center text-md-end">
              <div class="small opacity-75">Kode Pesanan:</div>
              <h4 class="fw-bold text-warning mb-1 font-monospace"><?= htmlspecialchars($pesanan['kode_pesanan']) ?></h4>
              <div class="small opacity-75">Tanggal: <?= date('d M Y, H:i', strtotime($pesanan['tanggal_pesanan'])) ?> WIB</div>
              <div class="mt-2">
                <span class="badge bg-light text-dark px-3 py-1 rounded-pill fw-bold">
                  Status: <?= htmlspecialchars($pesanan['status_pesanan']) ?>
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="p-4 p-md-5">
          <!-- Info Pembeli & Pengiriman -->
          <div class="row g-4 mb-4 pb-4 border-bottom">
            <div class="col-md-6">
              <h6 class="text-muted text-uppercase fw-bold small mb-2">Tujuan Pengiriman:</h6>
              <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($pesanan['nama_pembeli']) ?></h5>
              <div class="text-secondary small mb-1"><i class="bi bi-whatsapp text-success me-1"></i> <?= htmlspecialchars($pesanan['no_hp']) ?></div>
              <div class="text-secondary small" style="white-space: pre-line;"><?= htmlspecialchars($pesanan['alamat_pengiriman']) ?></div>
            </div>
            <div class="col-md-6">
              <h6 class="text-muted text-uppercase fw-bold small mb-2">Informasi Pembayaran:</h6>
              <div class="mb-1">
                <span class="text-muted small">Metode:</span>
                <strong class="text-dark small d-block"><?= htmlspecialchars($pesanan['metode_pembayaran']) ?></strong>
              </div>
              <?php if (!empty($pesanan['catatan'])): ?>
                <div class="mt-2">
                  <span class="text-muted small">Catatan Pembeli:</span>
                  <div class="p-2 bg-light rounded small text-secondary fst-italic">"<?= htmlspecialchars($pesanan['catatan']) ?>"</div>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Rincian Produk -->
          <h6 class="text-muted text-uppercase fw-bold small mb-3">Rincian Barang Pesanan:</h6>
          <div class="table-responsive mb-4">
            <table class="table table-bordered">
              <thead class="table-light">
                <tr>
                  <th style="width: 50px;" class="text-center">No</th>
                  <th>Nama Produk Handphone / Aksesoris</th>
                  <th class="text-end" style="width: 150px;">Harga Satuan</th>
                  <th class="text-center" style="width: 80px;">Qty</th>
                  <th class="text-end" style="width: 160px;">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $no = 1;
                foreach ($detail_list as $det): 
                ?>
                  <tr>
                    <td class="text-center text-muted"><?= $no++ ?></td>
                    <td>
                      <div class="fw-bold text-dark"><?= htmlspecialchars($det['nama_produk']) ?></div>
                      <div class="text-muted small">Merk: <?= htmlspecialchars($det['merk']) ?></div>
                    </td>
                    <td class="text-end"><?= rupiah($det['harga_satuan']) ?></td>
                    <td class="text-center"><?= $det['jumlah'] ?></td>
                    <td class="text-end fw-semibold"><?= rupiah($det['subtotal']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr>
                  <th colspan="4" class="text-end">Total Pembayaran:</th>
                  <th class="text-end fs-5 text-primary fw-bold"><?= rupiah($pesanan['total_pembayaran']) ?></th>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Panduan Pembayaran Sesuai Metode -->
          <div class="p-4 bg-light rounded-4 border">
            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle-fill text-primary me-2"></i> Petunjuk Pembayaran & Pengiriman:</h6>
            <?php if (strpos($pesanan['metode_pembayaran'], 'BCA') !== false): ?>
              <p class="small text-secondary mb-2">Silakan transfer tepat sejumlah <strong><?= rupiah($pesanan['total_pembayaran']) ?></strong> ke rekening resmi toko kami:</p>
              <div class="d-inline-block bg-white p-2 px-3 border rounded-3 fw-bold font-monospace text-primary mb-2">
                Bank BCA: 123-456-7890 (a.n. Nanda Handphone)
              </div>
              <p class="small text-muted mb-0">Setelah transfer, mohon kirim bukti transfer ke WhatsApp admin kami untuk mempercepat proses pengiriman.</p>
            <?php elseif (strpos($pesanan['metode_pembayaran'], 'COD') !== false): ?>
              <p class="small text-secondary mb-0">Pesanan akan dipacking dan dikirim ke alamat Anda. Siapkan uang pas sebesar <strong><?= rupiah($pesanan['total_pembayaran']) ?></strong> saat kurir menyerahkan paket barang.</p>
            <?php else: ?>
              <p class="small text-secondary mb-0">Silakan selesaikan pembayaran sesuai total tagihan <strong><?= rupiah($pesanan['total_pembayaran']) ?></strong> melalui rekening e-wallet / QRIS toko dan konfirmasikan ke admin kami.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
