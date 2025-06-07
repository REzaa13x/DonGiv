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

// Direktori tempat menyimpan gambar
// PASTIKAN FOLDER INI ADA DAN DAPAT DITULIS OLEH SERVER WEB ANDA (izin 0777 atau 0755)
// Contoh: folder 'uploads' ada di luar folder 'admin', sejajar dengan 'users'
$upload_dir = '../uploads/program_images/'; 
// Pastikan path ini juga yang diakses oleh browser. Misal: http://localhost/DONGIV%2052/uploads/program_images/
$base_url_for_images = '../uploads/program_images/'; // Path yang akan disimpan di DB dan diakses oleh browser


if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true); // Buat direktori jika belum ada dengan izin penuh
}

// --- Handle Form Submissions (Tambah/Edit Program) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $program_name = $_POST['program_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $location = $_POST['location'] ?? '';
    $volunteers_needed = $_POST['volunteers_needed'] ?? 0;
    $status = $_POST['status'] ?? 'draft';
    $coordinator_id = $_POST['coordinator_id'] ?? null;
    $category_id = $_POST['category_id'] ?? null;
    $current_image_url_db = $_POST['current_image_url'] ?? null; // URL gambar yang saat ini tersimpan di DB

    // Konversi string kosong menjadi NULL untuk tanggal dan FK ID
    if ($start_date === '') $start_date = null;
    if ($end_date === '') $end_date = null;
    if ($coordinator_id === '') $coordinator_id = null; // Ini penting untuk binding sebagai NULL
    if ($category_id === '') $category_id = null; // Ini penting untuk binding sebagai NULL

    // Pastikan volunteers_needed adalah integer
    $volunteers_needed = intval($volunteers_needed);

    $image_url_to_save = $current_image_url_db; // Default ke URL gambar yang sudah ada di DB

    // Handle image upload
    if (isset($_FILES['program_image']) && $_FILES['program_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['program_image']['tmp_name'];
        $file_name = $_FILES['program_image']['name']; // Ambil nama asli untuk ekstensi
        $file_size = $_FILES['program_image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $max_file_size = 2 * 1024 * 1024; // 2MB

        if (!in_array($file_ext, $allowed_ext)) {
            $message = "Format gambar tidak didukung. Hanya JPG, JPEG, PNG, GIF yang diizinkan.";
            $message_type = "error";
        } elseif ($file_size > $max_file_size) {
            $message = "Ukuran gambar terlalu besar. Maksimal 2MB.";
            $message_type = "error";
        } else {
            $new_file_name = uniqid('program_') . '.' . $file_ext;
            $destination_filesystem_path = $upload_dir . $new_file_name; // Path di filesystem untuk move_uploaded_file
            $destination_db_url = $base_url_for_images . $new_file_name; // Path/URL yang akan disimpan di DB

            if (move_uploaded_file($file_tmp_name, $destination_filesystem_path)) {
                $image_url_to_save = $destination_db_url; // Simpan URL yang dapat diakses browser di DB
                
                // Hapus gambar lama jika ada dan berbeda dengan yang baru di filesystem
                if ($current_image_url_db && $current_image_url_db !== $image_url_to_save) {
                    $old_image_filesystem_path = str_replace($base_url_for_images, $upload_dir, $current_image_url_db);
                    if (file_exists($old_image_filesystem_path) && is_file($old_image_filesystem_path)) {
                        unlink($old_image_filesystem_path);
                    }
                }
            } else {
                $message = "Gagal mengunggah gambar. Pastikan folder '$upload_dir' memiliki izin tulis.";
                $message_type = "error";
            }
        }
    } 
    // Jika tidak ada file baru diupload DAN ada file lama yang dihapus dari form, set image_url_to_save jadi NULL
    elseif (isset($_POST['remove_current_image']) && $_POST['remove_current_image'] === '1') {
        if ($current_image_url_db) {
            $old_image_filesystem_path = str_replace($base_url_for_images, $upload_dir, $current_image_url_db);
            if (file_exists($old_image_filesystem_path) && is_file($old_image_filesystem_path)) {
                unlink($old_image_filesystem_path);
            }
        }
        $image_url_to_save = null; // Set ke NULL jika gambar dihapus
    }


    // Hanya lanjutkan proses simpan/update jika tidak ada error upload
    if (empty($message_type) || $message_type === 'success') {
        if ($id) {
            // Update existing program
            $stmt = $conn->prepare("UPDATE programs SET program_name=?, description=?, start_date=?, end_date=?, location=?, volunteers_needed=?, status=?, coordinator_id=?, category_id=?, image_url=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
            // String tipe untuk 11 parameter + 1 id = 12 parameter.
            // s:program_name, s:description, s:start_date, s:end_date, s:location, i:volunteers_needed, s:status, s:coordinator_id (bisa NULL), s:category_id (bisa NULL), s:image_url_to_save, i:id
            $stmt->bind_param("sssssissssi", // Diperbaiki: 'ii' menjadi 'ss' untuk coordinator_id dan category_id
                $program_name, $description, $start_date, $end_date, $location, 
                $volunteers_needed, $status, $coordinator_id, $category_id, 
                $image_url_to_save, $id
            );
            if ($stmt->execute()) {
                $message = "Data program berhasil diperbarui!";
                $message_type = "success";
            } else {
                $message = "Error Update: " . $stmt->error;
                $message_type = "error";
            }
            $stmt->close();
        } else {
            // Add new program
            $stmt = $conn->prepare("INSERT INTO programs (program_name, description, start_date, end_date, location, volunteers_needed, status, coordinator_id, category_id, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            // String tipe untuk 10 parameter.
            // s:program_name, s:description, s:start_date, s:end_date, s:location, i:volunteers_needed, s:status, s:coordinator_id (bisa NULL), s:category_id (bisa NULL), s:image_url_to_save
            $stmt->bind_param("sssssissss", // Diperbaiki: 'ii' menjadi 'ss' untuk coordinator_id dan category_id
                $program_name, $description, $start_date, $end_date, $location, 
                $volunteers_needed, $status, $coordinator_id, $category_id, 
                $image_url_to_save
            );
            if ($stmt->execute()) {
                $message = "Program baru berhasil ditambahkan!";
                $message_type = "success";
            } else {
                $message = "Error Insert: " . $stmt->error;
                $message_type = "error";
            }
            $stmt->close();
        }
    }
    // Redirect setelah POST untuk mencegah re-submission
    header("Location: ManajemenProgram.php?message=" . urlencode($message) . "&type=" . urlencode($message_type));
    exit();
}

// --- Handle Delete Action ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_to_delete = $_GET['id'];

    // Ambil URL gambar sebelum menghapus record
    $stmt_get_image = $conn->prepare("SELECT image_url FROM programs WHERE id = ?");
    $stmt_get_image->bind_param("i", $id_to_delete);
    $stmt_get_image->execute();
    $result_get_image = $stmt_get_image->get_result();
    $row_image = $result_get_image->fetch_assoc();
    $image_to_delete_url = $row_image['image_url'] ?? null;
    $stmt_get_image->close();

    $stmt = $conn->prepare("DELETE FROM programs WHERE id = ?");
    $stmt->bind_param("i", $id_to_delete);
    if ($stmt->execute()) {
        // Hapus file gambar fisik setelah record dihapus dari database
        if ($image_to_delete_url) {
            // Pastikan path filesystem yang benar untuk unlink
            $image_filesystem_path = str_replace($base_url_for_images, $upload_dir, $image_to_delete_url);
            if (file_exists($image_filesystem_path) && is_file($image_filesystem_path)) {
                unlink($image_filesystem_path);
            }
        }
        $message = "Program berhasil dihapus!";
        $message_type = "success";
    } else {
        $message = "Error menghapus program: " . $stmt->error;
        $message_type = "error";
    }
    $stmt->close();
    // Redirect setelah DELETE untuk mencegah re-submission
    header("Location: ManajemenProgram.php?message=" . urlencode($message) . "&type=" . urlencode($message_type));
    exit();
}

// Check for messages from redirect (e.g., after an action)
if (isset($_GET['message']) && isset($_GET['type'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['type']);
}


// --- Fetch Program Data for Table Display ---
$search_query = $_GET['search'] ?? '';
$sql_programs = "
    SELECT 
        p.*, 
        c.category_name, 
        u.name AS coordinator_username,
        (SELECT COUNT(pr.id) FROM program_registrations pr WHERE pr.program_id = p.id AND pr.status = 'approved') AS registered_volunteers
    FROM programs p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN users u ON p.coordinator_id = u.id
";
$where_clauses = [];
$params = [];
$param_types = "";

if (!empty($search_query)) {
    $where_clauses[] = "(p.program_name LIKE ? OR p.location LIKE ? OR c.category_name LIKE ? OR u.name LIKE ?)";
    $param = '%' . $search_query . '%';
    $params[] = $param;
    $params[] = $param;
    $params[] = $param;
    $params[] = $param;
    $param_types .= "ssss";
}

if (!empty($where_clauses)) {
    $sql_programs .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql_programs .= " ORDER BY p.created_at DESC";

$stmt_programs = $conn->prepare($sql_programs);
if (!empty($params)) {
    $stmt_programs->bind_param($param_types, ...$params);
}
$stmt_programs->execute();
$result_programs = $stmt_programs->get_result();

// --- Fetch Data for Edit Form (if 'edit' action) ---
$program_to_edit = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id_to_edit = $_GET['id'];
    $stmt_edit = $conn->prepare("SELECT * FROM programs WHERE id = ?");
    $stmt_edit->bind_param("i", $id_to_edit);
    $stmt_edit->execute();
    $result_edit = $stmt_edit->get_result();
    if ($result_edit->num_rows > 0) {
        $program_to_edit = $result_edit->fetch_assoc();
    }
    $stmt_edit->close();
}

// --- Fetch Categories for Dropdown ---
$sql_categories = "SELECT id, category_name FROM categories ORDER BY category_name ASC";
$result_categories = $conn->query($sql_categories);
$categories_options = [];
if ($result_categories->num_rows > 0) {
    while($row = $result_categories->fetch_assoc()) {
        $categories_options[] = $row;
    }
}

// --- Fetch Coordinators (users with role 'admin' atau 'coordinator' jika ada) for Dropdown ---
// Mengambil semua user dengan role admin atau coordinator.
$sql_coordinators = "SELECT id, name FROM users WHERE role IN ('admin', 'coordinator') ORDER BY name ASC"; 
$result_coordinators = $conn->query($sql_coordinators);
$coordinators_options = [];
if ($result_coordinators->num_rows > 0) {
    while($row = $result_coordinators->fetch_assoc()) {
        $coordinators_options[] = $row;
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
    <title>Manajemen Program</title>
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
        /* Status khusus untuk program */
        .status.draft { background-color: #DCEDC8; color: #8BC34A; } /* Lighter Green for draft */
        .status.cancelled { background-color: #FFCDD2; color: #E53935; } /* Lighter Red for cancelled */


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

        .actions-buttons button.edit:hover svg, .actions-buttons a.edit:hover svg { fill: #00BCD4; }
        .actions-buttons button.delete:hover svg, .actions-buttons a.delete:hover svg { fill: #F44336; }
        .actions-buttons button.view:hover svg, .actions-buttons a.view:hover svg { fill: #2196F3; }

        /* Form Section styles */
        .section-form {
            background-color: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: none; /* Hidden by default */
        }

        .section-form h2 {
            font-size: 1.8em;
            color: #212121;
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #212121;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="date"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select,
        .form-group input[type="file"] { /* Tambahkan input file */
            width: calc(100% - 20px);
            padding: 12px 10px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            color: #424242;
            transition: border-color 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="email"]:focus,
        .form-group input[type="tel"]:focus,
        .form-group input[type="date"]:focus,
        .form-group input[type="number"]:focus,
        .form-group textarea:focus,
        .form-group select:focus,
        .form-group input[type="file"]:focus {
            outline: none;
            border-color: #00BCD4;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .image-preview-container {
            margin-top: 10px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 150px;
            background-color: #f9fafb;
        }
        .image-preview {
            max-width: 100%;
            max-height: 150px;
            border-radius: 5px;
            object-fit: contain;
        }
        .remove-image-checkbox {
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9em;
            color: #424242;
        }
        .remove-image-checkbox input[type="checkbox"] {
            width: auto; /* Override 100% width from form-group inputs */
            margin-right: 5px;
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
            background-color: #4CAF50;
            color: white;
        }

        .btn-primary:hover {
            background-color: #388E3C;
        }

        .btn-secondary {
            background-color: #f0f2f5;
            color: #212121;
            border: 1px solid #e0e0e0;
        }

        .btn-secondary:hover {
            background-color: #e0e0e0;
        }

        .btn-add {
            background-color: #4CAF50;
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
            background-color: #4CAF50;
        }

        .message-box.error {
            background-color: #F44336;
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
            .image-preview-container {
                min-height: 100px;
            }
            .image-preview {
                max-height: 100px;
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
                    <a href="ManajemenProgram.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md active">
                        Manajemen Program
                    </a>
                    <a href="ManajemenPendaftaranProgram.php" class="block py-2 px-6 hover:bg-blue-900 rounded-md">
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
            <h1>Manajemen Program</h1>
        </header>

        <?php if (!empty($message)): ?>
            <div class="message-box <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Section: Daftar Program -->
        <section class="section-table" id="daftar-program" style="<?php echo ($program_to_edit || (isset($_GET['action']) && $_GET['action'] === 'add')) ? 'display: none;' : 'display: block;'; ?>">
            <div class="table-controls">
                <h2>Daftar Program</h2>
                <form method="GET" action="ManajemenProgram.php" class="search-box">
                    <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                    <input type="text" name="search" placeholder="Cari program..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" style="display: none;"></button>
                </form>
                <button class="btn btn-add" onclick="showForm('add-edit-program-form', 'Tambah Program Baru')">
                    <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                    Tambah Program
                </button>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Gambar</th>
                            <th>Nama Program</th>
                            <th>Tanggal</th>
                            <th>Lokasi</th>
                            <th>Dibutuhkan</th>
                            <th>Terdaftar</th>
                            <th>Status</th>
                            <th>Koordinator</th>
                            <th>Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_programs->num_rows > 0): ?>
                            <?php while($row = $result_programs->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td>
                                        <?php 
                                        $image_src = !empty($row['image_url']) ? htmlspecialchars($row['image_url']) : 'https://placehold.co/50x50/E0F7FA/00BCD4?text=No+Img';
                                        ?>
                                        <img src="<?php echo $image_src; ?>" alt="Program Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    </td>
                                    <td><?php echo htmlspecialchars($row['program_name']); ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['start_date']))); ?> - <?php echo htmlspecialchars(date('d M Y', strtotime($row['end_date']))); ?></td>
                                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                                    <td><?php echo htmlspecialchars($row['volunteers_needed']); ?></td>
                                    <td><?php echo htmlspecialchars($row['registered_volunteers']); ?></td>
                                    <td><span class="status <?php echo htmlspecialchars($row['status']); ?>"><?php echo htmlspecialchars(ucfirst($row['status'])); ?></span></td>
                                    <td><?php echo htmlspecialchars($row['coordinator_username'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['category_name'] ?? 'N/A'); ?></td>
                                    <td class="actions-buttons">
                                        <a href="?action=edit&id=<?php echo $row['id']; ?>" class="edit" title="Edit">
                                            <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                        </a>
                                        <button class="delete" title="Hapus" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['program_name']); ?>')">
                                            <svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="py-2 px-4 text-center text-gray-500">Tidak ada data program untuk ditampilkan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Section: Tambah/Edit Program Form -->
        <section class="section-form" id="add-edit-program-form" style="<?php echo ($program_to_edit || (isset($_GET['action']) && $_GET['action'] === 'add')) ? 'display: block;' : 'display: none;'; ?>">
            <h2><?php echo ($program_to_edit) ? 'Edit Program' : 'Tambah Program Baru'; ?></h2>
            <form method="POST" action="ManajemenProgram.php" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($program_to_edit['id'] ?? ''); ?>">
                <input type="hidden" name="current_image_url" value="<?php echo htmlspecialchars($program_to_edit['image_url'] ?? ''); ?>">

                <div class="form-group">
                    <label for="program_name">Nama Program</label>
                    <input type="text" id="program_name" name="program_name" placeholder="Masukkan nama program" value="<?php echo htmlspecialchars($program_to_edit['program_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Deskripsi Program</label>
                    <textarea id="description" name="description" placeholder="Jelaskan tujuan dan aktivitas program"><?php echo htmlspecialchars($program_to_edit['description'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="program_image">Gambar Program</label>
                    <input type="file" id="program_image" name="program_image" accept="image/jpeg,image/png,image/gif">
                    <p class="text-sm text-gray-500 mt-1">Format: JPG, JPEG, PNG, GIF. Ukuran maksimal: 2MB.</p>
                    <div class="image-preview-container" id="image-preview-container">
                        <?php 
                        $preview_image_src = !empty($program_to_edit['image_url']) ? htmlspecialchars($program_to_edit['image_url']) : 'https://placehold.co/150x150/E0F7FA/00BCD4?text=No+Image';
                        ?>
                        <img src="<?php echo $preview_image_src; ?>" alt="Current Image" class="image-preview" id="current-image-preview">
                    </div>
                    <?php if (!empty($program_to_edit['image_url'])): ?>
                        <label class="remove-image-checkbox">
                            <input type="checkbox" name="remove_current_image" value="1"> Hapus gambar yang ada
                        </label>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="start_date">Tanggal Mulai</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($program_to_edit['start_date'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="end_date">Tanggal Selesai</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($program_to_edit['end_date'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="location">Lokasi</label>
                    <input type="text" id="location" name="location" placeholder="Contoh: Balai Kota, Online, dll." value="<?php echo htmlspecialchars($program_to_edit['location'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="volunteers_needed">Relawan Dibutuhkan</label>
                    <input type="number" id="volunteers_needed" name="volunteers_needed" min="0" value="<?php echo htmlspecialchars($program_to_edit['volunteers_needed'] ?? 0); ?>">
                </div>
                <div class="form-group">
                    <label for="status">Status Program</label>
                    <select id="status" name="status">
                        <option value="draft" <?php echo (($program_to_edit['status'] ?? '') === 'draft') ? 'selected' : ''; ?>>Draf</option>
                        <option value="active" <?php echo (($program_to_edit['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Aktif</option>
                        <option value="completed" <?php echo (($program_to_edit['status'] ?? '') === 'completed') ? 'selected' : ''; ?>>Selesai</option>
                        <option value="cancelled" <?php echo (($program_to_edit['status'] ?? '') === 'cancelled') ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="coordinator_id">Koordinator Program</label>
                    <select id="coordinator_id" name="coordinator_id">
                        <option value="">Pilih Koordinator</option>
                        <?php foreach ($coordinators_options as $coordinator): ?>
                            <option value="<?php echo htmlspecialchars($coordinator['id']); ?>"
                                <?php echo (($program_to_edit['coordinator_id'] ?? '') == $coordinator['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($coordinator['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="category_id">Kategori Program</label>
                    <select id="category_id" name="category_id">
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($categories_options as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['id']); ?>"
                                <?php echo (($program_to_edit['category_id'] ?? '') == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="button-group">
                    <button type="button" class="btn btn-secondary" onclick="hideForm()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Program</button>
                </div>
            </form>
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
                submenu.classList.remove('active');
                chevron.classList.remove('rotate-180');
            } else {
                submenu.classList.add('active');
                chevron.classList.add('rotate-180');
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

        // Fungsi untuk menampilkan form tambah/edit program
        function showForm(formId, title) {
            document.getElementById('daftar-program').style.display = 'none';
            document.getElementById(formId).style.display = 'block';
            document.querySelector('.header h1').textContent = title;

            // Clear form if it's a new entry
            if (title === 'Tambah Program Baru') {
                document.querySelector('#' + formId + ' form').reset();
                document.querySelector('#' + formId + ' input[name="id"]').value = '';
                // Reset image preview to placeholder
                document.getElementById('image-preview-container').innerHTML = `<img src="https://placehold.co/150x150/E0F7FA/00BCD4?text=No+Image" alt="No Image" class="image-preview" id="current-image-preview">`;
            }
        }

        // Fungsi untuk menyembunyikan form dan kembali ke daftar
        function hideForm() {
            document.getElementById('daftar-program').style.display = 'block';
            document.getElementById('add-edit-program-form').style.display = 'none';
            document.querySelector('.header h1').textContent = 'Manajemen Program';
            window.history.pushState({}, document.title, "ManajemenProgram.php"); // Clean URL
        }

        // Fungsi konfirmasi hapus
        function confirmDelete(id, name) {
            if (confirm('Apakah Anda yakin ingin menghapus program "' + name + '"? Tindakan ini tidak dapat dibatalkan.')) {
                window.location.href = 'ManajemenProgram.php?action=delete&id=' + id;
            }
        }

        // JavaScript untuk preview gambar dan pesan notifikasi
        document.addEventListener("DOMContentLoaded", function () {
            const programImageInput = document.getElementById('program_image');
            const imagePreviewContainer = document.getElementById('image-preview-container');
            
            // Initial check for a hidden element that stores the original image URL when editing
            const hiddenCurrentImageUrlInput = document.querySelector('input[name="current_image_url"]');
            let initialImageSrc = hiddenCurrentImageUrlInput ? hiddenCurrentImageUrlInput.value : '';

            // Set initial preview based on current image or placeholder
            if (imagePreviewContainer) {
                if (initialImageSrc) {
                    imagePreviewContainer.innerHTML = `<img src="${initialImageSrc}" alt="Current Image" class="image-preview">`;
                } else {
                    imagePreviewContainer.innerHTML = `<img src="https://placehold.co/150x150/E0F7FA/00BCD4?text=No+Image" alt="No Image" class="image-preview">`;
                }
            }


            if (programImageInput && imagePreviewContainer) {
                programImageInput.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imagePreviewContainer.innerHTML = `<img src="${e.target.result}" alt="Image Preview" class="image-preview">`;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        // If no file selected (e.g., user opens file dialog and cancels)
                        // Revert to original image or placeholder if it was there
                        if (initialImageSrc) {
                            imagePreviewContainer.innerHTML = `<img src="${initialImageSrc}" alt="Current Image" class="image-preview">`;
                        } else {
                            imagePreviewContainer.innerHTML = `<img src="https://placehold.co/150x150/E0F7FA/00BCD4?text=No+Image" alt="No Image" class="image-preview">`;
                        }
                    }
                    // Uncheck "remove current image" if a new file is selected
                    const removeCheckbox = document.querySelector('input[name="remove_current_image"]');
                    if (removeCheckbox) {
                        removeCheckbox.checked = false;
                    }
                });
            }

            // Handle "remove current image" checkbox
            const removeImageCheckbox = document.querySelector('input[name="remove_current_image"]');
            if (removeImageCheckbox) {
                removeImageCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        // Clear file input if image is to be removed
                        if (programImageInput) programImageInput.value = '';
                        imagePreviewContainer.innerHTML = `<img src="https://placehold.co/150x150/E0F7FA/00BCD4?text=Removed" alt="Image Removed" class="image-preview">`;
                    } else {
                        // If unchecked, restore original image or placeholder
                        if (initialImageSrc) {
                            imagePreviewContainer.innerHTML = `<img src="${initialImageSrc}" alt="Current Image" class="image-preview">`;
                        } else {
                            imagePreviewContainer.innerHTML = `<img src="https://placehold.co/150x150/E0F7FA/00BCD4?text=No+Image" alt="No Image" class="image-preview">`;
                        }
                    }
                });
            }


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
                showForm('add-edit-program-form', 'Edit Program');
            } else if (urlParams.get('action') === 'add') {
                showForm('add-edit-program-form', 'Tambah Program Baru');
            }
        });
    </script>
</body>

</html>
