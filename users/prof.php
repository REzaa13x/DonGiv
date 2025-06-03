<?php
include './koneksi.php';
session_start();

// Periksa apakah user_id ada di session
if (!isset($_SESSION['user_id'])) {
    // Redirect ke halaman login jika user belum login
    header("Location: login.php"); // Sesuaikan dengan path halaman login Anda
    exit();
}

$id = $_SESSION['user_id'];

// Query untuk mengambil semua data pengguna dari database
// Pastikan nama kolom sesuai dengan tabel 'users' Anda (dana_didonasi, campaign_terdonasikan, poin)
$query = mysqli_query($conn, "SELECT * FROM users WHERE id='$id' LIMIT 1");
$data = mysqli_fetch_assoc($query);

// Jika data user tidak ditemukan (meskipun user_id ada di session, ini jarang terjadi)
if (!$data) {
    session_destroy(); // Hapus session
    header("Location: login.php"); // Redirect ke login
    exit();
}

// Ambil data yang diperlukan dari array $data
$foto = $data['foto'] ?? 'default.png'; // Path foto profil user
$saldo = $data['saldo'] ?? 0;
$dana_didonasikan = $data['dana_didonasi'] ?? 0; // Menggunakan nama kolom yang benar: dana_didonasi
$campaign_terdonasikan = $data['campaign_terdonasikan'] ?? 0;
$poin = $data['poin'] ?? 0;

// Format dana_didonasikan ke format Rupiah
$display_dana_didonasikan = 'Rp ' . number_format($dana_didonasikan, 0, ',', '.');

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Profile Page - <?php echo htmlspecialchars($data['name'] ?? 'User'); ?></title>
  <link rel="stylesheet" href="Prof.css"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <style>
    /* Tambahan styling untuk memastikan tampilan sesuai */
    body { font-family: 'Poppins', sans-serif; }
    .container {
        max-width: 900px;
        margin: 40px auto;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        padding: 20px;
    }
    .header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
        margin-bottom: 20px;
    }
    .user-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .user-info img {
        border: 2px solid #ddd;
    }
    .user-info strong {
        font-size: 1.2em;
        color: #333;
    }
    .user-info div {
        font-size: 0.9em;
        color: #555;
    }
    .edit-profile button {
        background-color: #2563eb;
        color: white;
        padding: 8px 15px;
        border-radius: 5px;
        font-size: 0.9em;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .edit-profile button:hover {
        background-color: #1a56cc;
    }
    .stats {
        display: flex;
        justify-content: space-around;
        text-align: center;
        margin-bottom: 30px;
    }
    .stat {
        flex: 1;
        padding: 10px;
    }
    .stat strong {
        display: block;
        font-size: 1.5em;
        color: #1e3a8a;
        margin-bottom: 5px;
    }
    .stat span {
        font-size: 0.85em;
        color: #777;
    }
    .wallet {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #f8f8f8;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #eee;
        margin-bottom: 20px;
    }
    .wallet span {
        font-weight: bold;
        color: #333;
    }
    .wallet button {
        background-color: #4CAF50;
        color: white;
        padding: 8px 15px;
        border-radius: 5px;
        font-size: 0.9em;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .wallet button:hover {
        background-color: #45a049;
    }
    .notice {
        background-color: #fffbe6;
        color: #856404;
        padding: 10px;
        border: 1px solid #ffeeba;
        border-radius: 5px;
        margin-bottom: 20px;
        font-size: 0.9em;
    }
    .menu a {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-bottom: 1px solid #eee;
        color: #333;
        text-decoration: none;
        transition: background-color 0.2s;
    }
    .menu a:last-child {
        border-bottom: none;
    }
    .menu a:hover {
        background-color: #f0f0f0;
    }
    .menu img {
        width: 28px;
        height: 28px;
    }
    .menu span {
        font-size: 1.0em;
    }
  </style>
</head>
<body>
  <nav class="bg-blue-600 sticky top-0 z-50 shadow-lg w-screen">
    <div class="flex justify-between items-center px-6 py-4">
      <a href="DonGiv.php" class="flex items-center">
        <img src="../foto/1-removebg-preview (1).png" class="h-12 mr-2" alt="DonGiv-Logo">
        <span class="text-white text-2xl font-semibold">DonGiv</span>
      </a>
      <div class="hidden md:flex space-x-6">
        <a href="DonGiv.php" class="text-white hover:text-blue-300">Home</a>
        <a href="donasi.php" class="text-white hover:text-blue-300">Donations</a> <a href="http://127.0.0.1:5500/Ab.html" class="text-white hover:text-blue-300">About</a>
        <a href="#Contact" class="text-white hover:text-blue-300">Contact</a>

        <div class="relative">
          <button id="dropdownButton" class="relative focus:outline-none">
            <img src="../foto/user.png" class="w-8 h-8 rounded-full border-2 border-white">
          </button>
          <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2">
            <div class="px-4 py-2 border-b">
              <p class="text-gray-800 font-semibold"><?php echo htmlspecialchars($data['name'] ?? 'User') ?></p>
              <p class="text-gray-500 text-sm"><?= htmlspecialchars($data['email'] ?? '') ?></p>
            </div>
            <a href="prof.php" class="block px-4 py-2 text-gray-800 hover:bg-blue-100">Profile</a> <a href="#settings" class="block px-4 py-2 text-gray-800 hover:bg-blue-100">Settings</a>
            <a href="#logout" class="block px-4 py-2 text-gray-800 hover:bg-blue-100">Logout</a>
          </div>
        </div>
      </div>
      <button class="md:hidden text-white focus:outline-none">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
    </div>
  </nav>

  <div class="container">
    <div class="header">
      <div class="user-info">
        <img src="<?= htmlspecialchars($foto) == 'default.png' ? '../foto/user.png' : '../uploads/' . htmlspecialchars($foto) ?>" alt="Foto Profil" class="rounded-full object-cover" style="width: 80px; height: 80px;">
        <div class="details">
          <strong><?= htmlspecialchars($data['name'] ?? '') ?></strong>
          <div><?= htmlspecialchars($data['email'] ?? '') ?></div>
        </div>
      </div>
      <div class="edit-profile">
        <button onclick="window.location.href='editUser.php'">Edit Profile</button>
      </div>
    </div>

    <div class="stats">
      <div class="stat">
        <strong><?= $display_dana_didonasikan ?></strong>
        <span>Dana Didonasikan</span>
      </div>
      <div class="stat">
        <strong><?= htmlspecialchars($campaign_terdonasikan) ?></strong>
        <span>Campaign Terdonasikan</span>
      </div>
      <div class="stat">
        <strong><?= htmlspecialchars($poin) ?></strong>
        <span>Poin</span>
      </div>
    </div>

    <div class="wallet">
      <span>Saldo Kantong Amal: Rp <?= number_format($saldo, 0, ',', '.') ?></span>
      <button onclick="window.location.href='../topup/topup.php'">+ Top Up</button>
    </div>

    <div class="notice">
      Mohon lengkapi profilmu untuk memverifikasi akun
    </div>

    <div class="menu">
      <a href="riwayat_donasi.php" class="menu-item flex items-center space-x-4 p-4 hover:bg-gray-100 rounded">
        <img src="../foto/transaction.png" alt="Transaksi Saya" class="w-8 h-8">
        <span>Transaksi saya</span>
      </a>
      <a href="donasi_saya.php" class="menu-item flex items-center space-x-4 p-4 hover:bg-gray-100 rounded">
        <img src="../foto/heart.png" alt="Donasi Saya" class="w-8 h-8">
        <span>Donasi saya</span>
      </a>
      <a href="http://127.0.0.1:5500/donasirutin.html" class="menu-item flex items-center space-x-4 p-4 hover:bg-gray-100 rounded">
        <img src="../foto/calendar.png" alt="Donasi Rutin Saya" class="w-8 h-8">
        <span>Donasi rutin saya</span>
      </a>
      <a href="http://127.0.0.1:5500/setting.html" class="menu-item flex items-center space-x-4 p-4 hover:bg-gray-100 rounded">
        <img src="../foto/settings.png" alt="Settings" class="w-8 h-8">
        <span>Setting</span>
      </a>
    </div>
  </div>
</body>
</html>