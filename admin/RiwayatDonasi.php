<?php
include '../users/koneksi.php';
session_start();

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
  $keyword = $_GET['keyword'] ?? '';
  $keyword = mysqli_real_escape_string($conn, $keyword); 

  $query = mysqli_query($conn, "SELECT pd.*, kd.nama_donasi
    FROM penyaluran_donasi pd
    JOIN kategori_donasi kd ON pd.id_donasi = kd.id_kategori
    WHERE pd.penanggung_jawab LIKE '%$keyword%'");

  $data = mysqli_fetch_all($query, MYSQLI_ASSOC);

  if (!empty($data)) {
    foreach ($data as $donasi) {
      echo "<tr>
        <td>{$donasi['tanggal']}</td>
        <td>{$donasi['penanggung_jawab']}</td>
        <td>{$donasi['nama_donasi']}</td>
        <td>Rp. " . number_format($donasi['total_donasi'], 0, ',', '.') . "</td>
        <td class='bukti-icon'>
          <a href='bukti.php'><i class='fas fa-file-alt'></i></a>
        </td>
        <td>
        </td>
      </tr>";
    }
  } else {
    echo "<tr><td colspan='6' class='text-center text-gray-500'>Data tidak ditemukan.</td></tr>";
  }
  exit;
}

// Query default untuk tampilan awal 
$query = mysqli_query($conn, "SELECT pd.*, kd.nama_donasi
  FROM penyaluran_donasi pd
  JOIN kategori_donasi kd ON pd.id_donasi = kd.id_kategori");

$data = mysqli_fetch_all($query, MYSQLI_ASSOC);
?>



<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Riwayat Donasi</title>
  <link rel="stylesheet" href="RiwayatDonasi.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>

<body>
  <!-- Sidebar -->
  <div class="sidebar text-white w-64 py-6 px-4 fixed h-full">
    <h2 class="text-2xl font-bold mb-6 flex items-center">
      <i class="fas fa-hand-holding-heart mr-2"></i> DonGiv
    </h2>
    <nav class="space-y-2">
      <!-- Dashboard -->
      <a
        href="Index.php"
        class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
        <i class="fas fa-home mr-2"></i> Dashboard
      </a>

      <!-- Donation dengan Submenu -->
      <div class="relative">
        <a
          href="#"
          class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center justify-between"
          onclick="toggleSubmenu('donation-submenu', event)">
          <span><i class="fas fa-donate mr-2"></i> Management</span>
          <i class="fas fa-chevron-down"></i>
        </a>
        <div
          id="donation-submenu"
          class="submenu bg-blue-800 mt-2 rounded-md">
          <a href="notifikasi.html" class="block py-2 px-6 hover:bg-blue-900 rounded-md">
            Notifikasi dan Email
          </a>
          <a href="Manajemen.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">
            Donation
          </a>
           <a href="User Manajement.php" class="block py-2 px-4 hover:bg-blue-800 rounded-md">
            Manajement User 
          </a>
        </div>
      </div>

      <!-- Riwayat Donasi -->
      <a
        href="RiwayatDonasi.php"
        class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
        <i class="fas fa-share-alt mr-2"></i> Riwayat Donasi
      </a>
       <a
    href="KelolaPenyaluran.php"
    class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
    <i class="fas fa-box mr-2"></i> Kelola Penyaluran</a>

      <!-- Log Out -->
      <a
        href="#"
        class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center"
        onclick="openLogoutModal()">
        <i class="fas fa-sign-out-alt mr-2"></i> Log Out
      </a>
      <!-- Log Out Modal -->
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

  <div class="container">
    <!-- Header -->
    <header class="header">
      <h1>Riwayat Donasi</h1>
    </header>

    <!-- Search Bar -->
    <div class="search-bar">
      <input type="text" placeholder="Cari donasi..." id="search-input" />
      <button id="search-btn"><i class="fas fa-search"></i> Cari</button>
    </div>


    <!-- Donation Table -->
    <!-- HTML -->
    <table>
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Penanggung Jawab</th>
          <th>Nama Donasi</th>
          <th>Total</th>
          <th>Bukti</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="donasi-tbody">
        <?php foreach ($data ?? [] as $donasi): ?>
          <tr>
            <td><?= $donasi['tanggal'] ?></td>
            <td><?= $donasi['penanggung_jawab'] ?></td>
            <td><?= $donasi['nama_donasi'] ?></td>
            <td>Rp. <?= number_format($donasi['total_donasi'], 0, ',', '.') ?>
            </td>
            <td class="bukti-icon">
              <a href="bukti.php">
                <i class="fas fa-file-alt"></i>
              </a>
            </td>
            <td>
      <form action="verifikasi_donasi.php" method="POST">
        <input type="hidden" name="penyaluran_donasi" value="<?= $donasi['penyaluran_donasi'] ?>">
        <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
          <i class="fas fa-check-circle"></i> Verifikasi
        </button>
      </form>
    </td>
          </tr>
        <?php endforeach; ?>
        <!-- Tambahkan baris lainnya di sini -->
      </tbody>
    </table>

    <!-- Footer -->
    <footer class="footer">
      <p>&copy; 2024 Donasi Online. All rights reserved.</p>
    </footer>
  </div>
  <script>
    document.getElementById("search-btn").addEventListener("click", function() {
      const keyword = document.getElementById("search-input").value;

      const xhr = new XMLHttpRequest();
      xhr.open("GET", "RiwayatDonasi.php?ajax=1&keyword=" + encodeURIComponent(keyword), true);
      xhr.onload = function() {
        if (xhr.status === 200) {
          document.getElementById("donasi-tbody").innerHTML = xhr.responseText;
        }
      };
      xhr.send();
    });
  </script>

</body>

</html>