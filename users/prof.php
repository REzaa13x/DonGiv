<?php
include './koneksi.php';
session_start();

$id = $_SESSION['user_id'];
$query = mysqli_query($conn, "SELECT * FROM users WHERE id='$id' LIMIT 1");
$data = mysqli_fetch_assoc($query);
$foto = $data['foto'] ?? 'default.png';
$saldo = $data['saldo'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Profile Page</title>
  <link rel="stylesheet" href="Prof.css"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
</head>
<body>
  <!-- Navbar -->
  <nav class="bg-blue-600 sticky top-0 z-50 shadow-lg w-screen">
    <div class="flex justify-between items-center px-6 py-4">
      <a href="#" class="flex items-center">
        <img src="../foto/1-removebg-preview (1).png" class="h-12 mr-2" alt="DonGiv-Logo">
        <span class="text-white text-2xl font-semibold">DonGiv</span>
      </a>
      <div class="hidden md:flex space-x-6">
        <a href="DonGiv.php" class="text-white hover:text-blue-300">Home</a>
        <a href="#Donations" class="text-white hover:text-blue-300">Donations</a>
        <a href="http://127.0.0.1:5500/Ab.html" class="text-white hover:text-blue-300">About</a>
        <a href="#Contact" class="text-white hover:text-blue-300">Contact</a>

        <div class="relative">
          <button id="dropdownButton" class="relative focus:outline-none">
            <img src="../foto/user.png" class="w-8 h-8 rounded-full border-2 border-white">
          </button>
          <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2">
            <div class="px-4 py-2 border-b">
              <p class="text-gray-800 font-semibold"><?php echo $data['name'] ?></p>
              <p class="text-gray-500 text-sm"><?= $data['email'] ?></p>
            </div>
            <a href="#profile" class="block px-4 py-2 text-gray-800 hover:bg-blue-100">Profile</a>
            <a href="#settings" class="block px-4 py-2 text-gray-800 hover:bg-blue-100">Settings</a>
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

  <!-- Profile Container -->
  <div class="container">
    <div class="header">
      <div class="user-info">
        <img src="../uploads/<?= htmlspecialchars($foto) ?>" alt="Foto Profil" class="rounded-full object-cover" style="width: 80px; height: 80px;">
        <div class="details">
          <strong><?= $data['name'] ?></strong>
          <div><?= $data['email'] ?></div>
        </div>
      </div>
      <div class="edit-profile">
        <button onclick="window.location.href='editUser.php'">Edit Profile</button>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats">
      <div class="stat">
        <strong>Rp 0</strong>
        <span>Dana Didonasikan</span>
      </div>
      <div class="stat">
        <strong>0</strong>
        <span>Campaign Terdonasikan</span>
      </div>
      <div class="stat">
        <strong>0</strong>
        <span>Poin</span>
      </div>
    </div>

    <!-- Wallet -->
    <div class="wallet">
      <span>Saldo Kantong Amal: Rp <?= number_format($saldo) ?></span>
      <button onclick="window.location.href='../topup/topup.php'">+ Top Up</button>
    </div>

    <!-- Notice -->
    <div class="notice">
      Mohon lengkapi profilmu untuk memverifikasi akun
    </div>

    <!-- Menu (Sudah disesuaikan dengan struktur CSS kamu) -->
    <div class="menu">
      <a href="riwayat_donasi.php" class="menu-item flex items-center space-x-4 p-4 hover:bg-gray-100 rounded">
        <img src="../foto/transaction.png" alt="Transaksi Saya" class="w-8 h-8">
        <span>Transaksi saya</span>
      </a>
      <a href="donations.html" class="menu-item flex items-center space-x-4 p-4 hover:bg-gray-100 rounded">
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
