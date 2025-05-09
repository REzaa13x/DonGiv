<?php
require_once dirname(__FILE__) . '/vendor/autoload.php';
include '../users/koneksi.php';

\Midtrans\Config::$serverKey = 'SB-Mid-server-CCq38NHL_PpqD1Dw1QTD9VbP';
\Midtrans\Config::$isProduction = false;

$json = file_get_contents("php://input");
$notification = json_decode($json);

if (!$notification) {
    http_response_code(400);
    echo 'Invalid input';
    exit;
}

$transaction = \Midtrans\Transaction::status($notification->order_id);
$status = $transaction->transaction_status;
$amount = $transaction->gross_amount;
$order_id = $transaction->order_id;
$payment_type = $transaction->payment_type;
$fraud_status = $transaction->fraud_status ?? null;
$transaction_time = $transaction->transaction_time;

// Contoh simpan ke DB
$stmt = $conn->prepare("INSERT INTO transaksi_donasi (order_id, amount, status, payment_type, waktu_transaksi) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $order_id, $amount, $status, $payment_type, $transaction_time);
$stmt->execute();
$stmt->close();

http_response_code(200);
echo 'Notification handled';
?>
