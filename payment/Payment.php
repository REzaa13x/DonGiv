<?php
session_start();
include '../users/koneksi.php'; // Path ini sudah benar berdasarkan screenshot folder Anda

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    // Simpan campaign_id agar bisa kembali ke halaman ini setelah login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: ../users/login.php"); // Arahkan ke halaman login (sesuaikan path)
    exit();
}

// Ambil user_id dari session
$user_id = $_SESSION['user_id'];

// Query untuk mengambil data pengguna dari database (untuk navbar/form)
$user_data = [];
// Menggunakan nama kolom yang benar dari tabel 'users' Anda
$query_user = "SELECT name, email, no_hp, foto FROM users WHERE id=?"; 
$stmt_user = $conn->prepare($query_user);

// PERBAIKAN: Pastikan statement berhasil disiapkan sebelum dieksekusi dan ditutup
if ($stmt_user) {
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($result_user && $result_user->num_rows > 0) {
        $user_data = $result_user->fetch_assoc();
        // Menggunakan 'name' untuk display username
        $display_username = $user_data['name'] ?? 'User'; 
        $display_email = $user_data['email'] ?? 'email@example.com';
        $display_foto = $user_data['foto'] ?? '../foto/user.png'; // Path default user foto
    } else {
        // Jika user tidak ditemukan, mungkin session rusak, arahkan ke login
        session_destroy();
        header("Location: ../users/login.php");
        exit();
    }
    mysqli_stmt_close($stmt_user); // Statement ditutup di sini, setelah digunakan
} else {
    // Handle error jika prepare statement user gagal
    error_log("Failed to prepare user query: " . $conn->error);
    echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Error</title></head><body><p>Gagal mengambil data pengguna. Silakan coba lagi.</p></body></html>";
    exit();
}


// --- Ambil data kampanye berdasarkan ID dari URL ---
$kampanye = null;
$message = '';
$campaign_id_from_url = 0; // Inisialisasi

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $campaign_id_from_url = (int)$_GET['id'];

    $sql_kampanye = "SELECT id_donasi, nama_donasi, deskripsi, gambar, target_dana, dana_terkumpul FROM kampanye_donasi WHERE id_donasi = ? AND status = 'active'";
    $stmt_kampanye = $conn->prepare($sql_kampanye);
    if ($stmt_kampanye) {
        $stmt_kampanye->bind_param("i", $campaign_id_from_url);
        $stmt_kampanye->execute();
        $result_kampanye = $stmt_kampanye->get_result();

        if ($result_kampanye->num_rows > 0) {
            $kampanye = $result_kampanye->fetch_assoc();
        } else {
            $message = "Kampanye tidak ditemukan atau tidak aktif.";
        }
        mysqli_stmt_close($stmt_kampanye);
    } else {
        $message = "Error saat menyiapkan query kampanye: " . $conn->error;
    }
} else {
    $message = "ID Kampanye tidak disediakan.";
}

// Jika kampanye tidak ditemukan, tampilkan error dan hentikan eksekusi
if (!$kampanye) {
    // Tampilkan pesan error di halaman
    echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Error</title></head><body><p>" . htmlspecialchars($message) . "</p></body></html>";
    exit();
}

// Logika pemisahan deskripsi/tujuan (dari detail_donasi.php)
$display_tujuan_singkat = '';
$full_description_display = ''; 
if (isset($kampanye['deskripsi'])) {
    if (strpos($kampanye['deskripsi'], 'Tujuan:') === 0) {
        $parts = explode("\n\n", $kampanye['deskripsi'], 2);
        $tujuan_line = trim($parts[0]);
        if (strpos($tujuan_line, 'Tujuan:') === 0) {
            $display_tujuan_singkat = substr($tujuan_line, strlen('Tujuan:'));
            $display_tujuan_singkat = trim($display_tujuan_singkat);
        }
        $full_description_display = (count($parts) > 1) ? trim($parts[1]) : '';
    } else {
        $full_description_display = $kampanye['deskripsi'];
    }
    if ($full_description_display === 'Tidak ada deskripsi rinci.') {
        $full_description_display = 'Belum ada deskripsi rinci untuk kampanye ini.';
    }
}

// Data untuk Midtrans Transaction (gross_amount akan diisi oleh JS)
$order_id_prefix = "DON-" . time() . "-" . $user_id . "-" . $campaign_id_from_url; 
$item_name = "Donasi untuk " . $kampanye['nama_donasi'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($kampanye['nama_donasi']) ?> - Donasi</title>
    <link rel="stylesheet" href="Style1.css"> 
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-m9iDaJfvlNPCekOS"></script>
    <style>
        /* Tambahan styling jika diperlukan */
        .campaign-details-card {
            background-color: #f8f8f8;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .campaign-details-card img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
        }
        .campaign-details-card .info {
            flex-grow: 1;
        }
        .campaign-details-card h3 {
            font-size: 1.5em;
            margin-bottom: 5px;
            color: #1e3a8a;
        }
        .campaign-details-card p {
            font-size: 0.9em;
            color: #555;
            margin-bottom: 3px;
        }
        /* Style for selected amount button */
        .donation-options button.selected {
            border: 2px solid #2563eb;
            background-color: #e0f2fe; /* Light blue background */
        }
    </style>
</head>

<body class="overflow-x-hidden">
    <nav>
        <div class="nav-container">
            <a href="../users/DonGiv.php" class="nav-logo">
                <img src="../foto/1-removebg-preview (1).png" alt="DonGiv-Logo">
                <span>DonGiv</span>
            </a>
            <div class="nav-links">
                <a href="../users/DonGiv.php">Home</a>
                <a href="../users/donasi.php">Donations</a> 
                <a href="#About">About</a>
                <a href="#Contact">Contact</a>
                <div class="dropdown">
                    <img src="<?= htmlspecialchars($display_foto) ?>" alt="User" id="dropdown-btn">
                    <div class="dropdown-menu">
                        <div>
                            <p class="font-semibold"><?= htmlspecialchars($display_username) ?></p>
                            <p class="text-sm"><?= htmlspecialchars($display_email) ?></p>
                        </div>
                        <a href="prof.html">Profile</a>
                        <a href="#settings">Settings</a>
                        <a href="#logout">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="main">
        <div class="content">
            <div class="image-section">
                <img src="../foto/WhatsApp Image 2024-12-04 at 21.56.45_efacde14.jpg" alt="Child and Parent">
                <div class="text-section">
                    <h2>Bantu Masyarakat Indonesia</h2>
                    <p>"Pemberian dari anda dapat sangat berharga bagi orang lain. Bantulah sesama kita niscaya kebahagiaan akan selalu ada."</p>
                </div>
            </div>

            <div class="donation-section">
                <h3>BERIKAN KEBAIKAN UNTUK PERUBAHAN JANGKA PANJANG HIDUP DI INDONESIA</h3>
                <div class="benefits">
                    <ul>
                        <li>✅ Donasi dengan mudah, aman dan nyaman</li>
                        <li>✅ Menjadi bagian dalam komunitas Pendekar Anak Indonesia</li>
                        <li>✅ Mendapatkan kiriman paket spesial</li>
                        <li>✅ Menerima pengingat dan informasi bulanan ke email terdaftar</li>
                    </ul>
                    <img src="../foto/tangan.jpg" alt="Gelang Pendekar Anak" class="bracelet">
                </div>

                <div class="campaign-details-card">
                    <img src="<?= !empty($kampanye['gambar']) ? '../' . htmlspecialchars($kampanye['gambar']) : 'https://via.placeholder.com/120x120?text=Campaign' ?>" alt="<?= htmlspecialchars($kampanye['nama_donasi']) ?>">
                    <div class="info">
                        <h3><?= htmlspecialchars($kampanye['nama_donasi']) ?></h3>
                        <p>Target: Rp <?= number_format($kampanye['target_dana'], 0, ',', '.') ?></p>
                        <p>Terkumpul: Rp <?= number_format($kampanye['dana_terkumpul'], 0, ',', '.') ?></p>
                        <p class="text-xs text-gray-600 mt-2"><?= htmlspecialchars($display_tujuan_singkat) ?></p>
                    </div>
                </div>

                <form id="donation-form" method="POST"> 
                    <div class="donation-options">
                        <button type="button" onclick="selectAmount(150000)">Rp 150.000 </button>
                        <button type="button" onclick="selectAmount(200000)">Rp 200.000 </button>
                        <button type="button" onclick="selectAmount(250000)">Rp 250.000 </button>
                        <button type="button" onclick="selectAmount(300000)">Rp 300.000 </button>
                        <button type="button" onclick="selectAmount(350000)">Rp 350.000 </button>
                        <button type="button" onclick="selectAmount(500000)">Rp 500.000 </button>
                    </div>

                    <div class="custom-donation">
                        <input type="number" id="customAmount" name="amount" placeholder="Jumlah Lainnya" min="1000" required>
                    </div>

                    <input type="hidden" name="user_id_for_midtrans" value="<?= htmlspecialchars($user_id); ?>"> 
                    <input type="hidden" name="campaign_id_for_midtrans" value="<?= htmlspecialchars($campaign_id_from_url); ?>">
                    <input type="hidden" name="order_id_for_midtrans" value=""> 
                    
                    <div class="custom-donation">
                        <label for="nama">Nama Lengkap</label><br>
                        <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($user_data['name'] ?? '') ?>" required><br><br>

                        <label for="email">Email</label><br>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" required><br><br>

                        <label for="no_hp">Nomor HP</label><br>
                        <input type="text" id="no_hp" name="no_hp" value="<?= htmlspecialchars($user_data['no_hp'] ?? '') ?>" required><br><br>
                    </div>

                    <button type="button" class="donate-now" id="donate-button">Bantu Sekarang</button>
                </form>

                <p class="note">
                    Dengan klik tombol “Bantu Sekarang”, Anda mengizinkan DonGiv menyimpan data pribadi serta
                    menyampaikan perkembangan program dengan menghubungi Anda melalui telepon, email, dan WhatsApp.
                </p>
            </div>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2024 DonGiv Indonesia. Semua Hak Dilindungi.</p>
    </footer>

    <script>
        let isSnapOpen = false;
        const userId = "<?= htmlspecialchars($user_id); ?>";
        const campaignId = "<?= htmlspecialchars($campaign_id_from_url); ?>"; // Ambil dari PHP
        const orderIdField = document.querySelector('input[name="order_id_for_midtrans"]');
        
        // Populate form fields with user data if available
        document.getElementById('nama').value = "<?= htmlspecialchars($user_data['name'] ?? '') ?>"; 
        document.getElementById('email').value = "<?= htmlspecialchars($user_data['email'] ?? '') ?>";
        document.getElementById('no_hp').value = "<?= htmlspecialchars($user_data['no_hp'] ?? '') ?>"; 

        function selectAmount(amount) {
            document.getElementById("customAmount").value = amount;
            // Remove 'selected' from all buttons
            document.querySelectorAll('.donation-options button').forEach(btn => {
                btn.classList.remove('selected');
            });
            // Add 'selected' to the clicked button
            event.target.classList.add('selected');
        }

        // Add event listener for customAmount input to remove selected class
        document.getElementById('customAmount').addEventListener('input', function() {
            document.querySelectorAll('.donation-options button').forEach(btn => {
                btn.classList.remove('selected');
            });
        });

        document.getElementById('donate-button').addEventListener('click', function () {
            if (isSnapOpen) return; // mencegah pemanggilan berulang

            const amount = parseInt(document.getElementById('customAmount').value || 0);
            const nama = document.getElementById('nama').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('no_hp').value.trim(); 
            
            if (amount < 1000) {
                alert("Jumlah donasi minimal Rp 1.000");
                return;
            }
            if (!nama || !email.includes("@") || phone.length < 8) {
                alert("Mohon isi data dengan benar.");
                return;
            }

            // Generate order_id for Midtrans
            const currentTimestamp = Math.floor(Date.now() / 1000); // Unix timestamp
            const order_id = `DON-${currentTimestamp}-${userId}-${campaignId}`;
            orderIdField.value = order_id; // Set hidden input value

            fetch('create_transaction.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    nama: nama, 
                    email: email, 
                    phone: phone, 
                    amount: amount, 
                    user_id: userId, // Kirim user_id
                    campaign_id: campaignId, // Kirim campaign_id
                    order_id: order_id // Kirim order_id
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.snapToken && typeof snap !== 'undefined') {
                    isSnapOpen = true; // Set flag agar tidak bisa klik dua kali
                    snap.pay(data.snapToken, {
                        onSuccess: function (result) {
                            window.location.href = "nota_transaksi.php?order_id=" + result.order_id;
                        },
                        onPending: function (result) {
                            window.location.href = "nota_transaksi.php?order_id=" + result.order_id;
                        },
                        onError: function (result) {
                            alert("Pembayaran gagal. " + result.status_message);
                            console.error("Midtrans error: ", result);
                            isSnapOpen = false; // Reset flag jika gagal
                        },
                        onClose: function () {
                            isSnapOpen = false; // Reset flag jika user menutup Snap
                            alert("Anda menutup jendela pembayaran. Transaksi dibatalkan.");
                        }
                    });
                } else {
                    alert("Gagal mendapatkan Snap Token. " + (data.message || ''));
                    console.log(data);
                }
            })
            .catch(error => {
                alert("Terjadi kesalahan saat berkomunikasi dengan server.");
                console.error("Fetch create_transaction error:", error);
            });
        });
    </script>
</body>
</html>