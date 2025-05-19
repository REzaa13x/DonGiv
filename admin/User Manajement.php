<?php
include '../users/koneksi.php';

 $query = mysqli_query($conn, "SELECT name, email, role
    FROM users");
   
   $data = mysqli_fetch_all($query, MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Salurkan Donasi</title>
  <link rel="stylesheet" href="Salur.css" />
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
        </div>
      </div>

      <!-- Channel -->
      <a
        href="Salur.php"
        class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
        <i class="fas fa-share-alt mr-2"></i> Channel
      </a>

      <!-- Finance -->
      <a
        href="User Manajement.php"
        class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
        <i class="fas fa-wallet mr-2"></i> Manajement User
      </a>

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
      <h1>Manajemen User</h1>
      <p>Kelola data pengguna yang terdaftar dalam sistem DonGiv.</p>
    </header>

    <!-- Search Bar -->
    <div class="search-bar">
      <input type="text" placeholder="Cari donasi..." id="search-input" />
      <button id="search-btn"><i class="fas fa-search"></i> Cari</button>
    </div>

   <!-- Table User -->
    <div class="overflow-x-auto bg-white rounded shadow">
      <table class="min-w-full table-auto text-sm text-left">
        <thead class="bg-blue-600 text-black">
          <tr>
            <th class="px-4 py-3">Nama</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3">Password</th>
            <th class="px-4 py-3">Role</th>
            <th class="px-4 py-3">Action</th>
          </tr>
        </thead>
        <tbody id="user-tbody">
           <?php foreach ($data ?? [] as $user): ?>
         <tr class="border-b">
  <td class="px-4 py-2"><?= $user['name'] ?></td>
  <td class="px-4 py-2"><?= $user['email'] ?></td>
  <td class="px-4 py-2">••••••••</td>
  <td class="px-4 py-2"><?= $user['role'] ?></td>
  <td class="px-4 py-2 flex gap-2">
    <a href="DeleteUser.php?id=1"
      class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600"
      onclick="return confirm('Hapus user ini?')">
      <i class="fas fa-trash-alt"></i> Hapus
    </a>
  </td>
</tr>
 <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Footer -->
    <footer class="footer">
      <p>&copy; 2024 Donasi Online. All rights reserved.</p>
    </footer>
  </div>
  <script src="Salur.js"></script>
  <script>
    document.getElementById("search-btn").addEventListener("click", function() {
      const keyword = document.getElementById("search-input").value;

      const xhr = new XMLHttpRequest();
      xhr.open("GET", "Salur.php?ajax=1&keyword=" + encodeURIComponent(keyword), true);
      xhr.onload = function() {
        if (xhr.status === 200) {
          document.getElementById("donasi-tbody").innerHTML = xhr.responseText;
        }
      };
      xhr.send();
    });

    console.log(<?php echo json_encode($data);?>);
  </script>

</body>

</html>