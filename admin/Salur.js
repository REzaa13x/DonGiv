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
