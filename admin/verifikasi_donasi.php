<?php
session_start();
include '../users/koneksi.php'; // Sesuaikan path koneksi

// --- PENTING: Validasi login admin di sini ---
// Jika menggunakan admin injector, bagian ini mungkin tidak diperlukan atau ditangani secara eksternal.
// if (!isset($_SESSION['admin_id'])) {
//     $_SESSION['admin_message'] = ['icon' => 'error', 'title' => 'Akses Ditolak', 'text' => 'Anda tidak memiliki izin untuk melakukan aksi ini.'];
//     header("Location: login_admin.php");
//     exit();
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    // $admin_id = $_SESSION['admin_id']; // Ambil ID admin yang melakukan verifikasi (jika perlu dicatat)

    // Update status donasi menjadi 'settlement' (atau 'success')
    $stmt = $conn->prepare("UPDATE donations SET payment_status = 'settlement' WHERE order_id = ? AND (payment_status = 'waiting_verification' OR payment_status = 'pending')");
    $stmt->bind_param("s", $order_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['admin_message'] = ['icon' => 'success', 'title' => 'Berhasil!', 'text' => 'Donasi berhasil diverifikasi.'];
        } else {
            $_SESSION['admin_message'] = ['icon' => 'info', 'title' => 'Informasi', 'text' => 'Donasi tidak ditemukan atau statusnya sudah terverifikasi.'];
        }
    } else {
        $_SESSION['admin_message'] = ['icon' => 'error', 'title' => 'Gagal!', 'text' => 'Gagal memverifikasi donasi: ' . $stmt->error];
    }
    $stmt->close();
} else {
    $_SESSION['admin_message'] = ['icon' => 'error', 'title' => 'Akses Tidak Valid', 'text' => 'Permintaan verifikasi tidak valid.'];
}

mysqli_close($conn);
header("Location: RiwayatDonasi.php"); // Redirect kembali ke halaman riwayat donasi
exit();
?>