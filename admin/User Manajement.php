<?php
include '../users/koneksi.php';
$query = mysqli_query($conn, "SELECT id, name, email, role FROM users");
$data = mysqli_fetch_all($query, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manajemen User</title>
  <link rel="stylesheet" href="Salur.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-gray-100">

  <!-- Sidebar -->
  <div class="sidebar bg-blue-700 text-white w-64 py-6 px-4 fixed h-full">
    <h2 class="text-2xl font-bold mb-6 flex items-center">
      <i class="fas fa-hand-holding-heart mr-2"></i> DonGiv
    </h2>
    <nav class="space-y-2">
      <a href="Index.php" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
        <i class="fas fa-home mr-2"></i> Dashboard
      </a>
      <div class="relative">
        <a href="#" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center justify-between" onclick="toggleSubmenu('donation-submenu', event)">
          <span><i class="fas fa-donate mr-2"></i> Management</span>
          <i class="fas fa-chevron-down"></i>
        </a>
        <div id="donation-submenu" class="submenu bg-blue-800 mt-2 rounded-md hidden">
          <a href="notifikasi.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">Notifikasi dan Email</a>
          <a href="Manajemen.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">Donation</a>
          <a href="User Manajement.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">Manajemen User</a>
        </div>
      </div>
      <a href="RiwayatDonasi.php" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
        <i class="fas fa-share-alt mr-2"></i> Riwayat Donasi
      </a>
      <a href="KelolaPenyaluran.php" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
        <i class="fas fa-box mr-2"></i> Kelola Penyaluran
      </a>
      <a href="#" onclick="openLogoutModal()" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
        <i class="fas fa-sign-out-alt mr-2"></i> Log Out
      </a>
    </nav>
  </div>

  <!-- Modal Hapus -->
  <div id="deleteModal" class="fixed inset-0 hidden bg-black bg-opacity-50 justify-center items-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
      <h2 class="text-lg font-semibold mb-2">Konfirmasi Hapus</h2>
      <p id="deleteMessage">Apakah Anda yakin ingin menghapus user ini?</p>
      <div class="flex justify-end mt-4 gap-2">
        <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Batal</button>
        <a id="confirmDeleteBtn" href="#" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Hapus</a>
      </div>
    </div>
  </div>

  <!-- Konten -->
  <div class="ml-64 p-6">
    <header class="mb-6">
      <h1 class="text-2xl font-bold">Manajemen User</h1>
      <p class="text-gray-600">Kelola data pengguna yang terdaftar dalam sistem DonGiv.</p>
    </header>

    <!-- Search -->
    <div class="mb-4 flex gap-2">
      <input type="text" id="search-input" placeholder="Cari pengguna..." class="px-4 py-2 border rounded w-full" />
      <button id="search-btn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"><i class="fas fa-search"></i> Cari</button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-white rounded shadow">
      <table class="min-w-full table-auto text-sm text-left">
        <thead class="bg-blue-600 text-white">
          <tr>
            <th class="px-4 py-3">Nama</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3">Password</th>
            <th class="px-4 py-3">Role</th>
            <th class="px-4 py-3">Action</th>
          </tr>
        </thead>
        <tbody id="user-tbody">
          <?php foreach ($data as $user): ?>
            <tr class="border-b">
              <td class="px-4 py-2"><?= htmlspecialchars($user['name']) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($user['email']) ?></td>
              <td class="px-4 py-2">••••••••</td>
              <td class="px-4 py-2"><?= htmlspecialchars($user['role']) ?></td>
              <td class="px-4 py-2">
                <button onclick="openDeleteModal('<?= addslashes($user['name']) ?>', <?= $user['id'] ?>)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 flex items-center gap-1">
                  <i class="fas fa-trash-alt"></i> Hapus
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <footer class="mt-6 text-center text-sm text-gray-500">
      &copy; 2024 Donasi Online. All rights reserved.
    </footer>
  </div>

  <!-- Scripts -->
  <script>
    function openDeleteModal(name, id) {
      const modal = document.getElementById("deleteModal");
      const deleteMessage = document.getElementById("deleteMessage");
      const deleteBtn = document.getElementById("confirmDeleteBtn");

      deleteMessage.textContent = `Apakah Anda yakin ingin menghapus user "${name}"?`;
      deleteBtn.href = `DeleteUser.php?id=${id}`;
      modal.classList.remove("hidden");
      modal.classList.add("flex");
    }

    function closeDeleteModal() {
      const modal = document.getElementById("deleteModal");
      modal.classList.add("hidden");
      modal.classList.remove("flex");
    }

    function toggleSubmenu(id, event) {
      event.preventDefault();
      const submenu = document.getElementById(id);
      submenu.classList.toggle("hidden");
    }

    // Debug output (bisa dihapus saat production)
    console.log(<?= json_encode($data) ?>);
  </script>
</body>
</html>
