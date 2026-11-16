<?php
require_once __DIR__ . '/config/database.php';

// Pastikan keranjang belanja tidak kosong
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['flash_warning'] = 'Keranjang belanja Anda masih kosong. Silakan pilih produk terlebih dahulu.';
    header('Location: ' . base_url('index.php'));
    exit;
}

// Hitung total belanja
$total_belanja = 0;
$total_qty = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_belanja += ($item['harga'] * $item['qty']);
    $total_qty += $item['qty'];
}

// Data pembeli jika sudah login
$val_nama   = $_SESSION['user_nama'] ?? '';
$val_hp     = $_SESSION['user_hp'] ?? '';
$val_alamat = $_SESSION['user_alamat'] ?? '';

$page_title = 'Checkout Pemesanan - Nanda Handphone';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= base_url('index.php') ?>">Beranda</a></li>
      <li class="breadcrumb-item"><a href="<?= base_url('keranjang.php') ?>">Keranjang Belanja</a></li>
      <li class="breadcrumb-item active" aria-current="page">Checkout Pesanan</li>
    </ol>
  </nav>

  <div class="mb-4">
    <h2 class="fw-bold"><i class="bi bi-shield-check text-primary me-2"></i> Form Pemesanan & Pembayaran Daring</h2>
    <p class="text-muted small">Lengkapi data penerima dan pilih metode pembayaran untuk menyelesaikan pesanan Anda.</p>
  </div>

  <?php if (!isset($_SESSION['user_id'])): ?>
    <div class="alert alert-info d-flex align-items-center justify-content-between rounded-4 p-3 mb-4 shadow-sm">
      <div class="d-flex align-items-center">
        <i class="bi bi-person-circle fs-3 me-3 text-primary"></i>
        <div>
          <span class="fw-bold d-block">Sudah memiliki Akun Pelanggan Nanda HP?</span>
          <span class="small text-muted">Masuk ke akun Anda agar data pengiriman terisi secara otomatis dan transaksi tercatat di riwayat belanja Anda.</span>
        </div>
      </div>
      <div>
        <a href="<?= base_url('login.php?redirect=checkout.php') ?>" class="btn btn-primary rounded-pill btn-sm px-3 fw-bold">
          Masuk Akun
        </a>
      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-success d-flex align-items-center rounded-4 p-3 mb-4 shadow-sm">
      <i class="bi bi-check-circle-fill fs-3 me-3 text-success"></i>
      <div>
        <span class="fw-bold d-block">Masuk sebagai Pelanggan: <?= htmlspecialchars($_SESSION['user_nama']) ?> (<?= htmlspecialchars($_SESSION['user_email']) ?>)</span>
        <span class="small text-muted">Data kontak dan alamat Anda otomatis terisi pada formulir di bawah ini.</span>
      </div>
    </div>
  <?php endif; ?>

  <form action="<?= base_url('proses-checkout.php') ?>" method="POST" id="formCheckout">
    <div class="row g-4">
      <!-- Kolom Data Pembeli -->
      <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
          <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">
            <i class="bi bi-person-lines-fill text-primary me-2"></i> 1. Informasi Data Pembeli
          </h5>

          <div class="mb-3">
            <label for="nama_pembeli" class="form-label fw-semibold">Nama Lengkap Penerima <span class="text-danger">*</span></label>
            <input type="text" class="form-control rounded-3 py-2" id="nama_pembeli" name="nama_pembeli" placeholder="Contoh: Budi Santoso" value="<?= htmlspecialchars($val_nama) ?>" required>
          </div>

          <div class="mb-3">
            <label for="no_hp" class="form-label fw-semibold">Nomor WhatsApp / HP Aktif <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="bi bi-whatsapp text-success"></i></span>
              <input type="tel" class="form-control rounded-end-3 py-2" id="no_hp" name="no_hp" placeholder="081234567890" value="<?= htmlspecialchars($val_hp) ?>" required>
            </div>
            <div class="form-text small">Nomor ini akan digunakan untuk konfirmasi status pengiriman pesanan.</div>
          </div>

          <div class="mb-3">
            <label for="alamat_pengiriman" class="form-label fw-semibold">Alamat Lengkap Pengiriman <span class="text-danger">*</span></label>
            <textarea class="form-control rounded-3" id="alamat_pengiriman" name="alamat_pengiriman" rows="3" placeholder="Nama Jalan, No. Rumah, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten, Kode Pos" required><?= htmlspecialchars($val_alamat) ?></textarea>
          </div>

          <div class="mb-2">
            <label for="catatan" class="form-label fw-semibold">Catatan Khusus Pesanan (Opsional)</label>
            <textarea class="form-control rounded-3" id="catatan" name="catatan" rows="2" placeholder="Contoh: Mohon packing bubble wrap ekstra tebal / Antar jam siang"></textarea>
          </div>
        </div>

        <!-- Metode Pembayaran -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
          <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">
            <i class="bi bi-credit-card text-success me-2"></i> 2. Metode Pembayaran
          </h5>

          <div class="d-flex flex-column gap-3">
            <div class="form-check p-3 border rounded-3 bg-light">
              <input class="form-check-input ms-0 me-3" type="radio" name="metode_pembayaran" id="pay_bca" value="Transfer Bank BCA" checked>
              <label class="form-check-label w-100" for="pay_bca">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <strong class="d-block text-dark">Transfer Bank BCA</strong>
                    <span class="text-muted small">No. Rekening: <strong>123-456-7890</strong> a.n. Toko Nanda Handphone</span>
                  </div>
                  <span class="badge bg-primary">Bank Transfer</span>
                </div>
              </label>
            </div>

            <div class="form-check p-3 border rounded-3">
              <input class="form-check-input ms-0 me-3" type="radio" name="metode_pembayaran" id="pay_cod" value="COD (Bayar di Tempat)">
              <label class="form-check-label w-100" for="pay_cod">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <strong class="d-block text-dark">COD (Bayar di Tempat)</strong>
                    <span class="text-muted small">Bayar tunai ke kurir saat barang handphone sampai di lokasi Anda.</span>
                  </div>
                  <span class="badge bg-warning text-dark">COD</span>
                </div>
              </label>
            </div>

            <div class="form-check p-3 border rounded-3">
              <input class="form-check-input ms-0 me-3" type="radio" name="metode_pembayaran" id="pay_ewallet" value="E-Wallet (DANA / GoPay / ShopeePay)">
              <label class="form-check-label w-100" for="pay_ewallet">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <strong class="d-block text-dark">E-Wallet (DANA / QRIS / GoPay)</strong>
                    <span class="text-muted small">Scan QRIS instan atau transfer ke nomor akun e-wallet toko.</span>
                  </div>
                  <span class="badge bg-info text-dark">QRIS / E-Wallet</span>
                </div>
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- Kolom Rincian Pesanan -->
      <div class="col-lg-5">
        <div class="summary-box sticky-top" style="top: 90px;">
          <h5 class="fw-bold mb-3 border-bottom pb-2">Rincian Belanja (<?= $total_qty ?> Item)</h5>

          <div class="mb-3" style="max-height: 280px; overflow-y: auto;">
            <?php foreach ($_SESSION['cart'] as $id => $item): 
              $subtotal = $item['harga'] * $item['qty'];
              $img_src = base_url('assets/uploads/' . ($item['gambar'] ?: 'default-phone.png'));
            ?>
              <div class="d-flex align-items-center gap-3 mb-3 pb-2 border-bottom">
                <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($item['nama_produk']) ?>" class="rounded-3 border" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.onerror=null; this.src='<?= base_url('assets/uploads/default-phone.png') ?>';">
                <div class="flex-grow-1">
                  <div class="fw-semibold text-dark small text-truncate" style="max-width: 200px;">
                    <?= htmlspecialchars($item['nama_produk']) ?>
                  </div>
                  <div class="text-muted small">
                    <?= $item['qty'] ?> &times; <?= rupiah($item['harga']) ?>
                  </div>
                </div>
                <div class="fw-bold text-dark small">
                  <?= rupiah($subtotal) ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Subtotal Produk</span>
            <span class="fw-semibold"><?= rupiah($total_belanja) ?></span>
          </div>

          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Biaya Pengiriman</span>
            <span class="text-success fw-bold">GRATIS ONGKIR</span>
          </div>

          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Asuransi Kehilangan</span>
            <span class="text-success fw-bold">TERLINDUNGI</span>
          </div>

          <hr>

          <div class="d-flex justify-content-between align-items-baseline mb-4">
            <span class="fs-6 fw-bold">Total Pembayaran:</span>
            <span class="fs-4 fw-extrabold text-primary fw-bold"><?= rupiah($total_belanja) ?></span>
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
              <i class="bi bi-bag-check-fill me-2"></i> Konfirmasi & Buat Pesanan
            </button>
            <a href="<?= base_url('keranjang.php') ?>" class="btn btn-outline-secondary rounded-pill btn-sm">
              <i class="bi bi-arrow-left me-1"></i> Kembali ke Keranjang
            </a>
          </div>

          <div class="alert alert-secondary small mt-3 mb-0 rounded-3">
            <i class="bi bi-lock-fill text-muted me-1"></i> Data Anda aman. Dengan membuat pesanan, Anda menyetujui ketentuan toko Nanda Handphone.
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
