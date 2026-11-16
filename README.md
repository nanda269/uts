# Marketplace "Nanda Handphone" & RESTful Web Service
## Tugas Soal 12 Pengembangan Web Service / REST API Marketplace
**Dosen Pengampu**: Pak Hary  
**Teknologi Utama**: PHP Native 8.x, MySQLi, JSON, Bootstrap 5.3, Apache XAMPP, Postman  

---

## Daftar Berkas Luaran Pengumpulan Tugas (Deliverables)

Sesuai instruksi soal, seluruh output tugas telah tersedia langsung di dalam repository project ini:

| No | Output yang Dikumpulkan | Lokasi / Berkas di Project | Deskripsi |
|:---:|:---|:---|:---|
| 1 | **Dokumen Rancangan Endpoint API** | [`DOKUMEN_RANCANGAN_API.md`](file:///c:/xampp/htdocs/utspakhary/DOKUMEN_RANCANGAN_API.md) | Rancangan arsitektur REST API, spesifikasi request/response, HTTP status codes, dan skema database ACID. |
| 2 | **Source Code PHP REST API** | Direktori [`api/`](file:///c:/xampp/htdocs/utspakhary/api) | Kode sumber PHP Native & MySQLi: `api_helper.php`, `produk.php`, `kategori.php`, `pesanan.php`, dan `index.php`. |
| 3 | **File Database SQL** | [`database.sql`](file:///c:/xampp/htdocs/utspakhary/database.sql) | DDL & DML skema database `uts_nanda_hp` lengkap dengan 5 tabel relasional dan data seed awal. |
| 4 | **Screenshot Pengujian XAMPP & Postman** | [`PANDUAN_SCREENSHOT_PENGUJIAN.md`](file:///c:/xampp/htdocs/utspakhary/PANDUAN_SCREENSHOT_PENGUJIAN.md) | Panduan langkah demi langkah dan checklist 6 screenshot wajib pengujian di XAMPP & Postman. |
| 5 | **URL Repository GitHub** | Bagian [Panduan Push ke GitHub](#panduan-push-ke-github-dan-url-repository) | Petunjuk inisialisasi Git, remote repository, dan tautan URL GitHub. |
| 6 | **Laporan Hasil Pengujian API** | [`LAPORAN_PENGUJIAN_API.md`](file:///c:/xampp/htdocs/utspakhary/LAPORAN_PENGUJIAN_API.md) | Laporan hasil uji 10 skenario positif dan negatif beserta status code dan bukti verifikasi database (100% PASS). |
| *Plus* | **Postman Collection Siap Import** | [`postman_collection.json`](file:///c:/xampp/htdocs/utspakhary/postman_collection.json) | File koleksi Postman v2.1.0 berisi seluruh 10 request uji coba untuk import 1-klik di Postman. |

---

## 1. Ringkasan Endpoint REST API

Base URL: `http://localhost/utspakhary`

| Endpoint URI | HTTP Method | Fungsi Utama | Format Input | HTTP Response Code |
|:---|:---:|:---|:---:|:---:|
| `/api/index.php` | `GET` | Ringkasan informasi & dokumentasi REST API | - | `200 OK` |
| `/api/produk.php` | `GET` | Mengambil seluruh katalog produk smartphone & aksesoris | Query Params (opsional) | `200 OK`, `500` |
| `/api/produk.php?id=1` | `GET` | Mengambil detail 1 produk spesifik berdasarkan ID | Query `id` (int, wajib) | `200 OK`, `400`, `404` |
| `/api/kategori.php` | `GET` | Mengambil seluruh data kategori produk & jumlah item | Query Params (opsional) | `200 OK`, `500` |
| `/api/pesanan.php` | `POST` | Membuat transaksi pesanan baru & potong stok otomatis | JSON Payload (wajib) | `201 Created`, `400`, `422`, `500` |
| `/api/pesanan.php?kode={kode}` | `GET` | Melacak status pesanan berdasarkan kode invoice | Query `kode` / `id` | `200 OK`, `404`, `405` |

---

## 2. Cara Menjalankan Project di XAMPP Localhost

### Langkah 1: Persiapan Server XAMPP
1. Buka aplikasi **XAMPP Control Panel**.
2. Klik tombol **Start** pada modul **Apache** dan **MySQL**.
3. Pastikan kedua modul berwarna hijau dan port aktif (Port 80/443 untuk Apache, Port 3306 untuk MySQL).

### Langkah 2: Import Basis Data MySQL
1. Buka web browser, lalu akses **phpMyAdmin**: [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/).
2. Buat database baru bernama `uts_nanda_hp` dengan collation `utf8mb4_unicode_ci` (atau biarkan otomatis dibuat oleh script).
3. Klik tab **Import** pada phpMyAdmin.
4. Klik **Choose File**, arahkan ke file:
   `c:\xampp\htdocs\utspakhary\database.sql`
5. Gulir ke bawah dan klik tombol **Import** / **Kirim**.
6. Pastikan 5 tabel berikut berhasil terbuat:
   - `pengguna`
   - `kategori`
   - `produk`
   - `pesanan`
   - `detail_pesanan`

### Langkah 3: Akses Web Marketplace & REST API
- **Etalase Marketplace (Pembeli)**: [http://localhost/utspakhary/](http://localhost/utspakhary/)
- **Dashboard Admin / Penjual (CRUD)**: [http://localhost/utspakhary/admin/](http://localhost/utspakhary/admin/)
  *(Login default: email `admin@nandahp.com` / password `admin123`)*
- **Endpoint Root REST API**: [http://localhost/utspakhary/api/index.php](http://localhost/utspakhary/api/index.php)

---

## 3. Panduan Pengujian Menggunakan Postman

### Metode Cepat (Import File Koleksi):
1. Buka aplikasi **Postman**.
2. Klik tombol **Import** di sudut kiri atas.
3. Seret (drag & drop) atau pilih file:
   `c:\xampp\htdocs\utspakhary\postman_collection.json`
4. Postman akan langsung membuat koleksi **"REST API Marketplace Handphone (Soal 12)"** dengan seluruh request yang telah dikonfigurasi.

---

### Contoh Request & Parameter Pengujian:

#### A. GET Semua Produk:
- **Method**: `GET`
- **URL**: `http://localhost/utspakhary/api/produk.php`
- **Status Response**: `200 OK`

#### B. GET Detail Produk:
- **Method**: `GET`
- **URL**: `http://localhost/utspakhary/api/produk.php?id=1`
- **Status Response**: `200 OK`

#### C. GET Semua Kategori:
- **Method**: `GET`
- **URL**: `http://localhost/utspakhary/api/kategori.php`
- **Status Response**: `200 OK`

#### D. POST Buat Pesanan Baru:
- **Method**: `POST`
- **URL**: `http://localhost/utspakhary/api/pesanan.php`
- **Headers**:
  - `Content-Type: application/json`
  - `Accept: application/json`
- **Body** (pilih tab `raw` -> format `JSON`):
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
- **Status Response**: `201 Created`
- **Verifikasi**: Stok produk ID 1 berkurang 1 unit, ID 7 berkurang 2 unit di database.

---

## 4. Panduan Push ke GitHub dan URL Repository

Jika project ini belum terhubung ke akun GitHub Anda, ikuti langkah berikut di terminal/PowerShell:

```bash
# 1. Masuk ke direktori project
cd c:\xampp\htdocs\utspakhary

# 2. Inisialisasi Git
git init

# 3. Tambahkan semua berkas ke staging area
git add .

# 4. Buat commit pertama
git commit -m "feat: Implementasi REST API Soal 12 Marketplace Handphone"

# 5. Ubah branch utama menjadi main
git branch -M main

# 6. Hubungkan ke repository GitHub Anda (ganti username dan nama-repo)
git remote add origin https://github.com/<username-anda>/<nama-repo-anda>.git

# 7. Unggah ke GitHub
git push -u origin main
```

> **Catatan**: Setelah di-push, URL repository yang dikumpulkan ke dosen adalah tautan repository GitHub Anda (contoh: `https://github.com/nanda/marketplace-utspakhary`).

---

## 5. Fitur Utama Sistem yang Tetap Terjaga (Website Monolitik)
Pengembangan REST API ini dilakukan tanpa mengubah alur atau merusak fitur lama:
1. **Etalase Produk (`index.php`)**: Filter kategori, pencarian real-time, badge stok.
2. **Detail Produk (`detail.php`)**: Informasi spesifikasi, garansi, tombol beli langsung.
3. **Keranjang Belanja (`keranjang.php`)**: Tambah/kurang kuantitas, kalkulasi dinamis.
4. **Checkout Tradisional (`checkout.php` & `proses-checkout.php`)**: Transaksi multi-item dengan verifikasi stok database.
5. **Panel Admin CRUD (`admin/`)**: Tambah produk, edit data, hapus dengan unlink gambar, dan status pesanan.
6. **Autentikasi Pengguna (`login.php`, `register.php`, `logout.php`)**: Keamanan password bcrypt.
