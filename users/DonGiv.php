<?php
include './koneksi.php';
session_start();

$id = $_SESSION['user_id'];
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE id='$id' LIMIT 1");
$data = mysqli_fetch_assoc($query);
?>

<?php


// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/Login.php');
    exit();
}

// Ambil nama dan email dari session
$username = $_SESSION['user_name'] ?? 'Username';
$email = $_SESSION['email'] ?? 'name@gmail.com';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>DonGiv - Home</title>

  <!-- CSS -->
  <link rel="stylesheet" href="DonGiv.css" />
  <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

  <!-- Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet" />

  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }
    :root {
      scroll-behavior: smooth;
    }
  </style>
</head>

<body class="overflow-x-hidden">
  <!-- Navbar -->
  <nav>
    <div class="nav-container">
      <a href="DonGiv.php" class="nav-logo">
        <img src="../foto/1-removebg-preview (1).png" alt="DonGiv-Logo" />
        <span>DonGiv</span>
      </a>

      <div class="nav-links">
        <a href="#Home">Home</a>
        <a href="#Donations">Donations</a>
        <a href="#About">About</a>
        <a href="#Contact">Contact</a>

        <!-- Dropdown User -->
        <div class="dropdown">
          <img src="../foto/user.png" alt="User" id="dropdown-btn" />
          <div class="dropdown-menu">
            <div class="dropdown-user-info">
              <p class="font-semibold"><?= htmlspecialchars($username) ?></p>
              <p class="text-sm"><?=$data['email'] ?></p>
            </div>
            <a href="prof.php">Profile</a>
            <a href="setting.php">Settings</a>
            <a href="../auth/logout.php">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </nav>  

  <!-- Main Content -->
  <?php include '../partials/hero.php'; ?>
  <?php include '../partials/donations.php'; ?>
  <?php include '../partials/review.php'; ?>
  <?php include '../partials/about.php'; ?>
  <?php include '../partials/contact.php'; ?>
  <?php include '../partials/footer.php'; ?>

  <!-- JS -->
  <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
  <script src="DonGiv.js"></script>
</body>
</html>
