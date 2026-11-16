<?php
require_once __DIR__ . '/config/database.php';

// Jika sudah login, redirect
if (isset($_SESSION['user_id'])) {
    header('Location: ' . base_url('index.php'));
    exit;
}

$errors = [];
$redirect = $_GET['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = bersihkan($koneksi, $_POST['nama_lengkap'] ?? '');
    $email        = bersihkan($koneksi, $_POST['email'] ?? '');
    $password     = $_POST['password'] ?? '';
    $konfirmasi   = $_POST['konfirmasi_password'] ?? '';
    $no_hp        = bersihkan($koneksi, $_POST['no_hp'] ?? '');
    $alamat       = bersihkan($koneksi, $_POST['alamat'] ?? '');

    // Validasi input
    if (empty($nama_lengkap)) $errors[] = 'Nama lengkap wajib diisi.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if (strlen($password) < 6) $errors[] = 'Password minimal terdiri dari 6 karakter.';
    if ($password !== $konfirmasi) $errors[] = 'Konfirmasi password tidak cocok.';
    if (empty($no_hp)) $errors[] = 'Nomor WhatsApp / HP wajib diisi.';
    if (empty($alamat)) $errors[] = 'Alamat lengkap wajib diisi untuk tujuan pengiriman barang.';

    // Cek apakah email sudah terdaftar
    if (empty($errors) && $db_connected) {
        $stmt_check = mysqli_prepare($koneksi, "SELECT id_pengguna FROM pengguna WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_check, "s", $email);
        mysqli_stmt_execute($stmt_check);
        $res_check = mysqli_stmt_get_result($stmt_check);
        if (mysqli_fetch_assoc($res_check)) {
            $errors[] = 'Email ini sudah terdaftar. Silakan gunakan email lain atau login.';
        }
    }

    // Simpan data pembeli baru
    if (empty($errors) && $db_connected) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'pembeli';

        $sql = "INSERT INTO pengguna (nama_lengkap, email, password, no_hp, alamat, role) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "ssssss", $nama_lengkap, $email, $password_hash, $no_hp, $alamat, $role);

        if (mysqli_stmt_execute($stmt)) {
            $new_user_id = mysqli_insert_id($koneksi);

            // Auto-login setelah registrasi sukses
            $_SESSION['user_id']     = $new_user_id;
            $_SESSION['user_nama']   = $nama_lengkap;
            $_SESSION['user_email']  = $email;
            $_SESSION['user_role']   = $role;
            $_SESSION['user_hp']     = $no_hp;
            $_SESSION['user_alamat'] = $alamat;

            $_SESSION['flash_success'] = 'Pendaftaran akun berhasil! Selamat datang di Nanda Handphone, <strong>' . htmlspecialchars($nama_lengkap) . '</strong>.';

            if (!empty($redirect)) {
                header('Location: ' . base_url($redirect));
            } else {
                header('Location: ' . base_url('index.php'));
            }
            exit;
        } else {
            $errors[] = 'Gagal mendaftarkan akun: ' . mysqli_error($koneksi);
        }
    }
}

$page_title = 'Daftar Akun Pelanggan Baru - Nanda Handphone';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
      
      <div class="card border-0 shadow-sm rounded-4 p-4">
        <div class="text-center mb-4">
          <span class="fs-1 text-success"><i class="bi bi-person-plus-fill"></i></span>
          <h3 class="fw-bold mt-2">Daftar Akun Pelanggan</h3>
          <p class="text-muted small">Buat akun untuk kemudahan belanja handphone daring, simpan alamat, dan pantau status pesanan.</p>
        </div>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger rounded-3 py-2 px-3 small mb-3">
            <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Pendaftaran belum berhasil:</h6>
            <ul class="mb-0 ps-3">
              <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form action="" method="POST">
          <div class="mb-3">
            <label for="nama_lengkap" class="form-label fw-semibold">Nama Lengkap Pembeli <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" placeholder="Contoh: Budi Santoso" value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>" required>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label for="email" class="form-label fw-semibold">Alamat Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="email" name="email" placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label for="no_hp" class="form-label fw-semibold">No. WhatsApp / HP <span class="text-danger">*</span></label>
              <input type="tel" class="form-control" id="no_hp" name="no_hp" placeholder="081234567890" value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>" required>
            </div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
              <input type="password" class="form-control" id="password" name="password" placeholder="Min. 6 karakter" required>
            </div>
            <div class="col-md-6">
              <label for="konfirmasi_password" class="form-label fw-semibold">Ulangi Password <span class="text-danger">*</span></label>
              <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ketik ulang password" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="alamat" class="form-label fw-semibold">Alamat Lengkap Pengiriman <span class="text-danger">*</span></label>
            <textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Alamat rumah/kantor untuk pengiriman smartphone..." required><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
            <div class="form-text small">Alamat ini akan otomatis digunakan saat Anda melakukan pembelian secara daring.</div>
          </div>

          <div class="d-grid gap-2 mt-4">
            <button type="submit" class="btn btn-success rounded-pill py-2 fw-bold">
              <i class="bi bi-check-circle me-1"></i> Buat Akun Pelanggan Sekarang
            </button>
          </div>
        </form>

        <hr class="my-4">

        <div class="text-center small text-muted">
          Sudah memiliki akun? <br>
          <a href="<?= base_url('login.php' . (!empty($redirect) ? '?redirect=' . urlencode($redirect) : '')) ?>" class="fw-bold text-primary text-decoration-none">
            Masuk ke Akun Anda di Sini &rarr;
          </a>
        </div>

      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
