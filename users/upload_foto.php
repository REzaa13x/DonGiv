<?php
session_start();
include './koneksi.php';

$id = $_SESSION['user_id'] ?? null;
if (!$id) {
    die("User belum login.");
}

$target_dir = "../uploads/"; // pastikan folder ini ada dan bisa ditulisi

if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    die("Terjadi kesalahan saat mengupload file.");
}

$filename = uniqid() . "_" . basename($_FILES["foto"]["name"]);
$target_file = $target_dir . $filename;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
$allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

if (!in_array($imageFileType, $allowed_types)) {
    die("File bukan gambar yang diperbolehkan (jpg, png, gif).");
}

if (getimagesize($_FILES["foto"]["tmp_name"]) === false) {
    die("File yang diupload bukan gambar.");
}

if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
    $query = "UPDATE users SET foto='$filename' WHERE id='$id'";
    mysqli_query($conn, $query);
    header("Location: ../users/editUser.php");
    exit;
} else {
    echo "Gagal mengupload gambar.";
}
?>
