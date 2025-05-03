<?php
include './koneksi.php';

// Ambil ID dari form
$id = $_POST['id'] ?? '';

// Validasi input
$name = $_POST['name'] ?? '';
$no_hp = $_POST['no_hp'] ?? '';
$tanggal_lahir = $_POST['tanggal_lahir'] ?? '';

// Query update
$query = "UPDATE users SET name='$name', no_hp='$no_hp', tanggal_lahir='$tanggal_lahir' WHERE id='$id'";

if (mysqli_query($koneksi, $query)) {
    echo "Data berhasil diupdate.";
    header("Location: prof.php");
    // header("Location: dashboard.php"); // Redirect kalau mau
} else {
    echo "Error: " . mysqli_error($koneksi);
}
?>
