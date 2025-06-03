<?php
include '../users/koneksi.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = mysqli_query($conn, "DELETE FROM users WHERE id = $id");

    if ($query) {
        echo "<script>alert('User berhasil dihapus'); window.location.href='User Manajement.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus user'); window.location.href='User Manajement.php';</script>";
    }
} else {
    echo "<script>alert('ID tidak ditemukan'); window.location.href='User Manajement.php';</script>";
}
?>
