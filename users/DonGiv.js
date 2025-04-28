document.getElementById('dropdown-btn').addEventListener('click', function(event) {
  event.stopPropagation();
  const dropdown = document.querySelector('.dropdown');
  dropdown.classList.toggle('active');
});

document.addEventListener('click', function(event) {
  const dropdown = document.querySelector('.dropdown');
  if (!dropdown.contains(event.target)) {
    dropdown.classList.remove('active');
  }
});

// card
// Share button logic (optional)
document.addEventListener('DOMContentLoaded', () => {
  const shareButtons = document.querySelectorAll('.share-btn');

  shareButtons.forEach(button => {
    button.addEventListener('click', () => {
      alert('Share functionality coming soon!');
    });
  });
});

// Review slide
document.addEventListener("DOMContentLoaded", () => {
  let currentIndex = 0; // Slide aktif
  const slides = document.querySelectorAll(".slide");
  const dots = document.querySelectorAll(".dot");

  // Fungsi untuk mengubah slide
  function showSlide(index) {
    // Jika index melebihi jumlah slide, kembali ke awal
    if (index >= slides.length) {
      currentIndex = 0;
    } else if (index < 0) {
      currentIndex = slides.length - 1;
    } else {
      currentIndex = index;
    }

    // Pindahkan slider menggunakan transform
    const offset = -currentIndex * 100; // Posisi berdasarkan index
    document.querySelector(".slider").style.transform = `translateX(${offset}%)`;

    // Perbarui dot aktif
    dots.forEach((dot, i) => {
      dot.classList.toggle("active", i === currentIndex);
    });
  }

  // Autoplay: Pindah slide otomatis setiap 3 detik
  function autoSlide() {
    showSlide(currentIndex + 1);
  }
  let slideInterval = setInterval(autoSlide, 3000);

  // Klik pada dot untuk mengubah slide
  dots.forEach((dot, index) => {
    dot.addEventListener("click", () => {
      clearInterval(slideInterval); // Hentikan autoplay sementara
      showSlide(index);
      slideInterval = setInterval(autoSlide, 3000); // Lanjutkan autoplay
    });
  });

  // Tampilkan slide pertama
  showSlide(currentIndex);
});

// en rivw
 // Pilih elemen teks
 const heroText = document.querySelector('.hero-text');

 // Gunakan Intersection Observer untuk mendeteksi saat elemen masuk viewport
 const observer = new IntersectionObserver((entries) => {
     entries.forEach(entry => {
         if (entry.isIntersecting) {
             heroText.classList.add('visible');
         }
     });
 });

 // Observasi elemen teks
 observer.observe(heroText);