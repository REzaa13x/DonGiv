<?php
include '../users/koneksi.php';
session_start();

// Ambil data dari dua tabel yang berelasi
$query = "
SELECT 
  kd.nama_donasi, 
  pd.penyaluran_donasi, 
  pd.jumlah_uang_masuk, 
  pd.target_dana 
FROM penyaluran_donasi pd
JOIN kategori_donasi kd ON pd.id_kategori = kd.id_kategori
";

$result = $conn->query($query);

$kategori_donasi = [];
while ($row = $result->fetch_assoc()) {
  $kategori_donasi[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manajemen Donasi</title>
  <link rel="stylesheet" href="Manajemen.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar text-white w-64 py-6 px-4 fixed h-full bg-blue-900">
    <h2 class="text-2xl font-bold mb-6 flex items-center">
      <i class="fas fa-hand-holding-heart mr-2"></i> DonGiv
    </h2>
    <nav class="space-y-2">
      <a href="Index.php" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
        <i class="fas fa-home mr-2"></i> Dashboard
      </a>
      <div class="relative">
        <a href="#" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center justify-between"
           onclick="toggleSubmenu('donation-submenu', event)">
          <span><i class="fas fa-donate mr-2"></i> Management</span>
          <i class="fas fa-chevron-down"></i>
        </a>
        <div id="donation-submenu" class="submenu bg-blue-800 mt-2 rounded-md">
          <a href="notifikasi.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">Notifikasi dan Email</a>
          <a href="Manajemen.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">Donation</a>
        </div>
      </div>
      <a href="Salur.php" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
        <i class="fas fa-share-alt mr-2"></i> Channel
      </a>
      <a href="Finansial.php" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
        <i class="fas fa-wallet mr-2"></i> Finance
      </a>
      <a href="#" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center" onclick="openLogoutModal()">
        <i class="fas fa-sign-out-alt mr-2"></i> Log Out
      </a>
    </nav>
  </div>

  <!-- Header -->
  <header class="header ml-64 p-4 bg-white shadow">
    <div class="company-info">
      <h1 class="text-xl font-bold"><i class="fas fa-hand-holding-heart"></i> Manajemen Donasi</h1>
    </div>
  </header>

  <!-- Main Content -->
  <main class="content ml-64 p-4">
    <div class="flex justify-between items-center mb-4">
      <div class="search-bar flex items-center border px-2 py-1 rounded">
        <input type="text" placeholder="Cari donasi..." class="outline-none px-2" />
        <i class="fas fa-search text-gray-500"></i>
      </div>
      <button onclick="location.href='Tambah.php'" class="bg-blue-700 text-white px-4 py-2 rounded hover:bg-blue-800">
        <i class="fas fa-plus"></i> Tambah Donasi
      </button>
    </div>

    <section class="table-container bg-white p-4 rounded shadow">
      <table class="w-full border-collapse">
        <thead>
          <tr class="bg-gray-200">
            <th class="border px-4 py-2">No</th>
            <th class="border px-4 py-2">Nama Donasi</th>
            <th class="border px-4 py-2">Penyaluran</th>
            <th class="border px-4 py-2">Jumlah Uang Masuk</th>
            <th class="border px-4 py-2">Target Dana</th>
            <th class="border px-4 py-2">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1; foreach ($kategori_donasi as $row): ?>
          <tr class="hover:bg-gray-100">
            <td class="border px-4 py-2"><?= $no++ ?></td>
            <td class="border px-4 py-2"><?= htmlspecialchars($row['nama_donasi']) ?></td>
            <td class="border px-4 py-2"><?= htmlspecialchars($row['penyaluran_donasi']) ?></td>
            <td class="border px-4 py-2"><?= 'Rp ' . number_format($row['jumlah_uang_masuk'], 0, ',', '.') ?></td>
            <td class="border px-4 py-2"><?= 'Rp ' . number_format($row['target_dana'], 0, ',', '.') ?></td>
            <td class="border px-4 py-2 text-center">
              <button class="text-red-600 hover:text-red-800" onclick="confirmDelete(this)">
                <i class="fas fa-trash"></i> Hapus
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </main>

  <!-- Footer -->
  <footer class="footer ml-64 p-4 text-center text-gray-600">
    <p>&copy; 2025 Manajemen Donasi. Semua hak dilindungi.</p>
  </footer>

  <!-- Script -->
  <script src="Manajemen.js"></script>
  <script>
    function confirmDelete(button) {
      const row = button.closest("tr");
      const donationName = row.querySelector("td:nth-child(2)").innerText;
      if (confirm(`Apakah Anda yakin ingin menghapus donasi: "${donationName}"?`)) {
        row.remove();
      }
    }
  </script>
</body>
</html>
