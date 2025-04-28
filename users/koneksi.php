<?php
$host = "localhost"; // nama host
$user = "root"; // username database
$pass = ""; // password database
$db   = "dongiv"; // nama database sesuai SQL tadi

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
