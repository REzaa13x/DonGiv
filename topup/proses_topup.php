<?php
session_start();

// Sertakan file koneksi
include '../users/koneksi.php';

// Ambil data dari form
$user_id = $_POST['user_id'] ?? ($_SESSION['user_id'] ?? 1);
$jumlah = $_POST['jumlah'] ?? 0;
$metode = $_POST['metode'] ?? "";
$status = "pending"; // Definisikan status sebagai variabel

// Validasi input
if ($jumlah < 1000) {
    die("Jumlah top up minimal adalah Rp 1.000");
}

if ($metode != "bank_transfer" && $metode != "e_wallet") {
    die("Metode pembayaran tidak valid");
}

// Proses Top Up (Simpan ke database)
$tanggal_sekarang = date("Y-m-d H:i:s");
$sql = "INSERT INTO topup (user_id, jumlah, metode, status, tanggal) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Gagal menyiapkan statement INSERT: " . $conn->error);
}
$stmt->bind_param("iissi", $user_id, $jumlah, $metode, $status, $tanggal_sekarang);

if ($stmt->execute()) {
    // Top up berhasil dicatat

    // Update saldo di tabel users
    $update_sql = "UPDATE users SET saldo = saldo + ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    if (!$update_stmt) {
        die("Gagal menyiapkan statement UPDATE: " . $conn->error);
    }
    $update_stmt->bind_param("ii", $jumlah, $user_id);
    if (!$update_stmt->execute()) {
        die("Gagal mengeksekusi statement UPDATE: " . $update_stmt->error);
    }

    // Ambil saldo terbaru untuk ditampilkan
    $get_saldo_sql = "SELECT saldo FROM users WHERE id = ?";
    $get_saldo_stmt = $conn->prepare($get_saldo_sql);
    if (!$get_saldo_stmt) {
        die("Gagal menyiapkan statement SELECT: " . $conn->error);
    }
    $get_saldo_stmt->bind_param("i", $user_id);
    if (!$get_saldo_stmt->execute()) {
        die("Gagal mengeksekusi statement SELECT: " . $get_saldo_stmt->error);
    }

    $get_saldo_stmt->bind_result($saldo_terbaru);  // Bind result columns
    if ($get_saldo_stmt->fetch()) {                // Fetch the data
        // Data ditemukan
    } else {
        $saldo_terbaru = 0; // Atau nilai default lainnya, dan mungkin log error
        echo "Gagal mengambil data saldo atau saldo tidak ditemukan.";
    }

    // Struktur HTML untuk bukti transaksi
    echo "<style>";
    include 'transaction.css'; // Atau bisa langsung letakkan kode CSS di sini
    echo "</style>";
    echo "<div class='transaction-receipt'>";
    echo "<h2>Top Up Berhasil</h2>";
    echo "<p>Tanggal: " . date("d F Y H:i:s") . "</p>"; // Format tanggal yang lebih baik
    echo "<p>Jumlah Top Up: Rp " . number_format($jumlah) . "</p>";
    echo "<p>Metode Pembayaran: " . ucfirst(str_replace("_", " ", $metode)) . "</p>"; // Format metode
    echo "<p>Saldo Anda sekarang: Rp " . number_format($saldo_terbaru) . "</p>";
    echo "<p>Silakan kembali ke <a href='../users/prof.php'>Profil</a></p>";
    echo "</div>";

    $update_stmt->close();
    $get_saldo_stmt->close();

} else {
    echo "Gagal melakukan top up: " . $stmt->error;
}


$stmt->close();
$conn->close();
?>