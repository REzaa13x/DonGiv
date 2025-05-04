<?php
include '../users/koneksi.php';

$sql = "SELECT subjek, isi, penerima, tanggal_kirim FROM email_kampanye ORDER BY tanggal_kirim DESC";
$result = $conn->query($sql);
?>

<table>
  <thead>
    <tr>
      <th>Avatar</th>
      <th>Nama</th>
      <th>Pesan</th>
      <th>Waktu</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
      <?php
        // Ambil inisial dari penerima misalnya "Semua Donatur" → SD
        $nama = $row['penerima'];
        $words = explode(' ', $nama);
        $inisial = strtoupper(substr($words[0], 0, 1) . substr($words[1] ?? '', 0, 1));

        // Format jam
        $jam = date("g:i A", strtotime($row['tanggal_kirim']));
      ?>
      <tr>
        <td><strong style="color:#<?php echo substr(md5($inisial), 0, 6); ?>"><?php echo $inisial; ?></strong></td>
        <td><?php echo htmlspecialchars($nama); ?></td>
        <td><?php echo htmlspecialchars($row['isi']); ?></td>
        <td><?php echo $jam; ?></td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>
