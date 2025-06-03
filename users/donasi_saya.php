<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    echo "Anda harus login terlebih dahulu.";
    exit;
}

$user_id = $_SESSION['user_id'];

$query = "
    SELECT d.amount,
           d.donated_at,
           k.nama_donasi      AS judul_kampanye,
           k.gambar           AS gambar
    FROM donations d
    JOIN kampanye_donasi k ON d.campaign_id = k.id_donasi
    WHERE d.user = ?
    ORDER BY d.donated_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total_donasi = 0;
$donations = [];
while ($row = $result->fetch_assoc()) {
    $total_donasi += $row['amount'];
    $donations[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Donasi Saya</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f9ff;
            margin: 0;
            padding: 0;
        }

        .header {
            padding: 20px;
            font-size: 22px;
            font-weight: bold;
            color: #003f88;
            display: flex;
            align-items: center;
        }

        .back-button {
            margin-right: 15px;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .total-box {
            margin: 20px;
            background-color: #3498db;
            color: white;
            padding: 15px;
            border-radius: 8px;
            font-size: 18px;
        }

        .donation-card {
            background: white;
            display: flex;
            padding: 15px;
            margin: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,63,136,0.1);
        }

        .donation-card img {
            width: 120px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }

        .donation-info {
            margin-left: 15px;
            flex-grow: 1;
        }

        .donation-title {
            font-size: 16px;
            color: #007bff;
            font-weight: bold;
            margin-bottom: 5px;
            text-decoration: none;
        }

        .donation-meta {
            font-size: 14px;
            color: #333;
        }

        .donation-meta span {
            font-weight: bold;
            color: #000;
        }
    </style>
</head>
<body>

    <div class="header">
        <a href="prof.php" class="back-button">← Kembali</a>
        Donasi Saya
    </div>

    <div class="total-box">Total Donasi: Rp <?= number_format($total_donasi, 0, ',', '.'); ?></div>

    <?php if (empty($donations)): ?>
        <p style="text-align:center; margin: 40px;">Belum ada donasi.</p>
    <?php else: ?>
        <?php foreach ($donations as $donasi): ?>
            <div class="donation-card">
                <img src="../campaign_img/<?= htmlspecialchars($donasi['gambar']) ?>" alt="Kampanye">
                <div class="donation-info">
                    <a class="donation-title" href="#"><?= htmlspecialchars($donasi['judul_kampanye']) ?></a>
                    <div class="donation-meta">
                        Donasimu <span>Rp <?= number_format($donasi['amount'], 0, ',', '.'); ?></span><br>
                        <?= date('d M Y - H.i', strtotime($donasi['donated_at'])) ?> WIB
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>
