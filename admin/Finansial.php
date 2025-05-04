<?php
include '../users/koneksi.php';
$sql = "SELECT 
  pd.id_donasi,
  pd.tanggal, 
  pd.penanggung_jawab, 
  kd.nama_donasi, 
  SUM(pd.jumlah_uang_masuk) AS uang_masuk
FROM 
  penyaluran_donasi pd
JOIN 
  kategori_donasi kd 
  ON pd.id_donasi = kd.id_kategori
GROUP BY 
  pd.id_donasi, pd.tanggal, pd.penanggung_jawab, kd.nama_donasi
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tracker Donasi</title>
  <link rel="stylesheet" href="finansial.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>

  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
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
        class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
        <i class="fas fa-home mr-2"></i> Dashboard
      </a>

      <!-- Donation dengan Submenu -->
      <div class="relative">
        <a
          href="#"
          class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center justify-between"
          onclick="toggleSubmenu('donation-submenu', event)">
          <span><i class="fas fa-donate mr-2"></i> Management</span>
          <i class="fas fa-chevron-down"></i>
        </a>
        <div
          id="donation-submenu"
          class="submenu bg-blue-800 mt-2 rounded-md">
          <a
            href="notifikasi.php"
            class="block py-2 px-6 hover:bg-blue-900 rounded-md">
            Notifikasi dan Email
          </a>
          <a
            href="Manajemen.php"
            class="block py-2 px-6 hover:bg-blue-900 rounded-md">
            Donation
          </a>
        </div>
      </div>

      <!-- Channel -->
      <a
        href="Salur.php"
        class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
        <i class="fas fa-share-alt mr-2"></i> Channel
      </a>

      <!-- Finance -->
      <a
        href="Finansial.php"
        class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center">
        <i class="fas fa-wallet mr-2"></i> Finance
      </a>

      <!-- Log Out -->
      <a
        href="#"
        class="block py-2 px-4 hover:bg-blue-800 rounded-md flex items-center"
        onclick="openLogoutModal()">
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

  <div class="container">
    <!-- Header -->
    <header class="header">
      <h1 class="title">Tracker Donasi</h1>
      <p class="subtitle">
        Pantau pendapatan dan pengeluaran donasi Anda dengan mudah.
      </p>
    </header>

    <!-- Chart -->
    <div class="content">
    <div id="chartContainer" class="relative h-96">
  <canvas id="mutasiChart"></canvas>
  <p id="noDataMsg" class="text-center text-gray-500 mt-4 hidden">Data bar chart kosong.</p>
</div>

      <!-- Tabel Riwayat Transaksi -->
      <div class="transaction-history">
        <h2>Riwayat Transaksi</h2>
        <table>
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Penanggung Jawab</th>
              <th>Nama Donasi</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($result && mysqli_num_rows($result) > 0) {
              while ($row = mysqli_fetch_assoc($result)) {
            ?>
                <tr>
                  <td><?php echo $row['tanggal'] ?></td>
                  <td><?php echo $row['penanggung_jawab'] ?></td>
                  <td><?php echo $row['nama_donasi'] ?></td>
                  <td>Rp. <?php echo number_format($row['uang_masuk'], 0, ',', '.') ?></td>
                </tr>
            <?php
              }
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <script src="Finansial.js"></script>
</body>

<script>
  const chartData = <?php
    $result->data_seek(0); 
    $data = [];
    while ($row = $result->fetch_assoc()) {
      $data[] = [
        'tanggal' => $row['tanggal'],
        'nama_donasi' => $row['nama_donasi'],
        'total' => (int)$row['uang_masuk']
      ];
    }
    echo json_encode($data);
  ?>;

  document.addEventListener("DOMContentLoaded", function () {
    const chartContainer = document.getElementById("chartContainer");
    const canvas = document.getElementById("mutasiChart");
    const noDataMsg = document.getElementById("noDataMsg");

    if (chartData.length === 0) {
      // Sembunyikan canvas, tampilkan pesan
      canvas.style.display = "none";
      noDataMsg.classList.remove("hidden");
      return;
    } else {
      // Tampilkan canvas, sembunyikan pesan
      canvas.style.display = "block";
      noDataMsg.classList.add("hidden");
    }

    const ctx = canvas.getContext("2d");
    const labels = chartData.map(item => item.nama_donasi + " (" + item.tanggal + ")");
    const dataUangMasuk = chartData.map(item => item.total);

    new Chart(ctx, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Jumlah Uang Masuk",
            data: dataUangMasuk,
            backgroundColor: "rgba(59, 130, 246, 0.7)",
            borderColor: "rgba(59, 130, 246, 1)",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "top",
            labels: {
              font: {
                size: 14,
              },
            },
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                return context.dataset.label + ": Rp " + context.raw.toLocaleString();
              },
            },
          },
        },
        scales: {
          x: {
            ticks: {
              font: {
                size: 12,
              },
            },
          },
          y: {
            beginAtZero: true,
            ticks: {
              callback: function (value) {
                return "Rp " + value.toLocaleString();
              },
            },
          },
        },
      },
    });
  });
</script>
</html>