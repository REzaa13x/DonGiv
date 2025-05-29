<?php
session_start();
include '../users/koneksi.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    error_log("Pengguna belum login. Session user_id tidak ditemukan.");
    echo json_encode(['success' => false, 'message' => 'Pengguna belum login.']);
    exit;
}

// Ambil user_id dari session
$user_id = $_SESSION['user_id'];

// Ambil data dari request (yang dikirim oleh frontend)
$data = json_decode(file_get_contents("php://input"), true);

// Cek apakah data berhasil di-decode
if (!$data) {
    error_log("Data tidak valid atau JSON decode gagal.");
    echo json_encode(['success' => false, 'message' => 'Data tidak valid atau JSON decode gagal.']);
    exit;
}

// Ambil informasi yang diperlukan dari request
$order_id = $data['order_id'] ?? ''; // ID order dari transaksi
$amount = $data['amount'] ?? 0; // Jumlah donasi
$campaign_id = $data['campaign_id'] ?? ''; // ID kampanye

// Validasi data
if (empty($order_id) || $amount <= 0 || empty($campaign_id) || empty($user_id)) {
    error_log("Data tidak lengkap atau tidak valid. order_id: $order_id, amount: $amount, campaign_id: $campaign_id, user_id: $user_id");
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap atau tidak valid.']);
    exit;
}

// Query untuk mengambil data pengguna
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    error_log("Error pada query: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Query gagal dijalankan.']);
    exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Jika pengguna tidak ditemukan
if ($result->num_rows === 0) {
    error_log("Pengguna dengan user_id: $user_id tidak ditemukan.");
    echo json_encode(['success' => false, 'message' => 'Pengguna tidak ditemukan.']);
    exit;
}

// Ambil data pengguna
$user = $result->fetch_assoc();

// Log untuk memeriksa data pengguna yang diterima
error_log("Data pengguna yang ditemukan: " . print_r($user, true));

// Perbarui saldo, total donasi, dan poin pengguna setelah transaksi
$new_balance = $user['saldo'] + $amount;
$new_total_donasi = $user['total_donasi'] + $amount;
$new_poin = $user['poin'] + 1; // Setiap transaksi berhasil, pengguna dapat 1 poin

// Log hasil perhitungan untuk debugging
error_log("Saldo baru: $new_balance, Total donasi baru: $new_total_donasi, Poin baru: $new_poin");

// Query untuk memperbarui data pengguna
$query_update = "UPDATE users SET saldo = ?, total_donasi = ?, poin = ?, updated_at = NOW() WHERE id = ?";
$stmt_update = $conn->prepare($query_update);

// Cek jika query update gagal
if ($stmt_update === false) {
    error_log("Error pada query update: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Query update gagal dijalankan.']);
    exit;
}

$stmt_update->bind_param("diii", $new_balance, $new_total_donasi, $new_poin, $user_id);

// Eksekusi update
if ($stmt_update->execute()) {
    // Log untuk memastikan data berhasil diperbarui
    error_log("Data berhasil diperbarui untuk user_id: $user_id");

    // Simpan donasi ke tabel campaign jika donasi berhasil
    $stmt_campaign = $conn->prepare("INSERT INTO donations (user, campaign_id, amount, message, donated_at, is_anonymous, payment_status, order_id) VALUES (?, ?, ?, '', NOW(), 0, 'success', ?)");
    $stmt_campaign->bind_param("iiis", $user_id, $campaign_id, $amount, $order_id);
    
    if ($stmt_campaign->execute()) {
        $stmt_campaign->close();
        echo json_encode(['success' => true, 'message' => 'Data berhasil diperbarui.']);
    } else {
        error_log("Error saat menyimpan donasi ke tabel donations: " . $stmt_campaign->error);
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan donasi.']);
    }

} else {
    // Menyimpan log error ke file error_log
    error_log("Error saat update user: " . $stmt_update->error);
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data pengguna.']);
}

$stmt_update->close();
$stmt->close();
?>
