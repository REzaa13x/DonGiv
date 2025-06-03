<?php
// File: admin/Hapus.php

// Pastikan hanya admin yang bisa mengakses halaman ini
// session_start();
// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: login_admin.php');
//     exit();
// }

include '../users/koneksi.php'; // Sesuaikan path koneksi Anda

$message = '';
$status = 'error'; // Default status

if (isset($_GET['id'])) {
    $id_donasi = mysqli_real_escape_string($conn, $_GET['id']);

    // Mulai transaksi untuk memastikan integritas data
    mysqli_begin_transaction($conn);

    try {
        // 1. Ambil path gambar kampanye sebelum dihapus
        $stmt_get_image = mysqli_prepare($conn, "SELECT gambar FROM kampanye_donasi WHERE id_donasi = ?");
        mysqli_stmt_bind_param($stmt_get_image, "i", $id_donasi);
        mysqli_stmt_execute($stmt_get_image);
        $result_image = mysqli_stmt_get_result($stmt_get_image);
        $campaign_data = mysqli_fetch_assoc($result_image);
        mysqli_stmt_close($stmt_get_image);

        $image_path_on_server = '';
        if ($campaign_data && !empty($campaign_data['gambar'])) {
            // Path relatif yang disimpan di DB perlu diubah ke path absolut di server
            $image_path_on_server = '../' . $campaign_data['gambar']; // Sesuaikan path jika perlu
        }

        // 2. Hapus kampanye dari database
        $stmt_delete = mysqli_prepare($conn, "DELETE FROM kampanye_donasi WHERE id_donasi = ?");
        mysqli_stmt_bind_param($stmt_delete, "i", $id_donasi);

        if (mysqli_stmt_execute($stmt_delete)) {
            // Jika penghapusan dari DB berhasil, coba hapus file gambar
            if (!empty($image_path_on_server) && file_exists($image_path_on_server)) {
                if (unlink($image_path_on_server)) {
                    $message = "Kampanye dan gambar terkait berhasil dihapus!";
                    $status = 'success';
                } else {
                    $message = "Kampanye berhasil dihapus, tetapi gagal menghapus file gambar.";
                    $status = 'warning'; // Status warning jika gambar gagal dihapus
                }
            } else {
                $message = "Kampanye berhasil dihapus (tidak ada gambar atau gambar tidak ditemukan).";
                $status = 'success';
            }
            mysqli_commit($conn); // Commit transaksi jika semua berhasil
        } else {
            $message = "Gagal menghapus kampanye: " . mysqli_error($conn);
            mysqli_rollback($conn); // Rollback transaksi jika ada error
        }
        mysqli_stmt_close($stmt_delete);

    } catch (Exception $e) {
        mysqli_rollback($conn); // Rollback jika ada exception
        $message = "Terjadi kesalahan: " . $e->getMessage();
    }
} else {
    $message = "ID kampanye tidak diberikan.";
}

mysqli_close($conn);

// Redirect kembali ke halaman manajemen dengan pesan status
header("Location: Manajemen.php?status=" . $status . "&msg=" . urlencode($message));
exit();
?>
