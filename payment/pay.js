function selectAmount(amount) {
    document.getElementById("customAmount").value = amount;
}

function donate() {
    const amount = document.getElementById("customAmount").value;
    const paymentMethod = document.getElementById("paymentMethod").value;
    if (amount && amount >= 1000) {
        alert(`Terima kasih! Anda telah memilih untuk mendonasikan Rp ${amount} melalui ${paymentMethod}.`);
    } else {
        alert("Silakan masukkan jumlah donasi yang valid.");
    }
}
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
  