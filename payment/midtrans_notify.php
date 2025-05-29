<?php
require_once dirname(__FILE__) . '/vendor/autoload.php';
include '../users/koneksi.php';

\Midtrans\Config::$serverKey = 'SB-Mid-server-CCq38NHL_PpqD1Dw1QTD9VbP';
\Midtrans\Config::$isProduction = false;

// Ambil notifikasi dari Midtrans
$json = file_get_contents("php://input");
$data = json_decode($json);

if (!$data) {
    http_response_code(400);
    echo 'Invalid input';
    exit;
}

// Ambil status transaksi dari Midtrans
$transaction = \Midtrans\Transaction::status($data->order_id);
$status = $transaction->transaction_status;
$amount = $transaction->gross_amount;
$order_id = $transaction->order_id;
$payment_type = $transaction->payment_type;
$transaction_time = $transaction->transaction_time;
$midtrans_response = json_encode($transaction);

// Ambil custom fields (dari Snap)
$user = isset($transaction->custom_field1) ? (int)$transaction->custom_field1 : null;
$campaign_id = isset($transaction->custom_field2) ? (int)$transaction->custom_field2 : null;

// Log transaksi (untuk testing/debugging)
file_put_contents('midtrans_notify_log.json', json_encode($transaction, JSON_PRETTY_PRINT));

// Simpan data ke tabel donations (jika sukses / settlement)
if ($status === 'settlement' || $status === 'capture') {
    // Cek apakah sudah ada order_id ini (hindari duplikat)
    $check = $conn->prepare("SELECT COUNT(*) FROM donations WHERE order_id = ?");
    $check->bind_param("s", $order_id);
    $check->execute();
    $check->bind_result($exists);
    $check->fetch();
    $check->close();

    if ($exists == 0) {
        // Simpan ke tabel donations
        $stmt = $conn->prepare("INSERT INTO donations (
            user, campaign_id, amount, message, donated_at, is_anonymous,
            payment_status, metode_pembayaran, order_id, midtrans_response
        ) VALUES (?, ?, ?, '', ?, 0, ?, ?, ?, ?)");
        $stmt->bind_param(
            "iisssssss",
            $user,
            $campaign_id,
            $amount,
            $transaction_time,
            $status,
            $payment_type,
            $order_id,
            $midtrans_response
        );
        if (!$stmt->execute()) {
            error_log("Error inserting donation: " . $stmt->error);
        }
        $stmt->close();

        // Update total_donasi dan poin
        $update_user = $conn->prepare("UPDATE users SET total_donasi = total_donasi + ?, poin = poin + 1 WHERE id = ?");
        $update_user->bind_param("di", $amount, $user);
        if (!$update_user->execute()) {
            error_log("Error updating user data: " . $update_user->error);
        }
        $update_user->close();

        // Cek apakah ini donasi pertama user ke campaign tsb
        $cek_campaign = $conn->prepare("SELECT COUNT(*) FROM donations WHERE user = ? AND campaign_id = ?");
        $cek_campaign->bind_param("ii", $user, $campaign_id);
        $cek_campaign->execute();
        $cek_campaign->bind_result($jumlah_donasi_ke_campaign);
        $cek_campaign->fetch();
        $cek_campaign->close();

        if ($jumlah_donasi_ke_campaign == 1) {
            $update_campaign = $conn->prepare("UPDATE users SET total_campaign = total_campaign + 1 WHERE id = ?");
            $update_campaign->bind_param("i", $user);
            if (!$update_campaign->execute()) {
                error_log("Error updating campaign data: " . $update_campaign->error);
            }
            $update_campaign->close();
        }
    }
}

http_response_code(200);
echo 'Notification handled';
?>
