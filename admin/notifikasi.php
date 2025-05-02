<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Notifikasi dan Email</title>
    <link rel="stylesheet" href="notifikasi.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
    />
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body>
    <!-- Sidebar -->
    <div class="sidebar text-white w-64 py-6 px-4 fixed h-full">
      <h2 class="text-2xl font-bold mb-6 flex items-center">
        <i class="fas fa-hand-holding-heart mr-2"></i> DonGiv
      </h2>
      <nav class="space-y-2">
        <!-- Dashboard -->
        <a
          href="Index.php"
          class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center"
        >
          <i class="fas fa-home mr-2"></i> Dashboard
        </a>

        <!-- Donation dengan Submenu -->
        <div class="relative">
          <a
            href="#"
            class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center justify-between"
            onclick="toggleSubmenu('donation-submenu', event)"
          >
            <span><i class="fas fa-donate mr-2"></i> Management</span>
            <i class="fas fa-chevron-down"></i>
          </a>
          <div
            id="donation-submenu"
            class="submenu bg-blue-800 mt-2 rounded-md"
          >
            <a
              href="notifikasi.php"
              class="block py-2 px-6 hover:bg-blue-900 rounded-md"
            >
              Notifkasi dan Email
            </a>
            <a
              href="Manajemen.php"
              class="block py-2 px-6 hover:bg-blue-900 rounded-md"
            >
              Donation
            </a>
          </div>
        </div>

        <!-- Channel -->
        <a
          href="Salur.php"
          class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center"
        >
          <i class="fas fa-share-alt mr-2"></i> Channel
        </a>

        <!-- Finance -->
        <a
          href="Finansial.php"
          class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center"
        >
          <i class="fas fa-wallet mr-2"></i> Finance
        </a>

        <!-- Log Out -->
        <a
          href="#"
          class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center"
          onclick="openLogoutModal()"
        >
          <i class="fas fa-sign-out-alt mr-2"></i> Log Out
        </a>
        <!-- Log Out Modal -->
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
    <!-- Notifikasi Start -->
    <header class="header">
      <h1>Notifikasi dan Email</h1>
    </header>

    <main class="main-content">
      <section class="notification-section">
        <h2>Daftar Notifikasi</h2>
        <ul class="notification-list">
          <ul class="notification-item">
            <p>
              <strong>Donasi Baru:</strong> Donasi untuk "Bencana Banjir"
              sebesar Rp500.000 telah diterima.
            </p>
            <span class="timestamp">2 menit yang lalu</span>
          </ul>

          <li class="notification-item">
            <p>
              <strong>Target Tercapai:</strong> Donasi "Pendidikan Anak" telah
              mencapai 100% dari target.
            </p>
            <span class="timestamp">1 jam yang lalu</span>
          </li>

          <li class="notification-item">
            <p>
              <strong>Pengingat:</strong> Donasi "Bantuan Kesehatan" akan
              berakhir dalam 3 hari.
            </p>
            <span class="timestamp">Kemarin</span>
          </li>
        </ul>

        <!-- Tambahkan di bawah bagian notifikasi -->
        <form class="email-form">
          <label for="email-subject">Subjek Email</label>
          <input
            type="text"
            id="email-subject"
            name="subject"
            placeholder="Masukkan subjek email"
          />

          <label for="email-content">Isi Email</label>
          <textarea
            id="email-content"
            name="content"
            placeholder="Tulis isi email Anda di sini..."
          ></textarea>

          <label for="email-recipients">Penerima</label>
          <select id="email-recipients" name="recipients">
            <option value="all">Semua Donatur</option>
            <option value="top">Top Donatur</option>
            <option value="recent">Donatur Terbaru</option>
          </select>

          <button type="submit" class="send-email-btn">Kirim Email</button>
        </form>
      </section>

      <!-- Kontak Pesan -->
      <section class="message-section">
        <h2>Kotak Pesan</h2>
        <div class="message">
          <div class="avatar" style="background: #2563eb">DN</div>
          <div class="message-content">
            <div class="name">Donatur</div>
            <div class="text">
              Terimakasih telah memberikan sebagian harta anda!
            </div>
          </div>
          <div class="message-time">3:59 PM</div>
        </div>

        <div class="message">
          <div class="avatar" style="background: #ff4081">MA</div>
          <div class="message-content">
            <div class="name">Maryam Amiri</div>
            <div class="text">Berikan bantuan untuk kesehatan adik Putra</div>
          </div>
          <div class="message-time">3:45 PM</div>
        </div>

        <div class="message">
          <div class="avatar" style="background: #43a047">AI</div>
          <div class="message-content">
            <div class="name">Ali Imran</div>
            <div class="text">Sudah ada donasi baru yang telah diupdate!</div>
          </div>
          <div class="message-time">3:38 PM</div>
        </div>

        <div class="message">
          <div class="avatar" style="background: #fb8c00">SH</div>
          <div class="message-content">
            <div class="name">Soleh Suhan</div>
            <div class="text">
              Terimakasih sudah meluangkan waktu anda untuk berdonasi di DonGiv!
            </div>
          </div>
          <div class="message-time">2:55 PM</div>
        </div>

        <div class="message">
          <div class="avatar" style="background: #fb8c00">SH</div>
          <div class="message-content">
            <div class="name">Soleh Suhan</div>
            <div class="text">
              Terimakasih sudah meluangkan waktu anda untuk berdonasi di DonGiv!
            </div>
          </div>
          <div class="message-time">2:55 PM</div>
        </div>
        
        <div class="message">
          <div class="avatar" style="background: #43a047">AI</div>
          <div class="message-content">
            <div class="name">Ali Imran</div>
            <div class="text">Sudah ada donasi baru yang telah diupdate!</div>
          </div>
          <div class="message-time">3:38 PM</div>
        </div>

        <div class="message">
          <div class="avatar" style="background: #3f51b5">AM</div>
          <div class="message-content">
            <div class="name">Alice Morgan</div>
            <div class="text">
              Berikan bantuan mu untuk donasi pada hari ini!
            </div>
          </div>
          <div class="message-time">1:45 PM</div>
        </div>
      </section>
    </main>
    <script>
      // Fungsi untuk toggle submenu
      function toggleSubmenu(submenuId, event) {
        event.preventDefault(); // Mencegah perilaku default
        const submenu = document.getElementById(submenuId);
        submenu.classList.toggle("active");
      }
      // Fungsi untuk membuka modal logout
      function openLogoutModal() {
        document.getElementById("logoutModal").style.display = "block";
      }

      // Fungsi untuk menutup modal logout
      function closeLogoutModal() {
        document.getElementById("logoutModal").style.display = "none";
      }

      // Fungsi untuk mengonfirmasi logout
      function confirmLogout() {
        alert("You have been logged out.");
        closeLogoutModal();
        // Redirect ke halaman login setelah logout
        window.location.href = "login.html";
      }

      // Menutup modal jika pengguna mengklik di luar modal
      window.onclick = function (event) {
        const modal = document.getElementById("logoutModal");
        if (event.target === modal) {
          closeLogoutModal();
        }
      };
    </script>
  </body>
</html>
