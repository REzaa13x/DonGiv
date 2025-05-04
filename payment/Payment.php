<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonGiv Indonesia - Donasi</title>
    <link rel="stylesheet" href="Style1.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</head>
  <body class="overflow-x-hidden">

    <nav>
        <div class="nav-container">
          <a href="#" class="nav-logo">
            <img src="1-removebg-preview (1).png" alt="DonGiv-Logo">
            <span>DonGiv</span>
          </a>
      
          <div class="nav-links">
            <a href="#">Home</a>
            <a href="#Donations">Donations</a>
            <a href="#About">About</a>
            <a href="#Contact">Contact</a>
      
            <div class="dropdown">
              <img src="user.png" alt="User" id="dropdown-btn">
              <div class="dropdown-menu">
                <div>
                  <p class="font-semibold">Username</p>
                  <p class="text-sm">name@gmail.com</p>
                </div>
                <a href="prof.html">Profile</a>
                <a href="#settings">Settings</a>
                <a href="#logout">Logout</a>
              </div>
            </div>
          </div>
        </div>
      </nav>
    <main class="main">
        <div class="content">
            <div class="image-section">
                <img src="WhatsApp Image 2024-12-04 at 21.56.45_efacde14.jpg" alt="Child and Parent">
                <div class="text-section">
                <h2>Bantu Masyarakat Indonesia</h2>
                <p>
                    "Pemberian dari anda dapat sangat berharga bagi orang lain. Bantulah sesama kita niscaya kebahagiaan
                    akan selalu ada."
                </p>
              </div>
            </div>
                <div class="donation-section">
                    <h3>BERIKAN KEBAIKAN UNTUK PERUBAHAN JANGKA PANJANG HIDUP DI INDONESIA</h3>
                    <div class="benefits">
                        <ul>
                            <ol>✅ Donasi dengan mudah, aman dan nyaman</ol>
                            <ol>✅ Menjadi bagian dalam komunitas Pendekar Anak Indonesia</ol>
                            <ol>✅ Mendapatkan kiriman paket spesial</ol>
                            <ol>✅ Menerima pengingat dan informasi bulanan ke email terdaftar</ol>
                        </ul>
                        <img src="tangan.jpg" alt="Gelang Pendekar Anak" class="bracelet">
                    </div>
                    <div class="donation-options">
                        <button onclick="selectAmount(150000)">Rp 150.000 </button>
                        <button onclick="selectAmount(200000)">Rp 200.000 </button>
                        <button onclick="selectAmount(250000)">Rp 250.000 </button>
                        <button onclick="selectAmount(300000)">Rp 300.000 </button>
                        <button onclick="selectAmount(350000)">Rp 350.000 </button>
                        <button onclick="selectAmount(350000)">Rp 500.000 </button>
                    </div>
                    <div class="custom-donation">
                        <input type="number" id="customAmount" placeholder="Jumlah Lainnya" min="1000">
                    </div>
                    <div class="paymentMethod">Pilih metode pembayaran
                      <div class="custom-dropdown">
                        <button type="button" data-role="offcanvas:toggle" offcanvas-target="#payment-channels" class="button ui-primary caret" fdprocessedid="dtdzk">
                            <a href="http://127.0.0.1:3000/Kartuatm.html" class="dropbtn">Pilih</a>
                        </button>
                    </div>
                  </div>
                    <button class="donate-now" onclick="donate()">Bantu Sekarang</button>
                 
                    <p class="note">
                        Dengan klik tombol “Bantu Sekarang”, Anda mengizinkan DonGiv menyimpan data pribadi serta
                        menyampaikan perkembangan program dengan menghubungi Anda melalui telepon, email, dan WhatsApp.
                    </p>
                </div>
            </div>
        </div>

    </main>

    <footer class="footer">
        <p>&copy; 2024 DonGiv Indonesia. Semua Hak Dilindungi.</p>
    </footer>

    <script src="pay.js"></script>
</body>

</html>