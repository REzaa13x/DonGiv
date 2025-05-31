<?php
include '../users/koneksi.php';

// Ambil keyword dari pencarian
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Query data berdasarkan pencarian
if ($search) {
  $sql = "SELECT * FROM tambah_donasi 
          WHERE judul LIKE '%$search%' 
          OR tujuan_penerima_donasi LIKE '%$search%' 
          OR kategori LIKE '%$search%'";
} else {
  $sql = "SELECT * FROM tambah_donasi";
}

$result = mysqli_query($conn, $sql);

$donasi = [];
if ($result && mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $donasi[] = $row;
  }
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
        <a href="User Manajement.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">Manjement User</a>
      </div>
    </div>
    <a href="Salur.php" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
      <i class="fas fa-share-alt mr-2"></i> Riwayat Donasi
    </a>
     <a
    href="KelolaPenyaluran.php"
    class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
    <i class="fas fa-box mr-2"></i> Kelola Penyaluran</a>
    <a href="#" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center" onclick="openLogoutModal()">
      <i class="fas fa-sign-out-alt mr-2"></i> Log Out
    </a>
  </nav>
</div>

<!-- Header -->
<header class="header">
<h1 style="color: white;">Manajemen Donasi</h1>
    </header>

<!-- Main Content -->
<?php if (isset($_GET['status']) && isset($_GET['msg'])): ?>
  <script>
    window.onload = function() {
      alert("<?= htmlspecialchars($_GET['msg']) ?>");
    };
  </script>
<?php endif; ?>

<main class="content ml-64 px-6 py-4 max-w-screen-xl mx-auto">
  <div class="flex justify-between items-center mb-6">
    <form method="GET" class="search-bar flex items-center border px-3 py-2 rounded w-full max-w-md">
      <input type="text" name="search" placeholder="Cari donasi..." class="outline-none px-2 w-full"
        value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" />
      <button type="submit" class="ml-2 text-gray-500"><i class="fas fa-search"></i></button>
    </form>

    <button onclick="location.href='Tambah.php'" class="bg-blue-700 text-white px-4 py-2 rounded hover:bg-blue-800 ml-4">
      <i class="fas fa-plus"></i> Tambah Donasi
    </button>
  </div>

  <section class="table-container flex justify-center">
  <table class="min-w-fit border-collapse mx-auto text-sm">
  <table class="table-fixed border-collapse mx-auto text-sm">
  <thead>
    <tr class="bg-gray-200 text-left">
      <th class="border px-4 py-2 w-12">No</th>
      <th class="border px-4 py-2 w-64">Program Donasi</th>
      <th class="border px-4 py-2 w-48">Target Donasi</th>
      <th class="border px-4 py-2 w-64">Tujuan Penerima Donasi</th>
      <th class="border px-4 py-2 w-40">Kategori Donasi</th>
      <th class="border px-4 py-2 text-center w-32">Action</th>
    </tr>
  </thead>
      <tbody>
        <?php $no = 1; foreach ($donasi as $row): ?>
          <tr class="hover:bg-gray-100">
            <td class="border px-4 py-2"><?= $no++ ?></td>
            <td class="border px-4 py-2"><?= htmlspecialchars($row['judul']) ?></td>
            <td class="border px-4 py-2"><?= 'Rp ' . number_format($row['jumlah'], 0, ',', '.') ?></td>
            <td class="border px-4 py-2"><?= htmlspecialchars($row['tujuan_penerima_donasi']) ?></td>
            <td class="border px-4 py-2"><?= htmlspecialchars($row['kategori']) ?></td>
            <td class="border px-4 py-2 text-center space-x-2">
            <?php
  $id = $row['id_donasi'];
  $judul = htmlspecialchars($row['judul'], ENT_QUOTES);
  $link = "Hapus.php?id=$id";
  $pesan = "Apakah Anda yakin ingin menghapus donasi: $judul?";
?>
<a href="<?= $link ?>" 
   onclick="return confirm('<?= $pesan ?>')" 
   class="text-red-600 hover:text-red-800">
   <i class="fas fa-trash"></i> Hapus
</a>

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
