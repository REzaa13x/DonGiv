<?php
include '../users/koneksi.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); 

    $query = "DELETE FROM tambah_donasi WHERE id_donasi = $id";
    $result = mysqli_query($conn, $query);

    if ($result) {
        header("Location: Manajemen.php?status=success&msg=Donasi berhasil dihapus");
    } else {
        header("Location: Manajemen.php?status=error&msg=Gagal menghapus donasi");
    }
} else {
    header("Location: Manajemen.php?status=error&msg=ID tidak valid");
}
exit();
?>
