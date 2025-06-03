<?php
// create_transaction.php
file_put_contents("debug_log.txt", "Incoming JSON: " . file_get_contents("php://input") . "\n", FILE_APPEND); // Log input JSON
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Pastikan path ke autoload.php benar
require_once dirname(__FILE__) . '/vendor/autoload.php';
// Pastikan path ke koneksi.php benar
include '../users/koneksi.php'; 

header('Content-Type: application/json'); // Pastikan responsnya JSON

$response = ['snapToken' => null, 'order_id' => null, 'message' => 'Terjadi kesalahan tidak diketahui.'];

try {
    $raw_input = file_get_contents("php://input");
    $data = json_decode($raw_input, true);

    if (!$data) {
        throw new Exception('Invalid JSON input or empty request body.');
    }

    // Ambil data dari input JSON yang dikirim dari payment.php
    $amount         = isset($data['amount']) ? intval($data['amount']) : 0;
    $customer_name  = $data['nama'] ?? 'Donatur';
    $customer_email = $data['email'] ?? 'donatur@dongiv.id';
    $customer_phone = $data['phone'] ?? '081234567890'; // Dari 'phone' di JS
    $user_id        = isset($data['user_id']) ? intval($data['user_id']) : 0; // Dari 'user_id' di JS
    $campaign_id    = isset($data['campaign_id']) ? intval($data['campaign_id']) : 0; // Dari 'campaign_id' di JS
    $order_id_from_client = $data['order_id'] ?? ''; // Ambil order_id yang sudah digenerate di client-side JS
    $pesan_donatur  = $data['pesan_donatur'] ?? ''; // Dari 'pesan_donatur' di JS, jika ada

    // Validasi data awal
    if ($amount <= 0) {
        throw new Exception('Jumlah donasi tidak valid.');
    }
    if (empty($customer_name) || empty($customer_email) || empty($customer_phone)) {
        throw new Exception('Data donatur (nama, email, nomor HP) tidak boleh kosong.');
    }
    if ($user_id <= 0 || $campaign_id <= 0) {
        throw new Exception('ID pengguna atau ID kampanye tidak valid.');
    }
    if (empty($order_id_from_client)) {
        throw new Exception('Order ID tidak valid.');
    }

    // Midtrans config
    // GANTI DENGAN SERVER KEY ANDA (SB-Mid-server-XXXXX)
    \Midtrans\Config::$serverKey = 'SB-Mid-server-CM85Vg05mehy-1jINyQ4uYw7'; 
    \Midtrans\Config::$isProduction = false; // Set ke true jika di production
    \Midtrans\Config::$isSanitized = true;
    \Midtrans\Config::$is3ds = true;

    // Gunakan order_id yang sudah digenerate dari client-side
    $order_id = $order_id_from_client;

    // Buat parameter transaksi untuk Midtrans
    $params = [
        'transaction_details' => [
            'order_id' => $order_id,
            'gross_amount' => $amount
        ],
        'customer_details' => [
            'first_name' => $customer_name,
            'email' => $customer_email,
            'phone' => $customer_phone
        ],
        'item_details' => [[
            'id' => (string)$campaign_id, // ID kampanye sebagai ID item
            'price' => $amount,
            'quantity' => 1,
            'name' => "Donasi untuk Kampanye ID: " . $campaign_id // Nama item donasi
        ]],
        // Metadata penting untuk diambil di midtrans_notify.php
        'custom_field1' => (string) $user_id,    // user_id
        'custom_field2' => (string) $campaign_id, // campaign_id
        'custom_field3' => $pesan_donatur,        // pesan donatur
        'callbacks' => [
            'finish' => 'http://localhost/DONGiv2k252/payment/nota_transaksi.php?order_id=' . $order_id,
            'error' => 'http://localhost/DONGiv2k252/payment/nota_transaksi.php?order_id=' . $order_id,
            'pending' => 'http://localhost/DONGiv2k252/payment/nota_transaksi.php?order_id=' . $order_id,
        ],
    ];

    // Ambil Snap Token dari Midtrans
    $snapToken = \Midtrans\Snap::getSnapToken($params);

    // --- Simpan setiap transaksi donasi awal di database (sebelum pembayaran selesai) ---
    // Pastikan tabel 'donations' memiliki kolom yang sesuai:
    // user (int), campaign_id (int), amount (decimal), message (text/varchar),
    // donated_at (datetime), is_anonymous (tinyint/boolean), payment_status (varchar),
    // metode_pembayaran (varchar), order_id (varchar), snap_token (varchar),
    // nama (varchar), email (varchar), no_hp (varchar)
    
    // Asumsi is_anonymous defaultnya 0 (tidak anonim) jika tidak ada input dari form
    $is_anonymous = 0; // Anda bisa tambahkan checkbox di payment.php jika ingin opsi anonim

    // PERBAIKAN: Definisikan variabel untuk nilai literal sebelum bind_param
    $payment_status_db = 'pending';  // Variabel untuk status pembayaran awal
    $metode_pembayaran_db = 'Midtrans'; // Variabel untuk metode pembayaran

    $stmt = $conn->prepare("INSERT INTO donations 
    (user, campaign_id, amount, message, donated_at, is_anonymous, payment_status, metode_pembayaran, order_id, snap_token, nama, email, no_hp) 
    VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?)");

    // Perbaikan bind_param: iidsisssssss (12 parameter)
    // i (user_id), i (campaign_id), d (amount), s (message), i (is_anonymous), 
    // s (payment_status), s (metode_pembayaran), s (order_id), s (snap_token), 
    // s (customer_name), s (customer_email), s (customer_phone)
    $stmt->bind_param(
        "iidsisssssss", 
        $user_id,
        $campaign_id,
        $amount,
        $pesan_donatur, // Menggunakan pesan donatur dari input
        $is_anonymous,  // Default 0
        $payment_status_db,     // <-- Gunakan variabel
        $metode_pembayaran_db,  // <-- Gunakan variabel
        $order_id,
        $snapToken,
        $customer_name,
        $customer_email,
        $customer_phone
    );

    if ($stmt->execute()) {
        $response['snapToken'] = $snapToken;
        $response['order_id'] = $order_id;
        $response['message'] = "Snap Token berhasil didapatkan dan donasi awal disimpan.";
    } else {
        // Jika gagal menyimpan ke database, log error dan lemparkan exception
        error_log("Failed to insert donation into DB: " . $stmt->error);
        throw new Exception('Gagal menyimpan donasi ke database: ' . $stmt->error);
    }
    $stmt->close();

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    $response['message'] = "Kesalahan server: " . $e->getMessage();
    error_log("Error in create_transaction.php: " . $e->getMessage() . " | Raw Input: " . $raw_input); // Log error dan raw input
} finally {
    // Pastikan koneksi ditutup
    if ($conn) {
        $conn->close();
    }
}

echo json_encode($response);
?>