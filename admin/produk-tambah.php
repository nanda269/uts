<?php
require_once __DIR__ . '/../config/database.php';

$errors = [];
$kategori_list = [];

if ($db_connected) {
    $res_kat = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
    while ($k = mysqli_fetch_assoc($res_kat)) {
        $kategori_list[] = $k;
    }
}

// Proses Form Submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = bersihkan($koneksi, $_POST['nama_produk'] ?? '');
    $merk        = bersihkan($koneksi, $_POST['merk'] ?? '');
    $id_kategori = (int)($_POST['id_kategori'] ?? 0);
    $harga       = (float)($_POST['harga'] ?? 0);
    $stok        = (int)($_POST['stok'] ?? 0);
    $kondisi     = bersihkan($koneksi, $_POST['kondisi'] ?? 'Baru');
    $garansi     = bersihkan($koneksi, $_POST['garansi'] ?? '');
    $deskripsi   = bersihkan($koneksi, $_POST['deskripsi'] ?? '');

    // Validasi
    if (empty($nama_produk)) $errors[] = 'Nama produk wajib diisi.';
    if (empty($merk)) $errors[] = 'Merk handphone wajib diisi.';
    if ($id_kategori <= 0) $errors[] = 'Silakan pilih kategori produk.';
    if ($harga <= 0) $errors[] = 'Harga produk harus lebih dari 0.';
    if ($stok < 0) $errors[] = 'Stok tidak boleh bernilai negatif.';

    // Handle Upload Gambar
    $gambar_nama = 'default-phone.png';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['gambar']['tmp_name'];
        $file_name = $_FILES['gambar']['name'];
        $file_size = $_FILES['gambar']['size'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
        if (in_array($file_ext, $allowed_ext)) {
            if ($file_size <= 3 * 1024 * 1024) { // Max 3MB
                $new_file_name = 'prod_' . time() . '_' . uniqid() . '.' . $file_ext;
                $upload_dir = __DIR__ . '/../assets/uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                    $gambar_nama = $new_file_name;
                } else {
                    $errors[] = 'Gagal menyimpan file gambar yang diunggah.';
                }
            } else {
                $errors[] = 'Ukuran gambar maksimal adalah 3 MB.';
            }
        } else {
            $errors[] = 'Format gambar hanya boleh berupa JPG, PNG, WEBP, atau SVG.';
        }
    }

    // Jika tidak ada error, simpan ke database
    if (empty($errors) && $db_connected) {
        $id_penjual = 1; // Default penjual Nanda Store
        $sql = "INSERT INTO `produk` 
                (`id_kategori`, `id_penjual`, `nama_produk`, `merk`, `harga`, `stok`, `kondisi`, `garansi`, `deskripsi`, `gambar`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "iissdissss", $id_kategori, $id_penjual, $nama_produk, $merk, $harga, $stok, $kondisi, $garansi, $deskripsi, $gambar_nama);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_success'] = 'Produk <strong>' . htmlspecialchars($nama_produk) . '</strong> berhasil ditambahkan ke katalog!';
            header('Location: ' . base_url('admin/produk.php'));
            exit;
        } else {
            $errors[] = 'Gagal menyimpan ke database: ' . mysqli_error($koneksi);
        }
    }
}

$page_title = 'Tambah Produk Baru - Nanda Handphone';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <div class="row justify-content-center">
    <div class="col-lg-9">
      
      <!-- Page Navigation -->
      <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
          <h2 class="fw-bold mb-1"><i class="bi bi-plus-circle text-primary me-2"></i> Tambah Produk Baru</h2>
          <p class="text-muted small mb-0">Isi formulir berikut untuk memasukkan handphone atau aksesoris baru ke marketplace.</p>
        </div>
        <a href="<?= base_url('admin/produk.php') ?>" class="btn btn-outline-secondary rounded-pill btn-sm">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger rounded-4 shadow-sm mb-4">
          <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i> Terdapat kesalahan pengisian data:</h6>
          <ul class="mb-0 ps-3">
            <?php foreach ($errors as $err): ?>
              <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <!-- Form Tambah Produk -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-5">
        <form action="" method="POST" enctype="multipart/form-data">
          
          <div class="row g-3 mb-3">
            <div class="col-md-8">
              <label for="nama_produk" class="form-label fw-bold">Nama Produk Handphone <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="nama_produk" name="nama_produk" placeholder="Contoh: Samsung Galaxy S24 Ultra (12GB/512GB)" value="<?= htmlspecialchars($_POST['nama_produk'] ?? '') ?>" required>
            </div>

            <div class="col-md-4">
              <label for="merk" class="form-label fw-bold">Merk / Brand <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="merk" name="merk" placeholder="Contoh: Samsung, Apple, Xiaomi" value="<?= htmlspecialchars($_POST['merk'] ?? '') ?>" required>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="id_kategori" class="form-label fw-bold">Kategori Produk <span class="text-danger">*</span></label>
              <select class="form-select" id="id_kategori" name="id_kategori" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($kategori_list as $k): ?>
                  <option value="<?= $k['id_kategori'] ?>" <?= (isset($_POST['id_kategori']) && $_POST['id_kategori'] == $k['id_kategori']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($k['nama_kategori']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-3">
              <label for="kondisi" class="form-label fw-bold">Kondisi Fisik <span class="text-danger">*</span></label>
              <select class="form-select" id="kondisi" name="kondisi">
                <option value="Baru" <?= (isset($_POST['kondisi']) && $_POST['kondisi'] === 'Baru') ? 'selected' : '' ?>>Baru (Segel / Brand New)</option>
                <option value="Bekas" <?= (isset($_POST['kondisi']) && $_POST['kondisi'] === 'Bekas') ? 'selected' : '' ?>>Bekas (Second Mulus)</option>
              </select>
            </div>

            <div class="col-md-3">
              <label for="garansi" class="form-label fw-bold">Garansi</label>
              <input type="text" class="form-control" id="garansi" name="garansi" placeholder="1 Tahun Resmi SEIN" value="<?= htmlspecialchars($_POST['garansi'] ?? '1 Tahun Resmi') ?>">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="harga" class="form-label fw-bold">Harga Produk (Rp) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" class="form-control" id="harga" name="harga" placeholder="Contoh: 5999000" min="1000" step="1000" value="<?= htmlspecialchars($_POST['harga'] ?? '') ?>" required>
              </div>
            </div>

            <div class="col-md-6">
              <label for="stok" class="form-label fw-bold">Jumlah Stok (Unit) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="stok" name="stok" placeholder="Contoh: 10" min="0" value="<?= htmlspecialchars($_POST['stok'] ?? '10') ?>" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="deskripsi" class="form-label fw-bold">Deskripsi & Spesifikasi Produk</label>
            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" placeholder="Tuliskan spesifikasi lengkap (Chipset, RAM, Layar, Kamera, Baterai, Kelengkapan dus/charger)..."><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
          </div>

          <div class="mb-4">
            <label for="inputGambar" class="form-label fw-bold">Foto Produk</label>
            <input class="form-control" type="file" id="inputGambar" name="gambar" accept="image/*">
            <div class="form-text small">Format didukung: JPG, PNG, WEBP, SVG (Maks. 3 MB). Jika dikosongkan, gambar placeholder default akan otomatis digunakan.</div>
            
            <div class="mt-3">
              <img id="previewGambar" src="#" alt="Preview Foto" class="rounded-3 border p-1" style="display: none; max-width: 150px; max-height: 150px; object-fit: cover;">
            </div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
              <i class="bi bi-save me-1"></i> Simpan Produk
            </button>
            <a href="<?= base_url('admin/produk.php') ?>" class="btn btn-outline-secondary rounded-pill px-4">
              Batal
            </a>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
