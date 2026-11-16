</main>

<footer>
  <div class="container">
    <div class="row g-4 mb-4">
      <div class="col-lg-4 col-md-6">
        <h5 class="text-white fw-bold mb-3 d-flex align-items-center">
          <i class="bi bi-phone text-warning me-2 fs-4"></i> Nanda Handphone
        </h5>
        <p class="small text-secondary mb-3">
          Marketplace terpercaya untuk belanja smartphone Android, iPhone resmi & second berkualitas, serta aksesoris gadget terlengkap dengan garansi aman.
        </p>
        <div class="d-flex gap-2">
          <a href="#" class="btn btn-sm btn-outline-secondary rounded-circle"><i class="bi bi-whatsapp"></i></a>
          <a href="#" class="btn btn-sm btn-outline-secondary rounded-circle"><i class="bi bi-instagram"></i></a>
          <a href="#" class="btn btn-sm btn-outline-secondary rounded-circle"><i class="bi bi-facebook"></i></a>
        </div>
      </div>

      <div class="col-lg-2 col-md-6">
        <h6 class="text-white fw-bold mb-3">Kategori Produk</h6>
        <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
          <li><a href="<?= base_url('index.php?kategori=android') ?>">Smartphone Android</a></li>
          <li><a href="<?= base_url('index.php?kategori=iphone') ?>">Apple iPhone (iOS)</a></li>
          <li><a href="<?= base_url('index.php?kategori=second') ?>">Handphone Second</a></li>
          <li><a href="<?= base_url('index.php?kategori=aksesoris') ?>">Aksesoris Gadget</a></li>
        </ul>
      </div>

      <div class="col-lg-3 col-md-6">
        <h6 class="text-white fw-bold mb-3">Layanan & Bantuan</h6>
        <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
          <li><a href="<?= base_url('keranjang.php') ?>">Keranjang Belanja</a></li>
          <li><a href="<?= base_url('index.php#katalog') ?>">Semua Produk</a></li>
          <li><a href="https://infinityfree.com" target="_blank" rel="noopener">Deployment InfinityFree</a></li>
        </ul>
      </div>

      <div class="col-lg-3 col-md-6">
        <h6 class="text-white fw-bold mb-3">Toko Fisik & Kontak</h6>
        <ul class="list-unstyled small text-secondary d-flex flex-column gap-2 mb-0">
          <li><i class="bi bi-geo-alt text-danger me-2"></i> Jl. Pemuda No. 45, Semarang Tengah</li>
          <li><i class="bi bi-whatsapp text-success me-2"></i> +62 812-3456-7890 (CS Nanda)</li>
          <li><i class="bi bi-envelope text-info me-2"></i> sales@nandahp.com</li>
          <li><i class="bi bi-clock text-warning me-2"></i> Buka Setiap Hari: 09.00 - 21.00 WIB</li>
        </ul>
      </div>
    </div>

    <hr class="border-secondary my-4">

    <div class="row align-items-center">
      <div class="col-md-6 text-center text-md-start mb-2 mb-md-0 small">
        &copy; <?= date('Y') ?> <strong>Nanda Handphone</strong>. All rights reserved.
      </div>
      <div class="col-md-6 text-center text-md-end small text-secondary">
        <span>Tugas Ujian Tengah Semester (UTS) Pemrograman Web &bull; Dosen: <strong>Pak Hary</strong></span>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?= base_url('assets/js/main.js') ?>"></script>

</body>
</html>
