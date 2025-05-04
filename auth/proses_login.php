<?php
session_start();
include '../users/koneksi.php';

// Tangkap input
$email = mysqli_real_escape_string($conn, $_POST['email']);
$password = $_POST['password'];

// Cek apakah email terisi
if (empty($email) || empty($password)) {
    echo "<script>alert('Email dan Password harus diisi!'); window.location='login.php';</script>";
    exit;
}

// Cari user berdasarkan email
$query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' LIMIT 1");
$data = mysqli_fetch_assoc($query);

// Validasi user dan password
if ($data && password_verify($password, $data['password'])) {
    // Set session
    $_SESSION['user_id'] = $data['id'];
    $_SESSION['user_name'] = $data['name'];

    if ($data['role'] == 'admin') {
        header('Location: ../admin/index.php');
        exit;
    } else {
        header('Location: ../users/DonGiv.php');
        exit;
    }
    
    // Redirect ke halaman utama
   
} else {
    echo "<script>alert('Email atau Password salah!'); window.location='login.php';</script>";
    exit;
}
?>
