<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Donasi</title>
    <link rel="stylesheet" href="Selengkapnya.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</head>
<body>
   <!-- Dropdown -->
 <nav>
    <div class="nav-container">
      <a href="#" class="nav-logo">
        <img src="../foto/1-removebg-preview (1).png" alt="DonGiv-Logo">
        <span>DonGiv</span>
      </a>
  
      <div class="nav-links">
        <a href="DonGiv.php #home">Home</a>
        <a href="DonGiv.php#Donations">Donations</a>
        <a href="DonGiv.php#About">About</a>
        <a href="DonGiv.php#Contact">Contact</a>
  
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


    <!-- Header -->
    <header class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Ayo Berkontribusi untuk Masa Depan Lebih Baik</h1>
                <p>
                    Donasi Anda membantu kami melindungi lingkungan, meningkatkan pendidikan, dan 
                    memberikan harapan kepada mereka yang membutuhkan. Bersama, kita bisa membuat perubahan nyata.
                </p>
                <a href="donasi.php " class="btn-primary">Donasi Sekarang</a>
            </div>
            <div class="hero-image">
                <img src="../foto/war-6261980_1280-removebg-preview.png" alt="Relawan" />
                <div class="stats">
                    <div class="stat-item">
                        <p>Terkumpul</p>
                        <span>Rp. 250 Juta</span>
                    </div>
                    <div class="stat-item">
                        <p>Donatur</p>
                        <span>1.500 Orang</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Statistik -->
    <section id="statistics" class="statistics">
        <div class="container">
            <div class="stat-box">
                <h3>Rp. 100 Jt</h3>
                <p>Dana Untuk Pendidikan</p>
            </div>
            <div class="stat-box">
                <h3>Rp. 80 Jt</h3>
                <p>Dana Untuk Lingkungan</p>
            </div>
            <div class="stat-box">
                <h3>Rp. 70 Jt</h3>
                <p>Dana Untuk Kemanusiaan</p>
            </div>
        </div>
    </section>

    <!-- Manfaat Penanaman Pohon -->
    <section class="benefits">
        <h2>Manfaat Donasi Anda</h2>
        <p>Setiap kontribusi memberikan dampak positif yang besar, seperti:</p>
        
        <div class="benefits-grid">
            <div class="benefit-card">
                <img src="../foto/school.png" alt="pendidikan">
                <h4>Meningkatkan Pendidikan</h4>
                <p>
                    Donasi Anda membantu menyediakan fasilitas belajar dan beasiswa untuk anak-anak yang membutuhkan.
                </p>
            </div>
            <div class="benefit-card">
                <img src="../foto/save-the-planet.png" alt="lingkungan">
                <h4>Melestarikan Lingkungan</h4>
                <p>
                    Kami menggunakan dana Anda untuk menanam pohon, melindungi hutan, dan menjaga ekosistem alami.
                </p>
            </div>
            <div class="benefit-card">
                <img src="../foto/help.png" alt="help">
                <h4>Membantu Sesama</h4>
                <p>
                    Anda membantu memberikan makanan, tempat tinggal, dan dukungan medis bagi mereka yang membutuhkan.
                </p>
            </div>
        </div>
    </section>
    


    <script src="Selengkapnya.js"></script>
</body>
</html>
