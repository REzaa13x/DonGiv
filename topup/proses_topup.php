<?php
session_start();

// Validasi input
$jumlah = isset($_POST['jumlah']) ? (int) $_POST['jumlah'] : 0;
$metode = $_POST['metode'] ?? '';

if ($jumlah < 1000 || empty($metode)) {
    // Redirect balik kalau input tidak valid
    header("Location: index.php?error=1");
    exit();
}

// Simpan data ke session
$_SESSION['jumlah'] = $jumlah;
$_SESSION['metode'] = $metode;

// Arahkan ke halaman instruksi pembayaran
header("Location: instruksi.php");
exit();
?>
