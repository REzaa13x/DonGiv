<?php
session_start(); // Mulai sesi paling awal, hanya sekali.

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/Login.php'); // Redirect ke halaman login jika belum login
    exit();
}

// Sertakan file koneksi database
// Sesuaikan path ini sesuai lokasi file koneksi.php Anda
include 'koneksi.php'; // Asumsi 'koneksi.php' berada di direktori yang sama dengan DonGiv.php

// Ambil ID user dari sesi
$id_user_session = $_SESSION['user_id'];

// Ambil data user dari database berdasarkan ID sesi
// Ini lebih robust karena mengambil data terbaru dari DB
$query_user = mysqli_query($conn, "SELECT id, name, email FROM users WHERE id='$id_user_session' LIMIT 1");
$data_user = mysqli_fetch_assoc($query_user);

// Pastikan data user ditemukan
if (!$data_user) {
    // Jika data user tidak ditemukan di DB, mungkin sesi tidak valid atau user sudah dihapus
    session_destroy(); // Hancurkan sesi
    header('Location: ../auth/Login.php'); // Redirect ke halaman login
    exit();
}

// Gunakan data dari database untuk ditampilkan di dropdown user
$username_display = htmlspecialchars($data_user['name']);
$email_display = htmlspecialchars($data_user['email']);

// --- Logika Pengambilan Notifikasi Terbaru untuk Pop-up (Toast) ---
// Ini adalah kode PHP yang akan mengambil notifikasi dari database.
// Pastikan tabel `notifications` sudah ada dan memiliki kolom `user_id` (INT NULL).

$sql_dashboard_notifs = "SELECT type, message, created_at FROM notifications WHERE user_id = ? OR user_id IS NULL ORDER BY created_at DESC LIMIT 2"; // Ambil 2 notifikasi terbaru
$stmt_dashboard_notifs = null;
$notifications_to_show = []; // Untuk menyimpan hasil notifikasi yang akan ditampilkan sebagai pop-up

try {
    $stmt_dashboard_notifs = $conn->prepare($sql_dashboard_notifs);
    $stmt_dashboard_notifs->bind_param("i", $id_user_session); // Bind user_id yang sedang login
    $stmt_dashboard_notifs->execute();
    $result_db_notifs = $stmt_dashboard_notifs->get_result();

    if ($result_db_notifs && $result_db_notifs->num_rows > 0) {
        while ($row_notif = $result_db_notifs->fetch_assoc()) {
            $notifications_to_show[] = $row_notif;
        }
    }
} catch (mysqli_sql_exception $e) {
    // Tangani jika terjadi kesalahan database
    error_log("Database error fetching toast notifications in DonGiv.php: " . $e->getMessage());
} finally {
    if ($stmt_dashboard_notifs) {
        $stmt_dashboard_notifs->close();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>DonGiv - Home</title>

  <!-- CSS -->
  <link rel="stylesheet" href="DonGiv.css" />
  <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

  <!-- Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />

  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }
    :root {
      scroll-behavior: smooth;
    }
  </style>
</head>

<body class="overflow-x-hidden">
    <div id="toastContainer" class="toast-notification-container"></div>

    <nav>
        <div class="nav-container">
            <a href="DonGiv.php" class="nav-logo">
                <img src="../foto/1-removebg-preview (1).png" alt="DonGiv-Logo" />
                <span>DonGiv</span>
            </a>

            <div class="nav-links">
                <a href="DonGiv.php">Home</a>
                <a href="#Donations">Donations</a>
                <a href="#Volunter">Volunteer</a>
                <a href="#About">About</a>
                <a href="#Contact">Contact</a>

                <div class="dropdown">
                    <img src="../foto/user.png" alt="User" id="dropdown-btn" />
                    <div class="dropdown-menu">
                        <div class="dropdown-user-info">
                            <p class="font-semibold"><?= $username_display ?></p>
                            <p class="text-sm"><?= $email_display ?></p>
                        </div>
                        <a href="prof.php">Profile</a>
                        <a href="setting.php">Settings</a>
                        <a href="../auth/logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>  

    <main>
        <?php include '../partials/hero.php'; ?>
        <?php include '../partials/donations.php'; ?>
        <?php include '../Relawan/Pagerelawan.php'; ?>
        <?php include '../partials/review.php'; ?>
        <?php include '../partials/about.php'; ?>
        <?php include '../partials/contact.php'; ?>
    </main>
    <?php include '../partials/footer.php'; ?>

    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script src="DonGiv.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>

    <script>
        // Data notifikasi dari PHP, akan berisi 1 atau 2 notifikasi terbaru
        const notificationsData = <?= json_encode($notifications_to_show); ?>;

        // Fungsi untuk menampilkan toast notifikasi
        function showToastNotification(notificationContent) {
            const toastContainer = document.getElementById('toastContainer');
            if (!toastContainer) {
                console.error("Toast container not found!");
                return; 
            }

            const toast = document.createElement('div');
            toast.classList.add('toast-notification');
            
            // Tentukan kelas kategori (opsional, untuk warna)
            let categoryClass = 'info'; // Default
            if (notificationContent.type && notificationContent.type.includes('Donasi Baru')) {
                categoryClass = 'success';
            } else if (notificationContent.type && notificationContent.type.includes('Pengingat')) {
                categoryClass = 'error'; 
            } else if (notificationContent.type && notificationContent.type.includes('Pesan Admin')) { // Tambah ini
                categoryClass = 'info'; // Atau warna lain untuk pesan admin
            }
            toast.classList.add(categoryClass);

            // Hitung waktu yang lalu
            const now = new Date();
            let timeAgo = 'Baru saja';
            if (notificationContent.created_at) {
                const createdDate = new Date(notificationContent.created_at);
                const diffMs = now - createdDate;
                const diffMinutes = Math.round(diffMs / (1000 * 60));

                if (diffMinutes > 60 * 24 * 30 * 12) {
                    timeAgo = Math.round(diffMinutes / (60 * 24 * 30 * 12)) + ' tahun yang lalu';
                } else if (diffMinutes > 60 * 24 * 30) {
                    timeAgo = Math.round(diffMinutes / (60 * 24 * 30)) + ' bulan yang lalu';
                } else if (diffMinutes > 60 * 24) {
                    timeAgo = Math.round(diffMinutes / (60 * 24)) + ' hari yang lalu';
                } else if (diffMinutes > 60) {
                    timeAgo = Math.round(diffMinutes / 60) + ' jam yang lalu';
                } else if (diffMinutes > 0) {
                    timeAgo = diffMinutes + ' menit yang lalu';
                }
            }
            
            toast.innerHTML = `
                <strong>${notificationContent.type || 'Notifikasi'}</strong>
                <p>${notificationContent.message || 'Pesan kosong'} <span style="font-size: 0.8em; color: rgba(255,255,255,0.7);">(${timeAgo})</span></p>
            `;

            toastContainer.appendChild(toast);

            // Set timer untuk menghilangkan notifikasi setelah animasi masuk selesai
            setTimeout(() => {
                toast.style.animation = 'fadeOut 0.5s forwards'; // Mulai animasi fadeOut
                setTimeout(() => {
                    toast.remove(); // Hapus elemen dari DOM setelah animasi fadeOut selesai
                }, 500); // Sesuai durasi animasi fadeOut
            }, 5000); // Notifikasi akan terlihat selama 5 detik (0.5s masuk + 4.5s diam)
        }

        // Tampilkan notifikasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', () => {
            if (notificationsData.length === 0) {
                console.log("No notifications to display.");
                return;
            }
            notificationsData.forEach((notif, index) => {
                // Beri sedikit jeda antar notifikasi jika ada beberapa
                setTimeout(() => {
                    showToastNotification(notif);
                }, index * 700); // Jeda 0.7 detik antar notifikasi
            });
        });

        // Contoh cara memicu notifikasi manual (misal dari AJAX request)
        // showToastNotification({ type: 'Info Baru', message: 'Ada pembaruan penting!', created_at: new Date().toISOString() });
    </script>
</body>
</html>
