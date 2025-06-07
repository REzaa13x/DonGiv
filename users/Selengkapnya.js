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
// Efek transisi pada scroll
window.addEventListener('scroll', function () {
    const stats = document.querySelectorAll('.stat-box');
    stats.forEach((stat, index) => {
        if (stat.getBoundingClientRect().top < window.innerHeight) {
            stat.style.transform = 'translateY(0)';
            stat.style.opacity = '1';
        }
    });
});

