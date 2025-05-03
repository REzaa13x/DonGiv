<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "tubes_webpro"; // database baru yang kamu pilih

$koneksi = mysqli_connect($host, $user, $pass, $db);

// Tambahkan ini agar $conn juga tersedia
$conn = $koneksi;

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
