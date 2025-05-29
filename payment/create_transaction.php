<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__FILE__) . '/vendor/autoload.php';
include '../users/koneksi.php';


// Ambil input JSON dari fetch
$raw_input = file_get_contents("php://input");
$data = json_decode($raw_input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

$amount = isset($data['amount']) ? intval($data['amount']) : 0;
if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid amount']);
    exit;
}

$customer_name  = $data['nama'] ?? 'Donatur';
$customer_email = $data['email'] ?? 'donatur@dongiv.id';
$customer_phone = $data['phone'] ?? '081234567890';

$user_id       = isset($data['user']) ? intval($data['user']) : 0;
$campaign_id   = isset($data['campaign_id']) ? intval($data['campaign_id']) : 0;
$message       = $data['message'] ?? '';
$is_anonymous  = isset($data['is_anonymous']) ? intval($data['is_anonymous']) : 0;
$payment_status = 'pending';
$metode_pembayaran = 'midtrans';

// Pastikan campaign_id dan user_id valid
if ($user_id <= 0 || $campaign_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid user or campaign']);
    exit;
}
// Midtrans config
\Midtrans\Config::$serverKey = 'SB-Mid-server-CM85Vg05mehy-1jINyQ4uYw7';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Order ID unik
$order_id = 'DONGIV-' . time() . '-' . rand(1000, 9999);

// Buat transaksi
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
        'id' => $campaign_id,
        'price' => $amount,
        'quantity' => 1,
        'name' => "Donasi"
    ]],
    'custom_field1' => (string) $user_id,
    'custom_field2' => (string) $campaign_id
];

// Ambil Snap Token
try {
    $snapToken = \Midtrans\Snap::getSnapToken($params);

   // Setiap transaksi donasi di database
$stmt = $conn->prepare("INSERT INTO donations 
(user, campaign_id, amount, message, donated_at, is_anonymous, payment_status, metode_pembayaran, order_id, snap_token, nama, email, no_hp) 
VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "iidsssssssss", // 12 parameter
    $user_id,
    $campaign_id,
    $amount,
    $message,
    $is_anonymous,
    $payment_status,
    $metode_pembayaran,
    $order_id,
    $snapToken,
    $customer_name,
    $customer_email,
    $customer_phone
);

if ($stmt->execute()) {
    echo json_encode(['snapToken' => $snapToken, 'order_id' => $order_id]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal menyimpan donasi: ' . $stmt->error]);
}
$stmt->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>