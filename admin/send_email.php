<?php
// send_email.php
// Pastikan path ini benar sesuai struktur folder proyek Anda
include '../users/koneksi.php';

// Cek jika request bukan POST, redirect kembali
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: notifikasi.php");
    exit();
}

// 1. Ambil dan validasi input dari form
$subject = trim($_POST['subject'] ?? ''); // Ambil subjek
$pesan = trim($_POST['pesan'] ?? '');
$penerima_kategori = trim($_POST['recipients'] ?? '');

// Validasi dasar input
if (empty($subject) || empty($pesan) || empty($penerima_kategori)) {
    header("Location: notifikasi.php?status=gagal&error=" . urlencode("Subjek, pesan, atau kategori penerima tidak boleh kosong."));
    exit();
}

// Untuk menyimpan data penerima (nama) untuk riwayat email_kampanye
$daftar_nama_penerima_for_email_kampanye = [];
// Untuk menyimpan user_id jika notifikasi toast perlu spesifik (optional, untuk saat ini akan NULL)
$user_ids_for_notifications = []; 

$display_recipient_name = ''; // Untuk ditampilkan di pesan sukses

// 2. Logika Penentuan Penerima Berdasarkan Kategori
// Kali ini, kita akan juga mengambil user_id dari user jika memungkinkan
if ($penerima_kategori == 'all') {
    $display_recipient_name = 'Semua Donatur';
    // Mengambil ID dan nama user
    $stmt_donatur = $conn->prepare("SELECT id, name FROM users WHERE role = 'donatur'");
    if ($stmt_donatur->execute()) {
        $result_donatur = $stmt_donatur->get_result();
        while ($row_donatur = $result_donatur->fetch_assoc()) {
            $daftar_nama_penerima_for_email_kampanye[] = $row_donatur['name'];
            $user_ids_for_notifications[] = $row_donatur['id']; // Simpan ID untuk notifikasi spesifik
        }
    } else {
        error_log("Error fetching all donaturs: " . $stmt_donatur->error);
        header("Location: notifikasi.php?status=gagal&error=" . urlencode("Gagal mengambil daftar semua donatur."));
        exit();
    }
    $stmt_donatur->close();

} elseif ($penerima_kategori == 'top_donatur') {
    $display_recipient_name = 'Top Donatur';
    $stmt_donatur = $conn->prepare("SELECT id, name FROM users WHERE role = 'donatur' ORDER BY dana_donasi DESC LIMIT 10");
    if ($stmt_donatur->execute()) {
        $result_donatur = $stmt_donatur->get_result();
        while ($row_donatur = $result_donatur->fetch_assoc()) {
            $daftar_nama_penerima_for_email_kampanye[] = $row_donatur['name'];
            $user_ids_for_notifications[] = $row_donatur['id'];
        }
    } else {
        error_log("Error fetching top donaturs: " . $stmt_donatur->error);
        header("Location: notifikasi.php?status=gagal&error=" . urlencode("Gagal mengambil daftar top donatur."));
        exit();
    }
    $stmt_donatur->close();

} elseif ($penerima_kategori == 'recent_donatur') {
    $display_recipient_name = 'Donatur Terbaru';
    $stmt_donatur = $conn->prepare("SELECT id, name FROM users WHERE role = 'donatur' ORDER BY created_at DESC LIMIT 10");
    if ($stmt_donatur->execute()) {
        $result_donatur = $stmt_donatur->get_result();
        while ($row_donatur = $result_donatur->fetch_assoc()) {
            $daftar_nama_penerima_for_email_kampanye[] = $row_donatur['name'];
            $user_ids_for_notifications[] = $row_donatur['id'];
        }
    } else {
        error_log("Error fetching recent donaturs: " . $stmt_donatur->error);
        header("Location: notifikasi.php?status=gagal&error=" . urlencode("Gagal mengambil daftar donatur terbaru."));
        exit();
    }
    $stmt_donatur->close();

} else {
    header("Location: notifikasi.php?status=gagal&error=" . urlencode("Kategori penerima tidak valid."));
    exit();
}

// Cek jika tidak ada penerima ditemukan
if (empty($daftar_nama_penerima_for_email_kampanye)) {
    header("Location: notifikasi.php?status=berhasil&recipient=" . urlencode($display_recipient_name) . "&info=" . urlencode("Tidak ada donatur yang ditemukan untuk kategori ini."));
    exit();
}

// 3. Simpan email ke tabel email_kampanye untuk setiap penerima
$stmt_insert_email_kampanye = $conn->prepare("INSERT INTO email_kampanye (isi, subjek, penerima, nama_penerima, tanggal_kirim) VALUES (?, ?, ?, ?, NOW())");

// Menggunakan transaksi untuk memastikan semua insert berhasil atau tidak sama sekali
$conn->begin_transaction();
$all_inserts_successful = true;

foreach ($daftar_nama_penerima_for_email_kampanye as $name_for_history) {
    if (!$stmt_insert_email_kampanye->bind_param("ssss", $pesan, $subject, $penerima_kategori, $name_for_history)) {
        error_log("Binding parameters for email_kampanye failed: " . $stmt_insert_email_kampanye->error);
        $all_inserts_successful = false;
        break;
    }
    if (!$stmt_insert_email_kampanye->execute()) {
        error_log("Execute for email_kampanye failed: " . $stmt_insert_email_kampanye->error);
        $all_inserts_successful = false;
        break;
    }
}

if ($all_inserts_successful) {
    // Commit transaksi untuk email_kampanye
    $conn->commit(); 
    $stmt_insert_email_kampanye->close();

    // --- BAGIAN BARU: SISIPKAN JUGA KE TABEL NOTIFICATIONS ---
    $notification_type_admin = "Pesan Admin: " . $display_recipient_name; // Type notifikasi di user
    $notification_message_content = htmlspecialchars(strip_tags($pesan)); // Pesan notifikasi (strip HTML jika ada)
    
    // Pilih cara memasukkan notifikasi ke tabel `notifications`:
    // Opsi A: Satu notifikasi umum untuk semua user yang relevan (user_id = NULL)
    //         Ini cocok untuk broadcast atau pengumuman umum dari admin.
    $stmt_insert_notif_general = $conn->prepare("INSERT INTO notifications (type, message, user_id, created_at) VALUES (?, ?, NULL, NOW())");
    $stmt_insert_notif_general->bind_param("ss", $notification_type_admin, $notification_message_content);
    if (!$stmt_insert_notif_general->execute()) {
        error_log("Failed to insert general notification: " . $stmt_insert_notif_general->error);
        // Ini tidak akan menyebabkan redirect gagal, tapi error tetap dilog
    }
    $stmt_insert_notif_general->close();

    // Opsi B: Notifikasi spesifik untuk setiap user yang terdaftar dalam daftar penerima (jika Anda ingin)
    //         Ini akan membuat baris notifikasi untuk setiap user_id yang diambil.
    //         (Gunakan jika Anda ingin fitur "sudah dibaca" per user untuk notifikasi admin ini)
    /*
    $stmt_insert_notif_specific = $conn->prepare("INSERT INTO notifications (type, message, user_id, created_at) VALUES (?, ?, ?, NOW())");
    foreach ($user_ids_for_notifications as $user_id_to_notify) {
        if (!$stmt_insert_notif_specific->bind_param("ssi", $notification_type_admin, $notification_message_content, $user_id_to_notify)) {
            error_log("Binding parameters for specific notification failed: " . $stmt_insert_notif_specific->error);
        }
        if (!$stmt_insert_notif_specific->execute()) {
            error_log("Execute for specific notification failed: " . $stmt_insert_notif_specific->error);
        }
    }
    $stmt_insert_notif_specific->close();
    */

    // --- LOGIKA PENGIRIMAN EMAIL SUNGGUHAN (MENGGUNAKAN PHPMailer) ---
    // Ini adalah tempat Anda akan mengimplementasikan PHPMailer.
    // Anda perlu mengambil alamat email (SELECT id, name, email FROM users...)
    // untuk setiap user_id di $user_ids_for_notifications
    // $stmt_get_emails = $conn->prepare("SELECT email FROM users WHERE id IN (" . implode(',', $user_ids_for_notifications) . ")");
    // ... logic PHPMailer ...

    header("Location: notifikasi.php?status=berhasil&recipient=" . urlencode($display_recipient_name));
    exit();
} else {
    $conn->rollback(); // Rollback transaksi jika ada yang gagal
    $stmt_insert_email_kampanye->close();
    error_log("Failed to insert all emails into email_kampanye. Transaction rolled back.");
    header("Location: notifikasi.php?status=gagal&error=" . urlencode("Gagal menyimpan riwayat email ke database."));
    exit();
}

// Tutup koneksi database
$conn->close();
?>