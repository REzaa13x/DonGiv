<?php
include '../users/koneksi.php'; // koneksi ke database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama   = $_POST['name'] ?? '';
    $email  = $_POST['email'] ?? '';
    $pesan  = $_POST['message'] ?? '';

    if ($nama && $email && $pesan) {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO kontak (nama, email, pesan) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $nama, $email, $pesan);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: ../users/DonGiv.php?status=ok");
            exit;
        } else {
            header("Location: ../users/DonGiv.php?status=error");
            exit;
        }

        mysqli_stmt_close($stmt);
    } else {
        header("Location: ../users/DonGiv.php?status=error");
        exit;
    }
} else {
    header("Location: ../users/DonGiv.php");
    exit;
}
?>
