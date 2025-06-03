<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "";
include '../users/koneksi.php'; 

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Modifikasi query SQL untuk JOIN dengan tabel kategori_donasi
if ($search) {
    $sql = "SELECT kd.*, k.nama_kategori 
            FROM kampanye_donasi kd
            LEFT JOIN kategori_donasi k ON kd.id_kategori = k.id_kategori
            WHERE kd.nama_donasi LIKE '%$search%' 
            OR kd.deskripsi LIKE '%$search%' 
            OR k.nama_kategori LIKE '%$search%'"; // Mencari berdasarkan nama kategori juga
} else {
    $sql = "SELECT kd.*, k.nama_kategori 
            FROM kampanye_donasi kd
            LEFT JOIN kategori_donasi k ON kd.id_kategori = k.id_kategori
            ORDER BY kd.created_at DESC"; 
}

$result = mysqli_query($conn, $sql);

$kampanye_donasi = []; 
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $kampanye_donasi[] = $row;
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manajemen Kampanye Donasi</title>
    <link rel="stylesheet" href="Manajemen.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        .submenu { /* CUKUP KOSONGKAN INI, JANGAN ADA display: none; */ } 
        .submenu.active { display: block; } /* Ini mungkin tidak lagi diperlukan jika hanya mengandalkan toggle 'hidden' dari Tailwind */
    </style>
</head>

<body>

<div class="sidebar text-white py-6 px-4 fixed h-full bg-blue-900 rounded-tr-lg rounded-br-lg">
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
            <div id="donation-submenu" class="submenu bg-blue-800 mt-2 rounded-md hidden"> 
                <a href="notifikasi.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">Notifikasi dan Email</a>
                <a href="Manajemen.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">Donation</a>
                <a href="User Manajement.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">Manajemen User</a>
            </div>
        </div>
        <a href="RiwayatDonasi.php" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
            <i class="fas fa-share-alt mr-2"></i> Riwayat Donasi
        </a>
        <a
            href="KelolaPenyaluran.php"
            class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
            <i class="fas fa-box mr-2"></i> Kelola Penyaluran
        </a>
        <a href="#" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center" onclick="openLogoutModal()">
            <i class="fas fa-sign-out-alt mr-2"></i> Log Out
        </a>
    </nav>
</div>

<header class="header ml-64">
    <h1 style="color: white;">Manajemen Kampanye Donasi</h1>
</header>

<?php if (isset($_GET['status']) && isset($_GET['msg'])): ?>
    <script>
        window.onload = function() {
            alert("<?= htmlspecialchars($_GET['msg']) ?>");
        };
    </script>
<?php endif; ?>

<main class="content ml-64 px-6 py-4">
    <div class="flex justify-between items-center mb-6 flex-wrap">
        <form method="GET" class="search-bar flex items-center border px-3 py-2 rounded w-full max-w-md mb-4 md:mb-0">
            <input type="text" name="search" placeholder="Cari kampanye donasi..." class="outline-none px-2 w-full"
                value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['GET']) : '' ?>" />
            <button type="submit" class="ml-2 text-gray-500"><i class="fas fa-search"></i></button>
        </form>

        <button onclick="location.href='Tambah.php'" class="bg-blue-700 text-white px-4 py-2 rounded hover:bg-blue-800 ml-0 md:ml-4 w-full md:w-auto">
            <i class="fas fa-plus"></i> Tambah Kampanye
        </button>
    </div>

    <section class="table-container bg-white rounded-lg shadow-md p-4">
        <table class="min-w-full border-collapse text-sm">
            <thead>
                <tr class="bg-gray-200 text-left">
                    <th class="border px-4 py-2 w-12 rounded-tl-lg">No</th>
                    <th class="border px-4 py-2 w-24">Gambar</th>
                    <th class="border px-4 py-2 w-64">Judul Kampanye</th>
                    <th class="border px-4 py-2 w-48">Target Donasi</th>
                    <th class="border px-4 py-2 w-64">Deskripsi & Tujuan</th>
                    <th class="border px-4 py-2 w-40">Kategori</th> 
                    <th class="border px-4 py-2 w-32">Status</th>
                    <th class="border px-4 py-2 text-center w-48 rounded-tr-lg">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; if (!empty($kampanye_donasi)): ?>
                    <?php foreach ($kampanye_donasi as $row): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="border px-4 py-2"><?= $no++ ?></td>
                            <td class="border px-4 py-2">
                                <?php if (!empty($row['gambar'])): ?>
                                    <img src="../<?= htmlspecialchars($row['gambar']) ?>" alt="Gambar Kampanye" class="w-20 h-20 object-cover rounded-md">
                                <?php else: ?>
                                    Tidak ada gambar
                                <?php endif; ?>
                            </td>
                            <td class="border px-4 py-2"><?= htmlspecialchars($row['nama_donasi'] ?? 'N/A') ?></td>
                            <td class="border px-4 py-2"><?= 'Rp ' . number_format($row['target_dana'] ?? 0, 0, ',', '.') ?></td>
                            <td class="border px-4 py-2 text-justify">
                                <?php
                                    $display_description = $row['deskripsi'] ?? ''; 
                                    if (empty(trim($display_description)) || $display_description === '0') {
                                        echo 'Tidak ada deskripsi.';
                                    } else {
                                        echo nl2br(htmlspecialchars($display_description));
                                    }
                                ?>
                            </td>
                            <td class="border px-4 py-2"><?= htmlspecialchars($row['nama_kategori'] ?? 'N/A') ?></td> 
                            <td class="border px-4 py-2">
                                <?php
                                    $status_val = $row['status'] ?? ''; 
                                    $status_class = 'bg-gray-100 text-gray-800'; 
                                    $display_status = 'Tidak Diketahui'; 

                                    if ($status_val == 'active') {
                                        $status_class = 'bg-green-100 text-green-800';
                                        $display_status = 'Aktif';
                                    } elseif ($status_val == 'closed') {
                                        $status_class = 'bg-red-100 text-red-800';
                                        $display_status = 'Ditutup';
                                    } elseif ($status_val == 'completed') {
                                        $status_class = 'bg-blue-100 text-blue-800';
                                        $display_status = 'Selesai';
                                    } elseif ($status_val == 'disbursed') { // Tambahkan status disbursed
                                        $status_class = 'bg-purple-100 text-purple-800';
                                        $display_status = 'Disalurkan';
                                    }
                                ?>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $status_class ?>">
                                    <?= $display_status ?>
                                </span>
                            </td>
                            <td class="border px-4 py-2 text-center space-x-2">
                                <?php
                                    $id = $row['id_donasi'];
                                    $judul = htmlspecialchars($row['nama_donasi'] ?? 'N/A', ENT_QUOTES);
                                    $delete_link = "Hapus.php?id=$id";
                                    $edit_link = "Edit.php?id=$id";
                                ?>
                                <a href="<?= $edit_link ?>" class="text-blue-600 hover:text-blue-800" title="Edit Kampanye">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="<?= $delete_link ?>" 
                                    onclick="return confirm('Apakah Anda yakin ingin menghapus kampanye: <?= $judul ?>?')" 
                                    class="text-red-600 hover:text-red-800" title="Hapus Kampanye">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="border px-4 py-2 text-center">Tidak ada kampanye donasi yang ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<footer class="footer ml-64 p-4 text-center text-gray-600">
    <p>&copy; 2025 Manajemen Donasi. Semua hak dilindungi.</p>
</footer>

<script>
    function toggleSubmenu(id, event) {
        console.log('toggleSubmenu called for ID:', id); // DEBUGGING: Tambahkan log ini
        event.preventDefault();
        const submenu = document.getElementById(id);
        submenu.classList.toggle('hidden'); // Ini akan toggle class 'hidden'
        // Opsional: Toggle ikon chevron
        const chevron = event.currentTarget.querySelector('.fa-chevron-down, .fa-chevron-up'); 
        if (chevron) {
            chevron.classList.toggle('fa-chevron-down');
            chevron.classList.toggle('fa-chevron-up');
        }
    }

    function openLogoutModal() {
        const logoutModal = document.getElementById('logoutModal');
        logoutModal.style.display = 'flex'; // Menggunakan flex untuk pemusatan
        document.body.classList.add('modal-open'); // Opsional: tambahkan kelas untuk mencegah scroll body
    }

    function closeLogoutModal() {
        const logoutModal = document.getElementById('logoutModal');
        logoutModal.style.display = 'none'; // Sembunyikan modal
        document.body.classList.remove('modal-open'); // Opsional: hapus kelas
    }

    function confirmLogout() {
        window.location.href = 'logout.php';
    }
</script>
</body>

</html>