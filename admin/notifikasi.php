<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Notifikasi dan Email</title>
    <link rel="stylesheet" href="notifikasi.css" />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
    />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="sidebar text-white w-64 py-6 px-4 fixed h-full">
        <h2 class="text-2xl font-bold mb-6 flex items-center">
            <i class="fas fa-hand-holding-heart mr-2"></i> DonGiv
        </h2>
        <nav class="space-y-2">
            <a
                href="Index.php"
                class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center"
            >
                <i class="fas fa-home mr-2"></i> Dashboard
            </a>

            <div class="relative">
                <a
                    href="#"
                    class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center justify-between"
                    onclick="toggleSubmenu('donation-submenu', event)"
                >
                    <span><i class="fas fa-donate mr-2"></i> Management</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div
                    id="donation-submenu"
                    class="submenu bg-blue-800 mt-2 rounded-md"
                >
                    <a
                        href="notifikasi.php"
                        class="block py-2 px-6 hover:bg-blue-900 rounded-md"
                    >
                        Notifkasi dan Email
                    </a>
                    <a
                        href="Manajemen.php"
                        class="block py-2 px-6 hover:bg-blue-900 rounded-md"
                    >
                        Donation
                    </a>
                     <a
                        href="User Manajement.php"
                        class="block py-2 px-4 hover:bg-blue-800 rounded-md">

                        Manajement User 
                    </a>
                </div>
            </div>

            <a
                href="Salur.php"
                class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center"
            >
                <i class="fas fa-share-alt mr-2"></i> Riwayat Donasi
            </a>
             <a
    href="KelolaPenyaluran.php"
    class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
    <i class="fas fa-box mr-2"></i> Kelola Penyaluran</a>

            <a
                href="#"
                class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center"
                onclick="openLogoutModal()"
            >
                <i class="fas fa-sign-out-alt mr-2"></i> Log Out
            </a>
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
    <header class="header">
        <h1>Notifikasi dan Email</h1>
    </header>

    <main class="main-content">
    <?php if (isset($_GET['status']) && $_GET['status'] === 'berhasil'): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 max-w-xl mx-auto">
        <strong class="font-bold">Berhasil!</strong>
        <span class="block sm:inline">Email berhasil dikirim ke <?= htmlspecialchars($_POST['recipients'] ?? 'donatur') ?>.</span>
        <span onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">
            <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20">
                <title>Close</title>
                <path d="M14.348 5.652a1 1 0 10-1.414-1.414L10 7.586 7.066 4.652A1 1 0 105.652 6.066L8.586 9l-2.934 2.934a1 1 0 101.414 1.414L10 10.414l2.934 2.934a1 1 0 001.414-1.414L11.414 9l2.934-2.934z"/>
            </svg>
        </span>
    </div>
    <?php endif; ?>

        <section class="notification-section">
            <h2>Daftar Notifikasi</h2>
            <ul class="notification-list">
                <ul class="notification-item">
                    <p>
                        <strong>Donasi Baru:</strong> Donasi untuk "Bencana Banjir"
                        sebesar Rp500.000 telah diterima.
                    </p>
                    <span class="timestamp">2 menit yang lalu</span>
                </ul>

                <li class="notification-item">
                    <p>
                        <strong>Target Tercapai:</strong> Donasi "Pendidikan Anak" telah
                        mencapai 100% dari target.
                    </p>
                    <span class="timestamp">1 jam yang lalu</span>
                </li>

                <li class="notification-item">
                    <p>
                        <strong>Pengingat:</strong> Donasi "Bantuan Kesehatan" akan
                        berakhir dalam 3 hari.
                    </p>
                    <span class="timestamp">Kemarin</span>
                </li>
            </ul>

            <form class="email-form" action="send_email.php" method="POST">
                <label for="email-pesan">Pesan</label>
                <textarea
                    id="email-pesan"
                    name="pesan"
                    placeholder="Masukkan subjek dan isi pesan email Anda di sini..."
                    rows="8"
                    required
                ></textarea>

                <label for="email-recipients">Penerima</label>
                <select id="email-recipients" name="recipients">
                    <option value="all">Semua Donatur</option>
                    <option value="top">Top Donatur</option>
                    <option value="recent">Donatur Terbaru</option>
                </select>

                <button type="submit" class="send-email-btn">Kirim Email</button>
            </form>
        </section>

        <section class="message-section">
    <h2 class="text-xl font-bold mb-4">Kotak Pesan</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="py-3 px-4 text-left">Penerima</th>
                    <th class="py-3 px-4 text-left">Pesan</th>
                    <th class="py-3 px-4 text-left">Waktu</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
            <?php
include '../users/koneksi.php';

$result = $conn->query("SELECT nama_penerima, isi, tanggal_kirim FROM email_kampanye ORDER BY tanggal_kirim DESC");

while ($row = $result->fetch_assoc()):
    $nama_penerima = htmlspecialchars($row['nama_penerima']);
    $pesan = htmlspecialchars($row['isi']);
    $jam = date("g:i A", strtotime($row['tanggal_kirim']));
?>

<!-- Contoh output -->
<p><strong><?= $nama_penerima ?></strong> - <?= $pesan ?> (<?= $jam ?>)</p>

<?php endwhile; ?>

            </tbody>
        </table>
    </div>
</section>
    </main>

    <script>
        // Fungsi untuk toggle submenu
        function toggleSubmenu(submenuId, event) {
            event.preventDefault(); // Mencegah perilaku default
            const submenu = document.getElementById(submenuId);
            submenu.classList.toggle("active");
        }
        // Fungsi untuk membuka modal logout
        function openLogoutModal() {
            document.getElementById("logoutModal").style.display = "block";
        }

        // Fungsi untuk menutup modal logout
        function closeLogoutModal() {
            document.getElementById("logoutModal").style.display = "none";
        }

        // Fungsi untuk mengonfirmasi logout
        function confirmLogout() {
            alert("You have been logged out.");
            closeLogoutModal();
            // Redirect ke halaman login setelah logout
            window.location.href = "login.html";
        }

        // Menutup modal jika pengguna mengklik di luar modal
        window.onclick = function (event) {
            const modal = document.getElementById("logoutModal");
            if (event.target === modal) {
                closeLogoutModal();
            }
        };
    </script>
</body>
</html>