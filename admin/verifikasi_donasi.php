<?php
include '../users/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id_penyaluran'];

  // Update status verifikasi di database
  $query = "UPDATE penyaluran_donasi SET status='Terverifikasi' WHERE id_penyaluran='$id'";
  if (mysqli_query($conn, $query)) {
    header("Location: RiwayatDonasi.php?status=success");
  } else {
    header("Location: RiwayatDonasi.php?status=error");
  }
}
?>
