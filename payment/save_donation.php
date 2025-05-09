<?php
require_once '../users/koneksi.php'; // pastikan file koneksi ke database ada

// Ambil data dari Midtrans
$data = json_decode(file_get_contents("php://input"), true);

$order_id = $data['order_id'];
$amount = $data['gross_amount'];
$payment_type = $data['payment_type'];
$transaction_status = $data['transaction_status'];
$transaction_time = $data['transaction_time'];
$midtrans_response = json_encode($data);
$user = $data['custom_field1'];
$campaign_id = $data['custom_field2'];


// Ambil custom fields jika kamu tambahkan ke Snap, misal:
$user = isset($data['user_id']) ? $data['user_id'] : null;
$campaign_id = isset($data['campaign_id']) ? $data['campaign_id'] : null;

$sql = "INSERT INTO donations (
    user, campaign_id, amount, message, donated_at, is_anonymous,
    payment_status, metode_pembayaran, order_id, midtrans_response
) VALUES (
    ?, ?, ?, '', ?, 0, ?, ?, ?, ?
)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "iisssssss",
    $user,
    $campaign_id,
    $amount,
    $transaction_time,
    $transaction_status,
    $payment_type,
    $order_id,
    $midtrans_response
);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}
?>
