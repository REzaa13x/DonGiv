<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banjir Bandang di Medan - Donasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="GU.css">
    <style> 
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">

  <body class="overflow-x-hidden">
    <nav class="bg-blue-600 sticky top-0 z-50 shadow-lg w-screen">
      <div class="flex justify-between items-center px-6 py-4">
          <a href="#" class="flex items-center">
              <img src="../foto/1-removebg-preview (1).png" class="h-12 mr-2" alt="DonGiv-Logo">
              <span class="text-white text-2xl font-semibold">DonGiv</span>
          </a>
  
          <div class="hidden md:flex space-x-6">
              <a href="http://127.0.0.1:5500/slide.html#" class="text-white hover:text-blue-300">Home</a>
              <a href="#Donations" class="text-white hover:text-blue-300">Donations</a>
              <a href="http://127.0.0.1:5500/Ab.html" class="text-white hover:text-blue-300">About</a>
              <a href="#Contact" class="text-white hover:text-blue-300">Contact</a>
  
              <!-- Dropdown -->
              <div class="relative">
                  <button id="dropdownButton" class="relative focus:outline-none">
                      <img src="user.png" class="w-8 h-8 rounded-full border-2 border-white">
                  </button>
                  <div id="dropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2">
                      <div class="px-4 py-2 border-b">
                          <p class="text-gray-800 font-semibold">Username</p>
                          <p class="text-gray-500 text-sm">name@gmail.com</p>
                      </div>
                      <a href="#profile" class="block px-4 py-2 text-gray-800 hover:bg-blue-100">Profile</a>
                      <a href="#settings" class="block px-4 py-2 text-gray-800 hover:bg-blue-100">Settings</a>
                      <a href="#logout" class="block px-4 py-2 text-gray-800 hover:bg-blue-100">Logout</a>
                  </div>
              </div>
          </div>
  
          <!-- Mobile Menu Button -->
          <button class="md:hidden text-white focus:outline-none">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
              </svg>
          </button>
      </div>
  </nav>
  

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-content">
      <h1>Banjir Bandang di Medan Membutuhkan Bantuan Anda</h1>
      <p>Anak-anak menyuarakan krisis iklim. Apakah kita mendengarkan?</p>
      <a href="http://127.0.0.1:5500/Payment.html#logout" class="cta">Donasi Sekarang</a>
    </div>
  </section>

  <!-- Main Content -->
  <div class="content">
    <img src="../foto/bencana-longsor-di-kabupaten-bandung-barat-3_169.jpeg.jpg" alt="Flash floods in Medan" class="content-image">

    <h2>Gambaran Kampanye</h2>
    <p>Banjir bandang yang melanda Medan telah menyebabkan banyak keluarga kehilangan tempat tinggal dan membutuhkan bantuan segera. Anak-anak adalah yang paling terdampak, kesulitan mendapatkan tempat berteduh, air bersih, dan makanan.</p>

    <h3>Cara Anda Dapat Membantu</h3>
    <ul>
      <li>Pasokan makanan darurat</li>
      <li>Tempat penampungan sementara</li>
      <li>Air bersih dan sanitasi</li>
      <li>Bantuan kesehatan untuk keluarga terdampak</li>
    </ul>

    <h3>Kontak Kami</h3>
    <p>Untuk informasi lebih lanjut atau ingin terlibat, hubungi kami di 
      <a href="mailto:support@charity.org">support@charity.org</a>.
    </p>
  </div>
  <!-- Leaderboard Pendonasi -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" id="Leaderboard">
  <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Leaderboard Pendonasi</h2>
  <div class="overflow-x-auto">
    <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md">
      <thead>
        <tr class="bg-blue-600 text-white">
          <th class="py-3 px-6 text-left">Peringkat</th>
          <th class="py-3 px-6 text-left">Nama</th>
          <th class="py-3 px-6 text-left">Jumlah Donasi</th>
        </tr>
      </thead>
      <tbody>
        <!-- Data Pendonasi -->
        <tr class="border-t">
          <td class="py-4 px-6">1</td>
          <td class="py-4 px-6">John Doe</td>
          <td class="py-4 px-6">Rp 10.000.000</td>
        </tr>
        <tr class="border-t">
          <td class="py-4 px-6">2</td>
          <td class="py-4 px-6">Jane Smith</td>
          <td class="py-4 px-6">Rp 8.000.000</td>
        </tr>
        <tr class="border-t">
          <td class="py-4 px-6">3</td>
          <td class="py-4 px-6">Budi Santoso</td>
          <td class="py-4 px-6">Rp 5.000.000</td>
        </tr>
        <tr class="border-t">
          <td class="py-4 px-6">4</td>
          <td class="py-4 px-6">Lisa Wijaya</td>
          <td class="py-4 px-6">Rp 4.500.000</td>
        </tr>
        <tr class="border-t">
          <td class="py-4 px-6">5</td>
          <td class="py-4 px-6">Ahmad Fauzi</td>
          <td class="py-4 px-6">Rp 3.800.000</td>
        </tr>
      </tbody>
    </table>
  </div>
</section>


  <!-- Footer -->
  <footer class="footer">
    <div class="footer-content">
      <p>&copy; 2024 Your Charity Organization</p>
      <div class="footer-links">
        <a href="#">Facebook</a>
        <a href="#">Twitter</a>
        <a href="#">Instagram</a>
      </div>
    </div>
  </footer>

  <script src="Get.js"></script>
</body>
</html>
