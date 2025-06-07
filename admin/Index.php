<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../users/koneksi.php'; // SESUAIKAN PATH INI JIKA PERLU!

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Data untuk Donasi (Existing) ---
// Total Donasi (Berhasil Dibayar)
$sql_total_donasi = "SELECT SUM(amount) as total_donasi FROM donations WHERE payment_status = 'settlement'";
$result_total_donasi = $conn->query($sql_total_donasi);
$total_donasi = 0;
if ($result_total_donasi && $result_total_donasi->num_rows > 0) {
    $row_total_donasi = $result_total_donasi->fetch_assoc();
    $total_donasi = $row_total_donasi['total_donasi'];
}

// Total Kampanye (aktif) - Menghitung kampanye yang sedang berjalan
$sql_kampanye = "SELECT COUNT(id_donasi) as total_kampanye FROM kampanye_donasi WHERE status = 'active'";
$result_kampanye = $conn->query($sql_kampanye);
$total_kampanye = 0;
if ($result_kampanye && $result_kampanye->num_rows > 0) {
    $row_kampanye = $result_kampanye->fetch_assoc();
    $total_kampanye = $row_kampanye['total_kampanye'];
}

// Total Donatur (Berhasil Dibayar)
$sql_donatur = "
    SELECT COUNT(DISTINCT CASE WHEN user IS NOT NULL AND user != '' THEN user ELSE email END) AS total_donatur
    FROM donations WHERE payment_status = 'settlement';
";
$result_donatur = $conn->query($sql_donatur);
$total_donatur = 0;
if ($result_donatur && $result_donatur->num_rows > 0) {
    $row_donatur = $result_donatur->fetch_assoc();
    $total_donatur = $row_donatur['total_donatur'];
}

// Grafik Kurva Dana Masuk
$sql_income_chart = "
    SELECT DATE_FORMAT(donated_at, '%Y-%m') AS month_key, DATE_FORMAT(donated_at, '%M') AS month_name, SUM(amount) AS total_amount
    FROM donations
    WHERE payment_status = 'settlement'
    AND donated_at >= DATE_SUB(CURDATE(), INTERVAL 8 MONTH)
    GROUP BY month_key, month_name
    ORDER BY month_key ASC;
";
$result_income_chart = $conn->query($sql_income_chart);

$incomeChartLabels = [];
$incomeChartValues = [];
$currentDate = new DateTime();
$monthsData = [];
for ($i = 7; $i >= 0; $i--) {
    $date = clone $currentDate;
    $date->modify("-$i months");
    $monthKey = $date->format('Y-m');
    $monthName = $date->format('F');
    $incomeChartLabels[] = $monthName;
    $monthsData[$monthKey] = 0;
}

if ($result_income_chart && $result_income_chart->num_rows > 0) {
    while ($row = $result_income_chart->fetch_assoc()) {
        $monthsData[$row['month_key']] = (float)$row['total_amount'];
    }
}
$incomeChartValues = array_values($monthsData);

// Pie Chart - Persentase Donasi per Kategori
$sql_pie_chart = "
    SELECT kd.nama_kategori AS category_name, SUM(d.amount) AS total_amount_per_category
    FROM donations d
    JOIN kampanye_donasi k ON d.campaign_id = k.id_donasi
    JOIN kategori_donasi kd ON k.id_kategori = kd.id_kategori
    WHERE d.payment_status = 'settlement'
    GROUP BY kd.nama_kategori
    ORDER BY total_amount_per_category DESC;
";
$result_pie_chart = $conn->query($sql_pie_chart);

$pieChartLabels = [];
$pieChartValues = [];
if ($result_pie_chart && $result_pie_chart->num_rows > 0) {
    while ($row = $result_pie_chart->fetch_assoc()) {
        $pieChartLabels[] = $row['category_name'];
        $pieChartValues[] = (float)$row['total_amount_per_category'];
    }
}

if (empty($pieChartLabels)) {
    $pieChartLabels = ['Tidak Ada Data'];
    $pieChartValues = [1]; // Placeholder for empty data
}

// Detail Donasi per Bulan
$sql_detail_donasi_table = "
    SELECT DATE_FORMAT(donated_at, '%M %Y') AS bulan, SUM(amount) AS jumlah_donasi, COUNT(DISTINCT CASE WHEN user IS NOT NULL AND user != '' THEN user ELSE email END) AS jumlah_donatur_per_bulan
    FROM donations
    WHERE payment_status = 'settlement'
    GROUP BY DATE_FORMAT(donated_at, '%Y-%m')
    ORDER BY MIN(donated_at) ASC;
";
$result_detail_donasi_table = $conn->query($sql_detail_donasi_table);


// --- Data untuk Relawan (New) ---

// Total Relawan
$sql_total_relawan = "SELECT COUNT(id) as total_relawan FROM volunteers";
$result_total_relawan = $conn->query($sql_total_relawan);
$total_relawan = 0;
if ($result_total_relawan && $result_total_relawan->num_rows > 0) {
    $row_total_relawan = $result_total_relawan->fetch_assoc();
    $total_relawan = $row_total_relawan['total_relawan'];
}

// Pendaftar Baru (Relawan dengan status 'pending')
$sql_pendaftar_baru = "SELECT COUNT(id) as pendaftar_baru FROM volunteers WHERE status = 'pending'";
$result_pendaftar_baru = $conn->query($sql_pendaftar_baru);
$pendaftar_baru = 0;
if ($result_pendaftar_baru && $result_pendaftar_baru->num_rows > 0) {
    $row_pendaftar_baru = $result_pendaftar_baru->fetch_assoc();
    $pendaftar_baru = $row_pendaftar_baru['pendaftar_baru'];
}

// Program Relawan Aktif (dari tabel 'programs' dengan status 'active')
$sql_program_relawan_aktif = "SELECT COUNT(id) as program_relawan_aktif FROM programs WHERE status = 'active'";
$result_program_relawan_aktif = $conn->query($sql_program_relawan_aktif);
$program_relawan_aktif = 0;
if ($result_program_relawan_aktif && $result_program_relawan_aktif->num_rows > 0) {
    $row_program_relawan_aktif = $result_program_relawan_aktif->fetch_assoc();
    $program_relawan_aktif = $row_program_relawan_aktif['program_relawan_aktif'];
}

// Diagram Relawan Berdasarkan Minat
$sql_interest_chart = "
    SELECT main_interest, COUNT(id) as count_interest
    FROM volunteers
    WHERE main_interest IS NOT NULL AND main_interest != ''
    GROUP BY main_interest
    ORDER BY count_interest DESC;
";
$result_interest_chart = $conn->query($sql_interest_chart);

$interestChartLabels = [];
$interestChartValues = [];
if ($result_interest_chart && $result_interest_chart->num_rows > 0) {
    while ($row = $result_interest_chart->fetch_assoc()) {
        $interestChartLabels[] = $row['main_interest'];
        $interestChartValues[] = (float)$row['count_interest'];
    }
}
if (empty($interestChartLabels)) {
    $interestChartLabels = ['Tidak Ada Data'];
    $interestChartValues = [1]; // Placeholder for empty data
}


// Diagram Partisipasi Program Relawan Bulanan
$sql_monthly_volunteer_chart = "
    SELECT DATE_FORMAT(registration_date, '%Y-%m') AS month_key, DATE_FORMAT(registration_date, '%M') AS month_name, COUNT(volunteer_id) AS total_registrations
    FROM program_registrations
    WHERE registration_date >= DATE_SUB(CURDATE(), INTERVAL 8 MONTH)
    GROUP BY month_key, month_name
    ORDER BY month_key ASC;
";
$result_monthly_volunteer_chart = $conn->query($sql_monthly_volunteer_chart);

$monthlyVolunteerChartLabels = [];
$monthlyVolunteerChartValues = [];
$currentDateForVolunteer = new DateTime();
$monthsDataVolunteer = [];
for ($i = 7; $i >= 0; $i--) {
    $date = clone $currentDateForVolunteer;
    $date->modify("-$i months");
    $monthKey = $date->format('Y-m');
    $monthName = $date->format('F');
    $monthlyVolunteerChartLabels[] = $monthName;
    $monthsDataVolunteer[$monthKey] = 0;
}

if ($result_monthly_volunteer_chart && $result_monthly_volunteer_chart->num_rows > 0) {
    while ($row = $result_monthly_volunteer_chart->fetch_assoc()) {
        $monthsDataVolunteer[$row['month_key']] = (float)$row['total_registrations'];
    }
}
$monthlyVolunteerChartValues = array_values($monthsDataVolunteer);


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <title>Admin Dashboard</title>
    <style>
        /* Impor font Poppins */
        @import url("https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;0,700;0,800;0,900&display=swap");

        /* Reset CSS dasar dan atur font-family */
        * {
            font-family: "Poppins", sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            outline: none;
            border: none;
            text-decoration: none;
            transition: all 0.2s linear;
        }

        /* Styling Sidebar */
        .sidebar {
            background-color: #1E3A8A; /* Warna latar belakang biru gelap */
            color: white;
            width: 250px;
            height: 100vh;
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            z-index: 50; /* Pastikan sidebar di atas konten lain */
        }

        .sidebar a {
            color: white;
            padding: 10px 15px;
            margin: 5px 0;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.2s ease;
            display: flex; /* Gunakan flex untuk ikon dan teks */
            align-items: center; /* Sejajarkan ikon dan teks vertikal */
        }
        .sidebar a span {
            flex-grow: 1; /* Pastikan teks mengambil ruang yang tersedia */
        }

        .sidebar a:hover {
            transform: translateX(0.3px);
            background-color: #007bff; /* Warna biru terang saat hover */
        }

        /* Styling Submenu */
        .submenu {
            display: none; /* Sembunyikan secara default */
            padding-left: 20px; /* Indentasi untuk submenu */
        }

        .submenu.active {
            display: block; /* Tampilkan saat aktif */
        }

        /* Pastikan modal disembunyikan saat halaman pertama kali dimuat */
        #logoutModal {
            display: none; /* Diperbaiki: Sembunyikan modal secara default */
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            justify-content: center;
            align-items: center;
            padding: 0;
            overflow: hidden;
            box-sizing: border-box;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            width: 300px; /* Lebar modal */
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .modal-content h2 {
            margin-bottom: 10px;
            color: black;
            font-size: 24px;
            font-weight: bold;
        }

        .modal-content p {
            margin-bottom: 20px;
            color: black;
            font-size: 16px;
        }

        .modal-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 20px; /* Jarak antara tombol */
        }

        .confirm-button,
        .cancel-button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .confirm-button {
            background-color: #dc3545; /* Merah */
            color: white;
        }

        .confirm-button:hover {
            background-color: #c82333; /* Merah lebih gelap saat hover */
        }

        .cancel-button {
            background-color: #6c757d; /* Abu-abu */
            color: white;
        }

        .cancel-button:hover {
            background-color: #5a6268; /* Abu-abu lebih gelap saat hover */
        }


        /* CSS untuk rotasi ikon panah */
        .rotate-180 {
            transform: rotate(180deg);
            transition: transform 0.3s ease;
        }

        /* Custom styles for dashboard cards and charts */
        .dashboard-cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); /* Adjusted for more cards */
            gap: 24px; /* Tailwind's gap-6 */
            margin-bottom: 24px; /* Tailwind's mb-6 */
        }

        .dashboard-card {
            background-color: white;
            padding: 16px; /* Tailwind's p-4 */
            border-radius: 6px; /* Tailwind's rounded-md */
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); /* Tailwind's shadow-md */
            text-align: center;
        }

        .dashboard-card h3 {
            font-size: 18px; /* Tailwind's text-lg */
            font-weight: 700; /* Tailwind's font-bold */
            margin-bottom: 8px; /* Tailwind's mb-2 */
        }

        .dashboard-card p {
            font-size: 24px; /* Tailwind's text-2xl */
            color: #2563eb; /* Tailwind's text-blue-700 */
            font-weight: 700; /* Tailwind's font-bold */
        }

        .dashboard-charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); /* Adjusted for more charts */
            gap: 24px; /* Tailwind's gap-6 */
            margin-bottom: 24px; /* Tailwind's mb-6 */
        }

        .dashboard-chart-card {
            background-color: white;
            padding: 24px; /* Tailwind's p-6 */
            border-radius: 6px; /* Tailwind's rounded-md */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); /* Tailwind's shadow-md */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 350px; /* Fixed height for consistent chart display */
        }

        .dashboard-chart-card h3 {
            font-size: 20px; /* Tailwind's text-xl */
            font-weight: 700; /* Tailwind's font-bold */
            margin-bottom: 16px; /* Tailwind's mb-4 */
        }

        .dashboard-chart-card .chart-canvas-wrapper {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        canvas {
            max-width: 100% !important;
            max-height: 100% !important;
            width: 100% !important;
            height: 100% !important;
        }

        .dashboard-table-card {
            background-color: white;
            padding: 24px; /* Tailwind's p-6 */
            border-radius: 6px; /* Tailwind's rounded-md */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); /* Tailwind's shadow-md */
            margin-top: 24px; /* Tailwind's mt-6 */
        }

        .dashboard-table-card h3 {
            font-size: 20px; /* Tailwind's text-xl */
            font-weight: 700; /* Tailwind's font-bold */
            margin-bottom: 16px; /* Tailwind's mb-4 */
        }

        .dashboard-table-card table {
            width: 100%;
            text-align: left;
            border-collapse: collapse;
        }

        .dashboard-table-card th,
        .dashboard-table-card td {
            padding: 8px 16px; /* Tailwind's py-2 px-4 */
            border-bottom: 2px solid #e5e7eb; /* Tailwind's border-b-2 border-gray-200 */
        }

        .dashboard-table-card th {
            background-color: #f9fafb; /* Tailwind's bg-gray-50 */
            font-weight: 600;
        }

        .dashboard-table-card tbody tr:last-child td {
            border-bottom: none;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 10px;
                flex-direction: row;
                justify-content: space-around;
                flex-wrap: wrap;
            }
            .sidebar h2 {
                display: none;
            }
            .sidebar nav {
                width: 100%;
            }
            .sidebar nav a {
                padding: 8px 10px;
                font-size: 0.9em;
                justify-content: center;
            }
            .sidebar nav a i {
                margin-right: 0;
            }
            .sidebar nav a span {
                display: none;
            }
            .submenu {
                padding-left: 0;
                text-align: center;
            }
            .submenu a {
                padding: 8px 10px;
            }

            #main-content {
                margin-left: 0;
                padding: 15px;
            }

            .dashboard-cards-container {
                grid-template-columns: 1fr;
            }
            .dashboard-charts-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar text-white w-64 py-6 px-4 fixed h-full">
        <h2 class="text-2xl font-bold mb-6 flex items-center">
            <i class="fas fa-hand-holding-heart mr-2"></i> DonGiv
        </h2>
        <nav class="space-y-2">
            <a href="#" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
                <i class="fas fa-home mr-2"></i> Dashboard
            </a>
            <div class="relative">
                <a href="#" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center justify-between"
                    onclick="toggleSubmenu('management-submenu', event)">
                    <span><i class="fas fa-donate mr-2"></i> Management</span>
                    <i class="fas fa-chevron-down" id="chevron-icon-management"></i>
                </a>
                <div id="management-submenu" class="submenu bg-blue-800 mt-2 rounded-md">
                    <a href="notifikasi.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">
                        Notifikasi dan Email
                    </a>
                    <a href="Manajemen.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">
                        Donation
                    </a>
                    <a href="User Manajement.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">
                        Manajement User
                    </a>
                    <!-- New link for Volunteer Management -->
                    <a href="ManajemenRelawan.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">
                        Manajemen Relawan
                    </a>
                    <a href="ManajemenProgram.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">
                       Manajemen Program
                    </a>
                    <a href="ManajemenPendaftaranProgram.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md active">
    Manajemen Pendaftaran
</a>
                </div>
            </div>
            <a href="RiwayatDonasi.php"
                class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
                <i class="fas fa-share-alt mr-2"></i> Riwayat Donasi
            </a>
            <a href="KelolaPenyaluran.php"
                class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
                <i class="fas fa-box mr-2"></i> Kelola Penyaluran
            </a>
            <a href="#" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center"
                onclick="openLogoutModal()">
                <i class="fas fa-sign-out-alt mr-2"></i> Log Out
            </a>
            <div id="logoutModal" class="modal">
                <div class="modal-content">
                    <h2>Log Out</h2>
                    <p>Are you sure you want to log out?</p>
                    <div class="modal-buttons">
                        <button class="confirm-button" onclick="confirmLogout()">
                            Yes, Log Out
                        </button>
                        <button class="cancel-button" onclick="closeLogoutModal()">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    <div id="main-content" class="flex-1 p-6 ml-64">
        <!-- Dashboard Header -->
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Dashboard</h1>

        <!-- Dashboard Cards Container -->
        <div class="dashboard-cards-container">
            <!-- Existing Donation Cards -->
            <div class="dashboard-card">
                <h3>Total Donasi</h3>
                <p>Rp. <?php echo number_format($total_donasi ?: 0, 0, ',', '.'); ?></p>
            </div>
            <div class="dashboard-card">
                <h3>Jumlah Kampanye</h3>
                <p><?php echo $total_kampanye ?? 0; ?></p>
            </div>
            <div class="dashboard-card">
                <h3>Jumlah Donatur</h3>
                <p><?php echo $total_donatur ?? 0; ?></p>
            </div>

            <!-- New Volunteer Cards -->
            <div class="dashboard-card">
                <h3>Total Relawan</h3>
                <p><?php echo $total_relawan ?? 0; ?></p>
            </div>
            <div class="dashboard-card">
                <h3>Pendaftar Baru</h3>
                <p><?php echo $pendaftar_baru ?? 0; ?></p>
            </div>
            <div class="dashboard-card">
                <h3>Program Relawan Aktif</h3>
                <p><?php echo $program_relawan_aktif ?? 0; ?></p>
            </div>
        </div>

        <!-- Dashboard Charts Container -->
        <div class="dashboard-charts-container">
            <!-- Existing "Kurva Dana Masuk" (Line Chart) -->
            <div class="dashboard-chart-card">
                <h3>Kurva Dana Masuk</h3>
                <div class="chart-canvas-wrapper">
                    <canvas id="kurvaDanaMasuk"></canvas>
                </div>
            </div>
            <!-- Existing "Persentase Total Donasi" (Doughnut Chart) -->
            <div class="dashboard-chart-card">
                <h3>Persentase Total Donasi</h3>
                <div class="chart-canvas-wrapper">
                    <canvas id="diagramVenn"></canvas>
                </div>
            </div>
            <!-- New Chart: Relawan Berdasarkan Minat (Doughnut Chart) -->
            <div class="dashboard-chart-card">
                <h3>Relawan Berdasarkan Minat</h3>
                <div class="chart-canvas-wrapper">
                    <canvas id="interestDoughnutChart"></canvas>
                </div>
            </div>
            <!-- New Chart: Partisipasi Program Relawan Bulanan (Bar Chart) -->
            <div class="dashboard-chart-card">
                <h3>Partisipasi Program Relawan Bulanan</h3>
                <div class="chart-canvas-wrapper">
                    <canvas id="monthlyVolunteerBarChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Existing "Detail Donasi per Bulan" Table -->
        <div class="dashboard-table-card">
            <h3>Detail Donasi per Bulan</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left table-auto border-collapse">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b-2 border-gray-200 bg-gray-50">Bulan</th>
                            <th class="py-2 px-4 border-b-2 border-gray-200 bg-gray-50">Jumlah Donasi</th>
                            <th class="py-2 px-4 border-b-2 border-gray-200 bg-gray-50">Jumlah Donatur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (isset($result_detail_donasi_table) && $result_detail_donasi_table->num_rows > 0) {
                            while ($row_detail_donasi_table = $result_detail_donasi_table->fetch_assoc()) {
                        ?>
                                <tr>
                                    <td class="py-2 px-4 border-b border-gray-200"><?php echo $row_detail_donasi_table['bulan']; ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200">Rp. <?php echo number_format($row_detail_donasi_table['jumlah_donasi'], 0, ',', '.'); ?></td>
                                    <td class="py-2 px-4 border-b border-gray-200"><?php echo $row_detail_donasi_table['jumlah_donatur_per_bulan']; ?></td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo '<tr><td colspan="3" class="py-2 px-4 text-center text-gray-500">Tidak ada data donasi untuk ditampilkan.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // --- Existing Donation Charts ---
            const incomeLabels = <?php echo json_encode($incomeChartLabels); ?>;
            const incomeValues = <?php echo json_encode($incomeChartValues); ?>;

            const kurvaDanaMasukCtx = document.getElementById("kurvaDanaMasuk").getContext("2d");
            new Chart(kurvaDanaMasukCtx, {
                type: "line",
                data: {
                    labels: incomeLabels,
                    datasets: [{
                        label: "Dana Masuk",
                        borderColor: "blue",
                        backgroundColor: "rgba(0, 0, 255, 0.1)", // Warna area di bawah garis
                        tension: 0.3, // Membuat garis sedikit melengkung
                        fill: false, // Set false untuk tidak mengisi area di bawah garis
                        pointRadius: 5, // Ukuran titik data
                        pointHoverRadius: 8, // Ukuran titik saat dihover
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value, index, values) {
                                    return 'Rp. ' + value.toLocaleString('id-ID');
                                }
                            },
                            title: {
                                display: true,
                                text: 'Jumlah Donasi (Rp)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Bulan'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += 'Rp. ' + context.parsed.y.toLocaleString('id-ID');
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                },
            });

            const pieLabels = <?php echo json_encode($pieChartLabels); ?>;
            const pieValues = <?php echo json_encode($pieChartValues); ?>;

            const diagramVennCtx = document.getElementById("diagramVenn").getContext("2d");
            new Chart(diagramVennCtx, {
                type: "doughnut",
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: pieValues,
                        backgroundColor: [
                            "#8ecae6", "#219ebc", "#023047", "#ffb703", "#fb8500",
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40',
                            '#007bff', '#28a745', '#dc3545', '#6c757d',
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 2,
                        hoverOffset: 8,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'right',
                            labels: {
                                boxWidth: 20,
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed) {
                                        const total = context.dataset.data.reduce((acc, current) => acc + current, 0);
                                        const percentage = (context.parsed / total * 100).toFixed(2);
                                        label += 'Rp. ' + context.parsed.toLocaleString('id-ID') + ' (' + percentage + '%)';
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                },
            });

            // --- New Volunteer Charts ---
            const interestChartLabels = <?php echo json_encode($interestChartLabels); ?>;
            const interestChartValues = <?php echo json_encode($interestChartValues); ?>;

            const interestDoughnutCtx = document.getElementById("interestDoughnutChart").getContext("2d");
            new Chart(interestDoughnutCtx, {
                type: 'doughnut',
                data: {
                    labels: interestChartLabels,
                    datasets: [{
                        data: interestChartValues,
                        backgroundColor: [
                            '#00BCD4', /* primary-color */
                            '#4CAF50', /* secondary-color */
                            '#2196F3', /* info-color */
                            '#FFC107', /* warning-color */
                            '#9C27B0', /* Purple */
                            '#FF5722'  /* Deep Orange */
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        title: {
                            display: false,
                            text: 'Relawan Berdasarkan Minat'
                        }
                    }
                }
            });

            const monthlyVolunteerChartLabels = <?php echo json_encode($monthlyVolunteerChartLabels); ?>;
            const monthlyVolunteerChartValues = <?php echo json_encode($monthlyVolunteerChartValues); ?>;

            const monthlyVolunteerBarCtx = document.getElementById("monthlyVolunteerBarChart").getContext("2d");
            new Chart(monthlyVolunteerBarCtx, {
                type: 'bar',
                data: {
                    labels: monthlyVolunteerChartLabels,
                    datasets: [{
                        label: 'Relawan Berpartisipasi',
                        data: monthlyVolunteerChartValues,
                        backgroundColor: 'rgba(0, 188, 212, 0.7)', /* primary-color with opacity */
                        borderColor: 'rgba(0, 188, 212, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: false,
                            text: 'Partisipasi Program Relawan Bulanan'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value, index, values) {
                                    return value; // Display raw number for volunteer count
                                }
                            },
                            title: {
                                display: true,
                                text: 'Jumlah Relawan'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Bulan'
                            }
                        }
                    }
                }
            });


            // Fungsi untuk membuka atau menutup submenu
            function toggleSubmenu(submenuId, event) {
                var submenu = document.getElementById(submenuId);
                var chevron = event.currentTarget.querySelector('i.fa-chevron-down'); // Target the chevron icon within the clicked link

                // Close all other submenus
                document.querySelectorAll('.submenu').forEach(sub => {
                    if (sub.id !== submenuId && sub.classList.contains('active')) {
                        sub.classList.remove('active');
                        sub.previousElementSibling.querySelector('i.fa-chevron-down').classList.remove('rotate-180');
                    }
                });

                // Toggle the clicked submenu
                if (submenu.classList.contains('active')) {
                    submenu.classList.remove('active');
                    chevron.classList.remove('rotate-180');
                } else {
                    submenu.classList.add('active');
                    chevron.classList.add('rotate-180');
                }
            }
            window.toggleSubmenu = toggleSubmenu; // Make it globally accessible


            // Fungsi untuk membuka modal log out
            document.addEventListener('DOMContentLoaded', function() {
                // Pastikan modal dimulai dalam keadaan disembunyikan
                document.getElementById('logoutModal').style.display = 'none';
            });

            // Fungsi untuk membuka modal log out
            function openLogoutModal() {
                document.getElementById('logoutModal').style.display = 'flex'; // Menampilkan modal
            }
            window.openLogoutModal = openLogoutModal; // Make it globally accessible

            // Fungsi untuk menutup modal log out
            function closeLogoutModal() {
                document.getElementById('logoutModal').style.display = 'none'; // Menyembunyikan modal
            }
            window.closeLogoutModal = closeLogoutModal; // Make it globally accessible

            // Fungsi untuk konfirmasi log out
            function confirmLogout() {
                window.location.href = '../auth/logout.php'; // Arahkan ke halaman logout atau lakukan proses logout
            }
            window.confirmLogout = confirmLogout; // Make it globally accessible
        });
    </script>
</body>

</html>
