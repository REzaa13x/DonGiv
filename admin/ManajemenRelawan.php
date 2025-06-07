<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../users/koneksi.php'; // SESUAIKAN PATH INI JIKA PERLU!

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = ''; // Untuk pesan sukses/error
$message_type = ''; // 'success' atau 'error'

// --- Handle Form Submissions (Tambah/Edit Relawan) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $address = $_POST['address'] ?? '';
    $main_interest = $_POST['main_interest'] ?? '';
    $skills = $_POST['skills'] ?? '';
    $emergency_contact_name = $_POST['emergency_contact_name'] ?? '';
    $emergency_contact_phone = $_POST['emergency_contact_phone'] ?? '';
    $status = $_POST['status'] ?? 'pending'; // Default status

    if ($date_of_birth === '') { // Set to NULL if empty string
        $date_of_birth = null;
    }

    if ($id) {
        // Update existing volunteer
        $stmt = $conn->prepare("UPDATE volunteers SET full_name=?, email=?, phone_number=?, date_of_birth=?, address=?, main_interest=?, skills=?, emergency_contact_name=?, emergency_contact_phone=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
        $stmt->bind_param("ssssssssssi", $full_name, $email, $phone_number, $date_of_birth, $address, $main_interest, $skills, $emergency_contact_name, $emergency_contact_phone, $status, $id);
        if ($stmt->execute()) {
            $message = "Data relawan berhasil diperbarui!";
            $message_type = "success";
        } else {
            $message = "Error: " . $stmt->error;
            $message_type = "error";
        }
        $stmt->close();
    } else {
        // Add new volunteer
        $stmt = $conn->prepare("INSERT INTO volunteers (full_name, email, phone_number, date_of_birth, address, main_interest, skills, emergency_contact_name, emergency_contact_phone, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", $full_name, $email, $phone_number, $date_of_birth, $address, $main_interest, $skills, $emergency_contact_name, $emergency_contact_phone, $status);
        if ($stmt->execute()) {
            $message = "Relawan baru berhasil ditambahkan!";
            $message_type = "success";
        } else {
            $message = "Error: " . $stmt->error;
            $message_type = "error";
        }
        $stmt->close();
    }
}

// --- Handle Delete Action ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_to_delete = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM volunteers WHERE id = ?");
    $stmt->bind_param("i", $id_to_delete);
    if ($stmt->execute()) {
        $message = "Relawan berhasil dihapus!";
        $message_type = "success";
    } else {
        $message = "Error menghapus relawan: " . $stmt->error;
        $message_type = "error";
    }
    $stmt->close();
}

// --- Fetch Volunteer Data for Table Display ---
$search_query = $_GET['search'] ?? '';
$sql_volunteers = "SELECT * FROM volunteers";
if (!empty($search_query)) {
    $sql_volunteers .= " WHERE full_name LIKE ? OR email LIKE ? OR phone_number LIKE ?";
}
$sql_volunteers .= " ORDER BY created_at DESC"; // Pastikan kolom 'created_at' ada di tabel volunteers

$stmt_volunteers = $conn->prepare($sql_volunteers);
if (!empty($search_query)) {
    $param = '%' . $search_query . '%';
    $stmt_volunteers->bind_param("sss", $param, $param, $param);
}
$stmt_volunteers->execute();
$result_volunteers = $stmt_volunteers->get_result();

// --- Fetch Data for Edit Form (if 'edit' action) ---
$volunteer_to_edit = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id_to_edit = $_GET['id'];
    $stmt_edit = $conn->prepare("SELECT * FROM volunteers WHERE id = ?");
    $stmt_edit->bind_param("i", $id_to_edit);
    $stmt_edit->execute();
    $result_edit = $stmt_edit->get_result();
    if ($result_edit->num_rows > 0) {
        $volunteer_to_edit = $result_edit->fetch_assoc();
    }
    $stmt_edit->close();
}

// --- Fetch Distinct Interests for Dropdown ---
$sql_interests = "SELECT DISTINCT main_interest FROM volunteers WHERE main_interest IS NOT NULL AND main_interest != ''";
$result_interests = $conn->query($sql_interests);
$interests_options = [];
if ($result_interests->num_rows > 0) {
    while($row = $result_interests->fetch_assoc()) {
        $interests_options[] = $row['main_interest'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <title>Manajemen Relawan</title>
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
            display: none; /* Sembunyikan modal secara default */
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
            width: 300px; /* Lebar modal */
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

        /* Section Table styles (re-used from admin dashboard) */
        .section-table {
            background-color: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* shadow-medium */
            margin-bottom: 30px;
        }

        .section-table h2 {
            font-size: 1.8em;
            color: #212121; /* text-gray-800 */
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #e0e0e0; /* border-gray-200 */
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

        .table-controls .search-box input {
            border: none;
            outline: none;
            padding: 8px;
            font-size: 1em;
            width: 200px;
        }

        .table-controls .search-box svg {
            fill: #757575; /* text-gray-600 */
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
            border-bottom: 1px solid #e0e0e0; /* border-gray-200 */
            text-align: left;
        }

        .data-table th {
            background-color: #f0f2f5; /* bg-gray-100 */
            color: #212121; /* text-gray-800 */
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr:hover {
            background-color: #f5f5f5; /* gray-50 */
        }

        .data-table .status {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85em;
            font-weight: 600;
            text-align: center;
            display: inline-block;
        }

        .status.active { background-color: #e8f5e9; color: #4CAF50; } /* Light Green */
        .status.pending { background-color: #fffde7; color: #FFC107; } /* Light Yellow */
        .status.inactive { background-color: #ffebee; color: #F44336; } /* Light Red */

        .actions-buttons button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            margin: 0 3px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .actions-buttons button svg {
            width: 18px;
            height: 18px;
            fill: #757575; /* text-gray-600 */
        }

        .actions-buttons button:hover {
            background-color: #f0f2f5; /* bg-gray-100 */
        }

        .actions-buttons button.edit:hover svg { fill: #00BCD4; } /* primary-color */
        .actions-buttons button.delete:hover svg { fill: #F44336; } /* danger-color */
        .actions-buttons button.view:hover svg { fill: #2196F3; } /* info-color */

        /* Form Section styles (re-used from admin dashboard) */
        .section-form {
            background-color: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* shadow-medium */
            margin-bottom: 30px;
            display: none; /* Hidden by default */
        }

        .section-form h2 {
            font-size: 1.8em;
            color: #212121; /* text-gray-800 */
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #e0e0e0; /* border-gray-200 */
            padding-bottom: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #212121; /* text-gray-800 */
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="date"],
        .form-group textarea,
        .form-group select {
            width: calc(100% - 20px);
            padding: 12px 10px;
            border: 1px solid #e0e0e0; /* border-gray-200 */
            border-radius: 8px;
            font-size: 1em;
            color: #424242; /* text-gray-700 */
            transition: border-color 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="email"]:focus,
        .form-group input[type="tel"]:focus,
        .form-group input[type="date"]:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #00BCD4; /* primary-color */
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
            background-color: #4CAF50; /* secondary-color */
            color: white;
        }

        .btn-primary:hover {
            background-color: #388E3C; /* Darker Green */
        }

        .btn-secondary {
            background-color: #f0f2f5; /* bg-gray-100 */
            color: #212121; /* text-gray-800 */
            border: 1px solid #e0e0e0; /* border-gray-200 */
        }

        .btn-secondary:hover {
            background-color: #e0e0e0; /* border-gray-200 */
        }

        .btn-add {
            background-color: #4CAF50; /* secondary-color */
            color: white;
            margin-bottom: 20px;
        }

        .btn-add:hover {
            background-color: #388E3C;
        }
        .btn-add svg {
            margin-right: 8px;
            fill: currentColor;
            width: 18px;
            height: 18px;
        }

        /* Message Box for success/error */
        .message-box {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 1000;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            opacity: 0;
            transform: translateY(-20px);
            animation: slideIn 0.5s forwards;
        }

        .message-box.success {
            background-color: #4CAF50; /* Green */
        }

        .message-box.error {
            background-color: #F44336; /* Red */
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

            .dashboard-cards-container {
                grid-template-columns: 1fr;
            }
            .dashboard-charts-container {
                grid-template-columns: 1fr;
            }

            .table-controls {
                flex-direction: column;
                align-items: flex-start;
            }
            .table-controls .search-box input {
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
                    <a href="ManajemenRelawan.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md active">
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
            <h1>Manajemen Relawan</h1>
        </header>

        <?php if (!empty($message)): ?>
            <div class="message-box <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Section: Daftar Relawan -->
        <section class="section-table" id="daftar-relawan" style="<?php echo ($volunteer_to_edit || (isset($_GET['action']) && $_GET['action'] === 'add')) ? 'display: none;' : 'display: block;'; ?>">
            <div class="table-controls">
                <h2>Daftar Relawan</h2>
                <form method="GET" action="ManajemenRelawan.php" class="search-box">
                    <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                    <input type="text" name="search" placeholder="Cari relawan..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" style="display: none;"></button> <!-- Hidden submit button for search -->
                </form>
                <button class="btn btn-add" onclick="showForm('add-edit-volunteer-form', 'Tambah Relawan Baru')">
                    <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                    Tambah Relawan
                </button>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Minat</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_volunteers->num_rows > 0): ?>
                            <?php while($row = $result_volunteers->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                                    <td><?php echo htmlspecialchars($row['main_interest']); ?></td>
                                    <td><span class="status <?php echo htmlspecialchars($row['status']); ?>"><?php echo htmlspecialchars(ucfirst($row['status'])); ?></span></td>
                                    <td class="actions-buttons">
                                        <a href="?action=edit&id=<?php echo $row['id']; ?>" class="edit" title="Edit">
                                            <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                        </a>
                                        <button class="delete" title="Hapus" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['full_name']); ?>')">
                                            <svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="py-2 px-4 text-center text-gray-500">Tidak ada data relawan untuk ditampilkan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Section: Tambah/Edit Relawan Form -->
        <section class="section-form" id="add-edit-volunteer-form" style="<?php echo ($volunteer_to_edit || (isset($_GET['action']) && $_GET['action'] === 'add')) ? 'display: block;' : 'display: none;'; ?>">
            <h2><?php echo ($volunteer_to_edit) ? 'Edit Relawan' : 'Tambah Relawan Baru'; ?></h2>
            <form method="POST" action="ManajemenRelawan.php">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($volunteer_to_edit['id'] ?? ''); ?>">
                <div class="form-group">
                    <label for="full_name">Nama Lengkap</label>
                    <input type="text" id="full_name" name="full_name" placeholder="Masukkan nama lengkap" value="<?php echo htmlspecialchars($volunteer_to_edit['full_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Masukkan alamat email" value="<?php echo htmlspecialchars($volunteer_to_edit['email'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone_number">Telepon</label>
                    <input type="tel" id="phone_number" name="phone_number" placeholder="Masukkan nomor telepon" value="<?php echo htmlspecialchars($volunteer_to_edit['phone_number'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="date_of_birth">Tanggal Lahir</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($volunteer_to_edit['date_of_birth'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="address">Alamat Lengkap</label>
                    <textarea id="address" name="address" placeholder="Masukkan alamat relawan"><?php echo htmlspecialchars($volunteer_to_edit['address'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="main_interest">Minat Utama</label>
                    <select id="main_interest" name="main_interest">
                        <option value="">Pilih minat</option>
                        <?php
                        $all_interests = ['Lingkungan', 'Pendidikan', 'Kesehatan', 'Sosial & Kemanusiaan', 'Seni & Budaya', 'Teknologi', 'Lainnya'];
                        foreach ($all_interests as $interest) {
                            $selected = ($volunteer_to_edit['main_interest'] ?? '') === $interest ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($interest) . '" ' . $selected . '>' . htmlspecialchars($interest) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="skills">Keterampilan (Pisahkan dengan koma)</label>
                    <textarea id="skills" name="skills" placeholder="Contoh: Desain Grafis, Public Speaking, Penulisan"><?php echo htmlspecialchars($volunteer_to_edit['skills'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="emergency_contact_name">Nama Kontak Darurat</label>
                    <input type="text" id="emergency_contact_name" name="emergency_contact_name" placeholder="Nama kontak darurat" value="<?php echo htmlspecialchars($volunteer_to_edit['emergency_contact_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="emergency_contact_phone">Telepon Kontak Darurat</label>
                    <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" placeholder="Nomor telepon kontak darurat" value="<?php echo htmlspecialchars($volunteer_to_edit['emergency_contact_phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="status">Status Relawan</label>
                    <select id="status" name="status">
                        <option value="pending" <?php echo (($volunteer_to_edit['status'] ?? '') === 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="active" <?php echo (($volunteer_to_edit['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Aktif</option>
                        <option value="inactive" <?php echo (($volunteer_to_edit['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Tidak Aktif</option>
                    </select>
                </div>
                <div class="button-group">
                    <button type="button" class="btn btn-secondary" onclick="hideForm()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Relawan</button>
                </div>
            </form>
        </section>
    </div>

    <script>
        // Fungsi untuk membuka atau menutup submenu
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
                submenu.classList.remove('active');
                chevron.classList.remove('rotate-180');
            } else {
                submenu.classList.add('active');
                chevron.classList.add('rotate-180');
            }
        }

        // Fungsi untuk membuka modal log out
        function openLogoutModal() {
            document.getElementById('logoutModal').style.display = 'flex';
        }

        // Fungsi untuk menutup modal log out
        function closeLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
        }

        // Fungsi untuk konfirmasi log out
        function confirmLogout() {
            window.location.href = '../auth/logout.php';
        }

        // Fungsi untuk menampilkan form tambah/edit relawan
        function showForm(formId, title) {
            document.getElementById('daftar-relawan').style.display = 'none';
            document.getElementById(formId).style.display = 'block';
            document.querySelector('.header h1').textContent = title;

            // Clear form if it's a new entry
            if (title === 'Tambah Relawan Baru') {
                document.querySelector('#' + formId + ' form').reset();
                document.querySelector('#' + formId + ' input[name="id"]').value = '';
            }
        }

        // Fungsi untuk menyembunyikan form dan kembali ke daftar
        function hideForm() {
            document.getElementById('daftar-relawan').style.display = 'block';
            document.getElementById('add-edit-volunteer-form').style.display = 'none';
            document.querySelector('.header h1').textContent = 'Manajemen Relawan';
            window.history.pushState({}, document.title, "ManajemenRelawan.php"); // Clean URL
        }

        // Fungsi konfirmasi hapus
        function confirmDelete(id, name) {
            if (confirm('Apakah Anda yakin ingin menghapus relawan ' + name + '?')) {
                window.location.href = 'ManajemenRelawan.php?action=delete&id=' + id;
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            // Check if there's a message to display
            const messageBox = document.querySelector('.message-box');
            if (messageBox) {
                setTimeout(() => {
                    messageBox.style.opacity = '0';
                    messageBox.style.transform = 'translateY(-20px)';
                    setTimeout(() => messageBox.remove(), 500);
                }, 3000); // Hide after 3 seconds
            }

            // Handle initial form display based on URL parameters (for edit/add)
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('action') === 'edit' && urlParams.get('id')) {
                showForm('add-edit-volunteer-form', 'Edit Relawan');
            } else if (urlParams.get('action') === 'add') {
                showForm('add-edit-volunteer-form', 'Tambah Relawan Baru');
            }
        });
    </script>
</body>

</html>
