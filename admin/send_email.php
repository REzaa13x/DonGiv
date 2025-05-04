<?php
include '../users/koneksi.php';

$pesan = $_POST['pesan'];
$penerima_kategori = $_POST['recipients'];
$daftar_penerima = []; // Array untuk menyimpan nama-nama penerima

// Logika untuk mendapatkan daftar nama penerima berdasarkan kategori
if ($penerima_kategori == 'all') {
    // Ambil semua nama donatur dari tabel donatur (GANTI 'donatur' dan 'nama' sesuai tabelmu)
    $result_donatur = $conn->query("SELECT nama FROM donatur");
    while ($row_donatur = $result_donatur->fetch_assoc()) {
        $daftar_penerima[] = $row_donatur['nama'];
    }
} elseif ($penerima_kategori == 'top') {
    // Ambil nama-nama top donatur (GANTI 'donatur', 'nama', dan kriteria ORDER BY serta LIMIT)
    $result_donatur = $conn->query("SELECT nama FROM donatur ORDER BY jumlah_donasi DESC LIMIT 10");
    while ($row_donatur = $result_donatur->fetch_assoc()) {
        $daftar_penerima[] = $row_donatur['nama'];
    }
} elseif ($penerima_kategori == 'recent') {
    // Ambil nama-nama donatur terbaru (GANTI 'donatur', 'nama', dan kriteria ORDER BY serta LIMIT)
    $result_donatur = $conn->query("SELECT nama FROM donatur ORDER BY tanggal_donasi DESC LIMIT 10");
    while ($row_donatur = $result_donatur->fetch_assoc()) {
        $daftar_penerima[] = $row_donatur['nama'];
    }
}

// Simpan email ke tabel email_kampanye untuk setiap penerima
$stmt = $conn->prepare("INSERT INTO email_kampanye (isi, penerima, nama_penerima, tanggal_kirim) VALUES (?, ?, ?, NOW())");

foreach ($daftar_penerima as $nama) {
    $stmt->bind_param("sss", $pesan, $penerima_kategori, $nama); // Tetap simpan kategori dan nama
    $stmt->execute();
}

$stmt->close();
$conn->close();

header("Location: notifikasi.php?status=berhasil");
?>