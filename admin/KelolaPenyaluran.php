<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Kelola Penyaluran</title>
  <link rel="stylesheet" href="KelolaPenyaluran.css" />
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
      <h1>Kelola Penyaluran</h1>
    </header>
<main class="ml-64 p-6 bg-gray-100 min-h-screen">
  <div class="bg-white rounded-xl shadow-md p-6 max-w-3xl mx-auto">
    <h2 class="text-xl font-bold mb-4">Form Penyaluran Dana</h2>
    <form action="proses_penyaluran.php" method="POST" enctype="multipart/form-data" class="space-y-4">
      
      <div>
        <label for="nominal" class="block font-semibold">Nominal Transfer (Rp)</label>
        <input type="number" id="nominal" name="nominal" required class="w-full p-2 border rounded-md" placeholder="Contoh: 1000000">
      </div>

      <div>
        <label for="bukti" class="block font-semibold">Upload Bukti Penyaluran</label>
        <input type="file" id="bukti" name="bukti" accept="image/*,application/pdf" required class="w-full p-2 border rounded-md">
      </div>

      <div>
        <label for="dokumentasi" class="block font-semibold">Upload Dokumentasi Kegiatan</label>
        <input type="file" id="dokumentasi" name="dokumentasi[]" accept="image/*,video/*" multiple class="w-full p-2 border rounded-md">
      </div>
        </button>
        <button type="button" onclick="confirmSelesai()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
          Selesaikan Campaign
        </button>
    </form>
  </div>
</main>

<!-- Modal Selesaikan Campaign -->
<div id="selesaiModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 max-w-sm w-full text-center">
    <h3 class="text-lg font-semibold mb-4">Konfirmasi</h3>
    <p>Apakah Anda yakin ingin menyelesaikan campaign ini?</p>
    <div class="mt-4 flex justify-center gap-4">
      <form action="selesaikan_campaign.php" method="POST">
        <input type="hidden" name="campaign_id" value="<!-- isi ID Campaign -->">
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
          Ya, Selesaikan
        </button>
      </form>
      <button onclick="document.getElementById('selesaiModal').classList.add('hidden')" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">
        Batal
      </button>
    </div>
  </div>
</div>

<script>
  function confirmSelesai() {
    document.getElementById("selesaiModal").classList.remove("hidden");
  }
</script>


</html>
