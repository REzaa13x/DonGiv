<?php
$host = "localhost";
$pengguna = "root";
$lulus = "";
$db = "tubes_webpro";

$conn = mysqli_connect($host, $pengguna, $lulus, $db);
if (!$conn) {
  die("Koneksi gagal: " . mysqli_connect_error());
}
?>

