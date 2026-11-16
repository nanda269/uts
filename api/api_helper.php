<?php
/**
 * REST API Helper & Response Formatter
 * Nanda Handphone Marketplace API
 */

// Aktifkan Error Reporting untuk debugging jika diperlukan, tapi jangan leak ke JSON output
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Set Header JSON & CORS
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Tangani Request Preflight OPTIONS dari browser / Postman
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * Kirim response JSON berstandar REST API
 *
 * @param int $code HTTP Status Code (200, 201, 400, 404, 405, 422, 500)
 * @param string $status 'success' atau 'error'
 * @param string $message Pesan informatif
 * @param mixed $data Data payload (bisa array, object, null)
 * @param array $extra Data tambahan opsional (seperti total, errors, pagination)
 */
function api_response($code, $status, $message, $data = null, $extra = []) {
    http_response_code($code);

    $response = [
        'status'  => $status,
        'code'    => $code,
        'message' => $message
    ];

    if (!empty($extra)) {
        foreach ($extra as $key => $val) {
            $response[$key] = $val;
        }
    }

    if ($data !== null) {
        $response['data'] = $data;
    }

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Validasi HTTP Method yang diizinkan
 *
 * @param string|array $allowed_methods Metode yang diperbolehkan (misal: 'GET' atau ['POST'])
 */
function require_method($allowed_methods) {
    if (!is_array($allowed_methods)) {
        $allowed_methods = [$allowed_methods];
    }

    $current_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if (!in_array($current_method, $allowed_methods)) {
        api_response(
            405,
            'error',
            "Method {$current_method} tidak diizinkan untuk endpoint ini. Gunakan metode: " . implode(', ', $allowed_methods) . '.',
            null,
            ['allowed_methods' => $allowed_methods]
        );
    }
}

/**
 * Cek status koneksi MySQL
 *
 * @param bool $db_connected
 * @param string $db_error
 */
function check_db_connection($db_connected, $db_error = '') {
    if (!$db_connected) {
        api_response(
            500,
            'error',
            'Gagal terhubung ke database MySQL. Pastikan service MySQL di XAMPP telah aktif dan database `uts_nanda_hp` telah diimport.',
            null,
            ['db_error' => $db_error]
        );
    }
}

/**
 * Ambil payload request dari JSON Body (raw php://input) atau $_POST
 *
 * @return array|null Mengembalikan array data jika valid, atau null jika JSON corrupt
 */
function get_request_data() {
    $raw_input = file_get_contents('php://input');

    // Fallback untuk pengujian via PHP CLI stdin
    if (empty(trim($raw_input)) && php_sapi_name() === 'cli') {
        $raw_input = file_get_contents('php://stdin');
    }

    if (!empty(trim($raw_input))) {
        $decoded = json_decode($raw_input, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
        // Jika ada raw input tapi bukan JSON valid dan $_POST juga kosong, kembalikan null (error)
        if (empty($_POST)) {
            return null;
        }
    }

    // Fallback ke form-data / x-www-form-urlencoded ($_POST)
    if (!empty($_POST)) {
        return $_POST;
    }

    return [];
}
