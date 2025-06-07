<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../users/koneksi.php'; // SESUAIKAN PATH INI JIKA PERLU!

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sertakan PHPMailer jika Anda menggunakannya via Composer
// PATH INI DISESUAIKAN BERDASARKAN LOKASI FOLDER 'vendor' ANDA
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = ''; // Untuk pesan sukses/error
$message_type = ''; // 'success' atau 'error'

// --- Handle Action (Approve/Reject/Complete/Delete) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $registration_id = intval($_GET['id']); // Pastikan ID adalah integer
    $action = $_GET['action'];

    $stmt = null;
    $success = false;
    $new_status = ''; // Untuk status baru setelah aksi
    $transaction_successful = false; // Flag untuk transaksi

    $conn->begin_transaction(); // Mulai transaksi

    try {
        if ($action === 'approve') {
            $new_status = 'approved';
            $stmt = $conn->prepare("UPDATE program_registrations SET status = ?, updated_at = CURRENT_TIMESTAMP, verified_at = CURRENT_TIMESTAMP WHERE id = ? AND status = 'pending'");
            $stmt->bind_param("si", $new_status, $registration_id);
            $success = $stmt->execute();
            if ($success && $stmt->affected_rows > 0) {
                $message = "Pendaftaran berhasil disetujui!";
                $message_type = "success";
                $transaction_successful = true;
            } else {
                throw new Exception("Gagal menyetujui pendaftaran atau status sudah berubah: " . ($stmt->error ?? "No rows affected."));
            }
        } elseif ($action === 'reject') {
            $new_status = 'rejected';
            $stmt = $conn->prepare("UPDATE program_registrations SET status = ?, updated_at = CURRENT_TIMESTAMP, verified_at = CURRENT_TIMESTAMP WHERE id = ? AND status = 'pending'");
            $stmt->bind_param("si", $new_status, $registration_id);
            $success = $stmt->execute();
            if ($success && $stmt->affected_rows > 0) {
                $message = "Pendaftaran berhasil ditolak!";
                $message_type = "success";
                $transaction_successful = true;
            } else {
                throw new Exception("Gagal menolak pendaftaran atau status sudah berubah: " . ($stmt->error ?? "No rows affected."));
            }
        } elseif ($action === 'complete') {
            $new_status = 'completed'; // Status 'completed'
            $stmt = $conn->prepare("UPDATE program_registrations SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND status = 'approved'");
            $stmt->bind_param("si", $new_status, $registration_id);
            $success = $stmt->execute();
            if ($success && $stmt->affected_rows > 0) {
                $message = "Pendaftaran berhasil ditandai selesai!";
                $message_type = "success";
                $transaction_successful = true;
            } else {
                throw new Exception("Gagal menandai pendaftaran selesai atau status tidak 'approved': " . ($stmt->error ?? "No rows affected."));
            }
        } elseif ($action === 'delete') {
            // Delete action doesn't send email as registration is gone
            $stmt = $conn->prepare("DELETE FROM program_registrations WHERE id = ?");
            $stmt->bind_param("i", $registration_id);
            $success = $stmt->execute();
            if ($success && $stmt->affected_rows > 0) {
                $message = "Pendaftaran berhasil dihapus!";
                $message_type = "success";
                $transaction_successful = true; // No email sent for delete, but action is successful
            } else {
                throw new Exception("Gagal menghapus pendaftaran: " . ($stmt->error ?? "No rows affected."));
            }
        } else {
            throw new Exception("Aksi tidak valid.");
        }

        if ($stmt) {
            $stmt->close();
        }

        // Jika aksi yang mengubah status ('approve' atau 'reject') berhasil
        if ($transaction_successful && ($action === 'approve' || $action === 'reject')) {
            // Ambil detail pendaftaran, program, dan relawan untuk email notifikasi
            $sql_details = "
                SELECT
                    pr.status,
                    v.email AS volunteer_email,
                    v.full_name AS volunteer_name,
                    p.program_name,
                    p.start_date,
                    p.end_date,
                    p.location
                FROM program_registrations pr
                JOIN volunteers v ON pr.volunteer_id = v.id
                JOIN programs p ON pr.program_id = p.id
                WHERE pr.id = ?
            ";
            $stmt_details = $conn->prepare($sql_details);
            if (!$stmt_details) {
                throw new Exception("Prepare statement failed for fetching details: " . $conn->error);
            }
            $stmt_details->bind_param("i", $registration_id);
            $stmt_details->execute();
            $result_details = $stmt_details->get_result();
            $details = $result_details->fetch_assoc();
            $stmt_details->close();

            if ($details) {
                $volunteer_email = $details['volunteer_email'];
                $volunteer_name = $details['volunteer_name'];
                $program_name = $details['program_name'];
                $program_start_date = date('d M Y', strtotime($details['start_date']));
                $program_end_date = date('d M Y', strtotime($details['end_date']));
                $program_location = $details['location'];

                // Kirim Email Notifikasi
                $mail = new PHPMailer(true);
                try {
                    // Konfigurasi Server (GANTI DENGAN SETTING SMTP ANDA!)
                    // PASTIKAN SERVER SMTP, USERNAME, DAN PASSWORD EMAIL ANDA SUDAH BENAR
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.yourdomain.com'; // Contoh: 'smtp.gmail.com'
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'your_email@yourdomain.com'; // Ganti dengan email Anda
                    $mail->Password   = 'your_email_password';   // Ganti dengan password email Anda
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Atau ENCRYPTION_SMTPS (lebih aman, Port 465)
                    $mail->Port       = 587; // Atau 465 untuk SMTPS

                    // Penerima
                    $mail->setFrom('no-reply@dongiv.com', 'DonGiv Volunteer'); // Ganti dengan email pengirim Anda
                    $mail->addAddress($volunteer_email, $volunteer_name);

                    // Konten Email
                    $mail->isHTML(true);
                    $mail->Subject = ($action === 'approve') ?
                                    "Pendaftaran Relawan Anda Diterima untuk Program " . $program_name :
                                    "Pendaftaran Relawan Anda Ditolak untuk Program " . $program_name;

                    $email_body = "";
                    if ($action === 'approve') {
                        $email_body = "
                            Halo <strong>{$volunteer_name}</strong>,<br><br>
                            Selamat! Pendaftaran Anda sebagai relawan untuk program <strong>\"{$program_name}\"</strong> telah <strong>diterima</strong>.<br><br>
                            Detail Program:<br>
                            <ul>
                                <li><strong>Nama Program:</strong> {$program_name}</li>
                                <li><strong>Tanggal:</strong> {$program_start_date} - {$program_end_date}</li>
                                <li><strong>Lokasi:</strong> {$program_location}</li>
                            </ul>
                            Kami sangat menghargai komitmen Anda. Koordinator program akan segera menghubungi Anda untuk informasi lebih lanjut mengenai jadwal dan tugas.<br><br>
                            Anda juga bisa melihat status pendaftaran Anda di halaman profil DonGiv.<br><br>
                            Terima kasih telah menjadi bagian dari DonGiv!<br><br>
                            Hormat kami,<br>
                            Tim DonGiv
                        ";
                    } else { // action === 'reject'
                        $email_body = "
                            Halo <strong>{$volunteer_name}</strong>,<br><br>
                            Kami ingin memberitahukan bahwa pendaftaran Anda sebagai relawan untuk program <strong>\"{$program_name}\"</strong> telah <strong>ditolak</strong>.<br><br>
                            Meskipun demikian, kami sangat menghargai minat dan niat baik Anda untuk berkontribusi. Silakan jelajahi program-program relawan kami lainnya yang mungkin sesuai dengan minat dan kualifikasi Anda.<br><br>
                            Terima kasih atas pengertiannya.<br><br>
                            Hormat kami,<br>
                            Tim DonGiv
                        ";
                    }
                    $mail->Body = $email_body;
                    $mail->send();
                } catch (Exception $e) {
                    // Log the email error, but don't stop the main process.
                    // You might want to display a message on the admin UI that email sending failed.
                    error_log("Gagal mengirim email notifikasi ke {$volunteer_email}: {$mail->ErrorInfo}");
                }
            }
        }
        $conn->commit(); // Komit transaksi jika semua berhasil
    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaksi jika ada error
        $message = "Error: " . $e->getMessage();
        $message_type = "error";
    }

    // Redirect to clean URL after action
    header("Location: ManajemenPendaftaranProgram.php?message=" . urlencode($message) . "&type=" . urlencode($message_type));
    exit();
}

// Check for messages from redirect
if (isset($_GET['message']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type']);
}


// --- Fetch Registration Data for Table Display ---
$search_query = $_GET['search'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';

$sql_registrations = "
    SELECT
        pr.id,
        pr.registration_date,
        pr.status,
        pr.completed_hours,
        v.full_name AS volunteer_name,
        v.email AS volunteer_email,
        p.program_name AS program_name,
        p.start_date AS program_start_date,
        p.end_date AS program_end_date
    FROM program_registrations pr
    JOIN volunteers v ON pr.volunteer_id = v.id
    JOIN programs p ON pr.program_id = p.id
";

$where_clauses = [];
$params = [];
$param_types = "";

if (!empty($search_query)) {
    $where_clauses[] = "(v.full_name LIKE ? OR p.program_name LIKE ? OR v.email LIKE ?)";
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $param_types .= "sss";
}

if (!empty($filter_status)) {
    $where_clauses[] = "pr.status = ?";
    $params[] = $filter_status;
    $param_types .= "s";
}

if (!empty($where_clauses)) {
    $sql_registrations .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql_registrations .= " ORDER BY pr.registration_date DESC";

$stmt_registrations = $conn->prepare($sql_registrations);

if (!empty($params)) {
    $stmt_registrations->bind_param($param_types, ...$params);
}
$stmt_registrations->execute();
$result_registrations = $stmt_registrations->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <title>Manajemen Pendaftaran Program</title>
    <style>
        /* Impor font Poppins */
        @import url("https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;0,700;0,800;0,900&display=swap");

        /* Reset CSS dasar dan atur font-family */
        * {
            font-family: "Poppins", sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            outline: none;
            border: none;
            text-decoration: none;
            transition: all 0.2s linear;
        }

        /* Styling Sidebar (from your admin dashboard) */
        .sidebar {
            background-color: #1E3A8A;
            color: white;
            width: 250px;
            height: 100vh;
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            z-index: 50;
        }

        .sidebar a {
            color: white;
            padding: 10px 15px;
            margin: 5px 0;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.2s ease;
            display: flex;
            align-items: center;
        }
        .sidebar a span {
            flex-grow: 1;
        }

        .sidebar a:hover {
            transform: translateX(0.3px);
            background-color: #007bff;
        }

        /* Styling Submenu */
        .submenu {
            display: none;
            padding-left: 20px;
        }

        .submenu.active {
            display: block;
        }

        /* Modal Logout (from your admin dashboard) */
        #logoutModal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            justify-content: center;
            align-items: center;
            padding: 0;
            overflow: hidden;
            box-sizing: border-box;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            width: 300px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .modal-content h2 {
            margin-bottom: 10px;
            color: black;
            font-size: 24px;
            font-weight: bold;
        }

        .modal-content p {
            margin-bottom: 20px;
            color: black;
            font-size: 16px;
        }

        .modal-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .confirm-button,
        .cancel-button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .confirm-button {
            background-color: #dc3545;
            color: white;
        }

        .confirm-button:hover {
            background-color: #c82333;
        }

        .cancel-button {
            background-color: #6c757d;
            color: white;
        }

        .cancel-button:hover {
            background-color: #5a6268;
        }

        /* CSS untuk rotasi ikon panah */
        .rotate-180 {
            transform: rotate(180deg);
            transition: transform 0.3s ease;
        }

        /* Main Content Area */
        #main-content {
            flex-1 p-6 ml-64; /* Tailwind classes */
            margin-left: 250px; /* Sesuaikan dengan lebar sidebar */
            padding: 24px; /* p-6 */
            background-color: #f0f2f5; /* bg-gray-100 */
        }

        /* Header Main Content */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0; /* border-gray-200 */
        }

        .header h1 {
            font-size: 2em;
            color: #212121; /* text-gray-800 */
            margin: 0;
        }

        /* Section Table styles */
        .section-table {
            background-color: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .section-table h2 {
            font-size: 1.8em;
            color: #212121;
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 15px;
        }

        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .table-controls .search-box {
            display: flex;
            align-items: center;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 5px 10px;
            background-color: white;
        }

        .table-controls .search-box input,
        .table-controls .filter-select {
            border: none;
            outline: none;
            padding: 8px;
            font-size: 1em;
            width: 200px;
            background-color: transparent; /* For select */
        }

        .table-controls .search-box svg {
            fill: #757575;
            width: 20px;
            height: 20px;
            margin-right: 5px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .data-table th,
        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
        }

        .data-table th {
            background-color: #f0f2f5;
            color: #212121;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr:hover {
            background-color: #f5f5f5;
        }

        .data-table .status {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85em;
            font-weight: 600;
            text-align: center;
            display: inline-block;
        }

        .status.pending { background-color: #fffde7; color: #FFC107; }
        .status.approved { background-color: #e8f5e9; color: #4CAF50; }
        .status.rejected { background-color: #ffebee; color: #F44336; }
        .status.completed { background-color: #e3f2fd; color: #2196F3; }


        .actions-buttons button, .actions-buttons a {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            margin: 0 3px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .actions-buttons button svg, .actions-buttons a svg {
            width: 18px;
            height: 18px;
            fill: #757575;
        }

        .actions-buttons button:hover, .actions-buttons a:hover {
            background-color: #f0f2f5;
        }

        .actions-buttons button.approve:hover svg { fill: #4CAF50; }
        .actions-buttons button.reject:hover svg { fill: #FFC107; }
        .actions-buttons button.complete:hover svg { fill: #2196F3; }
        .actions-buttons button.delete:hover svg { fill: #F44336; }
        .actions-buttons button.view:hover svg { fill: #00BCD4; }

        /* Message Box for success/error */
        .message-box {
            position: fixed; /* Keep it fixed to always be visible at top right */
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 1000; /* Ensure it's on top */
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            opacity: 0;
            transform: translateY(-20px);
            animation: slideIn 0.5s forwards;
            display: flex; /* Added for icon alignment */
            align-items: center; /* Added for icon alignment */
            gap: 10px; /* Space between icon and text */
        }

        .message-box.success {
            background-color: #4CAF50;
        }

        .message-box.error {
            background-color: #F44336;
        }
        /* Add other types if needed, e.g., info */
        .message-box.info {
            background-color: #2196F3;
        }

        @keyframes slideIn {
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 10px;
                flex-direction: row;
                justify-content: space-around;
                flex-wrap: wrap;
            }
            .sidebar h2 {
                display: none;
            }
            .sidebar nav {
                width: 100%;
            }
            .sidebar nav a {
                padding: 8px 10px;
                font-size: 0.9em;
                justify-content: center;
            }
            .sidebar nav a i {
                margin-right: 0;
            }
            .sidebar nav a span {
                display: none;
            }
            .submenu {
                padding-left: 0;
                text-align: center;
            }
            .submenu a {
                padding: 8px 10px;
            }

            #main-content {
                margin-left: 0;
                padding: 15px;
            }

            .table-controls {
                flex-direction: column;
                align-items: flex-start;
            }
            .table-controls .search-box input,
            .table-controls .filter-select {
                width: 100%;
            }
            .data-table th, .data-table td {
                padding: 8px 10px;
                font-size: 0.9em;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar text-white w-64 py-6 px-4 fixed h-full">
        <h2 class="text-2xl font-bold mb-6 flex items-center">
            <i class="fas fa-hand-holding-heart mr-2"></i> DonGiv
        </h2>
        <nav class="space-y-2">
            <a href="index.php" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
                <i class="fas fa-home mr-2"></i> Dashboard
            </a>
            <div class="relative">
                <a href="#" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center justify-between"
                    onclick="toggleSubmenu('management-submenu', event)">
                    <span><i class="fas fa-donate mr-2"></i> Management</span>
                    <i class="fas fa-chevron-down" id="chevron-icon-management"></i>
                </a>
                <div id="management-submenu" class="submenu bg-blue-800 mt-2 rounded-md">
                    <a href="notifikasi.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">
                        Notifikasi dan Email
                    </a>
                    <a href="Manajemen.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">
                        Donation
                    </a>
                    <a href="User Manajement.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">
                        Manajement User
                    </a>
                    <a href="ManajemenRelawan.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">
                        Manajemen Relawan
                    </a>
                    <a href="ManajemenProgram.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">
                        Manajemen Program
                    </a>
                    <a href="ManajemenPendaftaranProgram.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md active">
                        Manajemen Pendaftaran
                    </a>
                </div>
            </div>
            <a href="RiwayatDonasi.php"
                class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
                <i class="fas fa-share-alt mr-2"></i> Riwayat Donasi
            </a>
            <a href="KelolaPenyaluran.php"
                class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
                <i class="fas fa-box mr-2"></i> Kelola Penyaluran
            </a>
            <a href="#" class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center"
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

    <div id="main-content">
        <header class="header">
            <h1>Manajemen Pendaftaran Program</h1>
        </header>

        <?php if (!empty($message)): ?>
            <div class="message-box <?php echo $message_type; ?>">
                <?php
                // Tambahkan ikon berdasarkan tipe pesan
                if ($message_type === 'success') {
                    echo '<i class="fas fa-check-circle"></i> ';
                } elseif ($message_type === 'error') {
                    echo '<i class="fas fa-times-circle"></i> ';
                }
                echo $message;
                ?>
            </div>
        <?php endif; ?>

        <section class="section-table" id="daftar-pendaftaran">
            <div class="table-controls">
                <h2>Daftar Pendaftaran</h2>
                <form method="GET" action="ManajemenPendaftaranProgram.php" class="flex flex-wrap gap-3">
                    <div class="search-box">
                        <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                        <input type="text" name="search" placeholder="Cari relawan/program..." value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                    <select name="filter_status" class="filter-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="pending" <?php echo ($filter_status === 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo ($filter_status === 'approved') ? 'selected' : ''; ?>>Disetujui</option>
                        <option value="rejected" <?php echo ($filter_status === 'rejected') ? 'selected' : ''; ?>>Ditolak</option>
                        <option value="completed" <?php echo ($filter_status === 'completed') ? 'selected' : ''; ?>>Selesai</option>
                    </select>
                    <button type="submit" style="display: none;"></button> </form>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Relawan</th>
                            <th>Email Relawan</th>
                            <th>Program</th>
                            <th>Tanggal Pendaftaran</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_registrations->num_rows > 0): ?>
                            <?php while($row = $result_registrations->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['volunteer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['volunteer_email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['program_name']); ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['registration_date']))); ?></td>
                                    <td><span class="status <?php echo htmlspecialchars($row['status']); ?>"><?php echo htmlspecialchars(ucfirst($row['status'])); ?></span></td>
                                    <td class="actions-buttons">
                                        <?php if ($row['status'] === 'pending'): ?>
                                            <button class="approve" title="Setujui" onclick="confirmAction(<?php echo $row['id']; ?>, 'approve', '<?php echo htmlspecialchars($row['volunteer_name']); ?>', '<?php echo htmlspecialchars($row['program_name']); ?>')">
                                                <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                            </button>
                                            <button class="reject" title="Tolak" onclick="confirmAction(<?php echo $row['id']; ?>, 'reject', '<?php echo htmlspecialchars($row['volunteer_name']); ?>', '<?php echo htmlspecialchars($row['program_name']); ?>')">
                                                <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                                            </button>
                                        <?php elseif ($row['status'] === 'approved'): ?>
                                            <button class="complete" title="Tandai Selesai" onclick="confirmAction(<?php echo $row['id']; ?>, 'complete', '<?php echo htmlspecialchars($row['volunteer_name']); ?>', '<?php echo htmlspecialchars($row['program_name']); ?>')">
                                                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                                            </button>
                                        <?php endif; ?>
                                        <button class="delete" title="Hapus" onclick="confirmAction(<?php echo $row['id']; ?>, 'delete', '<?php echo htmlspecialchars($row['volunteer_name']); ?>', '<?php echo htmlspecialchars($row['program_name']); ?>')">
                                            <svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="py-2 px-4 text-center text-gray-500">Tidak ada data pendaftaran program untuk ditampilkan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        // Fungsi untuk membuka atau menutup submenu (dari admin dashboard Anda)
        function toggleSubmenu(submenuId, event) {
            var submenu = document.getElementById(submenuId);
            var chevron = event.currentTarget.querySelector('i.fa-chevron-down');

            document.querySelectorAll('.submenu').forEach(sub => {
                if (sub.id !== submenuId && sub.classList.contains('active')) {
                    sub.classList.remove('active');
                    sub.previousElementSibling.querySelector('i.fa-chevron-down').classList.remove('rotate-180');
                }
            });

            if (submenu.classList.contains('active')) {
                submenu.classList.add('active');
                chevron.classList.add('rotate-180');
            } else {
                submenu.classList.remove('active');
                chevron.classList.remove('rotate-180');
            }
        }

        // Fungsi untuk membuka modal log out (dari admin dashboard Anda)
        function openLogoutModal() {
            document.getElementById('logoutModal').style.display = 'flex';
        }

        // Fungsi untuk menutup modal log out (dari admin dashboard Anda)
        function closeLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
        }

        // Fungsi untuk konfirmasi log out (dari admin dashboard Anda)
        function confirmLogout() {
            window.location.href = '../auth/logout.php';
        }

        // Fungsi konfirmasi aksi pendaftaran (Approve/Reject/Complete/Delete)
        function confirmAction(id, action, volunteerName, programName) {
            let message = '';
            let actionUrl = 'ManajemenPendaftaranProgram.php?action=' + action + '&id=' + id;

            if (action === 'approve') {
                message = 'Apakah Anda yakin ingin MENYETUJUI pendaftaran ' + volunteerName + ' untuk program "' + programName + '"?';
            } else if (action === 'reject') {
                message = 'Apakah Anda yakin ingin MENOLAK pendaftaran ' + volunteerName + ' untuk program "' + programName + '"?';
            } else if (action === 'complete') {
                message = 'Apakah Anda yakin ingin MENANDAI SELESAI pendaftaran ' + volunteerName + ' untuk program "' + programName + '"?';
            } else if (action === 'delete') {
                message = 'Apakah Anda yakin ingin MENGHAPUS pendaftaran ' + volunteerName + ' untuk program "' + programName + '"? Tindakan ini tidak dapat dibatalkan.';
            }

            if (confirm(message)) { // Using native confirm for simplicity
                window.location.href = actionUrl;
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            // Check if there's a message to display
            const messageBox = document.querySelector('.message-box');
            if (messageBox) {
                // Ensure it's visible by adding a class or setting opacity immediately
                messageBox.style.opacity = '1';
                messageBox.style.transform = 'translateY(0)';

                setTimeout(() => {
                    messageBox.style.opacity = '0';
                    messageBox.style.transform = 'translateY(-20px)';
                    // Remove after transition
                    setTimeout(() => messageBox.remove(), 500); // Matches CSS transition duration
                }, 5000); // Display for 5 seconds

                // Clean URL parameters after displaying message
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('message') || urlParams.has('type')) {
                    const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                    window.history.replaceState({ path: newUrl }, '', newUrl);
                }
            }
        });
    </script>
</body>

</html>