<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Donations</title>
  <link rel="stylesheet" href="View.css">
  <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="bg-blue-600 sticky top-0 z-50 shadow-lg w-screen">
        <div class="flex justify-between items-center px-6 py-4">
            <a href="#" class="flex items-center">
                <img src="../foto/1-removebg-preview (1).png" class="h-12 mr-2" alt="DonGiv-Logo">
                <span class="text-white text-2xl font-semibold">DonGiv</span>
            </a>
    
            <div class="hidden md:flex space-x-6">
                <a href="../users/DonGiv.php" class="text-white hover:text-blue-300">Home</a>
                <a href="#Donations" class="text-white hover:text-blue-300">Donations</a>
                <a href="http://127.0.0.1:5500/Ab.html" class="text-white hover:text-blue-300">About</a>
                <a href="#Contact" class="text-white hover:text-blue-300">Contact</a>
    
                <!-- Dropdown -->
                <div class="relative">
                    <button id="dropdownButton" class="relative focus:outline-none">
                        <img src="../foto/user.png" class="w-8 h-8 rounded-full border-2 border-white">
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
    


  <div class="container">
    <h1>Donations</h1>
    <div class="cards">

      <!-- Card 1 -->
      <div class="card">
        <img src="../foto/bencana-longsor-di-kabupaten-bandung-barat-3_169.jpeg.jpg" alt="Flash floods">
        <div class="card-content">
          <div class="type">CAMPAIGN</div>
          <div class="date">21 November 2024</div>
          <h3>Flash floods in Medan need help</h3>
          <p>Children are raising their voices on the climate crisis. Are we listening?</p>
          <a href="http://127.0.0.1:5500/GU.html">Get the details</a>
        </div>
      </div>

      <!-- Card 2 -->
      <div class="card">
        <img src="../foto/download (1).jpeg.jpg" alt="Children in Gaza">
        <div class="card-content">
          <div class="type">APPEAL</div>
          <div class="date">21 November 2024</div>
          <h3>Children in Gaza need life-saving support</h3>
          <p>No safe place for children as humanitarian crisis deepens.</p>
          <a href="http://127.0.0.1:5500/GU2.html">View the appeal</a>
        </div>
      </div>

      <!-- Card 3 -->
      <div class="card">
        <img src="../foto/wanita-palestina-bersama-anaknya-di-dekat-rumahnya-yang-hancur-_200114213428-417.jpg" alt="Myanmar conflict">
        <div class="card-content">
          <div class="type">STATEMENT</div>
          <div class="date">21 November 2024</div>
          <h3>Urgent need to protect children amid escalating conflict in Myanmar</h3>
          <p>There are even those who channel quite a lot of funds for this need.</p>
          <a href="http://127.0.0.1:5500/GU3.html">Read now</a>
        </div>
      </div>

      <!-- Card 4 -->
      <div class="card">
        <img src="../foto/article-2698150-1FC8751B00000578-652_964x623.jpg" alt="Lebanon crisis">
        <div class="card-content">
          <div class="type">APPEAL</div>
          <div class="date">19 November 2024</div>
          <h3>Lebanon’s Escalating Violence on Children</h3>
          <p>A very amateur case in a city that claimed many victims, especially children.</p>
          <a href="http://127.0.0.1:5500/GU4.html">View the appeal</a>
        </div>
      </div>

      <!-- Card 4 -->
      <div class="card">
        <img src="../foto/kanker.jpg" alt="Lebanon crisis">
        <div class="card-content">
          <div class="type">APPEAL</div>
          <div class="date">19 November 2024</div>
          <h3>Seorang anak berjuang melawan kanker</h3>
          <p>A very amateur case in a city that claimed many victims, especially children.</p>
          <a href="#">View the appeal</a>
        </div>
      </div>

      <!-- Card 4 -->
      <div class="card">
        <img src="../foto/Benihbaik_2024-04-24_17139495616628cb7992314.jpeg" alt="Lebanon crisis">
        <div class="card-content">
          <div class="type">APPEAL</div>
          <div class="date">19 November 2024</div>
          <h3>Lebanon’s Escalating Violence on Children</h3>
          <p>A very amateur case in a city that claimed many victims, especially children.</p>
          <a href="#">View the appeal</a>
        </div>
      </div>

      <!-- Card 4 -->
      <div class="card">
        <img src="yatim.png" alt="Lebanon crisis">
        <div class="card-content">
          <div class="type">APPEAL</div>
          <div class="date">19 November 2024</div>
          <h3>Lebanon’s Escalating Violence on Children</h3>
          <p>A very amateur case in a city that claimed many victims, especially children.</p>
          <a href="#">View the appeal</a>
        </div>
      </div>

      <!-- Card 4 -->
      <div class="card">
        <img src="kejang.jpeg" alt="Lebanon crisis">
        <div class="card-content">
          <div class="type">APPEAL</div>
          <div class="date">19 November 2024</div>
          <h3>Lebanon’s Escalating Violence on Children</h3>
          <p>A very amateur case in a city that claimed many victims, especially children.</p>
          <a href="#">View the appeal</a>
        </div>
      </div>

      <!-- Card 4 -->
      <div class="card">
        <img src="adek.jpeg" alt="Lebanon crisis">
        <div class="card-content">
          <div class="type">APPEAL</div>
          <div class="date">19 November 2024</div>
          <h3>Lebanon’s Escalating Violence on Children</h3>
          <p>A very amateur case in a city that claimed many victims, especially children.</p>
          <a href="#">View the appeal</a>
        </div>
      </div>

      <!-- Card 4 -->
      <div class="card">
        <img src="jantung.jpg" alt="Lebanon crisis">
        <div class="card-content">
          <div class="type">APPEAL</div>
          <div class="date">19 November 2024</div>
          <h3>Lebanon’s Escalating Violence on Children</h3>
          <p>A very amateur case in a city that claimed many victims, especially children.</p>
          <a href="#">View the appeal</a>
        </div>
      </div>

      <!-- Card 4 -->
      <div class="card">
        <img src="azmi.jpg_large" alt="Lebanon crisis">
        <div class="card-content">
          <div class="type">APPEAL</div>
          <div class="date">19 November 2024</div>
          <h3>Lebanon’s Escalating Violence on Children</h3>
          <p>A very amateur case in a city that claimed many victims, especially children.</p>
          <a href="#">View the appeal</a>
        </div>
      </div>

      <!-- Card 4 -->
      <div class="card">
        <img src="palestina ya.jpeg" alt="Lebanon crisis">
        <div class="card-content">
          <div class="type">APPEAL</div>
          <div class="date">19 November 2024</div>
          <h3>Lebanon’s Escalating Violence on Children</h3>
          <p>A very amateur case in a city that claimed many victims, especially children.</p>
          <a href="#">View the appeal</a>
        </div>
      </div>
      <!-- Add more cards below -->
      <div class="card">
        <img src="koma.jpeg" alt="Lebanon crisis">
        <div class="card-content">
          <div class="type">APPEAL</div>
          <div class="date">19 November 2024</div>
          <h3>Lebanon’s Escalating Violence on Children</h3>
          <p>A very amateur case in a city that claimed many victims, especially children.</p>
          <a href="#">View the appeal</a>
        </div>
      </div>

    </div>
  </div>
</body>
</html>
