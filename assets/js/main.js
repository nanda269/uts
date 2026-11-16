/**
 * Nanda Handphone - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
  // 1. Inisialisasi Tooltip Bootstrap jika ada
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // 2. Quantity Controller (+ and - buttons)
  const qtyWrappers = document.querySelectorAll('.qty-control');
  qtyWrappers.forEach(wrapper => {
    const btnMinus = wrapper.querySelector('.btn-qty-minus');
    const btnPlus = wrapper.querySelector('.btn-qty-plus');
    const input = wrapper.querySelector('input[type="number"]');

    if (btnMinus && btnPlus && input) {
      btnMinus.addEventListener('click', function () {
        let val = parseInt(input.value) || 1;
        const min = parseInt(input.getAttribute('min')) || 1;
        if (val > min) {
          input.value = val - 1;
          input.dispatchEvent(new Event('change'));
        }
      });

      btnPlus.addEventListener('click', function () {
        let val = parseInt(input.value) || 1;
        const max = parseInt(input.getAttribute('max')) || 999;
        if (val < max) {
          input.value = val + 1;
          input.dispatchEvent(new Event('change'));
        }
      });
    }
  });

  // 3. Image Preview untuk Upload Produk di Form Admin
  const imgInput = document.getElementById('inputGambar');
  const imgPreview = document.getElementById('previewGambar');
  if (imgInput && imgPreview) {
    imgInput.addEventListener('change', function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          imgPreview.src = e.target.result;
          imgPreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // 4. Konfirmasi Penghapusan Data
  const deleteButtons = document.querySelectorAll('.confirm-delete');
  deleteButtons.forEach(btn => {
    btn.addEventListener('click', function (e) {
      const msg = this.getAttribute('data-confirm') || 'Apakah Anda yakin ingin menghapus data ini?';
      if (!confirm(msg)) {
        e.preventDefault();
      }
    });
  });
});
