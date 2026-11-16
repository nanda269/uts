<?php
require_once __DIR__ . '/../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$produk = null;
$errors = [];
$kategori_list = [];

if ($db_connected && $id > 0) {
    // Ambil data kategori
    $res_kat = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
    while ($k = mysqli_fetch_assoc($res_kat)) {
        $kategori_list[] = $k;
    }

    // Ambil data produk yang akan diedit
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM produk WHERE id_produk = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $produk = mysqli_fetch_assoc($res);
}

if (!$produk) {
    $_SESSION['flash_error'] = 'Produk tidak ditemukan.';
    header('Location: ' . base_url('admin/produk.php'));
    exit;
}

// Proses Form Update (POST)
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

    // Handle Upload Gambar Baru (Jika ada)
    $gambar_nama = $produk['gambar']; // Default pakai gambar lama
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
                if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                    // Hapus gambar lama jika bukan default
                    if ($produk['gambar'] !== 'default-phone.png' && file_exists($upload_dir . $produk['gambar'])) {
                        @unlink($upload_dir . $produk['gambar']);
                    }
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

    // Update ke Database
    if (empty($errors) && $db_connected) {
        $sql = "UPDATE `produk` SET 
                `id_kategori` = ?, 
                `nama_produk` = ?, 
                `merk` = ?, 
                `harga` = ?, 
                `stok` = ?, 
                `kondisi` = ?, 
                `garansi` = ?, 
                `deskripsi` = ?, 
                `gambar` = ? 
                WHERE `id_produk` = ?";
        
        $stmt_up = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt_up, "issdissssi", $id_kategori, $nama_produk, $merk, $harga, $stok, $kondisi, $garansi, $deskripsi, $gambar_nama, $id);
        
        if (mysqli_stmt_execute($stmt_up)) {
            $_SESSION['flash_success'] = 'Data produk <strong>' . htmlspecialchars($nama_produk) . '</strong> berhasil diperbarui!';
            header('Location: ' . base_url('admin/produk.php'));
            exit;
        } else {
            $errors[] = 'Gagal memperbarui data: ' . mysqli_error($koneksi);
        }
    }
}

$page_title = 'Edit Produk: ' . htmlspecialchars($produk['nama_produk']) . ' - Nanda Handphone';
require_once __DIR__ . '/../includes/header.php';
$current_img_src = base_url('assets/uploads/' . ($produk['gambar'] ?: 'default-phone.png'));
?>

<div class="container my-4">
  <div class="row justify-content-center">
    <div class="col-lg-9">
      
      <!-- Page Navigation -->
      <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
          <h2 class="fw-bold mb-1"><i class="bi bi-pencil-square text-primary me-2"></i> Edit Data Produk</h2>
          <p class="text-muted small mb-0">Perbarui spesifikasi, harga, stok, atau foto produk.</p>
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

      <!-- Form Edit Produk -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-5">
        <form action="" method="POST" enctype="multipart/form-data">
          
          <div class="row g-3 mb-3">
            <div class="col-md-8">
              <label for="nama_produk" class="form-label fw-bold">Nama Produk Handphone <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="<?= htmlspecialchars($_POST['nama_produk'] ?? $produk['nama_produk']) ?>" required>
            </div>

            <div class="col-md-4">
              <label for="merk" class="form-label fw-bold">Merk / Brand <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="merk" name="merk" value="<?= htmlspecialchars($_POST['merk'] ?? $produk['merk']) ?>" required>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="id_kategori" class="form-label fw-bold">Kategori Produk <span class="text-danger">*</span></label>
              <select class="form-select" id="id_kategori" name="id_kategori" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($kategori_list as $k): 
                  $selected = (isset($_POST['id_kategori']) && $_POST['id_kategori'] == $k['id_kategori']) || (!isset($_POST['id_kategori']) && $produk['id_kategori'] == $k['id_kategori']);
                ?>
                  <option value="<?= $k['id_kategori'] ?>" <?= $selected ? 'selected' : '' ?>>
                    <?= htmlspecialchars($k['nama_kategori']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-3">
              <label for="kondisi" class="form-label fw-bold">Kondisi Fisik <span class="text-danger">*</span></label>
              <select class="form-select" id="kondisi" name="kondisi">
                <?php 
                  $current_kondisi = $_POST['kondisi'] ?? $produk['kondisi'];
                ?>
                <option value="Baru" <?= ($current_kondisi === 'Baru') ? 'selected' : '' ?>>Baru (Segel / Brand New)</option>
                <option value="Bekas" <?= ($current_kondisi === 'Bekas') ? 'selected' : '' ?>>Bekas (Second Mulus)</option>
              </select>
            </div>

            <div class="col-md-3">
              <label for="garansi" class="form-label fw-bold">Garansi</label>
              <input type="text" class="form-control" id="garansi" name="garansi" value="<?= htmlspecialchars($_POST['garansi'] ?? $produk['garansi']) ?>">
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="harga" class="form-label fw-bold">Harga Produk (Rp) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" class="form-control" id="harga" name="harga" min="1000" step="1000" value="<?= htmlspecialchars($_POST['harga'] ?? (int)$produk['harga']) ?>" required>
              </div>
            </div>

            <div class="col-md-6">
              <label for="stok" class="form-label fw-bold">Jumlah Stok (Unit) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="stok" name="stok" min="0" value="<?= htmlspecialchars($_POST['stok'] ?? $produk['stok']) ?>" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="deskripsi" class="form-label fw-bold">Deskripsi & Spesifikasi Produk</label>
            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5"><?= htmlspecialchars($_POST['deskripsi'] ?? $produk['deskripsi']) ?></textarea>
          </div>

          <div class="mb-4">
            <label for="inputGambar" class="form-label fw-bold">Ganti Foto Produk (Opsional)</label>
            <input class="form-control" type="file" id="inputGambar" name="gambar" accept="image/*">
            <div class="form-text small">Biarkan kosong jika tidak ingin mengubah foto saat ini. Format: JPG, PNG, WEBP, SVG (Maks. 3 MB).</div>
            
            <div class="d-flex align-items-center gap-3 mt-3">
              <div>
                <span class="d-block small text-muted mb-1">Foto Saat Ini:</span>
                <img src="<?= $current_img_src ?>" alt="Current Image" class="rounded-3 border p-1" style="width: 100px; height: 100px; object-fit: cover;">
              </div>
              <div>
                <span class="d-block small text-muted mb-1">Preview Foto Baru:</span>
                <img id="previewGambar" src="#" alt="Preview Foto" class="rounded-3 border p-1" style="display: none; width: 100px; height: 100px; object-fit: cover;">
              </div>
            </div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
              <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
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
