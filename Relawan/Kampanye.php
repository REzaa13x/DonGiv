<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dongiv - Kampanye Aktivitas</title>
    <link rel="stylesheet" href="Pagerelawan.css">
</head>

    <main>
        <section class="section-title">
            <h2>Cari Aktivitas, 32 aktivitas membutuhkan bantuan</h2>
            <div class="search-box">
                <input type="text" placeholder="🔍 Cari aktivitas">
            </div>
        </section>

        <section class="card-grid">
            <?php
            $stmt = $pdo->query("SELECT * FROM kegiatan ORDER BY tanggal DESC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<div class="activity-card">';
                echo '<img src="assets/' . htmlspecialchars($row['gambar']) . '" alt="' . htmlspecialchars($row['judul']) . '">';
                echo '<h3>' . htmlspecialchars($row['judul']) . '</h3>';
                echo '<div class="tags">';
                if (!empty($row['tag1'])) echo '<span class="tag">' . htmlspecialchars($row['tag1']) . '</span>';
                if (!empty($row['tag2'])) echo '<span class="tag">' . htmlspecialchars($row['tag2']) . '</span>';
                echo '</div>';
                echo '<div class="meta">';
                echo '<span>📅 ' . date('d F Y', strtotime($row['tanggal'])) . '</span>';
                echo '<span>📍 ' . htmlspecialchars($row['lokasi']) . '</span>';
                echo '</div>';
                echo '<a href="#" class="btn-small">Selengkapnya</a>';
                echo '</div>';
            }
            ?>
        </section>
    </main>
</body>
</html>
