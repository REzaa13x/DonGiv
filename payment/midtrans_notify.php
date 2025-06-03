<?php
// midtrans_notify.php
// Pastikan error reporting diaktifkan untuk debugging selama pengembangan
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log raw input dari Midtrans untuk debugging
file_put_contents('midtrans_notify_log.json', "Timestamp: " . date('Y-m-d H:i:s') . "\nInput: " . file_get_contents("php://input") . "\n\n", FILE_APPEND);

// Hapus baris error_log ini setelah semua debugging selesai
error_log("=== NOTIFY_V_FINAL_TEST_02062025_A: Script execution started ==="); 

// Perluasan path ke autoload.php dan koneksi.php
require_once dirname(__FILE__) . '/vendor/autoload.php';
include '../users/koneksi.php'; // Path ke koneksi.php Anda

// Set header respons, penting untuk Midtrans
header('Content-Type: application/json');

// Ambil notifikasi dari Midtrans (JSON POST body)
$json = file_get_contents("php://input");
$data = json_decode($json);

// Cek apakah data notifikasi valid
if (!$data || !isset($data->order_id)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Invalid or empty order_id in notification']);
    exit;
}

$order_id = $data->order_id;

// --- Verifikasi Notifikasi (Sangat Direkomendasikan untuk Produksi) ---
// Untuk Sandbox, ini opsional tapi bagus untuk latihan dan keamanan.
// Anda bisa menambahkan logic untuk memverifikasi signature hash jika diperlukan.
try {
    // BARIS INI: SERVER KEY UNTUK MIDTRANS
    \Midtrans\Config::$serverKey = 'SB-Mid-server-CM85Vg05mehy-1jINyQ4uYw7'; // <-- GANTI DENGAN SERVER KEY ANDA YANG SAMA DENGAN create_transaction.php
    \Midtrans\Config::$isProduction = false; // Sesuaikan dengan lingkungan Anda

    // Ambil status transaksi terbaru dari Midtrans API
    $transaction = \Midtrans\Transaction::status($order_id);
    
    // Ekstrak data yang relevan dari respons Midtrans
    $transaction_status = $transaction->transaction_status; // e.g., settlement, pending
    $gross_amount = (double)$transaction->gross_amount; // Penting: konversi ke double untuk perhitungan
    $payment_type = $transaction->payment_type; // e.g., bank_transfer, credit_card
    $transaction_time = $transaction->transaction_time;
    $fraud_status = $transaction->fraud_status;
    $midtrans_response_full = json_encode($transaction);

    // Ambil custom fields (metadata)
    $user_id = isset($transaction->custom_field1) ? (int)$transaction->custom_field1 : null;
    $campaign_id = isset($transaction->custom_field2) ? (int)$transaction->custom_field2 : null;
    $pesan_donatur = isset($transaction->custom_field3) ? $transaction->custom_field3 : ''; // Pesan donatur dari metadata

    // --- Mulai Transaksi Database ---
    $conn->begin_transaction();
    $processed_successfully = false;

    try {
        // 1. Ambil data donasi dari tabel `donations` berdasarkan order_id
        $stmt_check_donation = $conn->prepare("SELECT id, amount, payment_status, user, campaign_id FROM donations WHERE order_id = ? FOR UPDATE");
        if (!$stmt_check_donation) {
            throw new Exception("Prepare statement check donation failed: " . $conn->error);
        }
        $stmt_check_donation->bind_param("s", $order_id);
        $stmt_check_donation->execute();
        $result_check_donation = $stmt_check_donation->get_result();
        $existing_donation = $result_check_donation->fetch_assoc();
        $stmt_check_donation->close();

        // Cek jika record tidak ditemukan di DB lokal kita
        if (!$existing_donation) {
            error_log("Midtrans Notification: Order ID " . $order_id . " not found in donations table. Attempting insert if success.");

            if ($transaction_status === 'settlement' || $transaction_status === 'capture') {
                // INSERT baru jika record tidak ada dan statusnya sukses
                $insert_donations_stmt = $conn->prepare("INSERT INTO donations (
                    user, campaign_id, amount, message, donated_at, is_anonymous,
                    payment_status, metode_pembayaran, order_id, midtrans_response, snap_token, nama, email, no_hp
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"); // <-- JUMLAH '?' SUDAH BENAR (14)
                
                $is_anonymous_default = 0; // Default: tidak anonim
                $snap_token_from_db = ''; // Ambil jika disimpan di create_transaction, atau biarkan kosong
                $nama_from_midtrans = isset($transaction->customer_details->first_name) ? $transaction->customer_details->first_name : 'Anonim';
                $email_from_midtrans = isset($transaction->customer_details->email) ? $transaction->customer_details->email : 'anonim@example.com';
                $phone_from_midtrans = isset($transaction->customer_details->phone) ? $transaction->customer_details->phone : '0';

                $insert_donations_stmt->bind_param(
                    "iidsisssssssss", // <-- STRING TIPE DATA SUDAH BENAR (14 karakter)
                                       // i (user), i (campaign_id), d (amount), s (message), s (donated_at), i (is_anonymous),
                                       // s (payment_status), s (metode_pembayaran), s (order_id), s (midtrans_response),
                                       // s (snap_token), s (nama), s (email), s (no_hp)
                    $user_id, 
                    $campaign_id, 
                    $gross_amount,
                    $pesan_donatur,
                    $transaction_time, 
                    $is_anonymous_default,
                    $transaction_status, 
                    $payment_type,       
                    $order_id,
                    $midtrans_response_full,
                    $snap_token_from_db, 
                    $nama_from_midtrans,
                    $email_from_midtrans,
                    $phone_from_midtrans
                );
                if (!$insert_donations_stmt->execute()) {
                    throw new Exception("Failed to INSERT new donation record in DB: " . $insert_donations_stmt->error);
                }
                $insert_donations_stmt->close();
            } else {
                $conn->rollback(); 
                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => 'Notification received, order not found and not success status.']);
                exit;
            }
        } else { // Record sudah ada di DB lokal, UPDATE statusnya
            if ($existing_donation['payment_status'] === 'settlement' || $existing_donation['payment_status'] === 'capture') {
                error_log("Midtrans Notification: Order ID " . $order_id . " already processed as " . $existing_donation['payment_status'] . ". Skipping updates.");
                $conn->commit(); 
                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => 'Notification already handled.']);
                exit;
            }

            // Tambahkan baris error_log ini SEBELUM $stmt_update_donation->bind_param (untuk debugging)
            error_log("DEBUG: Update Donations - order_id: " . $order_id . 
                      " | Status: " . $transaction_status . 
                      " | Metode: " . $payment_type . 
                      " | Time: " . $transaction_time . 
                      " | Response: " . substr($midtrans_response_full, 0, 50)); 

            // Update status donasi di tabel `donations`
            $stmt_update_donation = $conn->prepare("UPDATE donations SET 
                payment_status = ?, 
                midtrans_response = ?, 
                metode_pembayaran = ?,  
                donated_at = NOW()      
                WHERE order_id = ?");
            
            if (!$stmt_update_donation) {
                throw new Exception("Prepare statement update donation failed: " . $conn->error);
            }
            // PERBAIKAN: Hapus $transaction_time dari daftar parameter karena donated_at = NOW()
            $stmt_update_donation->bind_param(
                "ssss", // s (status), s (midtrans_response), s (metode_pembayaran), s (order_id)
                $transaction_status, 
                $midtrans_response_full, 
                $payment_type, 
                $order_id
            );
            
            if (!$stmt_update_donation->execute()) {
                throw new Exception("Execute update donation failed: " . $stmt_update_donation->error);
            }
            $stmt_update_donation->close();
        }

        // --- Logika Update `dana_terkumpul` dan `users` hanya jika pembayaran sukses (settlement/capture) ---
        if ($transaction_status === 'settlement' || $transaction_status === 'capture') {
            // Update dana_terkumpul di kampanye_donasi
            $stmt_update_campaign_fund = $conn->prepare("UPDATE kampanye_donasi SET dana_terkumpul = dana_terkumpul + ? WHERE id_donasi = ?");
            if (!$stmt_update_campaign_fund) {
                throw new Exception("Prepare statement update campaign failed: " . $conn->error);
            }
            $stmt_update_campaign_fund->bind_param("di", $gross_amount, $campaign_id); 
            if (!$stmt_update_campaign_fund->execute()) {
                throw new Exception("Execute update campaign failed: " . $stmt_update_campaign_fund->error);
            }
            $stmt_update_campaign_fund->close();

            // Update dana_didonasi dan poin di tabel users
            $stmt_update_user_funds = $conn->prepare("UPDATE users SET dana_didonasi = dana_didonasi + ?, poin = poin + 1 WHERE id = ?"); 
            if (!$stmt_update_user_funds) {
                throw new Exception("Prepare statement update user funds failed: " . $conn->error);
            }
            $stmt_update_user_funds->bind_param("di", $gross_amount, $user_id); 
            if (!$stmt_update_user_funds->execute()) {
                throw new Exception("Execute update user funds failed: " . $stmt_update_user_funds->error);
            }
            $stmt_update_user_funds->close();

            // Cek apakah ini donasi pertama user ke campaign ini yang sukses (untuk update total_campaign)
            error_log("DEBUG: Checking first donation count for user_id: " . $user_id . " and campaign_id: " . $campaign_id . " for order: " . $order_id);
            
            $stmt_check_first_donation_to_campaign = $conn->prepare("SELECT COUNT(*) FROM donations WHERE user = ? AND campaign_id = ? AND payment_status IN ('settlement', 'capture')");
            if (!$stmt_check_first_donation_to_campaign) {
                throw new Exception("Prepare statement check first donation to campaign failed: " . $conn->error);
            }
            $stmt_check_first_donation_to_campaign->bind_param("ii", $user_id, $campaign_id);
            if (!$stmt_check_first_donation_to_campaign->execute()) {
                error_log("Failed to EXECUTE check first donation to campaign: " . $stmt_check_first_donation_to_campaign->error); 
                throw new Exception("Execute check first donation to campaign failed: " . $conn->error);
            }
            $stmt_check_first_donation_to_campaign->bind_result($donations_count_for_campaign);
            $stmt_check_first_donation_to_campaign->fetch();
            $stmt_check_first_donation_to_campaign->close();

            // Tambahkan logging untuk hasil COUNT
            error_log("DEBUG: Result of donations_count_for_campaign for order " . $order_id . " is: " . $donations_count_for_campaign);

            // Jika ini donasi sukses pertama user untuk campaign ini
            if ($donations_count_for_campaign == 1) { 
                $stmt_update_user_total_campaign = $conn->prepare("UPDATE users SET campaign_terdonasikan = campaign_terdonasikan + 1 WHERE id = ?");
                if (!$stmt_update_user_total_campaign) {
                    throw new Exception("Prepare statement update user total campaign failed: " . $conn->error);
                }
                $stmt_update_user_total_campaign->bind_param("i", $user_id);
                if (!$stmt_update_user_total_campaign->execute()) {
                    error_log("Failed to EXECUTE update user total campaign: " . $stmt_update_user_total_campaign->error); 
                    throw new Exception("Execute update user total campaign failed: " . $conn->error);
                }
                // Tambahkan logging untuk affected rows dari update campaign_terdonasikan
                error_log("DEBUG: User total campaign updated. Affected rows: " . $stmt_update_user_total_campaign->affected_rows . " for user_id: " . $user_id);
                $stmt_update_user_total_campaign->close();
            } else {
                error_log("DEBUG: campaign_terdonasikan NOT updated for order " . $order_id . ". Count was not 1. Current count: " . $donations_count_for_campaign);
            }
        }

        // Komit transaksi database jika semua berhasil di blok try
        $conn->commit();
        $processed_successfully = true;

    } catch (Exception $e) {
        // Rollback transaksi jika terjadi error di blok try
        $conn->rollback(); 
        error_log("Midtrans Notification Transaction Error: " . $e->getMessage() . " for order_id: " . $order_id);
    }

    if ($processed_successfully) {
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Notification handled successfully.']);
    } else {
        http_response_code(500); // Internal Server Error jika ada masalah
        echo json_encode(['status' => 'error', 'message' => 'Failed to process notification. Check server logs.']);
    }

} catch (Exception $e) {
    // Tangani error global yang mungkin terjadi di luar blok transaksi
    http_response_code(500); 
    error_log("Midtrans Notification Global Error: " . $e->getMessage() . " for order_id: " . ($order_id ?? 'N/A'));
    echo json_encode(['status' => 'error', 'message' => 'Global error processing notification: ' . $e->getMessage()]);
} finally {
    // Pastikan koneksi database ditutup
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>