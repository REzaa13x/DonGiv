<?php
session_start();
// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/Login.php'); // Sesuaikan path login Anda
    exit();
}

include 'koneksi.php'; // Sesuaikan path koneksi database Anda

// Ambil ID user dari sesi (akan digunakan di fase selanjutnya)
$id_user_session = $_SESSION['user_id'];

// Ambil data paket donasi rutin dari database
$routine_packages = [];
// --- QUERY YANG SUDAH DIPERBAIKI DAN MENGAMBIL PATH GAMBAR ---
$stmt_packages = $conn->prepare("SELECT 
                                rdp.id, 
                                rdp.name, 
                                rdp.description, 
                                rdp.amount_per_month, 
                                kd.nama_donasi AS program_name,
                                kd.gambar AS package_image_path -- Ini sudah mengambil path gambar dari kampanye_donasi
                                FROM donasi_rutin rdp 
                                LEFT JOIN kampanye_donasi kd ON rdp.program_id = kd.id_donasi 
                                WHERE rdp.is_active = TRUE
                                ORDER BY rdp.amount_per_month ASC");
// --- AKHIR BAGIAN QUERY YANG SUDAH DIPERBAIKI ---
try {
    $stmt_packages->execute();
    $result_packages = $stmt_packages->get_result();
    while ($row = $result_packages->fetch_assoc()) {
        $routine_packages[] = $row;
    }
} catch (mysqli_sql_exception $e) {
    error_log("Database error fetching routine packages: " . $e->getMessage());
    $error_message = "Maaf, tidak dapat memuat paket donasi rutin saat ini. (" . $e->getMessage() . ")"; // Tampilkan pesan error detail untuk debugging
} finally {
    if ($stmt_packages) {
        $stmt_packages->close();
    }
    $conn->close(); // Tutup koneksi setelah selesai
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi Rutin - DonGiv</title>
    <link rel="stylesheet" href="donasirutin.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    </head>
<body>
    <header style="background-color: #2563eb; color: white; padding: 15px 20px; text-align: center;">
        <a href="dashboard.php" style="color: white; text-decoration: none; float: left;">&#8592; Kembali</a>
        <h1>Donasi Rutin</h1>
    </header>

    <main class="main-content-donasi-rutin">
        <section class="hero-section-donasi-rutin">
            <img src="../foto/1login-removebg-preview.png" alt="Donasi" style="max-width: 150px; margin-bottom: 20px;">
            <h2>Tidak pernah ketinggalan untuk berbuat kebaikan setiap bulan</h2>
            <p>Donasi rutin hadir dalam berbagai paket.</p>
            <div class="buttons">
                <button onclick="alert('Ini adalah informasi tentang Donasi Rutin.');">Tentang donasi rutin</button>
                <button onclick="window.location.href='#packages';">Mulai berdonasi rutin</button>
            </div>
        </section>

        <section id="packages">
            <h2>Pilih paket donasi rutin</h2>
            <p>Donasi rutin hadir dalam berbagai paket untuk membantu mereka yang membutuhkan.</p>

            <?php if (isset($error_message)): ?>
                <p style="color: red; text-align: center;"><?= $error_message ?></p>
            <?php endif; ?>

            <div class="packages-grid">
                <?php if (!empty($routine_packages)): ?>
                    <?php foreach ($routine_packages as $package): ?>
                        <div class="donation-card">
                            <img src="<?= htmlspecialchars($package['package_image_path'] ?? '../foto/placeholder_donasi.jpg') ?>" alt="<?= htmlspecialchars($package['name']) ?>">
                            
                            <?php if ($package['program_name']): ?>
                                <p style="color: #666; margin-bottom: 5px;">Untuk Program: <br>#<?= htmlspecialchars($package['program_name']) ?></p>
                            <?php else: ?>
                                <p style="color: #666; margin-bottom: 5px;">Untuk Program: <br>#Umum</p>
                            <?php endif; ?>
                            
                            <h3><?= htmlspecialchars($package['name']) ?></h3>
                            <p><?= htmlspecialchars($package['description']) ?></p>
                            <div class="amount">Rp <?= number_format($package['amount_per_month'], 0, ',', '.') ?>/bulan</div>
                            <button onclick="selectRoutinePackage(<?= $package['id'] ?>, '<?= htmlspecialchars($package['name']) ?>', <?= $package['amount_per_month'] ?>)">Pilih Paket Ini</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center;">Tidak ada paket donasi rutin yang tersedia saat ini.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <p style="text-align: center; padding: 20px; background-color: #f0f0f0; margin-top: 50px;">&copy; <?= date("Y"); ?> DonGiv. All rights reserved.</p>
    </footer>

    <script>
        function selectRoutinePackage(packageId, packageName, amount) {
            alert(`Anda memilih paket: ${packageName} sebesar Rp ${amount}/bulan.\nAkan diarahkan ke proses pembayaran.`);
            
            // Dalam implementasi nyata, ganti alert ini dengan redirect ke proses pembayaran:
             window.location.href = `../payment/Payment.php?package_id=${packageId}`;
            // Atau tampilkan modal pembayaran dari payment gateway
        }
    </script>
</body>
</html>