<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bukti Pembayaran</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
    <link rel="stylesheet" href="bukti.css" />
    <!-- Menghubungkan file CSS -->
  </head>
  <body>
    <div class="receipt">
      <!-- Header -->
      <div class="header">
        <div class="icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <h1>Bukti Pembayaran</h1>
        <p class="subtitle">Transaksi Berhasil</p>
      </div>

      <!-- Informasi Pembayaran -->
      <div class="info">
        <div class="info-item">
          <span class="label">Tanggal</span>
          <span class="value">27/12/2024</span>
        </div>
        <div class="info-item">
          <span class="label">Nama Donasi</span>
          <span class="value">Bantuan Banjir</span>
        </div>
        <div class="info-item">
          <span class="label">Jumlah</span>
          <span class="value">Rp 5.000.000</span>
        </div>
        <div class="info-item">
          <span class="label">Metode Pembayaran</span>
          <span class="value">Transfer Bank (BCA)</span>
        </div>
        <div class="info-item">
          <span class="label">Nomor Transaksi</span>
          <span class="value">1234567890</span>
        </div>
        <div class="info-item">
          <span class="label">Status</span>
          <span class="value success">Berhasil</span>
        </div>
      </div>

      <!-- Footer -->
      <div class="footer">
        <p>Terima kasih telah berdonasi melalui <strong>DonGiv</strong>.</p>
        <p>Semoga kebaikan Anda menjadi sumber inspirasi bagi yang lain.</p>
      </div>

      <!-- Tombol Kembali -->
      <div class="button">
        <a href="Salur.php">Kembali ke Halaman Utama</a>
      </div>
    </div>
  </body>
</html>
