function toggleSubmenu(submenuId, event) {
  event.preventDefault(); 
  const submenu = document.getElementById(submenuId);
  submenu.classList.toggle("active");
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
  window.location.href = "login.html";
}

window.onclick = function (event) {
  const modal = document.getElementById("logoutModal");
  if (event.target === modal) {
    closeLogoutModal();
  }
};
