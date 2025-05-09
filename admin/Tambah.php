<?php
include '../users/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul     = $_POST['judul'];
    $jumlah    = $_POST['jumlah'];
    $tujuan    = $_POST['tujuan'];
    $kategori  = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];

    $query = "INSERT INTO tambah_donasi (judul, jumlah, tujuan_penerima_donasi, kategori, deskripsi)
              VALUES ('$judul', '$jumlah', '$tujuan', '$kategori', '$deskripsi')";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Donasi berhasil ditambahkan!'); window.location.href='Manajemen.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Donasi</title>
    <link rel="stylesheet" href="Tambah.css">
</head>

<body>
    <header class="header">
        <h1>Tambah Donasi Baru</h1>
    </header>

    <div class="form-container">
        <form method="POST" action="">
            <h2>Informasi Donasi</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="judul-donasi">Program Donasi</label>
                    <input type="text" id="judul-donasi" name="judul" placeholder="Contoh: Donasi Buku Pendidikan" required>
                </div>
                <div class="form-group">
                    <label for="jumlah-donasi">Jumlah Donasi (Rupiah)</label>
                    <input type="number" id="jumlah-donasi" name="jumlah" placeholder="Contoh: 5000000" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tujuan-donasi">Tujuan Penerima Donasi</label>
                    <input type="text" id="tujuan-donasi" name="tujuan" placeholder="Contoh: Yayasan Panti Asuhan" required>
                </div>
                <div class="form-group">
                    <label for="kategori-donasi">Kategori Donasi</label>
                    <input type="text" id="kategori-donasi" name="kategori" placeholder="Contoh: Pendidikan, Kesehatan, dll." required>
                </div>
            </div>

            <div class="form-group">
                <label for="deskripsi-donasi">Deskripsi Donasi</label>
                <textarea id="deskripsi-donasi" name="deskripsi" placeholder="Tambahkan catatan atau keterangan..." rows="5"></textarea>
            </div>

            <button type="submit" class="submit-button">Tambah Donasi</button>
        </form>
    </div>
</body>

</html>
