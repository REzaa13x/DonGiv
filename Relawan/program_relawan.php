<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../users/koneksi.php'; // SESUAIKAN PATH INI JIKA PERLU!

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search_query = $_GET['search'] ?? '';

// Query untuk mengambil program relawan yang 'active'
$sql_programs = "
    SELECT 
        p.id, 
        p.program_name, 
        p.description,
        p.start_date, 
        p.end_date, 
        p.location,
        p.volunteers_needed,   -- PASTIKAN KOLOM INI ADA DI TABEL `programs` ANDA
        p.image_url,           -- PASTIKAN KOLOM INI ADA DI TABEL `programs` ANDA
        c.category_name,
        (SELECT COUNT(pr.id) FROM program_registrations pr WHERE pr.program_id = p.id AND pr.status = 'approved') AS registered_volunteers_count
    FROM programs p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'active'
";

$where_clauses = [];
$params = [];
$param_types = "";

if (!empty($search_query)) {
    $where_clauses[] = "(p.program_name LIKE ? OR p.location LIKE ? OR p.description LIKE ? OR c.category_name LIKE ?)";
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $param_types .= "ssss";
}

if (!empty($where_clauses)) {
    $sql_programs .= " AND " . implode(" AND ", $where_clauses);
}

$sql_programs .= " ORDER BY p.start_date ASC";

$stmt_programs = $conn->prepare($sql_programs);

if (!empty($params)) {
    $stmt_programs->bind_param($param_types, ...$params);
}
$stmt_programs->execute();
$result_programs = $stmt_programs->get_result();

$total_aktivitas_ditemukan = $result_programs->num_rows;

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Aktivitas Relawan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #00BCD4; /* Warna biru utama dari logo/navbar lama */
            --primary-dark: #008C9E; /* Biru gelap untuk hover */
            --dark-blue: #212121; /* Warna teks gelap */
            --medium-gray: #424242; /* Warna teks sedang */
            --light-gray: #e0e0e0; /* Warna border/garis tipis */
            --background-light: #f0f2f5; /* Warna latar belakang ringan */
            --white: #ffffff;
            --shadow-light: 0 2px 5px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 4px 10px rgba(0, 0, 0, 0.1);

            /* Warna dari Navbar yang diberikan pengguna */
            --navbar-bg-blue: #2563eb;
            --navbar-hover-blue: #93c5fd;
            --dropdown-bg-dark: #1e293b;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-light);
            color: var(--medium-gray);
            overflow-x: hidden; /* Mencegah scroll horizontal yang tidak diinginkan */
        }

        /* Navbar (CSS dari input pengguna, disesuaikan sedikit) */
        nav {
            background-color: #2563eb; /* Menggunakan nilai langsung dari input Anda */
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
            padding: 1rem 20px; /* Diperbaiki: Menambahkan padding samping */
            max-width: 1500px;
            margin: 0 auto; /* Pusatkan navbar content */
        }
        .nav-logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        .nav-logo img {
            height: 3rem;
            margin-right: 0.5rem; /* Diperbaiki: dari 0,5rem menjadi 0.5rem */
            margin-left: 0; /* Dihapus 10px agar rata dengan padding container */
        }
        .nav-logo span {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }
        .nav-links {
            display: flex;
            gap: 1.5rem;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .nav-links a:hover {
            color: #93c5fd;
        }
        .dropdown {
            position: relative;
        }
        .dropdown img {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            border: 2px solid white;
            cursor: pointer;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            margin-top: 0.5rem;
            width: 12rem;
            background-color: #1e293b;
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
            color: white;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .dropdown-menu a:hover {
            background-color: #2563eb;
        }
        .dropdown-menu div {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .dropdown-menu p {
            margin: 0;
            color: white;
        }
        .dropdown-menu .text-sm {
            color: rgba(255, 255, 255, 0.7);
        }

        /* Main Content Wrapper */
        .main-content-wrapper { /* Menggunakan nama yang konsisten */
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* Search Section */
        .search-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .search-section h1 {
            font-size: 2.2em;
            color: var(--dark-blue);
            margin-bottom: 10px;
        }

        .search-bar-container {
            display: flex;
            align-items: center;
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: var(--shadow-medium);
            padding: 10px 20px;
            max-width: 600px;
            margin: 20px auto 0 auto; /* Pusatkan search bar */
        }

        .search-bar-container svg {
            fill: var(--medium-gray);
            width: 24px;
            height: 24px;
            margin-right: 10px;
        }

        .search-bar-container input {
            flex-grow: 1;
            border: none;
            outline: none;
            font-size: 1.1em;
            padding: 8px 0;
            color: var(--dark-blue);
        }

        /* Activity Cards Grid */
        .activity-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .activity-card {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow-light);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .activity-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .activity-card-image {
            width: 100%;
            height: 200px; /* Tinggi gambar tetap */
            object-fit: cover; /* Memastikan gambar mengisi area tanpa terdistorsi */
        }

        .activity-card-content {
            padding: 20px;
            flex-grow: 1; /* Memastikan konten mengisi ruang yang tersedia */
            display: flex;
            flex-direction: column;
        }

        .activity-card-title {
            font-size: 1.4em;
            color: var(--dark-blue);
            margin-top: 0;
            margin-bottom: 15px;
            font-weight: 600;
            line-height: 1.3;
        }

        .activity-card-categories {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 15px;
        }

        .category-tag {
            background-color: #E0F7FA; /* Light blue for tags */
            color: var(--primary-blue);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .activity-card-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-size: 0.95em;
            color: var(--medium-gray); /* Changed to medium-gray for better visibility */
        }

        .activity-card-info svg {
            fill: var(--medium-gray); /* Changed to medium-gray for better visibility */
            width: 18px;
            height: 18px;
            margin-right: 8px;
        }

        .activity-card-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-blue);
            color: var(--white);
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-top: auto; /* Dorong tombol ke bawah */
            transition: background-color 0.3s ease;
            cursor: pointer;
        }

        .activity-card-button:hover {
            background-color: var(--primary-dark); /* Menggunakan primary-dark dari root */
            color: var(--white); /* Memastikan teks tetap putih */
        }

        .activity-card-button svg {
            fill: var(--white);
            width: 15px;
            height: 15px;
            margin-left: 8px;
        }

        /* Footer */
        .footer {
            background-color: var(--dark-blue);
            color: var(--white);
            text-align: center;
            padding: 20px;
            margin-top: 50px;
            font-size: 0.9em;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                padding: 1rem 15px; /* Padding disesuaikan untuk mobile */
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

            .main-content-wrapper {
                margin: 20px auto;
                padding: 0 15px;
            }

            .search-section h1 {
                font-size: 1.8em;
            }

            .search-bar-container {
                padding: 8px 15px;
            }

            .search-bar-container input {
                font-size: 1em;
            }

            .activity-grid {
                grid-template-columns: 1fr; /* Satu kolom di layar kecil */
                gap: 20px;
            }

            .activity-card-image {
                height: 180px;
            }

            .activity-card-title {
                font-size: 1.2em;
            }

            .category-tag {
                padding: 5px 10px;
                font-size: 0.8em;
            }

            .activity-card-info {
                font-size: 0.85em;
            }

            .activity-card-button {
                padding: 10px 15px;
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar (dari input pengguna) -->
    <nav>
        <div class="nav-container">
            <a href="../users/DonGiv.php" class="nav-logo">
                <img src="../foto/1-removebg-preview (1).png" alt="DonGiv-Logo" />
                <span>DonGiv</span>
            </a>

            <div class="nav-links">
                <a href="../users/DonGiv.php#Home">Home</a>
                <a href="../users/DonGiv.php#Donations">Donations</a>
                <a href="../users/DonGiv.php#About">About</a>
                <a href="../users/DonGiv.php#Contact">Contact</a>

                <!-- Dropdown User -->
                <div class="dropdown">
                    <img src="../foto/user.png" alt="User" id="dropdown-btn" />
                    <div class="dropdown-menu">
                        <div class="dropdown-user-info">
                            <p class="font-semibold"><?= htmlspecialchars($username ?? 'Guest') ?></p>
                            <p class="text-sm"><?= htmlspecialchars($data['email'] ?? 'guest@example.com') ?></p>
                        </div>
                        <a href="prof.php">Profile</a>
                        <a href="setting.php">Settings</a>
                        <a href="../auth/logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="main-content-wrapper">

        <!-- Section: Daftar Aktivitas -->
        <div id="activity-list-view">
            <!-- Search Section -->
            <section class="search-section">
                <h1>Cari Aktivitas, <?php echo $total_aktivitas_ditemukan; ?> aktivitas membutuhkan bantuan</h1>
                <form method="GET" action="DaftarAktivitasRelawan.php" class="search-bar-container">
                    <svg viewBox="0 0 24 24">
                        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                    <input type="text" name="search" placeholder="Cari aktivitas" value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" style="display: none;"></button> <!-- Hidden submit button for search -->
                </form>
            </section>

            <!-- Activity Cards Grid -->
            <section class="activity-grid">
                <?php if ($result_programs->num_rows > 0): ?>
                    <?php while($program = $result_programs->fetch_assoc()): ?>
                        <div class="activity-card">
                            <!-- Menggunakan image_url dari database, dengan placeholder jika tidak ada -->
                            <img src="<?php echo htmlspecialchars($program['image_url'] ?? 'https://placehold.co/600x400/B2EBF2/00BCD4?text=Gambar+Tidak+Tersedia'); ?>" 
                                 alt="<?php echo htmlspecialchars($program['program_name'] ?? 'Program'); ?>" 
                                 class="activity-card-image"
                                 onerror="this.onerror=null;this.src='https://placehold.co/600x400/B2EBF2/00BCD4?text=Gambar+Tidak+Tersedia';">
                            <div class="activity-card-content">
                                <h3 class="activity-card-title"><?php echo htmlspecialchars($program['program_name'] ?? 'Nama Program Tidak Tersedia'); ?></h3>
                                <div class="activity-card-categories">
                                    <?php if (!empty($program['category_name'])): ?>
                                        <span class="category-tag"><?php echo htmlspecialchars($program['category_name']); ?></span>
                                    <?php else: ?>
                                        <!-- Optional: placeholder for category if not available -->
                                        <span class="category-tag">Umum</span>
                                    <?php endif; ?>
                                </div>
                                <div class="activity-card-info">
                                    <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
                                    <span><?php echo htmlspecialchars(date('d M Y', strtotime($program['start_date'] ?? 'now'))); ?> - <?php echo htmlspecialchars(date('d M Y', strtotime($program['end_date'] ?? 'now'))); ?></span>
                                </div>
                                <div class="activity-card-info">
                                    <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                    <span><?php echo htmlspecialchars($program['location'] ?? 'Lokasi Tidak Tersedia'); ?></span>
                                </div>
                                <div class="activity-card-info">
                                    <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                    <span>Relawan Terdaftar: <?php echo htmlspecialchars($program['registered_volunteers_count'] ?? 0); ?> / <?php echo htmlspecialchars($program['volunteers_needed'] ?? 0); ?></span>
                                </div>
                                <a href="detail_relawan.php?id=<?php echo htmlspecialchars($program['id']); ?>" class="activity-card-button">
                                    Selengkapnya
                                    <svg viewBox="0 0 24 24"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; width: 100%; grid-column: 1 / -1; padding: 20px;">Tidak ada aktivitas relawan yang ditemukan.</p>
                <?php endif; ?>
            </section>
        </div> <!-- End of activity-list-view -->

    </div> <!-- End of main-content-wrapper -->

    <!-- Footer -->
    <footer class="footer">
        &copy; 2025 DonGiv. All rights reserved.
    </footer>

    <script>
        // JavaScript for dropdown functionality (dari input pengguna)
        document.addEventListener('DOMContentLoaded', () => {
            const dropdownBtn = document.getElementById('dropdown-btn');
            const dropdown = document.querySelector('.dropdown');

            if (dropdownBtn && dropdown) {
                dropdownBtn.addEventListener('click', () => {
                    dropdown.classList.toggle('active');
                });

                // Close the dropdown if clicked outside
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
