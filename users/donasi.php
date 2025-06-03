<?php
// File: users/donasi.php (atau di root jika struktur Anda berbeda)

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include file koneksi database Anda
// Asumsi: koneksi.php ada di direktori yang sama atau bisa diakses
include 'koneksi.php'; 

$kampanye_donasi_aktif = [];

// Query untuk mengambil kampanye donasi yang aktif (status = 'active')
// Lakukan JOIN dengan kategori_donasi untuk menampilkan nama kategori
$sql = "SELECT kd.*, k.nama_kategori 
        FROM kampanye_donasi kd
        LEFT JOIN kategori_donasi k ON kd.id_kategori = k.id_kategori
        WHERE kd.status IN ('active', 'completed', 'disbursed') AND (kd.tanggal_akhir IS NULL OR kd.tanggal_akhir >= CURDATE())
        ORDER BY kd.created_at DESC";

$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $kampanye_donasi_aktif[] = $row;
    }
}

// Tutup koneksi database
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kampanye Donasi - DonGiv</title>
    <link rel="stylesheet" href="style.css"> 
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        /* Basic Styling for demonstration */
        body { font-family: Arial, sans-serif; background-color: #f0f2f5; margin: 0; padding-top: 20px; }
        .container { max-width: 1200px; margin: auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 40px; }
        .campaign-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .campaign-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.2s;
        }
        .campaign-card:hover {
            transform: translateY(-5px);
        }
        .campaign-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .card-content {
            padding: 15px;
        }
        .card-content h3 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #1e3a8a;
            font-size: 1.25em;
        }
        .card-content p {
            font-size: 0.9em;
            color: #555;
            margin-bottom: 10px;
            height: 4.5em; /* Limit description height */
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3; /* Show up to 3 lines */
            -webkit-box-orient: vertical;
        }
        .card-meta {
            font-size: 0.85em;
            color: #777;
            margin-bottom: 10px;
        }
        .progress-bar-container {
            background-color: #e0e0e0;
            border-radius: 5px;
            height: 10px;
            margin-bottom: 10px;
            overflow: hidden;
            position: relative; /* Ditambahkan untuk posisi persentase */
        }
        .progress-bar {
            height: 100%;
            background-color: #4CAF50;
            width: 0%; /* Will be set by PHP */
            border-radius: 5px;
            position: relative; /* Ditambahkan untuk teks di dalamnya */
            display: flex;
            align-items: center;
            justify-content: flex-end; /* Posisikan teks di kanan */
            padding-right: 5px; /* Sedikit padding dari kanan */
        }
        .progress-text { /* Kelas baru untuk teks persentase */
            font-size: 0.75em;
            color: #fff; /* Warna teks putih */
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5); /* Shadow agar terlihat di latar belakang hijau */
            white-space: nowrap; /* Pastikan teks tidak pecah baris */
        }
        .amount-info {
            display: flex;
            justify-content: space-between;
            font-size: 0.9em;
            color: #333;
            margin-bottom: 15px;
        }
        .donate-button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #1e3a8a;
            color: white;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        .donate-button:hover {
            background-color: #152b6a;
        }
        .no-campaigns {
            text-align: center;
            color: #777;
            padding: 50px;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1 class="text-4xl font-bold text-gray-800">Kampanye Donasi Aktif</h1>
            <p class="text-lg text-gray-600 mt-2">Ulurkan tangan bantu mereka yang membutuhkan.</p>
        </header>

        <?php if (!empty($kampanye_donasi_aktif)): ?>
            <div class="campaign-grid">
                <?php foreach ($kampanye_donasi_aktif as $kampanye): ?>
                    <?php
                        // Hitung persentase dana terkumpul
                        $persentase_terkumpul = ($kampanye['target_dana'] > 0) ? 
                                                min(100, ($kampanye['dana_terkumpul'] / $kampanye['target_dana']) * 100) : 0;

                        // PERBAIKAN: Jika persentase > 0 tapi sangat kecil, set minimal 1% agar terlihat
                        if ($persentase_terkumpul > 0 && $persentase_terkumpul < 1) {
                            $persentase_terkumpul = 1;
                        }
                        // Bulatkan untuk tampilan
                        $display_persentase = round($persentase_terkumpul);

                        // Ambil deskripsi dan pisahkan tujuan
                        $display_description = $kampanye['deskripsi'] ?? '';
                        $display_tujuan = '';

                        if (strpos($display_description, 'Tujuan:') === 0) {
                            $parts = explode("\n\n", $display_description, 2);
                            $tujuan_line = trim($parts[0]);
                            // Baris ini yang menyebabkan error, sudah dihapus
                            if (strpos($tujuan_line, 'Tujuan:') === 0) {
                                $display_tujuan = substr($tujuan_line, strlen('Tujuan:'));
                                $display_tujuan = trim($display_tujuan);
                            }
                            $display_description = (count($parts) > 1) ? trim($parts[1]) : '';
                        }
                        if (empty(trim($display_description)) || $display_description === 'Tidak ada deskripsi rinci.' || $display_description === '0') {
                            $display_description = 'Belum ada deskripsi rinci untuk kampanye ini.';
                        }
                    ?>
                    <div class="campaign-card">
                        <img src="<?= !empty($kampanye['gambar']) ? '../' . htmlspecialchars($kampanye['gambar']) : 'https://via.placeholder.com/400x200?text=Gambar+Donasi' ?>" 
                             alt="<?= htmlspecialchars($kampanye['nama_donasi']) ?>">
                        <div class="card-content">
                            <h3><?= htmlspecialchars($kampanye['nama_donasi']) ?></h3>
                            <p>
                                Tujuan: **<?= htmlspecialchars($display_tujuan) ?>**<br>
                                <?= htmlspecialchars($display_description) ?>
                            </p>
                            <div class="card-meta">
                                Kategori: <span class="font-semibold"><?= htmlspecialchars($kampanye['nama_kategori'] ?? 'Umum') ?></span><br>
                                Durasi: <?= htmlspecialchars(date('d M Y', strtotime($kampanye['tanggal_mulai']))) ?> 
                                <?php if ($kampanye['tanggal_akhir']): ?>
                                    - <?= htmlspecialchars(date('d M Y', strtotime($kampanye['tanggal_akhir']))) ?>
                                <?php else: ?>
                                    (Tidak Ada Batas Waktu)
                                <?php endif; ?>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: <?= $persentase_terkumpul ?>%;">
                                    <span class="progress-text"><?= $display_persentase ?>%</span>
                                </div>
                            </div>
                            <div class="amount-info">
                                <span>Terkumpul: **Rp <?= number_format($kampanye['dana_terkumpul'], 0, ',', '.') ?>**</span>
                                <span>Target: Rp <?= number_format($kampanye['target_dana'], 0, ',', '.') ?></span>
                            </div>
                            <a href="detail_donasi.php?id=<?= htmlspecialchars($kampanye['id_donasi']) ?>" class="donate-button">Lihat Detail & Donasi</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-campaigns">Tidak ada kampanye donasi aktif saat ini. Mohon kembali nanti.</p>
        <?php endif; ?>
    </div>
</body>
</html>