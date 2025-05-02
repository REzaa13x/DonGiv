<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Detail Donasi</title>
    <link rel="stylesheet" href="DetailDonasi.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
  </head>
  <body>
    <div class="container">
      <!-- Header -->
      <header class="header">
        <h1>Detail Donasi</h1>
        <p>Informasi lengkap tentang donasi yang dipilih.</p>
      </header>

      <!-- Detail Donasi -->
      <div class="donation-details">
        <div class="detail-section">
          <h2>Informasi Umum</h2>
          <div class="detail-item">
            <span class="label">Tanggal Donasi:</span>
            <span class="value">27/12/2024</span>
          </div>
          <div class="detail-item">
            <span class="label">Penanggung Jawab:</span>
            <span class="value">Joko</span>
          </div>
          <div class="detail-item">
            <span class="label">Nama Donasi:</span>
            <span class="value">Bantuan Banjir</span>
          </div>
          <div class="detail-item">
            <span class="label">Total Donasi:</span>
            <span class="value">Rp 5.000.000</span>
          </div>
        </div>

        <div class="detail-section">
          <h2>Detail Penerima</h2>
          <div class="detail-item">
            <span class="label">Nama Penerima:</span>
            <span class="value">Warga Desa Sukamaju</span>
          </div>
          <div class="detail-item">
            <span class="label">Lokasi:</span>
            <span class="value">Sukamaju, Jawa Barat</span>
          </div>
          <div class="detail-item">
            <span class="label">Deskripsi Kebutuhan:</span>
            <span class="value"
              >Bantuan untuk korban banjir berupa makanan, pakaian, dan
              obat-obatan.</span
            >
          </div>
        </div>

        <div class="detail-section">
          <h2>Bukti Donasi</h2>
          <!-- Proof of Donation -->
          <div class="proof">
            <img
              src="bukti donasi.jpg"
              alt="Bukti Donasi"
              class="proof-image"
            />
            <div class="download-container">
              <a href="bukti donasi.jpg" download class="download-button">
                <i class="fas fa-download"></i> Unduh Bukti
              </a>
            </div>
          </div>
        </div>

        <div class="detail-section">
          <h2>Status Donasi</h2>
          <div class="status">
            <span class="status-label">Status:</span>
            <span class="status-value">Terkirim</span>
          </div>
          <div class="detail-item">
            <span class="label">Tanggal Pengiriman:</span>
            <span class="value">28/12/2024</span>
          </div>
          <div class="detail-item">
            <span class="label">Tanggal Penerimaan:</span>
            <span class="value">30/12/2024</span>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <footer class="footer">
        <p>&copy; 2024 Donasi Online. All rights reserved.</p>
      </footer>
    </div>
  </body>
</html>
