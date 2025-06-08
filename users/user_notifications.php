<?php
session_start(); // Pastikan session dimulai di awal file

// Redirect ke halaman login jika user belum login
// Asumsi: 'user_id' diset di $_SESSION saat user berhasil login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); // Sesuaikan path ke halaman login Anda
    exit();
}

// Sertakan file koneksi database
// Sesuaikan path ini sesuai lokasi file koneksi.php Anda
// Jika user_notifications.php ada di folder 'user' dan koneksi.php ada di folder 'users' (di luar 'user'), maka path ini harus benar.
// Contoh: user/user_notifications.php -> users/koneksi.php
include 'koneksi.php'; 

// Pastikan koneksi berhasil
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Ambil nama user dari sesi untuk ditampilkan
$user_name = htmlspecialchars($_SESSION['user_name'] ?? 'DonGiv User'); // Default jika nama tidak ada di sesi
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Anda - DonGiv</title>
    <link rel="stylesheet" href="user_style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <header>
        <h1>Selamat Datang, <?= $user_name; ?></h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="profil.php">Profil</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <section class="user-notifications">
            <h2>Notifikasi Terbaru Untuk Anda</h2>
            <div class="notification-list">
                <?php
                // --- Logika Pengambilan Notifikasi ---
                // PENTING: Untuk menampilkan notifikasi, tabel `notifications` harus ada di database Anda.
                // Jika Anda belum membuatnya, silakan buat dulu (lihat jawaban sebelumnya).

                $sql_notifications = "SELECT type, message, created_at FROM notifications ";
                
                // Menambahkan filter jika Anda ingin notifikasi spesifik per user
                // Asumsi: Tabel 'notifications' memiliki kolom 'user_id' (nullable, jika notifikasi umum)
                // dan $_SESSION['user_id'] berisi ID user yang sedang login.
                $user_id_logged_in = $_SESSION['user_id'];
                
                // Contoh: Menampilkan notifikasi yang spesifik untuk user_id ini ATAU notifikasi umum (user_id IS NULL)
                // Jika tabel notifications tidak memiliki kolom user_id, hapus kondisi WHERE di bawah ini.
                $sql_notifications .= "WHERE user_id = ? OR user_id IS NULL "; 
                
                $sql_notifications .= "ORDER BY created_at DESC LIMIT 10";

                $stmt = null; // Inisialisasi stmt

                try {
                    $stmt = $conn->prepare($sql_notifications);
                    
                    // Bind parameter jika ada WHERE clause yang menggunakan user_id
                    // Perhatikan 'i' untuk integer (user_id)
                    $stmt->bind_param("i", $user_id_logged_in); 
                    
                    $stmt->execute();
                    $result_notifications = $stmt->get_result();

                    if ($result_notifications && $result_notifications->num_rows > 0) {
                        while ($row_notif = $result_notifications->fetch_assoc()) {
                            // Logika perhitungan waktu yang lalu
                            $time_ago = '';
                            $datetime = new DateTime($row_notif['created_at']);
                            $now = new DateTime();
                            $interval = $now->diff($datetime);

                            if ($interval->y > 0) { $time_ago = $interval->y . ' tahun yang lalu'; }
                            elseif ($interval->m > 0) { $time_ago = $interval->m . ' bulan yang lalu'; }
                            elseif ($interval->d > 0) { $time_ago = $interval->d . ' hari yang lalu'; }
                            elseif ($interval->h > 0) { $time_ago = $interval->h . ' jam yang lalu'; }
                            elseif ($interval->i > 0) { $time_ago = $interval->i . ' menit yang lalu'; }
                            else { $time_ago = 'Baru saja'; }

                            echo '<div class="notification-item">';
                            echo '<p><strong>' . htmlspecialchars($row_notif['type']) . ':</strong> ' . htmlspecialchars($row_notif['message']) . '</p>';
                            echo '<span class="timestamp">' . $time_ago . '</span>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>Belum ada notifikasi terbaru untuk Anda.</p>';
                    }
                } catch (mysqli_sql_exception $e) {
                    // Tangani jika tabel 'notifications' tidak ada atau error database lainnya
                    error_log("Database error in user_notifications.php: " . $e->getMessage());
                    echo '<p>Maaf, terjadi kesalahan saat memuat notifikasi. Silakan coba lagi nanti.</p>';
                    // Opsional: Tampilkan notifikasi statis sebagai fallback jika database bermasalah
                    // echo '<div class="notification-item"><p><strong>Donasi Baru:</strong> Donasi Anda telah diterima.</p><span class="timestamp">Baru saja</span></div>';
                } finally {
                    if ($stmt) {
                        $stmt->close(); // Tutup statement jika sudah digunakan
                    }
                    $conn->close(); // Tutup koneksi database
                }
                ?>
            </div>
        </section>

        </main>

    <footer>
        <p>&copy; <?= date("Y"); ?> DonGiv. All rights reserved.</p>
    </footer>
</body>
</html>