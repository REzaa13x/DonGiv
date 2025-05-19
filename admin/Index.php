<?php
include '../users/koneksi.php';

// Query total donasi
$sql_total_donasi = "SELECT SUM(jumlah_uang_masuk) as total_donasi FROM penyaluran_donasi";
$result_total_donasi = $conn->query($sql_total_donasi);
$row_total_donasi = mysqli_fetch_assoc($result_total_donasi);

// Query jumlah kampanye
$sql_kampanye = "SELECT COUNT(nama_donasi) as total_kampanye FROM kampanye_donasi";
$result_kampanye = $conn->query($sql_kampanye);
$row_kampanye = mysqli_fetch_assoc($result_kampanye);

// Query Jumlah donatur
$sql_donatur = "SELECT COUNT(penanggung_jawab) as total_donatur FROM penyaluran_donasi";
$result_donatur = $conn->query($sql_donatur);
$row_donatur = mysqli_fetch_assoc($result_donatur);

//Query bulan
$sql_detail_donasi = "
  SELECT 
      DATE_FORMAT(tanggal, '%M %Y') AS bulan,
      SUM(jumlah_uang_masuk) AS jumlah_donasi,
      COUNT(DISTINCT penanggung_jawab) AS jumlah_donatur
  FROM 
      penyaluran_donasi
  GROUP BY 
      DATE_FORMAT(tanggal, '%Y-%m')
  ORDER BY 
      MIN(tanggal) ASC
  ";

$result_detail_donasi = $conn->query($sql_detail_donasi);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <title>Admin Dashboard</title>
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap");

    * {
      font-family: "Poppins", sans-serif;
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      outline: none;
      border: none;
      text-decoration: none;
      text-transform: capitalize;
      transform: all 0.2s linear;
    }

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
    }

    .sidebar a {
      color: white;
      padding: 10px 15px;
      margin: 5px 0;
      border-radius: 5px;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .sidebar a:hover {
      transform: translateX(0.3px);
    }

    .submenu {
      display: none;
    }

    .submenu.active {
      display: block;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100vw;
      height: 100vh;
      background-color: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(5px);
      margin: 0;
      padding: 0;
      overflow: hidden;
      box-sizing: border-box;
    }

    .modal-content {
      background-color: white;
      margin: 15% auto;
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
    }

    .confirm-button,
    .cancel-button {
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      margin: 0 10px;
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
  </style>
</head>

<body class="bg-gray-100 font-sans">
  <div class="sidebar text-white w-64 py-6 px-4 fixed h-full">
    <h2 class="text-2xl font-bold mb-6 flex items-center">
      <i class="fas fa-hand-holding-heart mr-2"></i> DonGiv
    </h2>
    <nav class="space-y-2">
      <a
        href="#"
        class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
        <i class="fas fa-home mr-2"></i> Dashboard
      </a>
      <div class="relative">
        <a
          href="#"
          class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center justify-between"
          onclick="toggleSubmenu('donation-submenu', event)">
          <span><i class="fas fa-donate mr-2"></i> Management</span>
          <i class="fas fa-chevron-down"></i>
        </a>
        <div id="donation-submenu" class="submenu bg-blue-800 mt-2 rounded-md">
          <a
            href="notifikasi.php"
            class="block py-2 px-6 hover:bg-blue-900 rounded-md">
            Notifkasi dan Email </a>
          <a
            href="Manajemen.php"
            class="block py-2 px-6 hover:bg-blue-900 rounded-md">
            Donation </a>
        </div>
      </div>
      <a
        href="Salur.php"
        class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
        <i class="fas fa-share-alt mr-2"></i> Channel
      </a>
      <a
        href="User Manajement.php"
        class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
        <i class="fas fa-wallet mr-2"></i> Manajement User
        <a
          href="#"
          class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center"
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

  <script>
    function toggleSubmenu(submenuId, event) {
      event.preventDefault();
      const submenu = document.getElementById(submenuId);
      submenu.classList.toggle('active');
    }

    function openLogoutModal() {
      document.getElementById("logoutModal").style.display = "block";
    }

    function closeLogoutModal() {
      document.getElementById("logoutModal").style.display = "none";
    }

    function confirmLogout() {
      alert("You have been logged out.");
      closeLogoutModal();
      window.location.href = "login.php";
    }
    window.onclick = function(event) {
      const modal = document.getElementById("logoutModal");
      if (event.target === modal) {
        closeLogoutModal();
      }
    };
  </script>
  <div id="main-content" class="flex-1 p-6 ml-64">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-6">
      <div class="bg-white p-4 rounded-md shadow-md text-center">
        <h3 class="text-lg font-bold mb-2">Total Donasi</h3>
        <p class="text-2xl text-blue-700 font-bold"><?php echo $row_total_donasi['total_donasi'] ?></p>
      </div>
      <div class="bg-white p-4 rounded-md shadow-md text-center">
        <h3 class="text-lg font-bold mb-2">Jumlah Kampanye</h3>
        <p class="text-2xl text-blue-700 font-bold"><?php echo $row_kampanye['total_kampanye'] ?></p>
      </div>
      <div class="bg-white p-4 rounded-md shadow-md text-center">
        <h3 class="text-lg font-bold mb-2">Jumlah Donatur</h3>
        <p class="text-2xl text-blue-700 font-bold"><?php echo $row_donatur['total_donatur'] ?></p>
      </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <div class="bg-white p-12 rounded-md shadow-md">
        <h3 class="text-2xl font-bold mb-6">Kurva Dana Masuk</h3>
        <canvas id="kurvaDanaMasuk" width="1000" height="800"></canvas>
      </div>
      <div class="bg-white p-6 rounded-md shadow-md">
        <h3 class="text-xl font-bold mb-4">Persentase Total Donasi</h3>
        <canvas id="diagramVenn"></canvas>
      </div>
    </div>
    <div class="bg-white p-6 rounded-md shadow-md mt-6">
      <h3 class="text-xl font-bold mb-4">Detail Donasi per Bulan</h3>
      <table class="w-full text-left">
        <thead>

          <tr>
            <th class="py-2">Bulan</th>
            <th class="py-2">Jumlah Donasi</th>
            <th class="py-2">Jumlah Donatur</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($result_detail_donasi && mysqli_num_rows($result_detail_donasi) > 0) {
            while ($row_detail_donasi = mysqli_fetch_assoc($result_detail_donasi)) {
          ?>
              <tr>
                <td class="py-2"><?php echo $row_detail_donasi['bulan'] ?></td>
                <td class="py-2"><?php echo $row_detail_donasi['jumlah_donasi'] ?></td>
                <td class="py-2"><?php echo $row_detail_donasi['jumlah_donatur'] ?></td>
              </tr>
          <?php
            }
          }
          ?>
        </tbody>
      </table>
    </div>
    <div class="bg-white p-6 rounded-md shadow-md mt-6">
      <h3 class="text-xl font-bold mb-4">Perbandingan Donasi per Bulan</h3>
      <canvas id="perbandinganDonasi"></canvas>
    </div>

    <script>
      document.addEventListener("DOMContentLoaded", function() {
        const perbandinganDonasiCtx = document
          .getElementById("perbandinganDonasi")
          .getContext("2d");
        new Chart(perbandinganDonasiCtx, {
          type: "bar",
          data: {
            labels: [
              "Agustus",
              "September",
              "Oktober",
              "November",
              "Desember",
            ],
            datasets: [{
              label: "Donasi",
              data: [1000000, 1500000, 2000000, 1200000, 1800000],
              backgroundColor: [
                "#8ecae6",
                "#219ebc",
                "#023047",
                "#ffb703",
                "#fb8500",
              ],
            }, ],
          },
          options: {
            legend: {
              labels: [{
                  text: "Donasi Merah Muda",
                  fillStyle: "#FF69B4"
                },
                {
                  text: "Donasi Hijau",
                  fillStyle: "#32CD32"
                },
                {
                  text: "Donasi Biru",
                  fillStyle: "#6495ED"
                },
                {
                  text: "Donasi Oranye",
                  fillStyle: "#FFA07A"
                },
                {
                  text: "Donasi Coklat",
                  fillStyle: "#8B9467"
                },
              ],
            },
          },
        });
      });
    </script>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const kurvaDanaMasukCtx = document
        .getElementById("kurvaDanaMasuk")
        .getContext("2d");
      new Chart(kurvaDanaMasukCtx, {
        type: "line",
        data: {
          labels: [
            "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
          ],
          datasets: [{
            label: "Dana Masuk",
            data: [
              500000, 700000, 800000, 600000, 900000, 1200000, 1100000,
              1300000,
            ],
            borderColor: "blue",
            fill: false,
          }, ],
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
            },
          },
        },
      });
    });

    const diagramVennCtx = document
      .getElementById("diagramVenn")
      .getContext("2d");
    new Chart(diagramVennCtx, {
      type: "doughnut",
      data: {
        labels: ["Donasi A", "Donasi B", "Donasi C"],
        datasets: [{
          data: [40, 35, 25],
          backgroundColor: ["#8ecae6", "#219ebc", "#023047"],
        }, ],
      },
    });
  </script>
</body>

</html>