// Data untuk chart
const mutasiCtx = document.getElementById("mutasiChart").getContext("2d");

// Data Riwayat Transaksi
const riwayatTransaksi = [
  {
    tanggal: "25 Sep 2024, 20:34 WIB",
    penanggungJawab: "John Doe",
    namaDonasi: "Banjir bandang di Medan",
    total: 23000000,
  },
  {
    tanggal: "26 Sep 2024, 10:15 WIB",
    penanggungJawab: "Jane Smith",
    namaDonasi: "Bantuan untuk anak-anak Gaza",
    total: 9000000,
  },
  {
    tanggal: "27 Sep 2024, 18:15 WIB",
    penanggungJawab: "Reza arap",
    namaDonasi: "Kebutuhan mendesak untuk korban letusan gunung marapi",
    total: 15000000,
  },
  {
    tanggal: "27 Sep 2024, 23:45 WIB",
    penanggungJawab: "Darren Jason Watkins Jr",
    namaDonasi: "Bantuan untuk korban ledakan lebanon",
    total: 85000000,
  },
  {
    tanggal: "27 Sep 2024, 03:00 WIB",
    penanggungJawab: "Rijal Muhammad",
    namaDonasi: "bantuan darurat Korban perang dunia 3",
    total: 100000000,
  },
];

function tampilkanRiwayatTransaksi() {
  const tableBody = document.getElementById("transactionTableBody");

  // Kosongkan isi tabel sebelum menambahkan data baru
  tableBody.innerHTML = "";

  // Loop melalui data transaksi dan tambahkan ke tabel
  riwayatTransaksi.forEach((transaksi) => {
    const row = document.createElement("tr");

    // Kolom Tanggal (dengan ikon tangan)
    const tanggalCell = document.createElement("td");
    const tanggalWrapper = document.createElement("div");
    tanggalWrapper.style.display = "flex";
    tanggalWrapper.style.alignItems = "center";
    tanggalWrapper.style.gap = "10px"; // Jarak antara ikon dan teks

    // Ikon Tangan Memberi
    const ikonTangan = document.createElement("i");
    ikonTangan.className = "fas fa-hand-holding-heart"; // Ikon tangan memberi dari Font Awesome
    ikonTangan.style.color = "#2563eb"; // Warna biru
    ikonTangan.style.fontSize = "18px"; // Ukuran ikon

    // Teks Tanggal
    const tanggalText = document.createElement("span");
    tanggalText.textContent = transaksi.tanggal;

    // Gabungkan ikon dan teks ke dalam wrapper
    tanggalWrapper.appendChild(ikonTangan);
    tanggalWrapper.appendChild(tanggalText);

    // Masukkan wrapper ke dalam kolom Tanggal
    tanggalCell.appendChild(tanggalWrapper);
    row.appendChild(tanggalCell);

    // Kolom Penanggung Jawab
    const penanggungJawabCell = document.createElement("td");
    penanggungJawabCell.textContent = transaksi.penanggungJawab;
    row.appendChild(penanggungJawabCell);

    // Kolom Nama Donasi
    const namaDonasiCell = document.createElement("td");
    namaDonasiCell.textContent = transaksi.namaDonasi;
    row.appendChild(namaDonasiCell);

    // Kolom Total
    const totalCell = document.createElement("td");
    totalCell.textContent = `Rp ${transaksi.total.toLocaleString()}`;
    row.appendChild(totalCell);

    // Tambahkan baris ke tabel
    tableBody.appendChild(row);
  });
}

// Fungsi untuk membuat chart
function buatChart() {
  new Chart(mutasiCtx, {
    type: "bar",
    data: {
      labels: ["September", "October", "November", "December"],
      datasets: [
        {
          label: "Pendapatan",
          data: [25000000, 20000000, 15000000, 10000000],
          backgroundColor: "rgba(59, 130, 246, 0.7)",
          borderColor: "rgba(59, 130, 246, 1)",
          borderWidth: 1,
        },
        {
          label: "Pengeluaran",
          data: [15000000, 10000000, 20000000, 25000000],
          backgroundColor: "rgba(220, 38, 38, 0.7)",
          borderColor: "rgba(220, 38, 38, 1)",
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
          enabled: true,
          callbacks: {
            label: function (tooltipItem) {
              return (
                tooltipItem.dataset.label +
                ": Rp " +
                tooltipItem.raw.toLocaleString()
              );
            },
          },
        },
      },
      scales: {
        x: {
          grid: {
            display: false,
          },
          ticks: {
            font: {
              size: 12,
            },
          },
        },
        y: {
          beginAtZero: true,
          grid: {
            color: "#e5e7eb",
          },
          ticks: {
            font: {
              size: 12,
            },
            callback: function (value) {
              return "Rp " + value.toLocaleString();
            },
          },
        },
      },
    },
  });
}

// Panggil fungsi untuk menampilkan riwayat transaksi dan membuat chart saat halaman dimuat
document.addEventListener("DOMContentLoaded", function () {
  tampilkanRiwayatTransaksi();
  buatChart();
});
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
