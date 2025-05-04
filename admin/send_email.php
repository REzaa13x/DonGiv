<?php
include '../users/koneksi.php'; // sesuaikan path jika perlu

// Tangkap data dari form (dengan name yang sesuai)
$subjek = $_POST['subject'];
$isi = $_POST['content'];
$penerima = $_POST['recipients'];

// Cek apakah ada data kosong
if (empty($subjek) || empty($isi) || empty($penerima)) {
  die("Data tidak boleh kosong.");
}

// Simpan ke tabel email_kampanye
$stmt = $conn->prepare("INSERT INTO email_kampanye (subjek, isi, penerima) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $subjek, $isi, $penerima);

if ($stmt->execute()) {
  header("Location: notifikasi.php?status=berhasil");
} else {
  echo "Gagal menyimpan: " . $stmt->error;
}
?>
