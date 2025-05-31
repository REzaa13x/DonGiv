<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'koneksi.php'; // Koneksi ke database
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    error_log("Pengguna belum login. Session user_id tidak ditemukan.");
    echo json_encode(['success' => false, 'message' => 'Pengguna belum login.']);
    exit();
}

// Ambil user_id dari sesi
$user_id = $_SESSION['user_id'];

// Ambil data pengguna yang terbaru dari database
$query = "SELECT * FROM users WHERE id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    // Ambil data pengguna
    $foto = $data['foto'] ?? 'default.png';
    $saldo = $data['saldo'] ?? 0;
    $dana_didonasikan = $data['total_donasi'] ?? 0;
    $total_campaign = $data['total_campaign'] ?? 0;
    $poin = $data['poin'] ?? 0;
} else {
    echo json_encode(['success' => false, 'message' => 'Pengguna tidak ditemukan.']);
    exit();
}

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

// Perbarui saldo, total donasi, dan poin pengguna setelah transaksi
$new_balance = $saldo + $amount; // Update saldo
$new_total_donasi = $dana_didonasikan + $amount; // Tambahkan amount ke total donasi
$new_total_campaign = $total_campaign + 1; // Tambahkan 1 untuk total campaign
$new_poin = $poin + 1; // Setiap transaksi berhasil, pengguna dapat 1 poin

// Log hasil perhitungan untuk debugging
error_log("Saldo baru: $new_balance, Total donasi baru: $new_total_donasi, Total campaign baru: $new_total_campaign, Poin baru: $new_poin");

// Query untuk memperbarui data pengguna
$query_update = "UPDATE users SET saldo = ?, total_donasi = ?, total_campaign = ?, poin = ?, updated_at = NOW() WHERE id = ?";
$stmt_update = $conn->prepare($query_update);
$stmt_update->bind_param("diii", $new_balance, $new_total_donasi, $new_total_campaign, $new_poin, $user_id);

// Eksekusi update
if ($stmt_update->execute()) {
    // Simpan donasi ke tabel campaign jika berhasil
    $stmt_campaign = $conn->prepare("INSERT INTO donations (user, campaign_id, amount, message, donated_at, is_anonymous, payment_status, order_id) VALUES (?, ?, ?, '', NOW(), 0, 'success', ?)");
    $stmt_campaign->bind_param("iids", $user_id, $campaign_id, $amount, $order_id);

    if ($stmt_campaign->execute()) {
        // Beri feedback jika data berhasil disimpan
        echo json_encode(['success' => true, 'message' => 'Data berhasil diperbarui dan donasi berhasil disimpan.']);
    } else {
        error_log("Error saat menyimpan donasi ke tabel donations: " . $stmt_campaign->error);
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan donasi ke tabel donations.']);
    }

    $stmt_campaign->close();
} else {
    error_log("Error saat update user: " . $stmt_update->error);
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data pengguna.']);
}

$stmt_update->close();
$stmt->close();
?>
