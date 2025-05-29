<?php
include '../users/koneksi.php';

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$order_id = $_GET['order_id'] ?? '';

if (empty($order_id)) {
    echo "Order ID tidak ditemukan.";
    exit();
}

$uploadSuccess = isset($_GET['upload']) && $_GET['upload'] === 'success';

$query = "SELECT donations.*, kampanye_donasi.nama_donasi 
          FROM donations 
          JOIN kampanye_donasi ON donations.campaign_id = kampanye_donasi.id_donasi
          WHERE donations.order_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Transaksi tidak ditemukan.";
    exit();
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Transaksi - DonGiv</title>
    <link rel="stylesheet" href="nota_transaksi.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="nota-container">

        <div class="nota-header">
            <img src="../foto/1-removebg-preview (1).png" alt="DonGiv-Logo">
            <h2>Transaksi Berhasil</h2>
        </div>

        <div class="nota-info">
            <p><strong>Order ID:</strong> <?= htmlspecialchars($row['order_id']) ?></p>
            <p><strong>Nama:</strong> <?= htmlspecialchars($row['nama']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
            <p><strong>Nomor HP:</strong> <?= htmlspecialchars($row['no_hp']) ?></p>
            <p><strong>Kampanye:</strong> <?= htmlspecialchars($row['nama_donasi']) ?></p>
        </div>

        <div class="nota-details">
            <div class="item">
                <span>Jumlah Donasi</span>
                <span>Rp <?= number_format($row['amount'], 0, ',', '.') ?></span>
            </div>
            <div class="item">
                <span>Status Pembayaran</span>
                <span><?= ucfirst($row['payment_status']) ?></span>
            </div>
            <div class="item">
                <span>Tanggal Transaksi</span>
                <span><?= date('d-m-Y H:i', strtotime($row['donated_at'])) ?></span>
            </div>
            <div class="item">
                <span>Metode Pembayaran</span>
                <span><?= ucfirst($row['metode_pembayaran']) ?></span>
            </div>
        </div>

        <div class="nota-upload">
    <form action="upload_bukti.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="order_id" value="<?= htmlspecialchars($row['order_id']) ?>">
        <label for="bukti">Upload Bukti Transaksi :</label>
        <div class="upload-row">
            <input type="file" name="bukti" accept=".jpg,.jpeg,.png,.pdf" required>
            <button type="submit">Kirim Bukti ke Admin</button>
        </div>
    </form>
</div>

<div class="nota-back">
    <a href="../users/DonGiv.php" class="back-button">Kembali ke Halaman Utama</a>
</div>

<div class="nota-footer">
    <p>&copy; 2024 DonGiv Indonesia. Semua Hak Dilindungi.</p>
</div>

    </div>

    <?php if ($uploadSuccess): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Bukti berhasil dikirim! Tunggu verifikasi dari admin.',
            confirmButtonColor: '#8f7cf8'
        }).then(() => {
            const url = new URL(window.location.href);
            url.searchParams.delete('upload');
            window.history.replaceState({}, document.title, url);
        });
    </script>
    <?php endif; ?>
</body>
</html>
