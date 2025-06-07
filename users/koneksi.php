<?php
$host = "localhost";
$pengguna = "root";
$lulus = "";
$db = "tubes_webpro";

// Koneksi pakai OOP (bisa digunakan dengan $conn->query())
$conn = new mysqli($host, $pengguna, $lulus, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

?>
