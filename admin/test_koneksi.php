<?php
// Sesuaikan path ini agar sesuai dengan lokasi koneksi.php Anda
include '../users/koneksi.php';

if ($conn) {
    echo "Koneksi database berhasil! <br>";
    echo "Database: " . $db . "<br>";
    echo "Host: " . $host . "<br>";
    echo "User: " . $pengguna . "<br>";
    // Coba query sederhana untuk memastikan tabel 'donations' ada
    $test_query = $conn->query("SELECT 1 FROM donations LIMIT 1");
    if ($test_query) {
        echo "Tabel 'donations' ditemukan dan dapat diakses.<br>";
    } else {
        echo "Error: Tabel 'donations' tidak ditemukan atau tidak dapat diakses. Pesan error: " . $conn->error . "<br>";
    }
} else {
    echo "Koneksi database GAGAL TOTAL. Periksa kredensial di koneksi.php dan pastikan MySQL berjalan.<br>";
}
?>