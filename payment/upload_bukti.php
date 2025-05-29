<?php
session_start();
include '../users/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $upload_dir = 'bukti_uploads/';
    
    // Buat folder kalau belum ada
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $filename = basename($_FILES['bukti']['name']);
    $target_file = $upload_dir . time() . '_' . $filename;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validasi file
    $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
    if (!in_array($file_type, $allowed_types)) {
        $_SESSION['upload_status'] = 'Format file tidak didukung.';
        header("Location: ../users/riwayat_donasi.php");
        exit;
    }

    // Pindahkan file ke folder tujuan
    if (move_uploaded_file($_FILES['bukti']['tmp_name'], $target_file)) {
        // Simpan path file ke database
        $stmt = $conn->prepare("UPDATE donations SET bukti_upload = ?, payment_status = 'waiting_verification' WHERE order_id = ?");
        $stmt->bind_param("ss", $target_file, $order_id);
        $stmt->execute();

        $_SESSION['upload_status'] = 'success';
    } else {
        $_SESSION['upload_status'] = 'Gagal upload file.';
    }

    // Setelah upload berhasil:
header("Location: nota_transaksi.php?order_id=$order_id&upload=success");
exit();
} else {
    echo "Akses tidak valid.";
}
