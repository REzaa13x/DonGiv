<?php
include '../users/koneksi.php'; // Pastikan path ini benar relatif dari admin/
session_start();

// Cek apakah admin sudah login (sangat disarankan untuk produksi)
//if (!isset($_SESSION['admin_id'])) {
  //  header('Location: login_admin.php'); // Redirect ke halaman login admin Anda
    //exit();
//}
//$admin_user_id = $_SESSION['admin_id']; // Gunakan session admin ID setelah login diimplementasikan

// --- FUNGSI PEMBANTU UNTUK MENCETAK SATU BARIS TABEL ---
function print_donasi_table_row($donasi, $no_urut) {
    // Escape output untuk keamanan (melindungi dari XSS)
    $order_id_display = htmlspecialchars($donasi['order_id'] ?? 'N/A');
    $amount_formatted = "Rp " . number_format($donasi['amount'] ?? 0, 0, ',', '.');
    $donated_at_display = htmlspecialchars(date('d M Y H:i', strtotime($donasi['donated_at'] ?? '')));

    $donatur_info = '';
    if (isset($donasi['is_anonymous']) && $donasi['is_anonymous'] == 1) {
        $donatur_info = 'Anonim';
    } else {
        $donatur_info = htmlspecialchars($donasi['user_name'] ?? 'N/A');
        if (!empty($donasi['user_email'])) {
            $donatur_info .= '<br><small class="text-gray-500">' . htmlspecialchars($donasi['user_email']) . '</small>';
        }
    }

    $campaign_name_display = htmlspecialchars($donasi['campaign_name'] ?? 'N/A');

    $status_display = htmlspecialchars(ucfirst($donasi['payment_status'] ?? 'N/A'));
    $status_class = '';
    switch ($donasi['payment_status']) {
        case 'settlement':
        case 'capture': $status_class = 'settlement'; break;
        case 'pending':
        case 'waiting_verification': $status_class = 'pending'; break; // Status yang baru
        case 'failed':
        case 'expire':
        case 'deny': $status_class = 'failed'; break;
        default: $status_class = 'info'; break;
    }

    $metode_display = htmlspecialchars(ucfirst(str_replace('_', ' ', $donasi['metode_pembayaran'] ?? 'N/A'))); // Mengganti underscore
    $metode_class = str_replace(' ', '_', strtolower($donasi['metode_pembayaran'] ?? ''));
    if (empty($metode_class) || $metode_class == 'n_a') $metode_class = 'info';

    echo "<tr>
        <td class='py-3 px-4 border-b'>" . $no_urut . "</td>
        <td class='py-3 px-4 border-b'>{$donated_at_display}</td>
        <td class='py-3 px-4 border-b'>{$order_id_display}</td>
        <td class='py-3 px-4 border-b'>{$donatur_info}</td>
        <td class='py-3 px-4 border-b'>{$campaign_name_display}</td>
        <td class='py-3 px-4 border-b'>{$amount_formatted}</td>
        <td class='py-3 px-4 border-b'><span class='status-badge {$status_class}'>{$status_display}</span></td>
        <td class='py-3 px-4 border-b'><span class='status-badge {$metode_class}'>{$metode_display}</span></td>
        <td class='py-3 px-4 border-b space-x-1'>";

    // Tombol Aksi: Lihat Bukti & Verifikasi
    // Pastikan tombol "Bukti" hanya muncul jika bukti_upload ada
    if (!empty($donasi['bukti_upload'])) {
        // Asumsi bukti.php ada di folder admin yang sama dengan RiwayatDonasi.php
        echo "<a href='bukti.php?order_id={$order_id_display}' target='_blank' class='bg-blue-500 text-white px-2 py-1 rounded text-xs hover:bg-blue-600 mr-1'><i class='fas fa-eye'></i> Bukti</a>";
    }

    // Tombol Verifikasi hanya untuk donasi yang pending dan manual transfer
    if ($donasi['payment_status'] == 'waiting_verification' || $donasi['payment_status'] == 'pending') {
        // Anda bisa menambahkan kondisi metode_pembayaran jika hanya ingin verifikasi manual untuk bank transfer
        // && ($donasi['metode_pembayaran'] == 'manual_transfer' || $donasi['metode_pembayaran'] == 'bank_transfer')
        echo "<form action='verifikasi_donasi.php' method='POST' style='display:inline-block;' onsubmit='return confirmVerifikasi(this);'>
                <input type='hidden' name='order_id' value='{$order_id_display}'>
                <button type='submit' class='bg-green-600 text-white px-2 py-1 rounded text-xs hover:bg-green-700 ml-1'>
                    <i class='fas fa-check-circle'></i> Verifikasi
                </button>
            </form>";
    } else {
        echo "<span class='text-gray-500 text-xs'>Tidak ada aksi</span>";
    }
    echo "</td></tr>";
}
// --- AKHIR FUNGSI PEMBANTU ---

$donations_history = [];
$message = '';
$error_type = '';

$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : '';

// Query untuk mengambil data donasi dengan JOIN ke tabel users dan kampanye_donasi
$sql = "SELECT
            d.id,
            d.order_id,
            d.amount,
            d.payment_status,
            d.metode_pembayaran,
            d.donated_at,
            d.is_anonymous,
            u.name AS user_name,
            u.email AS user_email,
            kd.nama_donasi AS campaign_name,
            d.bukti_upload
        FROM
            donations d
        LEFT JOIN
            users u ON d.user = u.id
        LEFT JOIN
            kampanye_donasi kd ON d.campaign_id = kd.id_donasi";

if (!empty($keyword)) {
    $sql .= " WHERE u.name LIKE '%$keyword%' OR u.email LIKE '%$keyword%' OR kd.nama_donasi LIKE '%$keyword%' OR d.order_id LIKE '%$keyword%' OR d.payment_status LIKE '%$keyword%'";
}

$sql .= " ORDER BY d.donated_at DESC";

$result = mysqli_query($conn, $sql);

if ($result) {
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $donations_history[] = $row;
        }
    } else {
        $message = "Tidak ada riwayat donasi yang ditemukan.";
        $error_type = 'info';
    }
} else {
    $message = "Error mengambil data riwayat donasi: " . mysqli_error($conn);
    $error_type = 'error';
}

// Tidak menutup koneksi di sini jika ini dipanggil via AJAX,
// karena script akan dieksekusi lagi di AJAX response.
// Namun, jika tidak ada AJAX dan halaman dimuat penuh, boleh ditutup.
// Untuk kemudahan pengembangan saat ini, biarkan seperti ini.

// Cek apakah ini permintaan AJAX
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    $no = 1;
    if (!empty($donations_history)) {
        foreach ($donations_history as $donation) {
            print_donasi_table_row($donation, $no++);
        }
    } else {
        echo "<tr><td colspan='9' class='py-3 px-4 text-center text-gray-500'>Tidak ada riwayat donasi yang ditemukan.</td></tr>";
    }
    mysqli_close($conn); // Tutup koneksi setelah AJAX response
    exit; // Penting untuk menghentikan eksekusi script setelah AJAX response
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Riwayat Donasi Admin</title>
    <link rel="stylesheet" href="Manajemen.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Styling untuk pesan */
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .message.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .table-container {
            max-width: 100%;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background-color: #edf2f7;
            font-weight: bold;
            color: #4a5568;
        }
        tbody tr:hover {
            background-color: #f7fafc;
        }
        /* Styling untuk badge status dan metode pembayaran */
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 600;
            white-space: nowrap; /* Mencegah teks putus baris */
        }
        .status-badge.settlement, .status-badge.capture { background-color: #d1fae5; color: #065f46; } /* Hijau untuk sukses */
        .status-badge.pending, .status-badge.waiting_verification { background-color: #fff3cd; color: #9a6d00; } /* Kuning untuk pending */
        .status-badge.failed, .status-badge.expire, .status-badge.deny { background-color: #fee2e2; color: #991b1b; } /* Merah untuk gagal */
        .status-badge.manual_transfer, .status-badge.bank_transfer, .status-badge.credit_card, .status-badge.midtrans { background-color: #e0f2fe; color: #0c4a6e; } /* Biru untuk metode */
        .status-badge.n_a, .status-badge.info { background-color: #e2e8f0; color: #4a5568; } /* Abu-abu default */

        /* Styling untuk tombol di kolom Aksi */
        td form, td a {
            vertical-align: middle;
            margin-right: 5px;
            display: inline-block; /* Agar tombol/link sejajar */
            margin-bottom: 5px; /* Memberikan sedikit spasi antar tombol vertikal */
        }
        td form:last-child, td a:last-child {
            margin-right: 0;
            margin-bottom: 0;
        }


        /* Perbaikan khusus untuk header tabel agar tidak terpotong */
        .table-container table thead th:nth-child(1) { width: 5%; min-width: 40px; } /* No */
        .table-container table thead th:nth-child(2) { width: 15%; min-width: 120px; } /* Tanggal & Waktu */
        .table-container table thead th:nth-child(3) { width: 15%; min-width: 100px; } /* Order ID */
        .table-container table thead th:nth-child(4) { width: 15%; min-width: 120px; } /* Donatur */
        .table-container table thead th:nth-child(5) { width: 15%; min-width: 120px; } /* Kampanye */
        .table-container table thead th:nth-child(6) { width: 10%; min-width: 100px; } /* Jumlah Donasi */
        .table-container table thead th:nth-child(7) { width: 10%; min-width: 120px; } /* Status Pembayaran */
        .table-container table thead th:nth-child(8) { width: 10%; min-width: 120px; } /* Metode Pembayaran */
        .table-container table thead th:nth-child(9) { width: 10%; min-width: 100px; } /* Aksi */

        /* Tambahkan padding dan margin untuk h1 di header agar terlihat */
        header.header h1 {
            padding: 10px 0;
            margin: 0;
            color: white; /* Diatur ke putih agar terlihat di header biru */
        }
        /* Styling untuk modal logout */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            align-items: center; /* Center content vertically */
            justify-content: center; /* Center content horizontally */
        }
        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more specific, like max-width: 500px */
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            text-align: center;
        }
        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        /* CSS untuk rotasi ikon panah */
        .rotate-180 {
            transform: rotate(180deg);
            transition: transform 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="sidebar text-white w-64 py-6 px-4 fixed h-full bg-blue-900 rounded-tr-lg rounded-br-lg">
        <h2 class="text-2xl font-bold mb-6 flex items-center">
            <i class="fas fa-hand-holding-heart mr-2"></i> DonGiv
        </h2>
        <nav class="space-y-2">
            <a href="Index.php" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
                <i class="fas fa-home mr-2"></i> Dashboard
            </a>
            <div class="relative">
                <a href="#" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center justify-between"
                    onclick="toggleSubmenu('management-submenu', event)">
                    <span><i class="fas fa-donate mr-2"></i> Management</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div id="management-submenu" class="submenu bg-blue-800 mt-2 rounded-md hidden">
                    <a href="notifikasi.html" class="block py-2 px-6 hover:bg-blue-900 rounded-md">Notifikasi dan Email</a>
                    <a href="Manajemen.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">Donation</a>
                    <a href="User Manajement.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">Manajemen User</a>
                </div>
            </div>
            <a href="RiwayatDonasi.php" class="block py-2 px-4 bg-blue-800 rounded-md flex items-center"> <i class="fas fa-share-alt mr-2"></i> Riwayat Donasi
            </a>
            <a href="KelolaPenyaluran.php" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
                <i class="fas fa-box mr-2"></i> Kelola Penyaluran
            </a>
            <a href="#" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center" onclick="openLogoutModal()">
                <i class="fas fa-sign-out-alt mr-2"></i> Log Out
            </a>
        </nav>
    </div>

    <div class="container ml-64 p-6">
        <header class="header bg-blue-700 shadow-md p-4 mb-6 rounded-lg">
            <h1 class="text-2xl font-bold">Riwayat Donasi</h1>
        </header>

        <?php
        // SweetAlert untuk pesan dari verifikasi_donasi.php
        if (isset($_SESSION['admin_message'])) {
            echo "<script>
                Swal.fire({
                    icon: '{$_SESSION['admin_message']['icon']}',
                    title: '{$_SESSION['admin_message']['title']}',
                    text: '{$_SESSION['admin_message']['text']}',
                    confirmButtonText: 'OK'
                });
            </script>";
            unset($_SESSION['admin_message']); // Hapus pesan setelah ditampilkan
        }

        // Tampilkan pesan dari query data
        if (!empty($message) && ($error_type == 'info' || $error_type == 'error')): ?>
            <div class="message <?= $error_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="search-bar bg-white p-4 rounded-lg shadow-md mb-6 flex justify-between items-center">
            <input type="text" placeholder="Cari donasi (Nama Donatur, Kampanye, Order ID, Status)..." id="search-input" class="w-full p-2 border rounded-md mr-4" />
            <button id="search-btn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-search"></i> Cari
            </button>
        </div>

        <?php if (!empty($donations_history)): ?>
            <div class="table-container bg-white rounded-lg shadow-md p-4">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="py-3 px-4 border-b">No</th>
                            <th class="py-3 px-4 border-b">Tanggal & Waktu</th>
                            <th class="py-3 px-4 border-b">Order ID</th>
                            <th class="py-3 px-4 border-b">Donatur</th>
                            <th class="py-3 px-4 border-b">Kampanye</th>
                            <th class="py-3 px-4 border-b">Jumlah Donasi</th>
                            <th class="py-3 px-4 border-b">Status Pembayaran</th>
                            <th class="py-3 px-4 border-b">Metode Pembayaran</th>
                            <th class="py-3 px-4 border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="donasi-tbody">
                        <?php $no = 1; ?>
                        <?php foreach ($donations_history as $donation): ?>
                            <?php print_donasi_table_row($donation, $no++); ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md p-4">
                <p class="text-center text-gray-500">Tidak ada riwayat donasi yang ditemukan.</p>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer ml-64 p-4 text-center text-gray-600">
        <p>&copy; 2024 Manajemen Donasi. Semua hak dilindungi.</p>
    </footer>

    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeLogoutModal()">&times;</span>
            <p class="text-lg mb-4">Apakah Anda yakin ingin keluar?</p>
            <button class="bg-red-500 text-white px-4 py-2 rounded mr-2 hover:bg-red-600" onclick="confirmLogout()">Ya, Keluar</button>
            <button class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400" onclick="closeLogoutModal()">Batal</button>
        </div>
    </div>


    <script>
        function toggleSubmenu(id, event) {
            event.preventDefault();
            const submenu = document.getElementById(id);
            submenu.classList.toggle('hidden');
            const chevron = event.currentTarget.querySelector('.fa-chevron-down, .fa-chevron-up');
            if (chevron) {
                chevron.classList.toggle('fa-chevron-down');
                chevron.classList.toggle('fa-chevron-up');
            }
        }

        function openLogoutModal() {
            const logoutModal = document.getElementById('logoutModal');
            logoutModal.style.display = 'flex';
        }

        function closeLogoutModal() {
            const logoutModal = document.getElementById('logoutModal');
            logoutModal.style.display = 'none';
        }

        function confirmLogout() {
            window.location.href = 'logout.php'; // Pastikan Anda memiliki file logout.php
        }

        // JS untuk pencarian AJAX
        document.getElementById("search-btn").addEventListener("click", function() {
            const keyword = document.getElementById("search-input").value;
            fetch("RiwayatDonasi.php?ajax=1&keyword=" + encodeURIComponent(keyword))
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(data => {
                    document.getElementById("donasi-tbody").innerHTML = data;
                })
                .catch(error => {
                    console.error("Error loading data:", error);
                    document.getElementById("donasi-tbody").innerHTML = "<tr><td colspan='9' class='py-3 px-4 text-center text-red-500'>Error memuat data.</td></tr>";
                });
        });

        // Opsional: Lakukan pencarian saat mengetik
        document.getElementById("search-input").addEventListener("keyup", function(event) {
            document.getElementById("search-btn").click();
        });

        // Konfirmasi Verifikasi (untuk tombol Verifikasi)
        function confirmVerifikasi(form) {
            const orderId = form.elements['order_id'].value;
            Swal.fire({
                title: 'Konfirmasi Verifikasi',
                text: "Anda akan memverifikasi donasi dengan Order ID: " + orderId + ". Lanjutkan?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Verifikasi!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Kirim form jika dikonfirmasi
                }
            });
            return false; // Mencegah submit default form
        }
    </script>
</body>

</html>