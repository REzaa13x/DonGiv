<?php
session_start();
include './koneksi.php';

// Jika user tidak login, redirect
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/Login.php');
    exit();
}

// Proses penghapusan akun jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_account') {
    $id = $_SESSION['user_id'];

    // Hapus foto kalau bukan default.png
    $result = mysqli_query($conn, "SELECT foto FROM users WHERE id='$id'");
    $user = mysqli_fetch_assoc($result);
    $foto = $user['foto'] ?? 'default.png';

    if ($foto !== 'default.png') {
        $foto_path = __DIR__ . '/../uploads/' . $foto;
        if (file_exists($foto_path)) {
            unlink($foto_path);
        }
    }

    // Hapus user dari DB
    mysqli_query($conn, "DELETE FROM users WHERE id='$id'");
    
    // Hancurkan session
    session_destroy();

    // Redirect ke login
    header('Location: ../auth/Login.php');
    exit();
}
?>

<!-- Bagian tampilan form -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hapus Akun</title>
    <style>
        /* CSS seperti punyamu, dipersingkat di sini */
        body {
            font-family: Arial;
            background-color: #f4f7fc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            width: 500px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
        .alert {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            color: #856404;
        }
        .btn {
            background: #d63031;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Hapus Akun</h2>
    <div class="alert">
        ⚠️ Dengan menghapus akun, kamu akan kehilangan akses ke aplikasi ini secara permanen.
    </div>

    <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun? Ini tidak dapat dibatalkan.')">
        <input type="hidden" name="action" value="delete_account">
        <label for="reason">Alasan Anda:</label><br>
        <textarea name="reason" id="reason" rows="4" placeholder="(Opsional) Jelaskan alasan Anda menghapus akun..."></textarea>
        <br><br>
        <button type="submit" class="btn">Hapus Akun</button>
    </form>
</div>


    <script>
        function toggleOption(selected) {
            const emailOption = document.getElementById("email-option");
            const phoneOption = document.getElementById("phone-option");
            const emailInput = document.getElementById("email-input");
            const phoneInput = document.getElementById("phone-input");

            if (selected === "email") {
                emailOption.classList.add("active");
                phoneOption.classList.remove("active");
                emailInput.classList.remove("hidden");
                phoneInput.classList.add("hidden");
            } else if (selected === "phone") {
                phoneOption.classList.add("active");
                emailOption.classList.remove("active");
                phoneInput.classList.remove("hidden");
                emailInput.classList.add("hidden");
            }
        }
    </script>
</body>
</html>
