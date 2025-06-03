<?php
include '../users/koneksi.php';

$name = 'admin';
$email = 'admin@gmail.com';
$password = password_hash('admin123',PASSWORD_DEFAULT );
$role = 'admin';

$query = mysqli_query($conn, "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')");

if ($query) {
    echo "Admin berhasil ditambahkan.";
} else {
    echo "Gagal menambahkan admin: " . mysqli_error($conn);
}
?>