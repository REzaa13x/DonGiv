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
                        class="block py-2 px-6 hover:bg-blue-900 rounded-md active"
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
                    <a href="ManajemenRelawan.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">
                        Manajemen Relawan
                    </a>
                    <a href="ManajemenProgram.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">
                        Manajemen Program
                    </a>
                    <a href="ManajemenPendaftaranProgram.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">
                        Manajemen Pendaftaran
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
                <i class="fas fa-box mr-2"></i> Kelola Penyaluran
            </a>

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
        <?php
        // Include koneksi database di awal main content
        include '../users/koneksi.php';

        // Tampilkan pesan sukses/gagal
        if (isset($_GET['status'])) {
            if ($_GET['status'] === 'berhasil') {
                $recipient_display = htmlspecialchars($_GET['recipient'] ?? 'donatur');
                echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 max-w-xl mx-auto">';
                echo '    <strong class="font-bold">Berhasil!</strong>';
                echo '    <span class="block sm:inline">Email berhasil dikirim ke ' . $recipient_display . '.</span>';
                echo '    <span onclick="this.parentElement.style.display=\'none\';" class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">';
                echo '        <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 5.652a1 1 0 10-1.414-1.414L10 7.586 7.066 4.652A1 1 0 105.652 6.066L8.586 9l-2.934 2.934a1 1 0 101.414 1.414L10 10.414l2.934 2.934a1 1 0 001.414-1.414L11.414 9l2.934-2.934z"/></svg>';
                echo '    </span>';
                echo '</div>';
            } elseif ($_GET['status'] === 'gagal') {
                $error_message = htmlspecialchars($_GET['error'] ?? 'Terjadi kesalahan saat mengirim email.');
                echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 max-w-xl mx-auto">';
                echo '    <strong class="font-bold">Gagal!</strong>';
                echo '    <span class="block sm:inline">' . $error_message . '</span>';
                echo '    <span onclick="this.parentElement.style.display=\'none\';" class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">';
                echo '        <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 5.652a1 1 0 10-1.414-1.414L10 7.586 7.066 4.652A1 1 0 105.652 6.066L8.586 9l-2.934 2.934a1 1 0 101.414 1.414L10 10.414l2.934 2.934a1 1 0 001.414-1.414L11.414 9l2.934-2.934z"/></svg>';
                echo '    </span>';
                echo '</div>';
            }
        }
        ?>

        <section class="notification-section">
            <h2>Daftar Notifikasi</h2>
            <ul class="notification-list">
                <?php
                // === PENTING: PENANGANAN NOTIFIKASI DINAMIS ===
                // Pesan error sebelumnya "Table 'tubes_webpro.Notifications' Doesn't Exist"
                // berarti tabel `notifications` tidak ada di database Anda.
                // Anda memiliki 2 pilihan:
                // 1. BUAT TABEL `notifications` di database Anda dengan struktur:
                //    CREATE TABLE notifications (
                //        id INT AUTO_INCREMENT PRIMARY KEY,
                //        type VARCHAR(100) NOT NULL,
                //        message TEXT NOT NULL,
                //        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                //    );
                //    Dan masukkan beberapa data contoh ke dalamnya.
                // 2. KEMBALIKAN BAGIAN INI KE NOTIFIKASI STATIS (hardcoded) seperti yang Anda punya.
                //    Jika Anda memilih ini, hapus blok PHP di bawah dan gunakan HTML statis yang Anda berikan.

                // --- Contoh Logika Dinamis (Jika Anda punya tabel `notifications`) ---
                // Pastikan nama tabel dan kolom sesuai dengan database Anda
                $sql_notifications = "SELECT type, message, created_at FROM notifications ORDER BY created_at DESC LIMIT 5";
                $result_notifications = $conn->query($sql_notifications);

                if ($result_notifications && $result_notifications->num_rows > 0) {
                    while ($row_notif = $result_notifications->fetch_assoc()) {
                        $time_ago = '';
                        $datetime = new DateTime($row_notif['created_at']);
                        $now = new DateTime();
                        $interval = $now->diff($datetime);

                        if ($interval->y > 0) {
                            $time_ago = $interval->y . ' tahun yang lalu';
                        } elseif ($interval->m > 0) {
                            $time_ago = $interval->m . ' bulan yang lalu';
                        } elseif ($interval->d > 0) {
                            $time_ago = $interval->d . ' hari yang lalu';
                        } elseif ($interval->h > 0) {
                            $time_ago = $interval->h . ' jam yang lalu';
                        } elseif ($interval->i > 0) {
                            $time_ago = $interval->i . ' menit yang lalu';
                        } else {
                            $time_ago = 'Baru saja';
                        }

                        echo '<li class="notification-item">';
                        echo '<p><strong>' . htmlspecialchars($row_notif['type']) . ':</strong> ' . htmlspecialchars($row_notif['message']) . '</p>';
                        echo '<span class="timestamp">' . $time_ago . '</span>';
                        echo '</li>';
                    }
                } else {
                    // --- Fallback ke notifikasi statis jika tabel tidak ada atau kosong ---
                    // Ini adalah kode hardcoded yang Anda miliki sebelumnya
                    echo '<li class="notification-item">';
                    echo '    <p>';
                    echo '        <strong>Donasi Baru:</strong> Donasi untuk "Bencana Banjir" sebesar Rp500.000 telah diterima.';
                    echo '    </p>';
                    echo '    <span class="timestamp">2 menit yang lalu</span>';
                    echo '</li>';

                    echo '<li class="notification-item">';
                    echo '    <p>';
                    echo '        <strong>Target Tercapai:</strong> Donasi "Pendidikan Anak" telah mencapai 100% dari target.';
                    echo '    </p>';
                    echo '    <span class="timestamp">1 jam yang lalu</span>';
                    echo '</li>';

                    echo '<li class="notification-item">';
                    echo '    <p>';
                    echo '        <strong>Pengingat:</strong> Donasi "Bantuan Kesehatan" akan berakhir dalam 3 hari.';
                    echo '    </p>';
                    echo '    <span class="timestamp">Kemarin</span>';
                    echo '</li>';
                    // Jika Anda ingin pesan "Belum ada notifikasi" saat tabel tidak ada, uncomment baris di bawah ini dan hapus notifikasi statis di atas
                    // echo '<li class="notification-item"><p>Belum ada notifikasi (atau tabel `notifications` tidak ditemukan).</p></li>';
                }
                ?>
            </ul>

            <form class="email-form" action="send_email.php" method="POST">
                <label for="email-subject">Subjek Email</label>
                <input
                    type="text"
                    id="email-subject"
                    name="subject"
                    placeholder="Masukkan subjek email Anda..."
                    required
                />

                <label for="email-pesan">Isi Pesan</label>
                <textarea
                    id="email-pesan"
                    name="pesan"
                    placeholder="Masukkan isi pesan email Anda di sini..."
                    rows="8"
                    required
                ></textarea>

                <label for="email-recipients">Penerima</label>
                <select id="email-recipients" name="recipients">
                    <option value="all">Semua Donatur</option>
                    <option value="top_donatur">Top Donatur</option>
                    <option value="recent_donatur">Donatur Terbaru</option>
                    </select>

                <button type="submit" class="send-email-btn">Kirim Email</button>
            </form>
        </section>

        <section class="message-section">
            <h2 class="text-xl font-bold mb-4">Riwayat Pengiriman Email</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="py-3 px-4 text-left">Penerima</th>
                            <th class="py-3 px-4 text-left">Subjek</th>
                            <th class="py-3 px-4 text-left">Isi Pesan (Potongan)</th>
                            <th class="py-3 px-4 text-left">Waktu Kirim</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                    <?php
                    // Pastikan $conn dari koneksi.php sudah tersedia
                    $sql_emails = "SELECT nama_penerima, subjek, isi, tanggal_kirim FROM email_kampanye ORDER BY tanggal_kirim DESC";
                    $result_emails = $conn->query($sql_emails);

                    if ($result_emails && $result_emails->num_rows > 0) {
                        while ($row = $result_emails->fetch_assoc()) {
                            $nama_penerima = htmlspecialchars($row['nama_penerima']);
                            $subjek = htmlspecialchars($row['subjek']);
                            // Potong isi pesan agar tidak terlalu panjang di tabel
                            $pesan_potongan = htmlspecialchars(mb_strimwidth($row['isi'], 0, 100, "...", "UTF-8"));
                            $tanggal_kirim = date("d M Y H:i", strtotime($row['tanggal_kirim']));
                            ?>
                            <tr>
                                <td class="py-3 px-4"><?= $nama_penerima ?></td>
                                <td class="py-3 px-4"><?= $subjek ?></td>
                                <td class="py-3 px-4"><?= $pesan_potongan ?></td>
                                <td class="py-3 px-4"><?= $tanggal_kirim ?></td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="4" class="py-3 px-4 text-center">Belum ada email yang terkirim.</td></tr>';
                    }
                    // Tutup koneksi database
                    // $conn->close(); // Tutup koneksi jika Anda yakin tidak ada lagi operasi DB setelah ini
                    ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        // Fungsi untuk toggle submenu
        function toggleSubmenu(submenuId, event) {
            event.preventDefault(); // Mencegah perilaku default link
            const submenu = document.getElementById(submenuId);
            const chevron = event.currentTarget.querySelector('.fa-chevron-down');

            submenu.classList.toggle("active");
            if (chevron) {
                // Asumsi Anda punya CSS untuk .rotate-180 atau Tailwind class 'rotate-180'
                chevron.classList.toggle('rotate-180');
            }
        }

        // Pastikan submenu management terbuka secara default karena halaman notifikasi ada di dalamnya
        document.addEventListener('DOMContentLoaded', function() {
            const submenu = document.getElementById('donation-submenu');
            if (submenu) {
                submenu.classList.add('active');
                // Juga rotasi ikon chevron saat halaman dimuat jika submenu aktif
                const chevron = submenu.previousElementSibling.querySelector('.fa-chevron-down');
                if (chevron) {
                    chevron.classList.add('rotate-180');
                }
            }
        });

        // Fungsi untuk membuka modal logout
        function openLogoutModal() {
            const modal = document.getElementById("logoutModal");
            modal.style.display = "flex"; // Menggunakan 'flex' untuk centering modal
        }

        // Fungsi untuk menutup modal logout
        function closeLogoutModal() {
            const modal = document.getElementById("logoutModal");
            modal.style.display = "none";
        }

        // Fungsi untuk mengonfirmasi logout
        function confirmLogout() {
            alert("You have been logged out."); // Notifikasi sederhana
            closeLogoutModal();
            // Redirect ke halaman login setelah logout
            window.location.href = "login.html"; // Sesuaikan dengan path halaman login Anda
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