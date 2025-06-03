<?php
session_start();
include '../users/koneksi.php'; // Sesuaikan path koneksi

// --- PENTING: Validasi login admin di sini ---
// Jika menggunakan admin injector, bagian ini mungkin tidak diperlukan atau ditangani secara eksternal.
// if (!isset($_SESSION['admin_id'])) {
//     header('Location: login_admin.php');
//     exit();
// }

if (isset($_GET['order_id'])) {
    $order_id = mysqli_real_escape_string($conn, $_GET['order_id']);

    // Ambil path bukti_upload dari database
    $query = "SELECT bukti_upload FROM donations WHERE order_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $bukti_path_from_db = $row['bukti_upload']; // Ini adalah 'bukti_uploads/namafile.jpg'

        // PENTING: SESUAIKAN BASE PATH UNTUK GAMBAR
        // Jika 'bukti_uploads/' adalah subfolder dari 'payment/', dan bukti.php ada di 'admin/'
        // Maka path dari admin/ ke payment/bukti_uploads/ adalah ../payment/
        $base_upload_dir = '../payment/'; // Relative path dari admin/ ke payment/
        $full_path = $base_upload_dir . $bukti_path_from_db; // Menggabungkan base_upload_dir dengan path dari DB

        // Periksa apakah file ada dan dapat diakses
        if (file_exists($full_path) && is_readable($full_path)) {
            $mime_type = mime_content_type($full_path); // Mendapatkan MIME type file

            // Set header untuk menampilkan gambar atau PDF
            header('Content-Type: ' . $mime_type);
            header('Content-Length: ' . filesize($full_path));
            readfile($full_path); // Baca dan tampilkan file
            exit;
        } else {
            echo "File bukti tidak ditemukan atau tidak dapat diakses di: " . htmlspecialchars($full_path);
        }
    } else {
        echo "Bukti pembayaran tidak ditemukan untuk Order ID ini.";
    }
    $stmt->close();
} else {
    echo "Order ID tidak diberikan.";
}

mysqli_close($conn);
?>