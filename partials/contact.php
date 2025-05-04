<?php
include '../users/koneksi.php';

$status = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama    = $_POST['name'] ?? '';
    $email   = $_POST['email'] ?? '';
    $pesan   = $_POST['message'] ?? '';

    if ($nama && $email && $pesan) {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO kontak (nama, email, pesan) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $nama, $email, $pesan);

        if (mysqli_stmt_execute($stmt)) {
            $status = 'ok';
        } else {
            $status = 'error';
        }
        mysqli_stmt_close($stmt);
    } else {
        $status = 'error';
    }
}
?>

<div class="contact-container-wrapper">
  <div class="contact-h1-text" id="Contact">
    <h1>Contact</h1>
  </div>
  <div class="contact-page-wrapper">
    <div class="contact-page contact-container">

      <!-- Maps Section -->
      <div class="contact-map">
        <iframe 
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.304493095332!2d107.62777107356627!3d-6.973357268279448!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68e9adf177bf8d%3A0x437398556f9fa03!2sUniversitas%20Telkom!5e0!3m2!1sid!2sid!4v1733332711674!5m2!1sid!2sid" 
          width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy">
        </iframe>
      </div>
      <?php
if (isset($_GET['status']) && $_GET['status'] === 'ok') {
    echo "<p style='color:green;'>Pesan berhasil dikirim!</p>";
} elseif (isset($_GET['status']) && $_GET['status'] === 'error') {
    echo "<p style='color:red;'>Gagal mengirim pesan. Coba lagi.</p>";
}
?>

      <!-- Form Section -->
      <div class="contact-form-container">
        <div class="contact-form">
          <h2>Contact Us</h2>
          <?php if ($status === 'ok'): ?>
            <p style="color:green;">Pesan berhasil dikirim!</p>
          <?php elseif ($status === 'error'): ?>
            <p style="color:red;">Gagal mengirim pesan. Silakan coba lagi.</p>
          <?php endif; ?>

          <form action="" method="post">
            <input type="text" name="name" placeholder="Your Name" required>
            <input type="email" name="email" placeholder="Your Email" required>
            <textarea name="message" rows="5" placeholder="Your Message" required></textarea>
            <button type="submit">Send Message</button>
          </form>
        </div>

        <!-- Office Info -->
        <div class="contact-office-info">
          <strong>Hub DonTeam:</strong><br>
          <p><span class="icon">📞</span> 012.7584.0990</p>
          <p><span class="icon">📧</span> DonTeam@DonGiv.com</p>
          <p><strong>Jam Operasional:</strong> Senin - Jumat pukul 09:00 - 20:00</p>
          Jl. Telekomunikasi No. 1, Bandung Terusan Buahbatu - Bojongsoang, Sukapura, Kec. Dayeuhkolot, Kabupaten Bandung, Jawa Barat 40257<br>
        </div>
      </div>

    </div>
  </div>
</div>
