<?php
session_start();

$metode = $_SESSION['metode'] ?? 'bank_transfer';
$jumlah = $_SESSION['jumlah'] ?? 0;

// Validasi ulang
if ($jumlah <= 0) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Instruksi Pembayaran</title>
  <link rel="stylesheet" href="topup.css">
</head>
<body>
  <div class="container">
    <h2>Instruksi Pembayaran</h2>

    <?php if ($metode === 'bank_transfer'): ?>
      <p>Silakan transfer ke rekening <strong>BCA 1234567890</strong> a.n. DonGiv.</p>
    <?php elseif ($metode === 'e_wallet'): ?>
      <p>Scan QR berikut untuk membayar melalui e-wallet (OVO, GoPay, dll):</p>
      <img src="qris/qris_code.png" width="200" alt="QR Code Pembayaran">
    <?php else: ?>
      <p>Metode pembayaran tidak dikenal.</p>
    <?php endif; ?>

    <p><strong>Jumlah:</strong> Rp <?= number_format($jumlah, 0, ',', '.') ?></p>
    <a href="index.php">Kembali</a>
  </div>
</body>
</html>
