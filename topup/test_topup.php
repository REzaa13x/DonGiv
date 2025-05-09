<?php
include '../users/koneksi.php'; // Sesuaikan path jika perlu

$user_id = 1; // Ganti dengan user_id yang valid dari database kamu
$jumlah = 1000;
$metode = "bank_transfer";
$status = "pending";
$tanggal = date("Y-m-d H:i:s");

$sql = "INSERT INTO topup (user_id, jumlah, metode, status, tanggal) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iissi", $user_id, $jumlah, $metode, $status, $tanggal);

if ($stmt->execute()) {
    echo "Berhasil";
} else {
    echo "Gagal: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>