<?php
session_start();
include './koneksi.php'; // pastikan path ini sesuai

// Ambil ID user dari session
$id = $_SESSION['user_id'] ?? null;

if (!$id) {
    die("User belum login.");
}

// Query user berdasarkan ID
$query = mysqli_query($conn, "SELECT * FROM users WHERE id='$id' LIMIT 1");

// Cek hasil query
if (!$query || mysqli_num_rows($query) === 0) {
    die("User tidak ditemukan.");
}

// Ambil data
$data = mysqli_fetch_assoc($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Form</title>
    <link rel="stylesheet" href="editUser.css">
</head>
<body>
    <div class="profile-form">
        <h2>Profile Saya</h2>
        <div class="profile-picture">
            <img src="../uploads/<?= htmlspecialchars($data['foto'] ?? 'default.png') ?>" alt="Profile Picture" width="150">
            <form action="upload_foto.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="foto" accept="image/*" required>
                <button type="submit">Upload</button>
            </form>
        </div>
        <form action="proses_update_user.php" method="POST">
    <input type="hidden" name="id" value="<?= htmlspecialchars($data['id'] ?? '') ?>">

    <div class="form-group">
        <label for="nickname">Nama</label>
        <input type="text" id="nickname" name="name" value="<?= htmlspecialchars($data['name'] ?? '') ?>" required>
    </div>

    <div class="form-group">
        <label for="birthdate">Tanggal Lahir</label>
        <input type="date" id="birthdate" name="tanggal_lahir" value="<?= htmlspecialchars($data['tanggal_lahir'] ?? '') ?>" required>
    </div>

    <div class="form-group">
        <label for="phone">Nomor Handphone</label>
        <input type="tel" id="phone" name="no_hp" value="<?= htmlspecialchars($data['no_hp'] ?? '') ?>" required>
    </div>

    <div class="form-actions">
        <a href="prof.php" class="cancel-button">Cancel</a>
        <button type="submit" class="save-button">Save</button>
    </div>
</form>

    </div>
</body>
</html>
