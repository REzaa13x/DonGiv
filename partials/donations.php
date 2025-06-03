<?php
// File: ../partials/donations.php

// Pastikan koneksi database sudah tersedia.
// Karena file ini di-include oleh DonGiv.php, kita bisa asumsi koneksi.php sudah di-include di DonGiv.php.
// Namun, untuk jaga-jaga dan agar lebih modular, kita bisa include lagi di sini.
// Sesuaikan path jika 'koneksi.php' tidak berada di '../users/koneksi.php'
if (!isset($conn)) { // Cek apakah $conn sudah ada dari file yang meng-include
    include '../koneksi.php'; // Asumsi koneksi.php ada di direktori yang sama dengan DonGiv.php
}

$featured_campaigns = [];
// Query untuk mengambil 4 kampanye donasi aktif terbaru
// Pastikan mengambil dana_terkumpul dan target_dana
$sql_featured = "SELECT kd.id_donasi, kd.nama_donasi, kd.deskripsi, kd.gambar, kd.created_at, kd.dana_terkumpul, kd.target_dana, k.nama_kategori 
                 FROM kampanye_donasi kd
                 LEFT JOIN kategori_donasi k ON kd.id_kategori = k.id_kategori
                 WHERE kd.status IN ('active', 'completed', 'disbursed') AND (kd.tanggal_akhir IS NULL OR kd.tanggal_akhir >= CURDATE())
                 ORDER BY kd.created_at DESC
                 LIMIT 4"; // Batasi hanya 4 kampanye

$result_featured = mysqli_query($conn, $sql_featured);

if ($result_featured && mysqli_num_rows($result_featured) > 0) {
    while ($row_featured = mysqli_fetch_assoc($result_featured)) {
        $featured_campaigns[] = $row_featured;
    }
}

// Tidak perlu mysqli_close($conn) di sini jika $conn digunakan oleh DonGiv.php atau partials lain
// yang di-include setelah ini.
?>

<style>
    /* Styling khusus untuk progress bar dan teks di partials/donations.php */
    .progress-bar-container {
        background-color: #e0e0e0;
        border-radius: 5px;
        height: 10px;
        margin-top: 10px; /* Jarak dari deskripsi */
        margin-bottom: 10px;
        overflow: hidden;
        position: relative;
    }
    .progress-bar {
        height: 100%;
        background-color: #4CAF50; /* Warna hijau */
        width: 0%; 
        border-radius: 5px;
        position: relative; 
        display: flex;
        align-items: center;
        justify-content: flex-end; /* Posisikan teks di kanan */
        padding-right: 5px; /* Sedikit padding dari kanan */
    }
    .progress-text { 
        font-size: 0.75em;
        color: #fff; 
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5); 
        white-space: nowrap; 
    }
    .amount-info {
        display: flex;
        justify-content: space-between;
        font-size: 0.9em;
        color: #333;
        margin-bottom: 15px;
    }
</style>

<div>
  <section class="news-container">
    <div class="text" id="Donations">
      <h1>Donations</h1>
    </div>
    <div class="cards">
      <?php if (!empty($featured_campaigns)): ?>
        <?php foreach ($featured_campaigns as $campaign): ?>
          <?php
            // Hitung persentase dana terkumpul
            $persentase_terkumpul = ($campaign['target_dana'] > 0) ? 
                                    min(100, ($campaign['dana_terkumpul'] / $campaign['target_dana']) * 100) : 0;

            // Jika persentase > 0 tapi sangat kecil, set minimal 1% agar terlihat
            if ($persentase_terkumpul > 0 && $persentase_terkumpul < 1) {
                $persentase_terkumpul = 1;
            }
            // Bulatkan untuk tampilan
            $display_persentase = round($persentase_terkumpul);

            // Ambil deskripsi dan pisahkan tujuan untuk tampilan singkat
            $display_description_short = $campaign['deskripsi'] ?? '';
            $display_tujuan_short = '';

            if (strpos($display_description_short, 'Tujuan:') === 0) {
                $parts_short = explode("\n\n", $display_description_short, 2);
                $tujuan_line_short = trim($parts_short[0]);
                if (strpos($tujuan_line_short, 'Tujuan:') === 0) {
                    $display_tujuan_short = substr($tujuan_line_short, strlen('Tujuan:'));
                    $display_tujuan_short = trim($display_tujuan_short);
                }
                $display_description_short = (count($parts_short) > 1) ? trim($parts_short[1]) : '';
            }
            if (empty(trim($display_description_short)) || $display_description_short === 'Tidak ada deskripsi rinci.' || $display_description_short === '0') {
                $display_description_short = 'Belum ada deskripsi singkat.'; // Pesan default jika deskripsi kosong
            }

            // Batasi deskripsi agar tidak terlalu panjang di home page
            $short_description_limit = 100; // Batasi 100 karakter
            if (strlen($display_description_short) > $short_description_limit) {
                $display_description_short = substr($display_description_short, 0, $short_description_limit) . '...';
            }
          ?>
          <div class="card">
            <img src="<?= !empty($campaign['gambar']) ? '../' . htmlspecialchars($campaign['gambar']) : 'https://via.placeholder.com/400x200?text=Gambar+Donasi' ?>" alt="<?= htmlspecialchars($campaign['nama_donasi']) ?>">
            <div class="card-content">
              <span class="tag"><?= htmlspecialchars($campaign['nama_kategori'] ?? 'Umum') ?></span>
              <small><?= htmlspecialchars(date('d M Y', strtotime($campaign['created_at']))) ?></small>
              <h2><?= htmlspecialchars($campaign['nama_donasi']) ?></h2>
              <p>
                <?php if (!empty($display_tujuan_short)): ?>
                    Tujuan: **<?= htmlspecialchars($display_tujuan_short) ?>**<br>
                <?php endif; ?>
                <?= htmlspecialchars($display_description_short) ?>
              </p>

              <div class="progress-bar-container">
                  <div class="progress-bar" style="width: <?= $persentase_terkumpul ?>%;">
                      <span class="progress-text"><?= $display_persentase ?>%</span>
                  </div>
              </div>
              <div class="amount-info">
                  <span>Terkumpul: **Rp <?= number_format($campaign['dana_terkumpul'], 0, ',', '.') ?>**</span>
                  <span>Target: Rp <?= number_format($campaign['target_dana'], 0, ',', '.') ?></span>
              </div>

              <a href="../users/detail_donasi.php?id=<?= htmlspecialchars($campaign['id_donasi']) ?>">Get the details</a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="no-campaigns" style="color: #555; text-align: center; width: 100%;">Tidak ada kampanye donasi aktif untuk ditampilkan.</p>
      <?php endif; ?>
    </div>
    <div class="view-all-container">
      <a href="../users/donasi.php" class="view-all-btn">View All</a>
    </div>
  </section>
</div>