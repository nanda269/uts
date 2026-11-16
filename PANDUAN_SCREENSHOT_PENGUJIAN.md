# Panduan Pengambilan Screenshot Pengujian XAMPP & Postman
**Tugas**: Soal 12 Pengembangan Web Service / REST API Marketplace  
**Dosen**: Pak Hary  

Dokumen ini memandu Anda langkah demi langkah dalam mengambil screenshot pengujian untuk laporan tugas.

---

## 1. Persiapan Awal
1. Buka aplikasi **XAMPP Control Panel**.
2. Pastikan modul **Apache** dan **MySQL** berstatus **Running** (tombol berubah menjadi *Stop*, teks port berwarna hijau: Port 80, 443 untuk Apache dan Port 3306 untuk MySQL).
3. Buka aplikasi **Postman**.
4. Klik tombol **Import** di Postman (kiri atas) -> pilih file `postman_collection.json` dari folder project:
   `c:\xampp\htdocs\utspakhary\postman_collection.json`
5. Seluruh request pengujian (1 s/d 10) akan langsung otomatis muncul di Postman!

---

## 2. Daftar 6 Screenshot Wajib yang Perlu Diambil

### Screenshot 1: XAMPP Control Panel Berjalan Normal
- **Yang Ditampilkan**: Layar aplikasi **XAMPP Control Panel**.
- **Kondisi**:
  - Modul **Apache**: Hijau (Status: Running, Port 80, 443).
  - Modul **MySQL**: Hijau (Status: Running, Port 3306).
- **Tujuan**: Membuktikan bahwa server web lokal dan basis data MySQL aktif di localhost.

---

### Screenshot 2: Postman - GET Semua Produk (Status 200 OK)
- **Request di Postman**: Buka folder `REST API Marketplace Handphone` -> pilih `2. GET - Semua Produk Handphone`.
- **Method**: `GET`
- **URL**: `http://localhost/utspakhary/api/produk.php`
- **Aksi**: Klik tombol **Send**.
- **Yang Harus Terlihat di Screenshot**:
  - URL dan Method `GET http://localhost/utspakhary/api/produk.php`
  - Status Code: **`200 OK`** (di panel kanan response)
  - Waktu respons (Response Time) & Ukuran (Response Size)
  - Body JSON menampilkan array produk dengan key `"status": "success"` dan `"total": 10`.

---

### Screenshot 3: Postman - GET Detail Produk by ID (Status 200 OK)
- **Request di Postman**: Pilih `3. GET - Detail Produk by ID (Valid)`.
- **Method**: `GET`
- **URL**: `http://localhost/utspakhary/api/produk.php?id=1`
- **Aksi**: Klik tombol **Send**.
- **Yang Harus Terlihat di Screenshot**:
  - URL `http://localhost/utspakhary/api/produk.php?id=1`
  - Status Code: **`200 OK`**
  - Body JSON menampilkan detail produk tunggal `"Samsung Galaxy A55 5G"`.

---

### Screenshot 4: Postman - GET Semua Kategori Produk (Status 200 OK)
- **Request di Postman**: Pilih `6. GET - Semua Kategori Produk`.
- **Method**: `GET`
- **URL**: `http://localhost/utspakhary/api/kategori.php`
- **Aksi**: Klik tombol **Send**.
- **Yang Harus Terlihat di Screenshot**:
  - URL `http://localhost/utspakhary/api/kategori.php`
  - Status Code: **`200 OK`**
  - Body JSON menampilkan 4 kategori: Android, iPhone, Second, Aksesoris beserta `"total_produk"`.

---

### Screenshot 5: Postman - POST Pembuatan Pesanan Baru (Status 201 Created)
- **Request di Postman**: Pilih `7. POST - Buat Pesanan Baru (Valid)`.
- **Method**: `POST`
- **URL**: `http://localhost/utspakhary/api/pesanan.php`
- **Tab Headers**: `Content-Type: application/json`
- **Tab Body**: Mode `raw` -> format `JSON`:
```json
{
  "nama_pembeli": "Muhammad Nanda",
  "no_hp": "081234567890",
  "alamat_pengiriman": "Jl. Pemuda No. 45, Semarang Tengah",
  "metode_pembayaran": "Transfer Bank BCA",
  "catatan": "Mohon dipacking bubble wrap tebal warna hitam ya",
  "items": [
    {
      "id_produk": 1,
      "jumlah": 1
    },
    {
      "id_produk": 7,
      "jumlah": 2
    }
  ]
}
```
- **Aksi**: Klik tombol **Send**.
- **Yang Harus Terlihat di Screenshot**:
  - Method `POST` dan URL `http://localhost/utspakhary/api/pesanan.php`
  - Tab Body menampilkan input JSON
  - Status Code: **`201 Created`**
  - Body JSON response menampilkan `"kode_pesanan": "NHP-..."`, total bayar, dan rincian item dengan pengurangan sisa stok.

---

### Screenshot 6: Postman - Pengujian Error / Validasi (Status 400 atau 422)
- **Pilihan A (Stok Tidak Cukup)**:
  - Pilih request `9. POST - Buat Pesanan (Stok Melebihi Batas)`.
  - Klik **Send**.
  - Status Code: **`422 Unprocessable Entity`** dengan pesan error stok tidak mencukupi.
- **Pilihan B (Produk Tidak Ditemukan)**:
  - Pilih request `4. GET - Detail Produk by ID (Not Found)`.
  - URL: `http://localhost/utspakhary/api/produk.php?id=9999`.
  - Klik **Send**.
  - Status Code: **`404 Not Found`**.

---

### Screenshot Opsional (Nilai Tambah): phpMyAdmin
- Buka browser ke `http://localhost/phpmyadmin/`
- Pilih database `uts_nanda_hp` -> buka tabel `pesanan`
- Ambil screenshot yang menampilkan baris data pesanan yang baru saja dibuat dari Postman!
