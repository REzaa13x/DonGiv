<?php
// File: users/detail_donasi.php (atau di root)

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'koneksi.php'; // Sesuaikan path jika 'koneksi.php' tidak ada di folder yang sama

$kampanye = null;
$message = '';
$persentase_terkumpul = 0; // Inisialisasi
$tujuan_penerima_display = ''; // Inisialisasi
$deskripsi_kampanye_display = ''; // Inisialisasi
$jumlah_disalurkan = 0; // Inisialisasi jumlah disalurkan

// Ambil data user untuk navbar (jika user login)
session_start(); // Pastikan session_start() ada di awal jika belum ada
$user_logged_in = false;
$user_data_navbar = [];
if (isset($_SESSION['user_id'])) {
    $user_logged_in = true;
    $user_id_navbar = $_SESSION['user_id'];
    // Gunakan prepared statement untuk keamanan
    $stmt_user_navbar = mysqli_prepare($conn, "SELECT name, email, foto FROM users WHERE id=?");
    if ($stmt_user_navbar) {
        mysqli_stmt_bind_param($stmt_user_navbar, "i", $user_id_navbar);
        mysqli_stmt_execute($stmt_user_navbar);
        $result_user_navbar = mysqli_stmt_get_result($stmt_user_navbar);
        if (mysqli_num_rows($result_user_navbar) > 0) {
            $user_data_navbar = mysqli_fetch_assoc($result_user_navbar);
        }
        mysqli_stmt_close($stmt_user_navbar);
    }
}


// Pastikan ada ID kampanye yang dikirimkan
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_kampanye = (int)$_GET['id'];

    // Query untuk mengambil data kampanye tunggal
    // Memastikan kampanye aktif, sudah selesai, atau sudah disalurkan, dan belum berakhir
    $sql = "SELECT kd.*, k.nama_kategori 
            FROM kampanye_donasi kd
            LEFT JOIN kategori_donasi k ON kd.id_kategori = k.id_kategori
            WHERE kd.id_donasi = ? AND kd.status IN ('active', 'completed', 'disbursed') AND (kd.tanggal_akhir IS NULL OR kd.tanggal_akhir >= CURDATE())"; 
    
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_kampanye);
        $stmt->execute(); 
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $kampanye = mysqli_fetch_assoc($result);

            // Hitung persentase dana terkumpul
            $persentase_terkumpul = ($kampanye['target_dana'] > 0) ? 
                                    min(100, ($kampanye['dana_terkumpul'] / $kampanye['target_dana']) * 100) : 0;

            // Logika untuk memisahkan Deskripsi & Tujuan
            $db_description = $kampanye['deskripsi'] ?? '';
            
            if (strpos($db_description, 'Tujuan:') === 0) {
                $parts = explode("\n\n", $db_description, 2);
                $tujuan_line = trim($parts[0]);
                if (strpos($tujuan_line, 'Tujuan:') === 0) {
                    $tujuan_penerima_display = substr($tujuan_line, strlen('Tujuan:'));
                    $tujuan_penerima_display = trim($tujuan_penerima_display);
                }
                $deskripsi_kampanye_display = (count($parts) > 1) ? trim($parts[1]) : '';
            } else {
                $deskripsi_kampanye_display = $db_description;
            }
            if (empty(trim($deskripsi_kampanye_display)) || $deskripsi_kampanye_display === 'Tidak ada deskripsi rinci.' || $deskripsi_kampanye_display === '0') {
                $deskripsi_kampanye_display = 'Belum ada deskripsi rinci untuk kampanye ini.';
            }

            // Ambil jumlah dana yang disalurkan dari tabel penyaluran_donasi jika statusnya 'disbursed'
            if ($kampanye['status'] == 'disbursed') {
                $sql_disbursed_amount = "SELECT SUM(nominal_disalurkan) AS total_disbursed FROM penyaluran_donasi WHERE campaign_id = ?"; 
                $stmt_disbursed = mysqli_prepare($conn, $sql_disbursed_amount);
                if ($stmt_disbursed) {
                    mysqli_stmt_bind_param($stmt_disbursed, "i", $id_kampanye);
                    mysqli_stmt_execute($stmt_disbursed); 
                    $result_disbursed = mysqli_stmt_get_result($stmt_disbursed);
                    $row_disbursed = mysqli_fetch_assoc($result_disbursed);
                    $jumlah_disalurkan = $row_disbursed['total_disbursed'] ?? 0;
                    mysqli_stmt_close($stmt_disbursed);
                }
            }
            
            // --- Query untuk Leaderboard Donatur Teratas (Di Sini) ---
            $leaderboard = [];
            $total_donasi_leaderboard = 0; // Untuk total di footer leaderboard

            // PERBAIKAN QUERY SESUAI STRUKTUR TABEL 'donations' YANG ANDA BERIKAN
            $sql_leaderboard = "
                SELECT
                    u.name AS donatur_name,
                    SUM(d.amount) AS total_donasi_user -- Kolom amount di tabel donations
                FROM donations d -- Nama tabel donasi Anda
                JOIN users u ON d.user = u.id -- Kolom 'user' di donations, join ke 'id' di users
                WHERE d.campaign_id = ? -- Kolom 'campaign_id' di tabel donations
                GROUP BY u.id, u.name
                ORDER BY total_donasi_user DESC
                LIMIT 5 -- Mengambil 5 donatur teratas
            ";

            $stmt_leaderboard = mysqli_prepare($conn, $sql_leaderboard);

            if ($stmt_leaderboard) {
                mysqli_stmt_bind_param($stmt_leaderboard, "i", $id_kampanye);
                mysqli_stmt_execute($stmt_leaderboard);
                $result_leaderboard = mysqli_stmt_get_result($stmt_leaderboard);

                while ($row = mysqli_fetch_assoc($result_leaderboard)) {
                    $leaderboard[] = $row;
                    $total_donasi_leaderboard += $row['total_donasi_user']; // Hitung total dari yang ada di leaderboard
                }
                mysqli_stmt_close($stmt_leaderboard);
            } else {
                error_log("Prepare failed for leaderboard query: " . mysqli_error($conn));
            }

        } else {
            $message = "Kampanye donasi tidak ditemukan atau tidak aktif.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = "Error saat menyiapkan query: " . mysqli_error($conn);
    }
} else {
    $message = "ID kampanye tidak disediakan.";
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $kampanye ? htmlspecialchars($kampanye['nama_donasi']) : 'Detail Donasi' ?> - DonGiv</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="GU.css"> 
    <style> 
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa; 
            color: #4b5563;
        }
        /* Kontainer Utama yang membungkus semua konten */
        .container { /* Digunakan untuk membungkus seluruh konten utama di bawah hero */
            max-width: 900px; 
            margin: 3rem auto; 
            padding: 2rem; 
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            color: #4b5563;
        }

        /* Styling untuk gambar di dalam konten utama (content-image-detail) - Diletakkan di sini untuk fleksibilitas */
        .content-image-detail { 
            width: 100%;
            height: 400px; 
            object-fit: cover;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem; 
        }

        /* Styling untuk progress bar dan info dana (sesuai screenshot) */
        .progress-container {
            background-color: #e0e0e0;
            border-radius: 5px;
            height: 15px;
            margin-bottom: 10px;
            overflow: hidden;
            margin-top: 20px; /* Jarak dari judul kampanye */
        }
        .progress-bar-fill {
            height: 100%;
            background-color: #4CAF50;
            width: 0%; 
            border-radius: 5px;
            transition: width 0.5s ease-in-out;
        }
        .amount-display {
            display: flex;
            justify-content: space-between;
            font-size: 1em;
            color: #333;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .disbursed-message-box { /* Styling untuk pesan dana disalurkan */
            font-size: 1em;
            color: #3c763d; 
            background-color: #dff0d8; 
            border: 1px solid #d6e9c6;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-top: 15px; /* Jarak dari amount-display */
            margin-bottom: 20px; /* Jarak dari deskripsi kampanye */
            font-weight: bold;
        }

        .leaderboard-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: var(--white);
            border-radius: 8px;
            overflow: hidden; /* For rounded corners on table */
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .leaderboard-table th,
        .leaderboard-table td {
            padding: 15px;
            border-bottom: 1px solid #d1d5db;
            text-align: left;
        }

        .leaderboard-table th {
            background-color: #e5f1ff;
            color: #1E3A8A;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
        }

        .leaderboard-table tbody tr:nth-child(even) {
            background-color: #f8fafc; /* Tailwind gray-50 */
        }

        .leaderboard-table tbody tr:hover {
            background-color: #eff6ff; /* Tailwind blue-50 */
        }

        .leaderboard-table tbody tr:last-child td {
            border-bottom: none;
        }

        .total-row {
            font-weight: 700;
            background-color: #e0f2f7; /* Light cyan */
            color: #1E3A8A;
            border-top: 2px solid #2563eb;
        }

        .total-row td {
            padding: 15px;
        }

        .no-data {
            padding: 30px;
            font-size: 1.1em;
            color: #424242;
        }


        /* Hero Section */
        .hero {
            background-color: #3b82f6;
            color: white;
            text-align: center;
            padding: 5rem 1rem;
            position: relative;
            overflow: hidden;
        }
        .hero-bg-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0.3;
            z-index: 0;
        }
        .hero-content {
            position: relative;
            z-index: 1;
        }
        .hero-content h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .hero-content p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
        }
        .hero-content .cta {
            background-color: #facc15;
            color: #1f2937;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .hero-content .cta:hover {
            background-color: #fbbf24;
        }
        .cta.disbursed-button { 
            background-color: #cccccc; 
            cursor: not-allowed;
            pointer-events: none; 
        }
        .cta.disbursed-button:hover {
            background-color: #cccccc; 
        }
        
        /* Konten di dalam .container (menggantikan .content-section) */
        .content-area h2 { 
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
        }
        .content-area p {
            font-size: 1rem;
            color: #4b5563;
            margin-bottom: 1.5rem;
        }
        .content-area h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }
        .content-area ul {
            list-style-type: disc;
            margin-left: 1.5rem;
            color: #4b5563;
        }
        .content-area ul li {
            margin-bottom: 0.5rem;
        }

        /* Footer */
        .footer {
            background-color: #3b82f6;
            color: white;
            text-align: center;
            padding: 1.5rem 1rem;
        }
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        .footer-links a {
            color: white;
            text-decoration: none;
            margin-left: 1rem;
            transition: color 0.3s;
        }
        .footer-links a:hover {
            color: #93c5fd;
        }
    </style>
</head>
<body>
    <nav class="bg-blue-600 sticky top-0 z-50 shadow-lg w-screen">
      <div class="flex justify-between items-center px-6 py-4">
          <a href="../users/DonGiv.php" class="flex items-center">
              <img src="../foto/1-removebg-preview (1).png" class="h-12 mr-2" alt="DonGiv-Logo">
              <span class="text-white text-2xl font-semibold">DonGiv</span>
          </a>
  
          <div class="hidden md:flex space-x-6">
              <a href="../users/DonGiv.php" class="text-white hover:text-blue-300">Home</a>
              <a href="donasi.php" class="text-white hover:text-blue-300">Donations</a>
              <a href="http://127.0.0.1:5500/Ab.html" class="text-white hover:text-blue-300">About</a>
              <a href="#Contact" class="text-white hover:text-blue-300">Contact</a>
  
              <div class="relative">
                  <button id="dropdownButton" class="relative focus:outline-none">
                      <img src="../foto/user.png" class="w-8 h-8 rounded-full border-2 border-white">
                  </button>
                  <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2">
                      <div class="px-4 py-2 border-b">
                          <p class="text-gray-800 font-semibold"><?= htmlspecialchars($user_data_navbar['name'] ?? 'Username') ?></p>
                          <p class="text-gray-500 text-sm"><?= htmlspecialchars($user_data_navbar['email'] ?? 'name@gmail.com') ?></p>
                      </div>
                      <a href="prof.php" class="block px-4 py-2 text-gray-800 hover:bg-blue-100">Profile</a>
                      <a href="#settings" class="block px-4 py-2 text-gray-800 hover:bg-blue-100">Settings</a>
                      <a href="#logout" class="block px-4 py-2 text-gray-800 hover:bg-blue-100">Logout</a>
                  </div>
              </div>
          </div>
  
          <button class="md:hidden text-white focus:outline-none">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
              </svg>
          </button>
      </div>
    </nav>
  
 <?php if ($kampanye): ?>
  <section class="hero">
    <div class="hero-bg-image" style="background-image: url('<?= !empty($kampanye['gambar']) ? '../' . htmlspecialchars($kampanye['gambar']) : 'https://via.placeholder.com/1500x800?text=Gambar+Donasi+Utama' ?>');"></div>
    <div class="hero-content">
      <h1><?= htmlspecialchars($kampanye['nama_donasi']) ?></h1>
      <p><?= htmlspecialchars($tujuan_penerima_display) ?></p>
      <?php if ($kampanye['status'] == 'disbursed' || $kampanye['dana_terkumpul'] >= $kampanye['target_dana']): ?>
          <a href="#" class="cta disbursed-button">Donasi Selesai / Target Tercapai</a>
      <?php else: ?>
          <a href="../payment/payment.php?id=<?= htmlspecialchars($kampanye['id_donasi']) ?>" class="cta">Donasi Sekarang</a>
      <?php endif; ?>
    </div>
  </section>

  <div class="container"> 
    <div class="progress-container">
      <div class="progress-bar-fill" style="width: <?= $persentase_terkumpul ?>%;"></div>
    </div>
    <div class="amount-display">
      <span>Terkumpul: **Rp <?= number_format($kampanye['dana_terkumpul'], 0, ',', '.') ?>**</span>
      <span>Target: Rp <?= number_format($kampanye['target_dana'], 0, ',', '.') ?></span>
    </div>

    <?php if ($kampanye['status'] == 'disbursed'): ?>
        <p class="disbursed-message-box">
            <i class="fas fa-check-circle mr-2"></i> Dana kampanye ini telah berhasil disalurkan kepada penerima. Jumlah disalurkan: **Rp <?= number_format($jumlah_disalurkan, 0, ',', '.') ?>**
        </p>
    <?php elseif ($kampanye['dana_terkumpul'] >= $kampanye['target_dana']): ?>
        <p class="disbursed-message-box" style="background-color: #fff3cd; color: #856404; border-color: #ffeeba;">
            <i class="fas fa-bullseye mr-2"></i> Target dana kampanye ini telah tercapai! Dana siap untuk disalurkan.
        </p>
    <?php endif; ?>

    <div class="content-area"> 
        <h2 class="text-2xl font-bold mb-4 text-gray-800">Deskripsi Kampanye</h2>
        <p class="text-base leading-relaxed mb-6 text-gray-700"><?= nl2br(htmlspecialchars($deskripsi_kampanye_display)) ?></p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="p-4 bg-gray-100 rounded-lg">
                <h3 class="text-xl font-semibold mb-2 text-gray-800">Informasi Tambahan</h3>
                <p class="text-gray-700"><strong>Kategori:</strong> <?= htmlspecialchars($kampanye['nama_kategori'] ?? 'Umum') ?></p>
                <p class="text-gray-700"><strong>Tanggal Mulai:</strong> <?= htmlspecialchars(date('d F Y', strtotime($kampanye['tanggal_mulai']))) ?></p>
                <?php if ($kampanye['tanggal_akhir']): ?>
                <p class="text-gray-700"><strong>Tanggal Berakhir:</strong> <?= htmlspecialchars(date('d F Y', strtotime($kampanye['tanggal_akhir']))) ?></p>
                <?php else: ?>
                <p class="text-gray-700"><strong>Durasi:</strong> Tidak ada batas waktu</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="p-4 bg-gray-100 rounded-lg mt-6">
            <h3 class="text-xl font-semibold mb-2 text-gray-800">Kontak Kami</h3>
            <p class="text-gray-700">Untuk informasi lebih lanjut atau ingin terlibat, hubungi kami di 
                <a href="mailto:support@charity.org" class="text-blue-600 hover:underline">support@charity.org</a>.
            </p>
        </div>
    </div> </div> <?php else: ?>
    <div class="container"> <p class="text-center text-red-500 font-semibold"><?= htmlspecialchars($message) ?></p>
    </div>
  <?php endif; ?>

  <section class="leaderboard-container">
    <h2>Leaderboard Pendonasi</h2>
    <div class="overflow-x-auto">
      <table class="leaderboard-table">
        <thead>
          <tr>
            <th>Peringkat</th>
            <th>Nama Donatur</th>
            <th>Jumlah Donasi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($leaderboard)): ?>
            <?php $rank = 1; ?>
            <?php foreach ($leaderboard as $donatur): ?>
              <tr>
                <td><?php echo $rank++; ?></td>
                <td><?php echo htmlspecialchars($donatur['donatur_name']); ?></td>
                <td>Rp <?php echo number_format($donatur['total_donasi_user'], 0, ',', '.'); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="3" class="no-leaderboard-data">Belum ada donasi untuk campaign ini.</td>
            </tr>
          <?php endif; ?>
        </tbody>
        <tfoot>
          <tr class="total-row">
            <td colspan="2">Total Terkumpul (Leaderboard):</td>
            <td>Rp <?php echo number_format($total_donasi_leaderboard, 0, ',', '.'); ?></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </section>

  <footer class="bg-gray-800 text-white py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center">
        <p class="text-center sm:text-left mb-4 sm:mb-0">&copy; 2024 DonGiv. All rights reserved.</p>
        <div class="flex space-x-4">
            <a href="#" class="hover:text-blue-300">Facebook</a>
            <a href="#" class="hover:text-blue-300">Twitter</a>
            <a href="#" class="hover:text-blue-300">Instagram</a>
        </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
  <script>
    // Dropdown functionality for the user profile menu
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownButton = document.getElementById('dropdownButton');
        const dropdownMenu = document.getElementById('dropdownMenu');

        if (dropdownButton && dropdownMenu) {
            dropdownButton.addEventListener('click', function() {
                dropdownMenu.classList.toggle('hidden');
            });

            // Close the dropdown if clicked outside
            document.addEventListener('click', function(event) {
                if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                    dropdownMenu.classList.add('hidden');
                }
            });
        }
    });
  </script>
</body>
</html>