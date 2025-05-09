<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DonGiv Indonesia - Donasi</title>
    <link rel="stylesheet" href="Style1.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-m9iDaJfvlNPCekOS"></script>
</head>

<body class="overflow-x-hidden">
    <nav>
        <div class="nav-container">
            <a href="../users/DonGiv.php" class="nav-logo">
                <img src="../foto/1-removebg-preview (1).png" alt="DonGiv-Logo">
                <span>DonGiv</span>
            </a>
            <div class="nav-links">
                <a href="../users/DonGiv.php">Home</a>
                <a href="#Donations">Donations</a>
                <a href="#About">About</a>
                <a href="#Contact">Contact</a>
                <div class="dropdown">
                    <img src="../foto/user.png" alt="User" id="dropdown-btn">
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
                <img src="../foto/WhatsApp Image 2024-12-04 at 21.56.45_efacde14.jpg" alt="Child and Parent">
                <div class="text-section">
                    <h2>Bantu Masyarakat Indonesia</h2>
                    <p>"Pemberian dari anda dapat sangat berharga bagi orang lain. Bantulah sesama kita niscaya kebahagiaan akan selalu ada."</p>
                </div>
            </div>

            <div class="donation-section">
                <h3>BERIKAN KEBAIKAN UNTUK PERUBAHAN JANGKA PANJANG HIDUP DI INDONESIA</h3>
                <div class="benefits">
                    <ul>
                        <li>✅ Donasi dengan mudah, aman dan nyaman</li>
                        <li>✅ Menjadi bagian dalam komunitas Pendekar Anak Indonesia</li>
                        <li>✅ Mendapatkan kiriman paket spesial</li>
                        <li>✅ Menerima pengingat dan informasi bulanan ke email terdaftar</li>
                    </ul>
                    <img src="../foto/tangan.jpg" alt="Gelang Pendekar Anak" class="bracelet">
                </div>

                <form id="donation-form">
                    <div class="donation-options">
                        <button type="button" onclick="selectAmount(150000)">Rp 150.000 </button>
                        <button type="button" onclick="selectAmount(200000)">Rp 200.000 </button>
                        <button type="button" onclick="selectAmount(250000)">Rp 250.000 </button>
                        <button type="button" onclick="selectAmount(300000)">Rp 300.000 </button>
                        <button type="button" onclick="selectAmount(350000)">Rp 350.000 </button>
                        <button type="button" onclick="selectAmount(500000)">Rp 500.000 </button>
                    </div>

                    <div class="custom-donation">
            <input type="number" id="customAmount" name="amount" placeholder="Jumlah Lainnya" min="1000" required>
          </div>

          <input type="hidden" name="user" value="1">
          <input type="hidden" name="campaign_id" value="1">

          <div class="custom-donation">
            <label for="nama">Nama Lengkap</label><br>
            <input type="text" id="nama" name="nama" required><br><br>

            <label for="email">Email</label><br>
            <input type="email" id="email" name="email" required><br><br>

            <label for="no_hp">Nomor HP</label><br>
            <input type="text" id="no_hp" name="no_hp" required><br><br>


          <button type="button" class="donate-now" id="donate-button">Bantu Sekarang</button>
        </form>

        <p class="note">
          Dengan klik tombol “Bantu Sekarang”, Anda mengizinkan DonGiv menyimpan data pribadi serta
          menyampaikan perkembangan program dengan menghubungi Anda melalui telepon, email, dan WhatsApp.
        </p>
      </div>
    </div>
  </main>

  <footer class="footer">
    <p>&copy; 2024 DonGiv Indonesia. Semua Hak Dilindungi.</p>
  </footer>

  <script>
    function selectAmount(amount) {
      document.getElementById("customAmount").value = amount;
    }

    document.getElementById('donate-button').addEventListener('click', function () {
      const amount = parseInt(document.getElementById('customAmount').value || 0);
      const nama = document.getElementById('nama').value.trim();
      const email = document.getElementById('email').value.trim();
      const phone = document.getElementById('no_hp').value.trim();
      const user = document.querySelector('input[name="user"]').value;
      const campaign_id = document.querySelector('input[name="campaign_id"]').value;

      if (amount < 1000) {
        alert("Jumlah donasi minimal Rp 1.000");
        return;
      }
      if (!nama || !email.includes("@") || phone.length < 8) {
        alert("Mohon isi data dengan benar.");
        return;
      }

      fetch('snap_process.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          nama, email, phone, amount, user, campaign_id,
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.snapToken) {
          snap.pay(data.snapToken, {
            onSuccess: function(result) {
              alert("Pembayaran berhasil!");
              console.log(result);
            },
            onPending: function(result) {
              alert("Menunggu pembayaran...");
              console.log(result);
            },
            onError: function(result) {
              alert("Pembayaran gagal.");
              console.error(result);
            }
          });
        } else {
          alert("Gagal mendapatkan Snap Token.");
          console.log(data);
        }
      })
      .catch(error => {
        alert("Terjadi kesalahan.");
        console.error(error);
      });
    });
  </script>



</body>

</html>
