<?php
session_start();
$metode = $_POST['metode'] ?? $_SESSION['metode'] ?? 'bank_transfer'; // kalau pakai session
$jumlah = $_POST['jumlah'] ?? $_SESSION['jumlah'] ?? 0;



?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Top Up Saldo</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="topup.css" />

</head>
<body>

  <div class="container">
    <h2>Top Up Kantong Amal</h2>
    <form action="proses_topup.php" method="POST">
      <label for="jumlah">Jumlah Top Up (Rp)</label>
      <input type="number" id="jumlah" name="jumlah" min="1000" placeholder="Masukkan jumlah minimal Rp 1.000" required>

      <label for="metode">Metode Pembayaran</label>
      <select id="metode" name="metode" required>
        <option value="">-- Pilih Metode --</option>
        <option value="bank_transfer">Transfer Bank</option>
        <option value="e_wallet">E-Wallet (OVO, GoPay, dll)</option>
      </select>

      <button type="submit">Kirim Top Up</button>
      <p class="note">Setelah top up, silakan ikuti instruksi pembayaran yang ditampilkan.</p>
    </form>
  </div>

</body>
</html>
