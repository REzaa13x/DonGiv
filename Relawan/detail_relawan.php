<?php
// MULAI OUTPUT BUFFERING PADA BARIS PALING PERTAMA.
// PASTIKAN TIDAK ADA SPASI, BARIS KOSONG, ATAU KARAKTER LAIN SEBELUM TAG <?php
ob_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../users/koneksi.php'; // SESUAIKAN PATH INI JIKA PERLU!

// Cek koneksi
if ($conn->connect_error) {
    // Bersihkan buffer sebelum mati
    ob_end_clean();
    die("Connection failed: " . $conn->connect_error);
}

$program_id = $_GET['id'] ?? null;

// Periksa apakah ID program diberikan dan valid
if (!$program_id || !is_numeric($program_id)) {
    // Bersihkan buffer sebelum redirect
    ob_end_clean();
    header("Location: DaftarAktivitasRelawan.php?message=" . urlencode("ID program tidak valid.") . "&type=error");
    exit();
}

// --- Handle Registration Form Submission (via AJAX) ---
// This block must come BEFORE fetching program details if it's handling a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure no prior output before JSON response
    ob_end_clean();
    header('Content-Type: application/json');

    $response = ['success' => false, 'message' => 'Terjadi kesalahan tidak dikenal.', 'type' => 'error'];

    try {
        $full_name = htmlspecialchars($_POST['regFullName'] ?? '');
        $email = htmlspecialchars($_POST['regEmail'] ?? '');
        $phone_number = htmlspecialchars($_POST['regPhone'] ?? '');
        $date_of_birth = htmlspecialchars($_POST['regDob'] ?? '');
        $address = htmlspecialchars($_POST['regAddress'] ?? '');
        $main_interest = htmlspecialchars($_POST['regInterest'] ?? '');
        $skills = htmlspecialchars($_POST['regSkills'] ?? '');
        $emergency_contact_name = htmlspecialchars($_POST['regEmergencyContactName'] ?? '');
        $emergency_contact_phone = htmlspecialchars($_POST['regEmergencyContactPhone'] ?? '');

        // Set null for empty strings if the database column allows NULL
        $date_of_birth = ($date_of_birth === '') ? null : $date_of_birth;
        $phone_number = ($phone_number === '') ? null : $phone_number;
        $address = ($address === '') ? null : $address;
        $main_interest = ($main_interest === '') ? null : $main_interest;
        $skills = ($skills === '') ? null : $skills;
        $emergency_contact_name = ($emergency_contact_name === '') ? null : $emergency_contact_name;
        $emergency_contact_phone = ($emergency_contact_phone === '') ? null : $emergency_contact_phone;

        $volunteer_id = null;

        // Check if volunteer exists
        $stmt_check_volunteer = $conn->prepare("SELECT id FROM volunteers WHERE email = ?");
        if (!$stmt_check_volunteer) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt_check_volunteer->bind_param("s", $email);
        $stmt_check_volunteer->execute();
        $result_check_volunteer = $stmt_check_volunteer->get_result();

        if ($result_check_volunteer->num_rows > 0) {
            // Volunteer exists, update their details
            $row_volunteer = $result_check_volunteer->fetch_assoc();
            $volunteer_id = $row_volunteer['id'];

            $stmt_update_volunteer = $conn->prepare("UPDATE volunteers SET full_name=?, phone_number=?, date_of_birth=?, address=?, main_interest=?, skills=?, emergency_contact_name=?, emergency_contact_phone=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
            if (!$stmt_update_volunteer) {
                throw new Exception("Prepare statement failed (update volunteer): " . $conn->error);
            }
            $stmt_update_volunteer->bind_param("ssssssssi", $full_name, $phone_number, $date_of_birth, $address, $main_interest, $skills, $emergency_contact_name, $emergency_contact_phone, $volunteer_id);
            if (!$stmt_update_volunteer->execute()) {
                throw new Exception("Gagal memperbarui info relawan: " . $stmt_update_volunteer->error);
            }
            $stmt_update_volunteer->close();
        } else {
            // Volunteer does not exist, insert new volunteer
            $stmt_insert_volunteer = $conn->prepare("INSERT INTO volunteers (full_name, email, phone_number, date_of_birth, address, main_interest, skills, emergency_contact_name, emergency_contact_phone, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            if (!$stmt_insert_volunteer) {
                throw new Exception("Prepare statement failed (insert volunteer): " . $conn->error);
            }
            $stmt_insert_volunteer->bind_param("sssssssss", $full_name, $email, $phone_number, $date_of_birth, $address, $main_interest, $skills, $emergency_contact_name, $emergency_contact_phone);
            if ($stmt_insert_volunteer->execute()) {
                $volunteer_id = $stmt_insert_volunteer->insert_id;
            } else {
                throw new Exception("Gagal menambahkan relawan baru: " . $stmt_insert_volunteer->error);
            }
            $stmt_insert_volunteer->close();
        }
        $stmt_check_volunteer->close();

        if ($volunteer_id) {
            // Check if already registered for this program
            $stmt_check_registration = $conn->prepare("SELECT id FROM program_registrations WHERE volunteer_id = ? AND program_id = ?");
            if (!$stmt_check_registration) {
                throw new Exception("Prepare statement failed (check registration): " . $conn->error);
            }
            $stmt_check_registration->bind_param("ii", $volunteer_id, $program_id);
            $stmt_check_registration->execute();
            $result_check_registration = $stmt_check_registration->get_result();

            if ($result_check_registration->num_rows > 0) {
                $response['message'] = "Anda sudah terdaftar untuk program ini!";
                $response['type'] = "error";
            } else {
                // Register volunteer for the program
                $stmt_register = $conn->prepare("INSERT INTO program_registrations (volunteer_id, program_id, status) VALUES (?, ?, 'pending')");
                if (!$stmt_register) {
                    throw new Exception("Prepare statement failed (register program): " . $conn->error);
                }
                $stmt_register->bind_param("ii", $volunteer_id, $program_id);
                if ($stmt_register->execute()) {
                    $response['success'] = true;
                    $response['message'] = "Pendaftaran Anda untuk program ini berhasil! Menunggu verifikasi dari admin.";
                    $response['type'] = "success";
                } else {
                    throw new Exception("Gagal mendaftar ke program: " . $stmt_register->error);
                }
                $stmt_register->close();
            }
            $stmt_check_registration->close();
        } else {
            throw new Exception("Gagal memproses pendaftaran relawan (ID relawan tidak ditemukan).");
        }
    } catch (Exception $e) {
        $response['message'] = "Error: " . $e->getMessage();
        $response['type'] = "error";
    }

    echo json_encode($response);
    $conn->close();
    exit(); // IMPORTANT: Exit after sending JSON response for AJAX
}

// Fetch program details (for non-POST requests)
$sql_program_detail = "
    SELECT
        p.*,
        c.category_name,
        u.name AS coordinator_name,
        u.email AS coordinator_email,
        u.no_hp AS coordinator_phone,
        (SELECT COUNT(pr.id) FROM program_registrations pr WHERE pr.program_id = p.id AND pr.status = 'approved') AS registered_volunteers_count
    FROM programs p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN users u ON p.coordinator_id = u.id
    WHERE p.id = ?
";
$stmt_program_detail = $conn->prepare($sql_program_detail);
$stmt_program_detail->bind_param("i", $program_id);
$stmt_program_detail->execute();
$result_program_detail = $stmt_program_detail->get_result();
$program_detail = $result_program_detail->fetch_assoc();
$stmt_program_detail->close();

if (!$program_detail) {
    ob_end_clean(); // Clean buffer before redirect
    header("Location: DaftarAktivitasRelawan.php?message=" . urlencode("Program tidak ditemukan atau tidak aktif.") . "&type=error");
    exit();
}

// Check for messages from redirect (for initial page load via GET)
$message = '';
$message_type = '';
if (isset($_GET['message']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type']);
}

// Dummy data for navbar if user not logged in
$username = 'Guest';
$data = ['email' => 'guest@example.com'];

// Ensure output buffering is flushed for non-POST requests
if (ob_get_length() > 0) {
    ob_end_flush();
}

// Close connection at the very end for non-POST requests
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kampanye: <?php echo htmlspecialchars($program_detail['program_name'] ?? 'Program Tidak Ditemukan'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            --primary-blue: #00BCD4;
            --primary-dark: #008C9E;
            --dark-blue: #212121;
            --medium-gray: #424242;
            --light-gray: #e0e0e0;
            --background-light: #f0f2f5;
            --white: #ffffff;
            --navbar-bg-blue: #2563eb;
            --navbar-hover-blue: #93c5fd;
            --dropdown-bg-dark: #1e293b;
            --success-green: #4CAF50;
            --danger-red: #F44336;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-light);
            color: var(--medium-gray);
            overflow-x: hidden;
            position: relative;
        }

        /* Navbar (Re-used from user's provided code and fixed) */
        nav {
            background-color: var(--navbar-bg-blue);
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
        }
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 20px;
            max-width: 1500px;
            margin: 0 auto;
        }
        .nav-logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        .nav-logo img {
            height: 3rem;
            margin-right: 0.5rem;
            margin-left: 0;
        }
        .nav-logo span {
            color: var(--white);
            font-size: 1.5rem;
            font-weight: 600;
        }
        .nav-links {
            display: flex;
            gap: 1.5rem;
        }
        .nav-links a {
            color: var(--white);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .nav-links a:hover {
            color: var(--navbar-hover-blue);
        }
        .dropdown {
            position: relative;
        }
        .dropdown img {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            border: 2px solid var(--white);
            cursor: pointer;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            margin-top: 0.5rem;
            width: 12rem;
            background-color: var(--dropdown-bg-dark);
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 60;
        }
        .dropdown.active .dropdown-menu {
            display: block;
        }
        .dropdown-menu a {
            display: block;
            padding: 0.75rem;
            color: var(--white);
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .dropdown-menu a:hover {
            background-color: var(--navbar-bg-blue);
        }
        .dropdown-menu div {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .dropdown-menu p {
            margin: 0;
            color: var(--white);
        }
        .dropdown-menu .text-sm {
            color: rgba(255, 255, 255, 0.7);
        }

        /* Campaign Detail Specific Styles */
        .campaign-detail-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .campaign-header-banner {
            position: relative;
            width: 100%;
            height: 350px;
            overflow: hidden;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .campaign-header-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.7);
        }

        .campaign-header-content {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 30px;
            color: var(--white);
            background: linear-gradient(to top, rgba(0,0,0,0.7), rgba(0,0,0,0));
        }

        .campaign-header-content h1 {
            font-size: 2.5em;
            margin-top: 0;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .campaign-header-content p {
            font-size: 1.1em;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .campaign-meta-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            font-size: 0.95em;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
        }

        .meta-item svg {
            fill: rgba(255, 255, 255, 0.9);
            width: 20px;
            height: 20px;
            margin-right: 8px;
        }

        .campaign-action-button {
            display: inline-flex;
            align-items: center;
            background-color: #4CAF50;
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1em;
            transition: background-color 0.3s ease;
            cursor: pointer;
        }

        .campaign-action-button:hover {
            background-color: #388E3C;
        }

        .campaign-action-button svg {
            fill: white;
            width: 20px;
            height: 20px;
            margin-left: 10px;
        }

        .campaign-body-content {
            padding: 30px;
        }

        .section-title {
            font-size: 1.8em;
            color: var(--dark-blue);
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--primary-blue);
            padding-bottom: 10px;
        }

        .campaign-body-content p {
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .campaign-body-content ul {
            list-style: disc;
            margin-left: 20px;
            margin-bottom: 20px;
        }

        .campaign-body-content ul li {
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .contact-person {
            background-color: var(--background-light);
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            border: 1px solid var(--light-gray);
        }

        .contact-person h3 {
            font-size: 1.2em;
            color: var(--dark-blue);
            margin-top: 0;
            margin-bottom: 10px;
        }

        .contact-person p {
            margin: 5px 0;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            background-color: var(--medium-gray);
            color: var(--white);
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
            cursor: pointer;
        }
        .back-button:hover {
            background-color: var(--dark-blue);
        }
        .back-button svg {
            fill: var(--white);
            width: 15px;
            height: 15px;
            margin-right: 8px;
        }


        /* Footer */
        .footer {
            background-color: var(--dark-blue);
            color: var(--white);
            text-align: center;
            padding: 20px;
            margin-top: 50px;
            font-size: 0.9em;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.6);
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal-content {
            background-color: var(--white);
            margin: auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            max-width: 600px;
            width: 100%;
            box-sizing: border-box;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-close-button {
            position: absolute;
            top: 15px;
            right: 20px;
            color: var(--medium-gray);
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .modal-close-button:hover,
        .modal-close-button:focus {
            color: var(--dark-blue);
            text-decoration: none;
            cursor: pointer;
        }

        .modal-content h2 {
            font-size: 2em;
            color: var(--dark-blue);
            margin-top: 0;
            margin-bottom: 25px;
            text-align: center;
            border-bottom: 2px solid var(--primary-blue);
            padding-bottom: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-blue);
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="date"],
        .form-group textarea,
        .form-group select {
            width: calc(100% - 20px);
            padding: 12px 10px;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            font-size: 1em;
            color: var(--medium-gray);
            transition: border-color 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="email"]:focus,
        .form-group input[type="tel"]:focus,
        .form-group input[type="date"]:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-blue);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .button-group {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background-color: var(--success-green);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: #388E3C;
        }

        .btn-secondary {
            background-color: var(--background-light);
            color: var(--medium-gray);
            border: 1px solid var(--light-gray);
        }

        .btn-secondary:hover {
            background-color: var(--light-gray);
        }

        /* Styles for the dynamic message box */
        #dynamic-message-box {
            position: relative; /* Changed from fixed */
            margin: 20px auto 0 auto; /* Centered horizontally, 20px from top */
            max-width: 600px;
            width: calc(100% - 40px); /* Adjust for padding/margin */
            box-sizing: border-box; /* Include padding in width */
            text-align: center;
            display: none; /* Default hidden */

            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 1em;
            z-index: 100; /* Lowered z-index but still high enough */
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            opacity: 0;
            transform: translateY(-20px);
            transition: opacity 0.5s ease-out, transform 0.5s ease-out;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        #dynamic-message-box.show {
            opacity: 1;
            transform: translateY(0);
        }

        #dynamic-message-box.success {
            background-color: var(--success-green);
        }

        #dynamic-message-box.error {
            background-color: var(--danger-red);
        }

        #dynamic-message-box.info {
            background-color: #2196F3;
        }


        /* Responsive adjustments */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                padding: 1rem 15px;
            }
            .nav-links {
                margin-top: 1rem;
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem;
            }
            .dropdown {
                margin-top: 1rem;
            }
            .dropdown-menu {
                left: 50%;
                transform: translateX(-50%);
            }

            .campaign-detail-container {
                margin: 20px auto;
                padding: 0;
            }

            .campaign-header-banner {
                height: 250px;
            }

            .campaign-header-content {
                padding: 20px;
            }

            .campaign-header-content h1 {
                font-size: 1.8em;
            }

            .campaign-header-content p {
                font-size: 1em;
            }

            .campaign-meta-info {
                flex-direction: column;
                gap: 10px;
            }

            .campaign-action-button {
                padding: 12px 20px;
                font-size: 1em;
            }

            .campaign-body-content {
                padding: 20px;
            }

            .section-title {
                font-size: 1.5em;
            }

            .modal-content {
                padding: 20px;
            }
            .modal-content h2 {
                font-size: 1.5em;
                margin-bottom: 15px;
            }
            .form-group input[type="text"],
            .form-group input[type="email"],
            .form-group input[type="tel"],
            .form-group input[type="date"],
            .form-group textarea,
            .form-group select {
                width: calc(100% - 16px);
                padding: 10px 8px;
            }
            .btn {
                padding: 10px 20px;
                font-size: 0.9em;
            }
            #dynamic-message-box {
                margin: 15px auto 0 auto; /* Sedikit lebih kecil margin di mobile */
                width: calc(100% - 30px); /* Sesuaikan lebar */
            }
        }
    </style>
</head>
<body>

    <nav>
        <div class="nav-container">
            <a href="../DonGiv.php" class="nav-logo"> <img src="../foto/1-removebg-preview (1).png" alt="DonGiv-Logo" /> <span>DonGiv</span>
            </a>

            <div class="nav-links">
                <a href="../users/DonGiv.php#Home">Home</a>
                 <a href="../users/DonGiv.php#Donations">Donations</a>
                <a href="../users/DonGiv.php#Volunter">Volunteer</a>
                 <a href="../users/DonGiv.php#About">About</a>
                <a href="../users/DonGiv.php#Contact">Contact</a>

                <div class="dropdown">
                    <img src="../foto/user.png" alt="User" id="dropdown-btn" /> <div class="dropdown-menu">
                        <div class="dropdown-user-info">
                            <p class="font-semibold"><?= htmlspecialchars($username ?? 'Guest') ?></p>
                            <p class="text-sm"><?= htmlspecialchars($data['email'] ?? 'guest@example.com') ?></p>
                        </div>
                        <a href="../prof.php">Profile</a> <a href="../setting.php">Settings</a> <a href="../auth/logout.php">Logout</a> </div>
                </div>
            </div>
        </div>
    </nav>

    <div id="dynamic-message-box" style="display: none;"></div>

    <div class="campaign-detail-container">
        <a href="program_relawan.php" class="back-button">
            <svg viewBox="0 0 24 24"><path d="M15.41 16.59L10.83 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
            Kembali ke Daftar Aktivitas
        </a>

        <section class="campaign-header-banner">
            <img src="<?php echo htmlspecialchars($program_detail['image_url'] ?? 'https://placehold.co/1000x350/B2EBF2/00BCD4?text=Gambar+Tidak+Tersedia'); ?>"
                 alt="<?php echo htmlspecialchars($program_detail['program_name'] ?? 'Program Tidak Ditemukan'); ?>"
                 onerror="this.onerror=null;this.src='https://placehold.co/1000x350/B2EBF2/00BCD4?text=Gambar+Tidak+Tersedia';">
            <div class="campaign-header-content">
                <h1><?php echo htmlspecialchars($program_detail['program_name'] ?? 'Nama Program Tidak Tersedia'); ?></h1>
                <p><?php echo htmlspecialchars($program_detail['description'] ?? 'Deskripsi tidak tersedia.'); ?></p>
                <div class="campaign-meta-info">
                    <div class="meta-item">
                        <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
                        <span><?php echo htmlspecialchars(date('d M Y', strtotime($program_detail['start_date'] ?? 'now'))); ?> - <?php echo htmlspecialchars(date('d M Y', strtotime($program_detail['end_date'] ?? 'now'))); ?></span>
                    </div>
                    <div class="meta-item">
                        <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                        <span><?php echo htmlspecialchars($program_detail['location'] ?? 'Lokasi Tidak Tersedia'); ?></span>
                    </div>
                    <div class="meta-item">
                        <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        <span>Relawan Dibutuhkan: <?php echo htmlspecialchars($program_detail['volunteers_needed'] ?? 0); ?> | Terdaftar: <?php echo htmlspecialchars($program_detail['registered_volunteers_count'] ?? 0); ?></span>
                    </div>
                </div>
                <a id="open-registration-modal" class="campaign-action-button">
                    Daftar Sekarang
                    <svg viewBox="0 0 24 24"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
                </a>
            </div>
        </section>

        <section class="campaign-body-content">
            <h2 class="section-title">Tentang Kampanye Ini</h2>
            <p><?php echo htmlspecialchars($program_detail['description'] ?? 'Tidak ada deskripsi rinci untuk program ini.'); ?></p>

            <h2 class="section-title">Tugas Relawan</h2>
            <ul>
                <li>Membantu distribusi logistik (makanan, air bersih, pakaian) kepada korban banjir.</li>
                <li>Membersihkan rumah dan fasilitas umum yang terdampak lumpur dan sampah.</li>
                <li>Mendirikan dan mengelola posko pengungsian.</li>
                <li>Memberikan dukungan psikososial dan trauma healing, terutama untuk anak-anak.</li>
                <li>Melakukan pendataan kebutuhan warga.</li>
                <li>Prioritas bagi yang memiliki keterampilan medis, logistik, atau pengalaman penanganan bencana.</li>
            </ul>

            <h2 class="section-title">Persyaratan Relawan</h2>
            <ul>
                <li>Berusia minimal 18 tahun.</li>
                <li>Memiliki kondisi fisik yang sehat dan prima.</li>
                <li>Bersedia bekerja dalam tim dan mengikuti instruksi koordinator.</li>
                <li>Mampu beradaptasi dengan kondisi lapangan yang dinamis.</li>
                <li>Memiliki empati dan kepedulian terhadap sesama.</li>
                <li>Prioritas bagi yang memiliki keterampilan medis, logistik, atau pengalaman penanganan bencana.</li>
            </ul>

            <h2 class="section-title">Informasi Kontak</h2>
            <div class="contact-person">
                <h3>Koordinator Kampanye</h3>
                <p>Nama: <?php echo htmlspecialchars($program_detail['coordinator_name'] ?? 'Tidak Tersedia'); ?></p>
                <p>Email: <?php echo htmlspecialchars($program_detail['coordinator_email'] ?? 'Tidak Tersedia'); ?></p>
                <p>Telepon: <?php echo htmlspecialchars($program_detail['coordinator_phone'] ?? 'Tidak Tersedia'); ?></p>
            </div>
        </section>
    </div>

    <footer class="footer">
        &copy; 2025 DonGiv. All rights reserved.
    </footer>

    <div id="registration-modal" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close-button">&times;</span>
            <h2>Formulir Pendaftaran Relawan</h2>
            <form method="POST" action="detail_relawan.php?id=<?php echo htmlspecialchars($program_id); ?>">
                <div class="form-group">
                    <label for="regFullName">Nama Lengkap</label>
                    <input type="text" id="regFullName" name="regFullName" placeholder="Masukkan nama lengkap Anda" required>
                </div>
                <div class="form-group">
                    <label for="regEmail">Email</label>
                    <input type="email" id="regEmail" name="regEmail" placeholder="Masukkan alamat email Anda" required>
                </div>
                <div class="form-group">
                    <label for="regPhone">Telepon</label>
                    <input type="tel" id="regPhone" name="regPhone" placeholder="Masukkan nomor telepon Anda">
                </div>
                <div class="form-group">
                    <label for="regDob">Tanggal Lahir</label>
                    <input type="date" id="regDob" name="regDob">
                </div>
                <div class="form-group">
                    <label for="regAddress">Alamat Lengkap</label>
                    <textarea id="regAddress" name="regAddress" placeholder="Masukkan alamat lengkap Anda"></textarea>
                </div>
                <div class="form-group">
                    <label for="regInterest">Minat Utama</label>
                    <select id="regInterest" name="regInterest">
                        <option value="">Pilih minat</option>
                        <option value="Lingkungan">Lingkungan</option>
                        <option value="Pendidikan">Pendidikan</option>
                        <option value="Kesehatan">Kesehatan</option>
                        <option value="Sosial & Kemanusiaan">Sosial & Kemanusiaan</option>
                        <option value="Seni & Budaya">Seni & Budaya</option>
                        <option value="Teknologi">Teknologi</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="regSkills">Keterampilan (Pisahkan dengan koma)</label>
                    <textarea id="regSkills" name="regSkills" placeholder="Contoh: Desain Grafis, Public Speaking, Penulisan"></textarea>
                </div>
                <div class="form-group">
                    <label for="regEmergencyContactName">Nama Kontak Darurat</label>
                    <input type="text" id="regEmergencyContactName" name="regEmergencyContactName" placeholder="Nama kontak darurat">
                </div>
                <div class="form-group">
                    <label for="regEmergencyContactPhone">Telepon Kontak Darurat</label>
                    <input type="tel" id="regEmergencyContactPhone" name="regEmergencyContactPhone" placeholder="Nomor telepon kontak darurat">
                </div>
                <div class="button-group">
                    <button type="button" class="btn btn-secondary" id="cancel-registration">Batal</button>
                    <button type="submit" class="btn btn-primary">Daftar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dropdownBtn = document.getElementById('dropdown-btn');
            const dropdown = document.querySelector('.dropdown');

            if (dropdownBtn && dropdown) {
                dropdownBtn.addEventListener('click', () => {
                    dropdown.classList.toggle('active');
                });

                document.addEventListener('click', (event) => {
                    if (!dropdown.contains(event.target) && !dropdownBtn.contains(event.target)) {
                        dropdown.classList.remove('active');
                    }
                });
            }

            const openModalBtn = document.getElementById('open-registration-modal');
            const registrationModal = document.getElementById('registration-modal');
            const closeModalBtn = document.querySelector('#registration-modal .modal-close-button');
            const cancelRegBtn = document.getElementById('cancel-registration');

            if (openModalBtn) {
                openModalBtn.addEventListener('click', () => {
                    registrationModal.style.display = 'flex';
                    document.body.style.overflow = 'hidden'; // Prevent scrolling background
                });
            }

            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', () => {
                    registrationModal.style.display = 'none';
                    document.body.style.overflow = ''; // Restore scrolling
                });
            }

            if (cancelRegBtn) {
                cancelRegBtn.addEventListener('click', () => {
                    registrationModal.style.display = 'none';
                    document.body.style.overflow = ''; // Restore scrolling
                });
            }

            if (registrationModal) {
                registrationModal.addEventListener('click', (event) => {
                    if (event.target === registrationModal) {
                        registrationModal.style.display = 'none';
                        document.body.style.overflow = ''; // Restore scrolling
                    }
                });
            }

            const registrationForm = document.querySelector('#registration-modal form');
            if (registrationForm) {
                registrationForm.addEventListener('submit', async (event) => {
                    event.preventDefault(); // Prevent default form submission

                    const formData = new FormData(registrationForm);

                    try {
                        const response = await fetch(registrationForm.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json' // Request JSON response
                            }
                        });

                        // Check if the response is OK (status 200)
                        if (!response.ok) {
                            // If response is not OK, try to parse JSON error or throw generic error
                            try {
                                const errorData = await response.json();
                                throw new Error(errorData.message || 'Server error occurred.');
                            } catch (e) {
                                throw new Error('Network response was not ok, and could not parse error message.');
                            }
                        }

                        const result = await response.json(); // Parse the JSON response directly

                        displayMessageBox(result.message, result.type);

                        if (result.success) {
                            registrationForm.reset(); // Clear the form on success
                            registrationModal.style.display = 'none';
                            document.body.style.overflow = ''; // Restore scrolling
                        }

                    } catch (error) {
                        console.error('Error during form submission:', error);
                        displayMessageBox('Terjadi kesalahan saat mengirim pendaftaran: ' + error.message, 'error');
                    }
                });
            }

            // Function to display message box with proper styling and behavior
            function displayMessageBox(msg, type) {
                let messageBox = document.getElementById('dynamic-message-box');
                if (!messageBox) {
                    messageBox = document.createElement('div');
                    messageBox.id = 'dynamic-message-box';
                    document.body.appendChild(messageBox);
                }

                // Remove existing type classes
                messageBox.classList.remove('success', 'error', 'info');
                // Add the new type class
                messageBox.classList.add(type);

                let iconHtml = '';
                if (type === 'success') {
                    iconHtml = '<i class="fas fa-check-circle"></i>';
                } else if (type === 'error') {
                    iconHtml = '<i class="fas fa-times-circle"></i>';
                } else {
                    iconHtml = '<i class="fas fa-info-circle"></i>';
                }

                messageBox.innerHTML = iconHtml + ' ' + msg; // Add a space for better separation

                // Show the message box
                messageBox.style.display = 'flex';
                messageBox.classList.add('show');

                // Set a timeout to hide the message box
                setTimeout(() => {
                    messageBox.classList.remove('show');
                    // Hide completely after the transition
                    setTimeout(() => {
                        messageBox.style.display = 'none';
                    }, 500); // This matches the CSS transition duration
                }, 5000); // Display for 5 seconds
            }

            // Display message box if message exists (from PHP GET redirect on initial load only)
            const urlParams = new URLSearchParams(window.location.search);
            const msgFromUrl = urlParams.get('message');
            const typeFromUrl = urlParams.get('type');

            if (msgFromUrl && typeFromUrl) {
                displayMessageBox(msgFromUrl, typeFromUrl);
                // Clean URL parameters after displaying message
                // This prevents the message from reappearing on page refresh
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({ path: newUrl }, '', newUrl);
            }
        });
    </script>
</body>
</html>