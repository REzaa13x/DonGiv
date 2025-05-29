<?php
require_once '../users/koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);

$order_id = $data['order_id'];
$amount = $data['gross_amount'];
$payment_type = $data['payment_type'];
$transaction_status = $data['transaction_status'];
$transaction_time = $data['transaction_time'];
$midtrans_response = json_encode($data);

// Ambil custom field dari Snap
$user = isset($data['custom_field1']) ? (int)$data['custom_field1'] : null;
$campaign_id = isset($data['custom_field2']) ? (int)$data['custom_field2'] : null;

// Validasi wajib
if (!is_numeric($user) || !is_numeric($campaign_id)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "User ID dan Campaign ID harus berupa angka."]);
    exit;
}

// Query untuk menyimpan data donasi
$sql = "INSERT INTO donations (
    user, campaign_id, amount, message, donated_at, is_anonymous,
    payment_status, metode_pembayaran, order_id, midtrans_response
) VALUES (?, ?, ?, '', ?, 0, ?, ?, ?, ?)";
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
    // Update total donasi dan poin
    $update_donasi = $conn->prepare("UPDATE users SET total_donasi = total_donasi + ?, poin = poin + 1 WHERE id = ?");
    $update_donasi->bind_param("di", $amount, $user);
    $update_donasi->execute();

    // Cek donasi pertama untuk campaign
    $cek_campaign = $conn->prepare("SELECT COUNT(*) FROM donations WHERE user = ? AND campaign_id = ?");
    $cek_campaign->bind_param("ii", $user, $campaign_id);
    $cek_campaign->execute();
    $cek_campaign->bind_result($jumlah_donasi_ke_campaign);
    $cek_campaign->fetch();
    $cek_campaign->close();

    if ($jumlah_donasi_ke_campaign == 1) {
        $update_campaign = $conn->prepare("UPDATE users SET total_campaign = total_campaign + 1 WHERE id = ?");
        $update_campaign->bind_param("i", $user);
        $update_campaign->execute();
    }

    echo json_encode(["success" => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan donasi: ' . $stmt->error]);
}
?>
