<?php
require_once __DIR__ . '/config/database.php';

// Inisialisasi Cart Session jika belum ada
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_GET['action'] ?? '';

// 1. Action: TAMBAH PRODUK KE KERANJANG
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk = isset($_POST['id_produk']) ? (int)$_POST['id_produk'] : 0;
    $qty = isset($_POST['qty']) ? max(1, (int)$_POST['qty']) : 1;
    $direct_buy = isset($_POST['direct_buy']) && $_POST['direct_buy'] == '1';

    if ($id_produk > 0 && $db_connected) {
        $stmt = mysqli_prepare($koneksi, "SELECT id_produk, nama_produk, merk, harga, stok, gambar FROM produk WHERE id_produk = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_produk);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $prod = mysqli_fetch_assoc($res);

        if ($prod) {
            if ($prod['stok'] <= 0) {
                $_SESSION['flash_error'] = 'Maaf, stok produk sedang habis.';
                header('Location: ' . base_url('detail.php?id=' . $id_produk));
                exit;
            }

            // Jika produk sudah ada di keranjang, tambahkan jumlahnya (maksimal sebatas stok)
            if (isset($_SESSION['cart'][$id_produk])) {
                $new_qty = $_SESSION['cart'][$id_produk]['qty'] + $qty;
                if ($new_qty > $prod['stok']) {
                    $new_qty = $prod['stok'];
                    $_SESSION['flash_info'] = 'Jumlah pesanan disesuaikan dengan sisa stok maksimal yang tersedia (' . $prod['stok'] . ' unit).';
                }
                $_SESSION['cart'][$id_produk]['qty'] = $new_qty;
            } else {
                if ($qty > $prod['stok']) {
                    $qty = $prod['stok'];
                }
                $_SESSION['cart'][$id_produk] = [
                    'id_produk'   => $prod['id_produk'],
                    'nama_produk' => $prod['nama_produk'],
                    'merk'        => $prod['merk'],
                    'harga'       => (float)$prod['harga'],
                    'stok'        => (int)$prod['stok'],
                    'gambar'      => $prod['gambar'],
                    'qty'         => $qty
                ];
            }

            if ($direct_buy) {
                header('Location: ' . base_url('checkout.php'));
                exit;
            }

            $_SESSION['flash_success'] = 'Produk <strong>' . htmlspecialchars($prod['nama_produk']) . '</strong> berhasil ditambahkan ke keranjang belanja!';
        } else {
            $_SESSION['flash_error'] = 'Produk tidak ditemukan.';
        }
    }
    header('Location: ' . base_url('keranjang.php'));
    exit;
}

// 2. Action: UPDATE JUMLAH (QTY)
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk = isset($_POST['id_produk']) ? (int)$_POST['id_produk'] : 0;
    $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

    if (isset($_SESSION['cart'][$id_produk])) {
        if ($qty <= 0) {
            unset($_SESSION['cart'][$id_produk]);
            $_SESSION['flash_info'] = 'Produk dihapus dari keranjang belanja.';
        } else {
            // Cek stok terkini dari database
            if ($db_connected) {
                $stmt = mysqli_prepare($koneksi, "SELECT stok FROM produk WHERE id_produk = ?");
                mysqli_stmt_bind_param($stmt, "i", $id_produk);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                $p = mysqli_fetch_assoc($res);
                $max_stok = $p ? (int)$p['stok'] : $_SESSION['cart'][$id_produk]['stok'];
            } else {
                $max_stok = $_SESSION['cart'][$id_produk]['stok'];
            }

            if ($qty > $max_stok) {
                $qty = $max_stok;
                $_SESSION['flash_info'] = 'Jumlah disesuaikan dengan batas stok maksimal (' . $max_stok . ').';
            }
            $_SESSION['cart'][$id_produk]['qty'] = $qty;
            $_SESSION['flash_success'] = 'Jumlah produk berhasil diperbarui.';
        }
    }
    header('Location: ' . base_url('keranjang.php'));
    exit;
}

// 3. Action: HAPUS SATU ITEM
if ($action === 'delete') {
    $id_produk = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (isset($_SESSION['cart'][$id_produk])) {
        $nama = $_SESSION['cart'][$id_produk]['nama_produk'];
        unset($_SESSION['cart'][$id_produk]);
        $_SESSION['flash_info'] = 'Produk <strong>' . htmlspecialchars($nama) . '</strong> telah dihapus dari keranjang.';
    }
    header('Location: ' . base_url('keranjang.php'));
    exit;
}

// 4. Action: KOSONGKAN KERANJANG
if ($action === 'clear') {
    $_SESSION['cart'] = [];
    $_SESSION['flash_info'] = 'Keranjang belanja telah dikosongkan.';
    header('Location: ' . base_url('keranjang.php'));
    exit;
}

// Hitung total harga
$total_belanja = 0;
$total_qty = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_belanja += ($item['harga'] * $item['qty']);
    $total_qty += $item['qty'];
}

$page_title = 'Keranjang Belanja - Nanda Handphone';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h2 class="fw-bold mb-1"><i class="bi bi-cart3 text-primary me-2"></i> Keranjang Belanja</h2>
      <p class="text-muted small mb-0">Periksa kembali barang pesanan Anda sebelum melanjutkan ke proses checkout.</p>
    </div>
    <?php if (!empty($_SESSION['cart'])): ?>
      <a href="<?= base_url('keranjang.php?action=clear') ?>" class="btn btn-outline-danger btn-sm rounded-pill confirm-delete" data-confirm="Apakah Anda yakin ingin mengosongkan seluruh isi keranjang belanja?">
        <i class="bi bi-trash3 me-1"></i> Kosongkan Keranjang
      </a>
    <?php endif; ?>
  </div>

  <?php if (empty($_SESSION['cart'])): ?>
    <div class="card border-0 shadow-sm text-center py-5 my-4 rounded-4">
      <div class="card-body">
        <div class="mb-3">
          <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
        </div>
        <h4 class="fw-bold">Keranjang Belanja Masih Kosong</h4>
        <p class="text-muted mb-4">Anda belum memasukkan produk handphone atau aksesoris ke dalam keranjang.</p>
        <a href="<?= base_url('index.php') ?>" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
          <i class="bi bi-arrow-left me-1"></i> Mulai Belanja Sekarang
        </a>
      </div>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <!-- Tabel Daftar Belanja -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
          <div class="table-responsive">
            <table class="table cart-table align-middle mb-0">
              <thead>
                <tr>
                  <th class="ps-4">Produk</th>
                  <th>Harga Satuan</th>
                  <th class="text-center" style="width: 170px;">Jumlah</th>
                  <th>Subtotal</th>
                  <th class="text-center" style="width: 50px;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($_SESSION['cart'] as $id => $item): 
                  $subtotal = $item['harga'] * $item['qty'];
                  $img_src = base_url('assets/uploads/' . ($item['gambar'] ?: 'default-phone.png'));
                ?>
                  <tr>
                    <td class="ps-4">
                      <div class="d-flex align-items-center gap-3">
                        <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($item['nama_produk']) ?>" class="cart-img" onerror="this.onerror=null; this.src='<?= base_url('assets/uploads/default-phone.png') ?>';">
                        <div>
                          <div class="badge bg-light text-secondary border mb-1"><?= htmlspecialchars($item['merk']) ?></div>
                          <div class="fw-bold text-dark">
                            <a href="<?= base_url('detail.php?id=' . $id) ?>" class="text-decoration-none text-dark">
                              <?= htmlspecialchars($item['nama_produk']) ?>
                            </a>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="fw-semibold text-secondary"><?= rupiah($item['harga']) ?></span>
                    </td>
                    <td>
                      <!-- Form Update Kuantitas -->
                      <form action="<?= base_url('keranjang.php?action=update') ?>" method="POST" class="d-flex justify-content-center">
                        <input type="hidden" name="id_produk" value="<?= $id ?>">
                        <div class="qty-control">
                          <button type="button" class="btn-qty-minus">-</button>
                          <input type="number" name="qty" value="<?= $item['qty'] ?>" min="1" max="<?= $item['stok'] ?>" onchange="this.form.submit()">
                          <button type="button" class="btn-qty-plus">+</button>
                        </div>
                      </form>
                    </td>
                    <td>
                      <span class="fw-bold text-primary"><?= rupiah($subtotal) ?></span>
                    </td>
                    <td class="text-center pe-3">
                      <a href="<?= base_url('keranjang.php?action=delete&id=' . $id) ?>" class="btn btn-outline-danger btn-sm rounded-circle p-1" title="Hapus produk ini" onclick="return confirm('Hapus item ini dari keranjang?')">
                        <i class="bi bi-trash"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-between">
            <a href="<?= base_url('index.php#katalog') ?>" class="btn btn-outline-secondary rounded-pill btn-sm">
              <i class="bi bi-arrow-left me-1"></i> Tambah Produk Lainnya
            </a>
            <span class="text-muted small">Total jenis produk: <strong><?= count($_SESSION['cart']) ?></strong> macam</span>
          </div>
        </div>
      </div>

      <!-- Ringkasan Belanja & Tombol Checkout -->
      <div class="col-lg-4">
        <div class="summary-box sticky-top" style="top: 90px;">
          <h5 class="fw-bold mb-3 border-bottom pb-2">Ringkasan Pesanan</h5>
          
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Total Jumlah Unit</span>
            <span class="fw-bold"><?= $total_qty ?> unit</span>
          </div>

          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Subtotal Produk</span>
            <span class="fw-bold"><?= rupiah($total_belanja) ?></span>
          </div>

          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Biaya Layanan</span>
            <span class="text-success fw-bold">GRATIS</span>
          </div>

          <div class="d-flex justify-content-between mb-3">
            <span class="text-muted">Ongkos Kirim</span>
            <span class="text-muted small">Dihitung saat checkout</span>
          </div>

          <hr>

          <div class="d-flex justify-content-between align-items-baseline mb-4">
            <span class="fs-6 fw-bold">Total Tagihan:</span>
            <span class="fs-4 fw-extrabold text-primary fw-bold"><?= rupiah($total_belanja) ?></span>
          </div>

          <div class="d-grid gap-2">
            <a href="<?= base_url('checkout.php') ?>" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
              Lanjut ke Checkout <i class="bi bi-arrow-right ms-1"></i>
            </a>
          </div>

          <div class="mt-4 p-3 bg-light rounded-3 text-secondary small">
            <div class="d-flex align-items-start gap-2 mb-2">
              <i class="bi bi-shield-check text-success fs-5"></i>
              <div>Transaksi aman dan bergaransi resmi toko Nanda HP.</div>
            </div>
            <div class="d-flex align-items-start gap-2">
              <i class="bi bi-cash-coin text-warning fs-5"></i>
              <div>Mendukung pembayaran COD (Bayar di Tempat) dan Transfer Bank.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
