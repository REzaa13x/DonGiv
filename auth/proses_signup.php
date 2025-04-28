<?php
include '../koneksi.php'; // naik satu folder (karena di dalam /auth/)

$name = $_POST['name'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Cek email sudah ada atau belum
$cek = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email'");
if (mysqli_num_rows($cek) > 0) {
    echo "<script>alert('Email sudah terdaftar, silakan login!'); window.location='Login.php';</script>";
    exit();
}

// Kalau email belum ada, baru insert
$query = mysqli_query($koneksi, "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')");

if ($query) {
    echo "<script>alert('Pendaftaran berhasil! Silakan login.'); window.location='Login.php';</script>";
} else {
    echo "<script>alert('Gagal mendaftar!'); window.location='Login.php';</script>";
}
?>
