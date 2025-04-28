<?php
session_start();

// Kalau sudah login, langsung redirect ke DonGiv.php
if (isset($_SESSION['user_id'])) {
    header('Location: ../DonGiv.php');
    exit();
}

// Panggil file koneksi
include '../koneksi.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login & Sign Up</title>
    <link rel="stylesheet" href="Login.css">
</head>
<body>
    <div class="main-container">
        <div class="left-section">
            <img src="../foto/1login-removebg-preview.png" alt="Illustration" class="illustration">
        </div>
        <div class="right-section">
            <div class="form-container">
                <div class="tabs">
                    <button class="tab active" data-tab="login">Login</button>
                    <button class="tab" data-tab="signup">Sign Up</button>
                </div>

                <!-- Login Form -->
                <form id="login" class="form active" method="POST" action="proses_login.php">
                    <h2>Get more things done with us</h2>
                    <p>Search sourcing the world's brightest professionals for your business.</p>

                    <label for="login-email">Email Address</label>
                    <input type="email" name="email" id="login-email" placeholder="Enter your email" required>

                    <label for="login-password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="login-password" placeholder="Enter your password" required>
                        <button type="button" class="toggle-password">Show</button>
                    </div>

                    <button type="submit" class="submit-btn">Login</button>
                </form>

                <!-- Sign Up Form -->
                <form id="signup" class="form" method="POST" action="proses_signup.php">
                    <h2>Sign Up to Get Started</h2>

                    <label for="signup-name">Full Name</label>
                    <input type="text" name="name" id="signup-name" placeholder="Enter your name" required>

                    <label for="signup-email">Email Address</label>
                    <input type="email" name="email" id="signup-email" placeholder="Enter your email" required>

                    <label for="signup-password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="signup-password" placeholder="Create a password" required>
                        <button type="button" class="toggle-password">Show</button>
                    </div>

                    <button type="submit" class="submit-btn">Sign Up</button>
                </form>

            </div>
        </div>
    </div>

    <script src="Login.js"></script>
</body>
</html>
