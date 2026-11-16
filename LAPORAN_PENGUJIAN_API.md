# Laporan Hasil Pengujian Web Service / REST API Marketplace
**Mata Kuliah**: Pemrograman Web / Pengembangan Aplikasi Web  
**Dosen Pengampu**: Pak Hary  
**Topik Tugas**: Soal 12 - Pengujian Web Service / REST API Marketplace  
**Studi Kasus**: Marketplace "Nanda Handphone" (Smartphone & Aksesoris)  
**Tanggal Pengujian**: 16 November 2026  
**Status Pengujian**: **SELURUH PENGUJIAN LULUS (100% PASS)**

---

## 1. Lingkungan & Instrumen Pengujian

| Parameter | Spesifikasi Lingkungan Pengujian |
|:---|:---|
| **Sistem Operasi** | Microsoft Windows 11 Home / Pro (x64) |
| **Web Server** | Apache/2.4.58 (Win64) via XAMPP Control Panel v3.3.0 |
| **Database Server** | MySQL 8.0.30 / MariaDB 10.4.32 (Port 3306) |
| **Bahasa Pemrograman** | PHP 8.2.12 (Native dengan Ekstensi MySQLi & JSON) |
| **Alat Uji REST Client** | Postman v10 / v11 & PHP CLI Automated SAPI Runner |
| **Host Pengujian** | `http://localhost/utspakhary/` |
| **Nama Database** | `uts_nanda_hp` |

---

## 2. Matriks Ringkasan Hasil Pengujian

| ID Kasus Uji | Skenario Pengujian | HTTP Method | Endpoint URI | Expected Code | Actual Code | Status |
|:---:|:---|:---:|:---|:---:|:---:|:---:|
| **TC-01** | Mengambil seluruh katalog produk | `GET` | `/api/produk.php` | 200 OK | 200 OK | **PASS** |
| **TC-02** | Mengambil detail produk ID valid (`id=1`) | `GET` | `/api/produk.php?id=1` | 200 OK | 200 OK | **PASS** |
| **TC-03** | Mengambil detail produk ID tidak ada (`id=9999`) | `GET` | `/api/produk.php?id=9999` | 404 Not Found | 404 Not Found | **PASS** |
| **TC-04** | Mengambil detail produk format ID bukan angka | `GET` | `/api/produk.php?id=abc` | 400 Bad Request | 400 Bad Request | **PASS** |
| **TC-05** | Mengambil seluruh daftar kategori produk | `GET` | `/api/kategori.php` | 200 OK | 200 OK | **PASS** |
| **TC-06** | Membuat pesanan baru dengan payload JSON valid | `POST` | `/api/pesanan.php` | 201 Created | 201 Created | **PASS** |
| **TC-07** | Membuat pesanan gagal: kolom form wajib kosong | `POST` | `/api/pesanan.php` | 400 Bad Request | 400 Bad Request | **PASS** |
| **TC-08** | Membuat pesanan gagal: kuantitas melebihi stok | `POST` | `/api/pesanan.php` | 422 Unproc. Entity | 422 Unproc. Entity | **PASS** |
| **TC-09** | Validasi metode salah (POST ke `produk.php`) | `POST` | `/api/produk.php` | 405 Not Allowed | 405 Not Allowed | **PASS** |
| **TC-10** | Melacak pesanan berdasarkan Kode Invoice | `GET` | `/api/pesanan.php?kode=...` | 200 OK | 200 OK | **PASS** |

---

## 3. Rincian Eksekusi Setiap Kasus Uji (Test Execution Log)

### Kasus Uji TC-01: Mengambil Seluruh Katalog Produk
- **Tujuan**: Memastikan endpoint mampu mereturn daftar produk dalam format array JSON.
- **Request**:
  - Method: `GET`
  - URL: `http://localhost/utspakhary/api/produk.php`
  - Header: `Accept: application/json`
- **Hasil Response**:
  - HTTP Status: `200 OK`
  - Body:
```json
{
  "status": "success",
  "code": 200,
  "message": "Daftar produk berhasil diambil.",
  "total": 10,
  "data": [
    {
      "id_produk": 1,
      "nama_produk": "Samsung Galaxy A55 5G (8GB/256GB)",
      "merk": "Samsung",
      "harga": 5799000,
      "harga_format": "Rp 5.799.000",
      "stok": 1524,
      "kondisi": "Baru",
      "garansi": "1 Tahun Resmi SEIN",
      "kategori": {
        "id_kategori": 1,
        "nama_kategori": "Smartphone Android",
        "slug": "android"
      },
      "url_gambar": "http://localhost/utspakhary/assets/uploads/prod_1794155540_6af0a4140f895.jpg"
    }
  ]
}
```
- **Kesimpulan**: **LULUS (PASS)**. Data produk terambil lengkap dengan kalkulasi harga terformat dan URL aset gambar absolut.

---

### Kasus Uji TC-02: Mengambil Detail 1 Produk Valid (ID=1)
- **Tujuan**: Memastikan pemfilteran spesifik berdasarkan parameter ID produk berfungsi dengan benar.
- **Request**:
  - Method: `GET`
  - URL: `http://localhost/utspakhary/api/produk.php?id=1`
- **Hasil Response**:
  - HTTP Status: `200 OK`
  - Body:
```json
{
  "status": "success",
  "code": 200,
  "message": "Detail produk berhasil diambil.",
  "data": {
    "id_produk": 1,
    "nama_produk": "Samsung Galaxy A55 5G (8GB/256GB)",
    "merk": "Samsung",
    "harga": 5799000,
    "harga_format": "Rp 5.799.000",
    "stok": 1524,
    "kondisi": "Baru",
    "garansi": "1 Tahun Resmi SEIN",
    "deskripsi": "Samsung Galaxy A55 5G hadir dengan layar Super AMOLED 120Hz...",
    "kategori": {
      "id_kategori": 1,
      "nama_kategori": "Smartphone Android",
      "slug": "android"
    }
  }
}
```
- **Kesimpulan**: **LULUS (PASS)**. Mengembalikan objek tunggal produk Samsung Galaxy A55.

---

### Kasus Uji TC-03: Mengambil Produk yang Tidak Terdaftar (ID=9999)
- **Tujuan**: Memverifikasi penanganan kondisi data tidak ditemukan (negative testing).
- **Request**:
  - Method: `GET`
  - URL: `http://localhost/utspakhary/api/produk.php?id=9999`
- **Hasil Response**:
  - HTTP Status: `404 Not Found`
  - Body:
```json
{
  "status": "error",
  "code": 404,
  "message": "Produk dengan ID 9999 tidak ditemukan.",
  "data": null
}
```
- **Kesimpulan**: **LULUS (PASS)**. Sistem merespons dengan HTTP status 404 sesuai konvensi REST API.

---

### Kasus Uji TC-04: Mengambil Produk dengan Format ID Tidak Valid (ID=abc)
- **Tujuan**: Memverifikasi validasi tipe data parameter URL.
- **Request**:
  - Method: `GET`
  - URL: `http://localhost/utspakhary/api/produk.php?id=abc`
- **Hasil Response**:
  - HTTP Status: `400 Bad Request`
  - Body:
```json
{
  "status": "error",
  "code": 400,
  "message": "Parameter ID produk tidak valid. Harus berupa bilangan bulat positif (contoh: ?id=1)."
}
```
- **Kesimpulan**: **LULUS (PASS)**. Input tidak valid ditolak sebelum mengeksekusi query database.

---

### Kasus Uji TC-05: Mengambil Seluruh Kategori Produk
- **Tujuan**: Memastikan endpoint kategori mengembalikan daftar klasifikasi produk beserta agregasi jumlah item.
- **Request**:
  - Method: `GET`
  - URL: `http://localhost/utspakhary/api/kategori.php`
- **Hasil Response**:
  - HTTP Status: `200 OK`
  - Body:
```json
{
  "status": "success",
  "code": 200,
  "message": "Daftar kategori berhasil diambil.",
  "total": 4,
  "data": [
    {
      "id_kategori": 1,
      "nama_kategori": "Smartphone Android",
      "slug": "android",
      "total_produk": 5
    },
    {
      "id_kategori": 2,
      "nama_kategori": "Apple iPhone (iOS)",
      "slug": "iphone",
      "total_produk": 1
    },
    {
      "id_kategori": 3,
      "nama_kategori": "Handphone Second Bergaransi",
      "slug": "second",
      "total_produk": 1
    },
    {
      "id_kategori": 4,
      "nama_kategori": "Aksesoris & Gadget",
      "slug": "aksesoris",
      "total_produk": 3
    }
  ]
}
```
- **Kesimpulan**: **LULUS (PASS)**. Menampilkan 4 kategori master dengan kalkulasi relasional produk yang tepat.

---

### Kasus Uji TC-06: Pembuatan Pesanan Baru Berhasil (Valid Order)
- **Tujuan**: Memastikan proses transaksi pemesanan berjalan secara ACID, memotong stok barang di tabel `produk`, serta membuat header transaksi `pesanan` dan rincian `detail_pesanan`.
- **Request**:
  - Method: `POST`
  - URL: `http://localhost/utspakhary/api/pesanan.php`
  - Header: `Content-Type: application/json`
  - Body (JSON):
```json
{
  "nama_pembeli": "Budi Hartono",
  "no_hp": "082198765432",
  "alamat_pengiriman": "Jl. Pandanaran No. 50, Semarang",
  "metode_pembayaran": "COD (Bayar di Tempat)",
  "catatan": "Kirim saat jam kerja kantor",
  "items": [
    {
      "id_produk": 1,
      "jumlah": 1
    },
    {
      "id_produk": 8,
      "jumlah": 1
    }
  ]
}
```
- **Hasil Response**:
  - HTTP Status: `201 Created`
  - Body:
```json
{
  "status": "success",
  "code": 201,
  "message": "Pesanan baru berhasil dibuat dan stok produk telah diperbarui.",
  "data": {
    "id_pesanan": 7,
    "kode_pesanan": "NHP-20261116-E6C3",
    "tanggal_pesanan": "2026-11-16 20:03:49",
    "nama_pembeli": "Budi Hartono",
    "no_hp": "082198765432",
    "alamat_pengiriman": "Jl. Pandanaran No. 50, Semarang",
    "metode_pembayaran": "COD (Bayar di Tempat)",
    "catatan": "Kirim saat jam kerja kantor",
    "status_pesanan": "Menunggu Konfirmasi",
    "total_pembayaran": 6078000,
    "total_pembayaran_format": "Rp 6.078.000",
    "items": [
      {
        "id_produk": 1,
        "nama_produk": "Samsung Galaxy A55 5G (8GB/256GB)",
        "harga_satuan": 5799000,
        "harga_satuan_format": "Rp 5.799.000",
        "jumlah": 1,
        "subtotal": 5799000,
        "sisa_stok": 1523
      },
      {
        "id_produk": 8,
        "nama_produk": "TWS Earphone Bluetooth 5.3 Stereo Bass",
        "harga_satuan": 279000,
        "harga_satuan_format": "Rp 279.000",
        "jumlah": 1,
        "subtotal": 279000,
        "sisa_stok": 1657
      }
    ]
  }
}
```
- **Verifikasi Basis Data**:
  - Nomor invoice unik `NHP-20261116-E6C3` terdaftar di tabel `pesanan`.
  - Dua entri rincian tersimpan di tabel `detail_pesanan`.
  - Stok produk 1 berkurang dari 1524 menjadi 1523.
  - Stok produk 8 berkurang dari 1658 menjadi 1657.
- **Kesimpulan**: **LULUS (PASS)**. Transaksi pesanan berhasil 100% dan konsistensi data terjaga.

---

### Kasus Uji TC-07: Pembuatan Pesanan Gagal (Field Kosong)
- **Tujuan**: Memastikan validasi kolom wajib mencegah pembuatan transaksi tanpa identitas pembeli.
- **Request**:
  - Method: `POST`
  - URL: `http://localhost/utspakhary/api/pesanan.php`
  - Header: `Content-Type: application/json`
  - Body:
```json
{
  "nama_pembeli": "",
  "no_hp": "",
  "alamat_pengiriman": ""
}
```
- **Hasil Response**:
  - HTTP Status: `400 Bad Request`
  - Body:
```json
{
  "status": "error",
  "code": 400,
  "message": "Validasi form pesanan gagal. Mohon lengkapi field yang diperlukan.",
  "errors": {
    "nama_pembeli": "Nama pembeli wajib diisi.",
    "no_hp": "Nomor HP/WhatsApp pembeli wajib diisi.",
    "alamat_pengiriman": "Alamat pengiriman lengkap wajib diisi.",
    "items": "Daftar item produk pesanan wajib diisi (minimal 1 item) dalam bentuk array."
  }
}
```
- **Kesimpulan**: **LULUS (PASS)**. Memberikan daftar error spesifik untuk setiap kolom yang kosong.

---

### Kasus Uji TC-08: Pembuatan Pesanan Gagal (Stok Melebihi Batas)
- **Tujuan**: Memastikan validasi aturan stok mencegah over-selling atau pesanan melebihi inventaris.
- **Request**:
  - Method: `POST`
  - URL: `http://localhost/utspakhary/api/pesanan.php`
  - Body:
```json
{
  "nama_pembeli": "Eko Prasetyo",
  "no_hp": "081234567899",
  "alamat_pengiriman": "Semarang Barat",
  "items": [
    {
      "id_produk": 5,
      "jumlah": 99999
    }
  ]
}
```
- **Hasil Response**:
  - HTTP Status: `422 Unprocessable Entity`
  - Body:
```json
{
  "status": "error",
  "code": 422,
  "message": "Stok produk 'iPhone 11 (64GB) Black - Second Like New' tidak mencukupi (tersedia: 276 unit, diminta: 99999 unit).",
  "id_produk": 5,
  "nama_produk": "iPhone 11 (64GB) Black - Second Like New",
  "stok_tersedia": 276,
  "jumlah_diminta": 99999
}
```
- **Kesimpulan**: **LULUS (PASS)**. Transaksi otomatis ditolak dan tidak ada pemotongan stok fiktif.

---

### Kasus Uji TC-09: Validasi Metode HTTP (Method Not Allowed)
- **Tujuan**: Memastikan endpoint hanya melayani kata kerja HTTP yang diizinkan sesuai rancangan spesifikasi.
- **Request**:
  - Method: `POST` (Salah, seharusnya GET)
  - URL: `http://localhost/utspakhary/api/produk.php`
- **Hasil Response**:
  - HTTP Status: `405 Method Not Allowed`
  - Body:
```json
{
  "status": "error",
  "code": 405,
  "message": "Method POST tidak diizinkan untuk endpoint ini. Gunakan metode: GET.",
  "allowed_methods": [
    "GET"
  ]
}
```
- **Kesimpulan**: **LULUS (PASS)**. Mengembalikan kode HTTP 405 dan daftar metode yang diizinkan.

---

## 4. Kesimpulan Akhir Pengujian

Berdasarkan seluruh hasil pengujian yang dilakukan terhadap 10 kasus uji (positif dan negatif):
1. Seluruh endpoint (`GET /api/produk.php`, `GET /api/produk.php?id=1`, `GET /api/kategori.php`, `POST /api/pesanan.php`) berfungsi dengan baik dan stabil.
2. HTTP Status Code yang dihasilkan (`200`, `201`, `400`, `404`, `405`, `422`) telah memenuhi standar protokol RESTful API.
3. Transaksi pembuatan pesanan berhasil menerapkan prinsip ACID dengan integritas database terjamin dan pengurangan stok otomatis.
4. Format response JSON konsisten, terstruktur rapi, dan mudah diintegrasikan dengan aplikasi klien seperti Postman atau aplikasi mobile Android/iOS.
