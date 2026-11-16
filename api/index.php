<?php
/**
 * REST API Index / Documentation Overview
 * Nanda Handphone Marketplace API
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/api_helper.php';

require_method('GET');

$api_info = [
    'nama_layanan' => 'REST API Marketplace Handphone & Aksesoris (Nanda Handphone)',
    'versi'        => '1.0.0',
    'deskripsi'    => 'Web Service / REST API untuk pengelolaan katalog dan transaksi pemesanan smartphone.',
    'lingkungan'   => 'XAMPP Localhost / InfinityFree',
    'status_db'    => $db_connected ? 'Terhubung (Connected)' : 'Gagal terhubung (Disconnected)',
    'base_url'     => base_url('api'),
    'endpoints'    => [
        [
            'metode'    => 'GET',
            'endpoint'  => '/api/produk.php',
            'deskripsi' => 'Mengambil seluruh katalog produk handphone dan aksesoris. Mendukung filter opsional ?kategori=1, ?q=samsung, ?kondisi=Baru',
            'contoh'    => base_url('api/produk.php')
        ],
        [
            'metode'    => 'GET',
            'endpoint'  => '/api/produk.php?id={id}',
            'deskripsi' => 'Mengambil rincian data 1 produk spesifik berdasarkan parameter ID.',
            'contoh'    => base_url('api/produk.php?id=1')
        ],
        [
            'metode'    => 'GET',
            'endpoint'  => '/api/kategori.php',
            'deskripsi' => 'Mengambil seluruh daftar kategori produk beserta jumlah produk terkait.',
            'contoh'    => base_url('api/kategori.php')
        ],
        [
            'metode'    => 'POST',
            'endpoint'  => '/api/pesanan.php',
            'deskripsi' => 'Membuat transaksi pesanan baru (Checkout) dengan pengurangan stok otomatis.',
            'contoh'    => base_url('api/pesanan.php'),
            'format_payload' => [
                'nama_pembeli'      => 'Ahmad Rizki',
                'no_hp'             => '081234567890',
                'alamat_pengiriman' => 'Jl. Pemuda No. 10, Semarang',
                'metode_pembayaran' => 'Transfer Bank BCA',
                'catatan'           => 'Mohon bubble wrap tebal',
                'items'             => [
                    [
                        'id_produk' => 1,
                        'jumlah'    => 1
                    ]
                ]
            ]
        ],
        [
            'metode'    => 'GET',
            'endpoint'  => '/api/pesanan.php?kode={kode_pesanan}',
            'deskripsi' => 'Pelacakan status dan rincian pesanan berdasarkan Kode Invoice atau ?id={id_pesanan}.',
            'contoh'    => base_url('api/pesanan.php?kode=NHP-20261101-001')
        ]
    ]
];

api_response(200, 'success', 'REST API Nanda Handphone siap digunakan.', $api_info);
