<?php
session_start();

// Periksa apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    // Redirect ke halaman login jika belum login
    // Asumsi file Login.php ada di auth/ di root project
    header("Location: ../auth/Login.php");
    exit();
}

ini_set('display_errors', 0); // Matikan display errors untuk lingkungan produksi
ini_set('display_startup_errors', 0);
error_reporting(E_ALL); // Biarkan error_reporting E_ALL untuk logging internal jika ada masalah

// PATH KONEKSI.PHP DISESUAIKAN:
// Karena NotifikasiUser.php ada di users/, dan koneksi.php juga ada di users/,
// maka path-nya cukup 'koneksi.php'
include 'koneksi.php';

// Cek koneksi database
if ($conn->connect_error) {
    // Di produksi, sebaiknya log error ini dan tampilkan pesan generik
    error_log("Database Connection failed: " . $conn->connect_error);
    die("Terjadi kesalahan koneksi database. Silakan coba lagi nanti.");
}

// Ambil user ID dari session
$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'] ?? ''; // Ambil email user dari session

// Ambil nama user untuk ditampilkan di navbar
$username = $_SESSION['username'] ?? 'Guest';
$data = ['email' => $user_email]; // Untuk ditampilkan di dropdown navbar

$notifications = []; // Inisialisasi array notifikasi

// Hanya jalankan query jika email user tersedia di session
if (!empty($user_email)) {
    // Query untuk mengambil semua pendaftaran relawan yang terkait dengan user ini
    // Status 'pending' juga ditampilkan agar user tahu mana yang masih menunggu
    $sql_notifications = "
        SELECT
            pr.id AS registration_id,
            pr.status,
            pr.registration_date,
            pr.verified_at,
            p.id AS program_id,
            p.program_name,
            p.start_date,
            p.end_date,
            p.location,
            p.image_url,
            c.category_name
        FROM program_registrations pr
        JOIN volunteers v ON pr.volunteer_id = v.id
        JOIN programs p ON pr.program_id = p.id
        JOIN categories c ON p.category_id = c.id
        WHERE v.email = ? -- Menggunakan email relawan untuk mencocokkan user yang login
        ORDER BY pr.registration_date DESC
    ";

    $stmt_notifications = $conn->prepare($sql_notifications);

    if ($stmt_notifications) {
        $stmt_notifications->bind_param("s", $user_email);
        $stmt_notifications->execute();
        $result_notifications = $stmt_notifications->get_result();

        if ($result_notifications) {
            while ($row = $result_notifications->fetch_assoc()) {
                $notifications[] = $row;
            }
        } else {
            // Log error jika get_result gagal
            error_log("Get result failed for notifications query: " . $stmt_notifications->error);
        }
        $stmt_notifications->close();
    } else {
        // Log error jika prepare gagal
        error_log("Prepare failed for notifications query: " . $conn->error);
    }
} else {
    // Jika email user kosong, ini akan tercatat di log (tidak tampil di browser)
    error_log("User logged in but user_email is empty for user_id: " . $user_id);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Anda - DonGiv</title>
    <link href="[https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap](https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap)" rel="stylesheet">
    <link rel="stylesheet" href="[https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css](https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css)" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            --primary-blue: #00BCD4;
            --primary-dark: #008C9E;
            --dark-blue: #212121;
            --medium-gray: #424242;
            --light-gray: #e0e0e0;
            --background-light: #f0f2f5;
            --white: #ffffff;
            --navbar-bg-blue: #2563eb;
            --navbar-hover-blue: #93c5fd;
            --dropdown-bg-dark: #1e293b;
            --success-green: #4CAF50;
            --danger-red: #F44336;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-light);
            color: var(--medium-gray);
            overflow-x: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Navbar */
        nav {
            background-color: var(--navbar-bg-blue);
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
        }
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 20px;
            max-width: 1500px;
            margin: 0 auto;
        }
        .nav-logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        .nav-logo img {
            height: 3rem;
            margin-right: 0.5rem;
            margin-left: 0;
        }
        .nav-logo span {
            color: var(--white);
            font-size: 1.5rem;
            font-weight: 600;
        }
        .nav-links {
            display: flex;
            gap: 1.5rem;
        }
        .nav-links a {
            color: var(--white);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .nav-links a:hover {
            color: var(--navbar-hover-blue);
        }
        .dropdown {
            position: relative;
        }
        .dropdown img {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            border: 2px solid var(--white);
            cursor: pointer;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            margin-top: 0.5rem;
            width: 12rem;
            background-color: var(--dropdown-bg-dark);
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 60;
        }
        .dropdown.active .dropdown-menu {
            display: block;
        }
        .dropdown-menu a {
            display: block;
            padding: 0.75rem;
            color: var(--white);
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .dropdown-menu a:hover {
            background-color: var(--navbar-bg-blue);
        }
        .dropdown-menu div {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .dropdown-menu p {
            margin: 0;
            color: var(--white);
        }
        .dropdown-menu .text-sm {
            color: rgba(255, 255, 255, 0.7);
        }

        /* Main Content Area */
        .main-content {
            flex: 1; /* Agar konten mengisi sisa ruang vertikal */
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .main-content h1 {
            font-size: 2.2em;
            color: var(--dark-blue);
            margin-bottom: 30px;
            text-align: center;
            border-bottom: 2px solid var(--primary-blue);
            padding-bottom: 15px;
        }

        .notification-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .notification-item {
            display: flex;
            align-items: flex-start;
            background-color: var(--background-light);
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .notification-item.approved { border-left: 5px solid var(--success-green); }
        .notification-item.rejected { border-left: 5px solid var(--danger-red); }
        .notification-item.pending { border-left: 5px solid #FFC107; /* Yellow */ }
        .notification-item.completed { border-left: 5px solid #2196F3; /* Blue */ }


        .notification-icon {
            font-size: 1.5em;
            margin-right: 15px;
            padding-top: 5px; /* Adjust vertical alignment */
        }
        .notification-item.approved .notification-icon { color: var(--success-green); }
        .notification-item.rejected .notification-icon { color: var(--danger-red); }
        .notification-item.pending .notification-icon { color: #FFC107; }
        .notification-item.completed .notification-icon { color: #2196F3; }


        .notification-content {
            flex: 1;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .notification-header h3 {
            margin: 0;
            font-size: 1.1em;
            color: var(--dark-blue);
        }

        .notification-date {
            font-size: 0.8em;
            color: var(--medium-gray);
        }

        .notification-body p {
            margin: 5px 0;
            line-height: 1.5;
            color: var(--medium-gray);
        }

        .notification-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75em;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 10px;
        }

        .notification-status.pending { background-color: #ffeb3b; color: #5d4037; }
        .notification-status.approved { background-color: #e8f5e9; color: var(--success-green); }
        .notification-status.rejected { background-color: #ffebee; color: var(--danger-red); }
        .notification-status.completed { background-color: #e3f2fd; color: #2196F3; }

        .program-details {
            margin-top: 10px;
            background-color: var(--white);
            padding: 10px;
            border-radius: 5px;
            border: 1px solid var(--light-gray);
            font-size: 0.9em;
        }

        .program-details p {
            margin: 3px 0;
        }

        .no-notifications {
            text-align: center;
            color: var(--medium-gray);
            padding: 50px;
            font-size: 1.1em;
        }

        /* Footer */
        .footer {
            background-color: var(--dark-blue);
            color: var(--white);
            text-align: center;
            padding: 20px;
            margin-top: auto; /* Push footer to bottom */
            font-size: 0.9em;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                padding: 1rem 15px;
            }
            .nav-links {
                margin-top: 1rem;
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem;
            }
            .dropdown {
                margin-top: 1rem;
            }
            .dropdown-menu {
                left: 50%;
                transform: translateX(-50%);
            }
            .main-content {
                margin: 20px auto;
                padding: 15px;
            }
            .main-content h1 {
                font-size: 1.8em;
                margin-bottom: 20px;
            }
            .notification-item {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            .notification-icon {
                margin-right: 0;
                margin-bottom: 10px;
            }
            .notification-header {
                flex-direction: column;
                align-items: center;
                gap: 5px;
            }
            .notification-status {
                margin-left: 0;
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>

    <nav>
        <div class="nav-container">
            <a href="../DonGiv.php" class="nav-logo"> <img src="../foto/1-removebg-preview (1).png" alt="DonGiv-Logo" /> <span>DonGiv</span>
            </a>

            <div class="nav-links">
                <a href="../DonGiv.php#Home">Home</a> <a href="../DonGiv.php#Donations">Donations</a>
                <a href="../DaftarAktivitasRelawan.php">Volunteer Activities</a>
                <a href="NotifikasiUser.php" class="active">Notifications</a> <a href="../DonGiv.php#About">About</a>
                <a href="../DonGiv.php#Contact">Contact</a>

                <div class="dropdown">
                    <img src="../foto/user.png" alt="User" id="dropdown-btn" /> <div class="dropdown-menu">
                        <div class="dropdown-user-info">
                            <p class="font-semibold"><?= htmlspecialchars($username ?? 'Guest') ?></p>
                            <p class="text-sm"><?= htmlspecialchars($data['email'] ?? 'guest@example.com') ?></p>
                        </div>
                        <a href="../prof.php">Profile</a> <a href="../setting.php">Settings</a> <a href="../auth/logout.php">Logout</a> </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <h1>Notifikasi Anda</h1>

        <?php if (empty($notifications)): ?>
            <p class="no-notifications">Anda belum memiliki notifikasi pendaftaran program.</p>
        <?php else: ?>
            <div class="notification-list">
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?= htmlspecialchars($notification['status']) ?>">
                        <div class="notification-icon">
                            <?php
                            // Tampilkan ikon berdasarkan status
                            if ($notification['status'] === 'approved') {
                                echo '<i class="fas fa-check-circle"></i>';
                            } elseif ($notification['status'] === 'rejected') {
                                echo '<i class="fas fa-times-circle"></i>';
                            } elseif ($notification['status'] === 'pending') {
                                echo '<i class="fas fa->clock"></i>'; // Ikon jam untuk pending
                            } elseif ($notification['status'] === 'completed') {
                                echo '<i class="fas fa-trophy"></i>'; // Ikon piala untuk selesai
                            } else {
                                echo '<i class="fas fa-info-circle"></i>'; // Ikon info default
                            }
                            ?>
                        </div>
                        <div class="notification-content">
                            <div class="notification-header">
                                <h3>Status Pendaftaran Program: "<?= htmlspecialchars($notification['program_name']) ?>"</h3>
                                <span class="notification-date">
                                    <?php
                                    // Tampilkan tanggal notifikasi (tanggal verifikasi jika ada, jika tidak, tanggal pendaftaran)
                                    if ($notification['verified_at']) {
                                        echo 'Verifikasi: ' . htmlspecialchars(date('d M Y H:i', strtotime($notification['verified_at'])));
                                    } else {
                                        echo 'Daftar: ' . htmlspecialchars(date('d M Y H:i', strtotime($notification['registration_date'])));
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="notification-body">
                                <?php if ($notification['status'] === 'approved'): ?>
                                    <p>Selamat! Pendaftaran Anda untuk program <strong>"<?= htmlspecialchars($notification['program_name']) ?>"</strong> telah <strong>disetujui</strong>.</p>
                                    <p>Koordinator program akan segera menghubungi Anda. Silakan bersiap untuk program.</p>
                                <?php elseif ($notification['status'] === 'rejected'): ?>
                                    <p>Mohon maaf, pendaftaran Anda untuk program <strong>"<?= htmlspecialchars($notification['program_name']) ?>"</strong> telah <strong>ditolak</strong>.</p>
                                    <p>Silakan coba program relawan kami yang lain.</p>
                                <?php elseif ($notification['status'] === 'pending'): ?>
                                    <p>Pendaftaran Anda untuk program <strong>"<?= htmlspecialchars($notification['program_name']) ?>"</strong> masih dalam status <strong>menunggu verifikasi</strong> oleh admin.</p>
                                    <p>Kami akan memberitahu Anda setelah statusnya diperbarui.</p>
                                <?php elseif ($notification['status'] === 'completed'): ?>
                                           <p>Program <strong>"<?= htmlspecialchars($notification['program_name']) ?>"</strong> yang Anda ikuti telah <strong>selesai</strong>!</p>
                                           <p>Terima kasih atas partisipasi dan kontribusi Anda.</p>
                                <?php endif; ?>
                            </div>
                            <div class="program-details">
                                <p><strong>Kategori:</strong> <?= htmlspecialchars($notification['category_name']) ?></p>
                                <p><strong>Lokasi:</strong> <?= htmlspecialchars($notification['location']) ?></p>
                                <p><strong>Tanggal:</strong> <?= htmlspecialchars(date('d M Y', strtotime($notification['start_date']))) ?> - <?= htmlspecialchars(date('d M Y', strtotime($notification['end_date']))) ?></p>
                                <p><a href="../Relawan/detail_relawan.php?id=<?= $notification['program_id'] ?>" style="color: var(--primary-blue); text-decoration: underline;">Lihat Detail Program</a></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        &copy; 2025 DonGiv. All rights reserved.
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dropdownBtn = document.getElementById('dropdown-btn');
            const dropdown = document.querySelector('.dropdown');

            if (dropdownBtn && dropdown) {
                dropdownBtn.addEventListener('click', () => {
                    dropdown.classList.toggle('active');
                });

                document.addEventListener('click', (event) => {
                    if (!dropdown.contains(event.target) && !dropdownBtn.contains(event.target)) {
                        dropdown.classList.remove('active');
                    }
                });
            }
        });
    </script>
</body>
</html>