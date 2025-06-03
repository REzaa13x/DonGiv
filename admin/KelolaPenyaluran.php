<?php
// File: admin/KelolaPenyaluran.php

session_start();
include '../users/koneksi.php'; // Pastikan path koneksi.php benar

// Cek apakah admin sudah login (sangat disarankan)
// if (!isset($_SESSION['admin_id'])) { // Asumsi ada admin_id di session setelah login admin
//     header('Location: login_admin.php'); // Redirect ke halaman login admin
//     exit();
// }
// $admin_user_id = $_SESSION['admin_id']; // Baris ini tidak lagi digunakan, tapi bisa tetap ada sebagai komentar

$message = ''; // Untuk menyimpan pesan sukses atau error
$error_type = ''; // 'success', 'error', atau 'info'

// --- 1. Ambil daftar kampanye yang siap disalurkan dari database ---
$campaigns_for_distribution = [];
// Asumsi: Kampanye siap disalurkan jika statusnya 'active' atau 'completed'
// Anda bisa menyesuaikan kondisi WHERE sesuai kebutuhan bisnis Anda
$sql_campaigns = "SELECT id_donasi, nama_donasi, dana_terkumpul, target_dana 
                  FROM kampanye_donasi 
                  WHERE status IN ('active', 'completed') 
                  ORDER BY created_at DESC";

$result_campaigns = mysqli_query($conn, $sql_campaigns);

if ($result_campaigns && mysqli_num_rows($result_campaigns) > 0) {
    while ($row = mysqli_fetch_assoc($result_campaigns)) {
        $campaigns_for_distribution[] = $row;
    }
} else {
    $message = "Tidak ada kampanye yang siap disalurkan.";
    $error_type = 'info'; // Jenis pesan informasi
}

// --- 2. Tangani Form Submission (saat form dikirim via POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['campaign_id'])) {
    $campaign_id_selected = (int)$_POST['campaign_id'];
    $nominal_transfer = (double)$_POST['nominal'];
    
    // PERBAIKAN: admin_user_id diubah kembali menjadi hardcode 1
    $admin_user_id = 1; // ID admin secara hardcode (ganti dengan $_SESSION['admin_id'] untuk produksi)

    // Validasi dasar
    if (empty($campaign_id_selected) || $nominal_transfer <= 0) {
        $message = "Mohon pilih kampanye dan masukkan nominal transfer yang valid.";
        $error_type = 'error';
    } else {
        // Ambil detail campaign yang dipilih untuk validasi lebih lanjut jika diperlukan
        $selected_campaign_details = null;
        foreach ($campaigns_for_distribution as $camp) {
            if ($camp['id_donasi'] == $campaign_id_selected) {
                $selected_campaign_details = $camp;
                break;
            }
        }

        if (!$selected_campaign_details) {
            $message = "Kampanye yang dipilih tidak valid atau tidak ditemukan.";
            $error_type = 'error';
        } else {
            // Inisialisasi path file upload
            $bukti_penyaluran_path = NULL; // Default NULL jika tidak ada upload
            $dokumentasi_kegiatan_paths = []; // Array untuk multiple files

            // Proses Upload Bukti Penyaluran (file tunggal)
            if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] == UPLOAD_ERR_OK) {
                $target_dir_bukti = "../uploads/penyaluran/bukti/";
                if (!is_dir($target_dir_bukti)) { mkdir($target_dir_bukti, 0777, true); }
                $file_extension_bukti = pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION);
                $file_name_bukti = uniqid('bukti_') . '.' . $file_extension_bukti;
                $target_file_bukti = $target_dir_bukti . $file_name_bukti;
                
                if (move_uploaded_file($_FILES['bukti']['tmp_name'], $target_file_bukti)) {
                    $bukti_penyaluran_path = 'uploads/penyaluran/bukti/' . $file_name_bukti;
                } else {
                    $message = "Gagal mengupload bukti penyaluran."; $error_type = 'error';
                }
            } else if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] != UPLOAD_ERR_NO_FILE) {
                 $message = "Error upload bukti penyaluran: Kode error " . $_FILES['bukti']['error']; $error_type = 'error';
            }

            // Proses Upload Dokumentasi Kegiatan (multiple files)
            if (empty($message) && isset($_FILES['dokumentasi']) && is_array($_FILES['dokumentasi']['name'])) {
                foreach ($_FILES['dokumentasi']['name'] as $key => $name) {
                    if ($_FILES['dokumentasi']['error'][$key] == UPLOAD_ERR_OK) {
                        $target_dir_dokumentasi = "../uploads/penyaluran/dokumentasi/";
                        if (!is_dir($target_dir_dokumentasi)) { mkdir($target_dir_dokumentasi, 0777, true); }
                        $file_extension_dokumentasi = pathinfo($name, PATHINFO_EXTENSION);
                        $file_name_dokumentasi = uniqid('dok_') . '.' . $file_extension_dokumentasi;
                        $target_file_dokumentasi = $target_dir_dokumentasi . $file_name_dokumentasi;
                        
                        if (move_uploaded_file($_FILES['dokumentasi']['tmp_name'][$key], $target_file_dokumentasi)) {
                            $dokumentasi_kegiatan_paths[] = 'uploads/penyaluran/dokumentasi/' . $file_name_dokumentasi;
                        } else {
                            $message = "Gagal mengupload beberapa dokumentasi kegiatan."; $error_type = 'error'; break;
                        }
                    } else if ($_FILES['dokumentasi']['error'][$key] != UPLOAD_ERR_NO_FILE) {
                        $message = "Error upload dokumentasi kegiatan: Kode error " . $_FILES['dokumentasi']['error'][$key]; $error_type = 'error'; break;
                    }
                }
            }
            $dokumentasi_kegiatan_path_str = !empty($dokumentasi_kegiatan_paths) ? json_encode($dokumentasi_kegiatan_paths) : NULL; // Simpan sebagai JSON string atau NULL

            if (empty($message)) { // Lanjutkan jika tidak ada error dari validasi input atau upload
                // --- Mulai Transaksi Database ---
                $conn->begin_transaction();
                try {
                    // Simpan data penyaluran ke tabel `penyaluran_donasi` yang baru
                    $tanggal_penyaluran = date('Y-m-d H:i:s'); // Waktu penyaluran sekarang

                    $stmt_insert_penyaluran = $conn->prepare("INSERT INTO penyaluran_donasi (
                        campaign_id, nominal_disalurkan, bukti_penyaluran_path, 
                        dokumentasi_kegiatan_path, tanggal_penyaluran, admin_user_id
                    ) VALUES (?, ?, ?, ?, ?, ?)");

                    if (!$stmt_insert_penyaluran) {
                        throw new Exception("Prepare statement insert penyaluran failed: " . $conn->error);
                    }
                    $stmt_insert_penyaluran->bind_param(
                        "idsssi", // i (campaign_id), d (nominal_disalurkan), s (bukti_path), s (dokumentasi_path), s (tanggal_penyaluran), i (admin_user_id)
                        $campaign_id_selected, 
                        $nominal_transfer, 
                        $bukti_penyaluran_path, 
                        $dokumentasi_kegiatan_path_str, 
                        $tanggal_penyaluran, 
                        $admin_user_id // Menggunakan ID admin hardcode 1
                    );
                    if (!$stmt_insert_penyaluran->execute()) {
                        throw new Exception("Execute insert penyaluran failed: " . $stmt_insert_penyaluran->error);
                    }
                    $stmt_insert_penyaluran->close();

                    // Update status kampanye di tabel `kampanye_donasi` menjadi 'disbursed'
                    $stmt_update_campaign_status = $conn->prepare("UPDATE kampanye_donasi SET status = 'disbursed' WHERE id_donasi = ?");
                    if (!$stmt_update_campaign_status) {
                        throw new Exception("Prepare statement update campaign status failed: " . $conn->error);
                    }
                    $stmt_update_campaign_status->bind_param("i", $campaign_id_selected);
                    if ($stmt_update_campaign_status->execute()) {
                        $message = "Penyaluran berhasil dicatat dan status kampanye diperbarui!";
                        $error_type = 'success';
                    } else {
                        $message = "Penyaluran dicatat, tapi gagal update status kampanye: " . $stmt_update_campaign_status->error; $error_type = 'error';
                    }
                    $stmt_update_campaign_status->close();

                    $conn->commit(); // Komit transaksi jika semua berhasil
                    // Optional: Redirect to prevent form re-submission on refresh
                    // header("Location: KelolaPenyaluran.php?status=success&msg=" . urlencode($message)); exit();

                } catch (Exception $e) {
                    $conn->rollback(); // Rollback transaksi jika terjadi error
                    $message = "Terjadi kesalahan database saat mencatat penyaluran: " . $e->getMessage();
                    $error_type = 'error';
                    error_log("Penyaluran Error: " . $e->getMessage()); // Log error ke file
                }
            }
        }
    }
}


mysqli_close($conn); // Tutup koneksi database
?>
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
    <style>
  .submenu { display: none; }
  .submenu.active { display: block; }

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
  /* CSS untuk rotasi ikon panah */
        .rotate-180 {
            transform: rotate(180deg);
            transition: transform 0.3s ease;
        }
</style>
</head>

<body>
  <div class="sidebar text-white w-64 py-6 px-4 fixed h-full">
    <h2 class="text-2xl font-bold mb-6 flex items-center">
      <i class="fas fa-hand-holding-heart mr-2"></i> DonGiv
    </h2>
    <nav class="space-y-2">
      <a
        href="Index.php"
        class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
        <i class="fas fa-home mr-2"></i> Dashboard
      </a>

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

      <a
        href="RiwayatDonasi.php"
        class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
        <i class="fas fa-share-alt mr-2"></i> Riwayat Donasi
      </a>
       <a
    href="KelolaPenyaluran.php"
    class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
    <i class="fas fa-box mr-2"></i> Kelola Penyaluran</a>

      <a
        href="#"
        class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center"
        onclick="openLogoutModal()">
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

  <div class="container">
    <header class="header">
      <h1>Kelola Penyaluran</h1>
    </header>
<main class="ml-64 p-6 bg-gray-100 min-h-screen">
  <div class="penyaluran-wrapper">
    <?php if (!empty($message)): ?>
        <div class="message <?php echo htmlspecialchars($error_type); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <h2 class="text-xl font-bold mb-4">Form Penyaluran Dana</h2>
    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4" id="penyaluranForm">
      
      <div>
        <label for="campaign_id" class="block font-semibold mb-2">Pilih Kampanye</label>
        <select id="campaign_id" name="campaign_id" required class="w-full p-2 border rounded-md">
          <option value="">-- Pilih Kampanye --</option>
          <?php if (!empty($campaigns_for_distribution)): ?>
              <?php foreach ($campaigns_for_distribution as $campaign): ?>
                <option value="<?= htmlspecialchars($campaign['id_donasi']) ?>">
                  <?= htmlspecialchars($campaign['nama_donasi']) ?> (Terkumpul: Rp <?= number_format($campaign['dana_terkumpul'], 0, ',', '.') ?> / Target: Rp <?= number_format($campaign['target_dana'], 0, ',', '.') ?>)
                </option>
              <?php endforeach; ?>
          <?php else: ?>
              <option value="">Tidak ada kampanye yang siap disalurkan.</option>
          <?php endif; ?>
        </select>
        <?php if (empty($campaigns_for_distribution)): ?>
            <p class="text-red-500 text-sm mt-1">Saat ini tidak ada kampanye yang bisa disalurkan. Pastikan ada kampanye 'active' atau 'completed'.</p>
        <?php endif; ?>
      </div>

      <div>
        <label for="nominal" class="block font-semibold">Nominal Transfer (Rp)</label>
        <input type="number" id="nominal" name="nominal" required class="w-full p-2 border rounded-md" placeholder="Contoh: 1000000" min="0">
      </div>

      <div>
        <label for="bukti" class="block font-semibold">Upload Bukti Penyaluran</label>
        <input type="file" id="bukti" name="bukti" accept="image/*,application/pdf" class="w-full p-2 border rounded-md">
        <small class="text-gray-500">Opsional: Format gambar atau PDF.</small>
      </div>

      <div>
        <label for="dokumentasi" class="block font-semibold">Upload Dokumentasi Kegiatan</label>
        <input type="file" id="dokumentasi" name="dokumentasi[]" accept="image/*,video/*" multiple class="w-full p-2 border rounded-md">
        <small class="text-gray-500">Opsional: Dapat mengupload beberapa file gambar atau video.</small>
      </div>
      
      <div class="flex justify-end">
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
          Selesaikan Campaign
        </button>
      </div>
    </form>
  </div>
</main>

<div id="confirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 max-w-sm w-full text-center">
    <h3 class="text-lg font-semibold mb-4">Konfirmasi Penyaluran</h3>
    <p class="mb-4">Anda akan mencatat penyaluran dana untuk kampanye <span id="campaignNameConfirm" class="font-bold"></span> sebesar <span id="nominalConfirm" class="font-bold"></span>. Apakah Anda yakin?</p>
    <div class="mt-4 flex justify-center gap-4">
      <button type="button" onclick="submitForm()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
        Ya, Selesaikan
      </button>
      <button type="button" onclick="closeConfirmModal()" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">
        Batal
      </button>
    </div>
  </div>
</div>


<script>
  // Fungsi untuk menampilkan modal konfirmasi
  document.getElementById('penyaluranForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Mencegah form langsung submit
    
    const selectedCampaign = document.getElementById('campaign_id');
    const campaignId = selectedCampaign.value;
    const campaignText = selectedCampaign.options[selectedCampaign.selectedIndex].text;
    const nominal = document.getElementById('nominal').value;

    if (!campaignId || nominal <= 0) {
        alert('Mohon lengkapi pilihan kampanye dan nominal transfer.');
        return;
    }

    document.getElementById('campaignNameConfirm').textContent = campaignText;
    document.getElementById('nominalConfirm').textContent = 'Rp ' + parseFloat(nominal).toLocaleString('id-ID');
    document.getElementById('confirmModal').classList.remove('hidden');
  });

  // Fungsi untuk menutup modal konfirmasi
  function closeConfirmModal() {
    document.getElementById('confirmModal').classList.add('hidden');
  }

  // Fungsi untuk submit form setelah konfirmasi
  function submitForm() {
    document.getElementById('confirmModal').classList.add('hidden');
    // Submit form secara manual
    document.getElementById('penyaluranForm').submit();
  }

  // Fungsi untuk toggle submenu (dari sidebar)
  function toggleSubmenu(id, event) {
    event.preventDefault();
    const submenu = document.getElementById(id);
    submenu.classList.toggle('active');
  }

  // Fungsi untuk logout modal (dari sidebar)
  function openLogoutModal() {
    document.getElementById("logoutModal").style.display = "flex";
  }

  function closeLogoutModal() {
    document.getElementById("logoutModal").style.display = "none";
  }

  function confirmLogout() {
    window.location.href = 'logout.php'; // Ganti dengan path logout sebenarnya
  }
</script>

<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</body>

</html>