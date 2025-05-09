<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kirim Email</title>
    <link rel="stylesheet" href="notifikasi.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>

<!-- Sidebar langsung disalin ke sini -->
<div class="sidebar text-white w-64 py-6 px-4 fixed h-full">
    <!-- ...isi sidebar sama persis... -->
</div>

<header class="header"><h1>Kirim Email</h1></header>

<main class="main-content">
    <?php if (isset($_GET['status']) && $_GET['status'] === 'berhasil'): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 max-w-xl mx-auto">
            <strong class="font-bold">Berhasil!</strong>
            <span class="block sm:inline">Email berhasil dikirim ke <?= htmlspecialchars($_POST['recipients'] ?? 'donatur') ?>.</span>
        </div>
    <?php endif; ?>

    <form class="email-form" action="send_email.php" method="POST">
        <!-- ...form kirim email... -->
    </form>

    <section class="message-section">
        <!-- ...kotak pesan dari database... -->
    </section>
</main>

<script>
// script logout modal tetap disalin
</script>
</body>
</html>
