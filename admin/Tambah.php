<?php
// File: admin/tambah.php

error_reporting(E_ALL); // Melaporkan semua jenis error PHP
ini_set('display_errors', 1); // Menampilkan error di layar

// Pastikan hanya admin yang bisa mengakses halaman ini (sangat disarankan untuk keamanan)
// session_start();
// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: login_admin.php'); // Ganti dengan halaman login admin Anda
//     exit();
// }

// Include file koneksi database Anda
include '../users/koneksi.php'; 

$message = ''; // Variabel untuk menyimpan pesan sukses atau error

// Ambil daftar kategori dari database untuk dropdown
$categories = [];
// PASTIKAN NAMA TABEL DAN KOLOM DI BAWAH INI SESUAI DENGAN DATABASE ANDA
// Sesuai dengan screenshot Anda, nama tabelnya adalah 'kategori_donasi'
$stmt_categories = mysqli_query($conn, "SELECT id_kategori, nama_kategori FROM kategori_donasi ORDER BY nama_kategori ASC");
if ($stmt_categories) {
    while ($cat_row = mysqli_fetch_assoc($stmt_categories)) {
        $categories[] = $cat_row;
    }
} else {
    // Pesan error ini akan muncul di $message jika ada masalah dengan query kategori
    $message .= "Error mengambil kategori: " . mysqli_error($conn) . "<br>";
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Ambil data dari formulir dan lakukan konversi tipe data eksplisit
    $judul_kampanye = mysqli_real_escape_string($conn, $_POST['judul']);
    $target_jumlah = (double) $_POST['jumlah']; 
    $tujuan_penerima = mysqli_real_escape_string($conn, $_POST['tujuan']);
    $kategori_id = (int) $_POST['kategori']; 
    $deskripsi_kampanye_input = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $tanggal_mulai = mysqli_real_escape_string($conn, $_POST['tanggal_mulai']);
    // Tanggal akhir bisa NULL di database, jadi pastikan dikirim sebagai NULL jika kosong
    $tanggal_akhir = !empty($_POST['tanggal_akhir']) ? mysqli_real_escape_string($conn, $_POST['tanggal_akhir']) : NULL;

    $image_path_for_db = ''; // Variabel untuk menyimpan path gambar yang diupload, akan masuk ke kolom 'gambar'

    // Gabungkan tujuan ke deskripsi
    $final_deskripsi_for_db = "";
    if (!empty(trim($tujuan_penerima))) {
        $final_deskripsi_for_db .= "Tujuan: " . $tujuan_penerima;
    }
    if (!empty(trim($deskripsi_kampanye_input))) {
        if (!empty($final_deskripsi_for_db)) { 
            $final_deskripsi_for_db .= "\n\n"; // Tambahkan 2 newline untuk pemisah
        }
        $final_deskripsi_for_db .= $deskripsi_kampanye_input;
    }

    // Jika keduanya kosong, berikan placeholder agar tidak menjadi "0" di DB
    if (empty(trim($final_deskripsi_for_db))) {
        $final_deskripsi_for_db = "Tidak ada deskripsi rinci.";
    }


    // 2. LOGIKA UPLOAD GAMBAR
    // Perbaikan: Tambahkan pemeriksaan gambar sebelum mengupload
    if (isset($_FILES['gambar_kampanye']) && $_FILES['gambar_kampanye']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../uploads/campaign_images/"; // Folder untuk menyimpan gambar kampanye
        if (!is_dir($target_dir)) { 
            mkdir($target_dir, 0777, true); // Pastikan folder ada dan bisa ditulis
        }

        $image_name = basename($_FILES["gambar_kampanye"]["name"]);
        $target_file = $target_dir . uniqid() . "_" . $image_name; // Nama file unik
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Cek apakah file adalah gambar asli
        $check = getimagesize($_FILES["gambar_kampanye"]["tmp_name"]); // <-- Definisi $check di sini
        if($check === false) { // Jika bukan gambar
            $message = "File yang diunggah bukan gambar.";
        } elseif ($_FILES["gambar_kampanye"]["size"] > 5000000) { // 5MB
            $message = "Maaf, ukuran file gambar terlalu besar (maks 5MB).";
        } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $message = "Maaf, hanya format JPG, JPEG, PNG & GIF yang diizinkan.";
        } else {
            // Jika semua validasi lolos, coba upload
            if (move_uploaded_file($_FILES["gambar_kampanye"]["tmp_name"], $target_file)) {
                $image_path_for_db = 'uploads/campaign_images/' . basename($target_file); // Path relatif untuk DB
            } else {
                $message = "Maaf, terjadi kesalahan saat mengunggah gambar Anda.";
                // Untuk debugging, bisa tambahkan: error_log("File upload error: " . $_FILES["gambar_kampanye"]["error"]);
            }
        }
    } elseif (isset($_FILES['gambar_kampanye']) && $_FILES['gambar_kampanye']['error'] != UPLOAD_ERR_NO_FILE) {
        // Tangani error upload lainnya (misalnya, file terlalu besar dari php.ini)
        $message = "Terjadi error saat upload file: " . $_FILES['gambar_kampanye']['error'];
        switch ($_FILES['gambar_kampanye']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $message .= " (Ukuran file melebihi batas upload server).";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message .= " (File hanya terunggah sebagian).";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message .= " (Folder sementara tidak ada).";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message .= " (Gagal menulis file ke disk).";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message .= " (Ekstensi PHP menghentikan upload file).";
                break;
        }
    }
    // Jika tidak ada file yang diupload ($image_path_for_db akan tetap kosong), tidak ada pesan error

    // Hanya lanjutkan proses INSERT jika tidak ada error dari upload gambar (atau jika tidak ada gambar yang diupload)
    if (empty($message)) { // Periksa lagi $message
        $query = "INSERT INTO kampanye_donasi (
                                id_kategori, nama_donasi, deskripsi, target_dana, dana_terkumpul,
                                status, created_at, updated_at, tanggal_mulai, tanggal_akhir,
                                gambar
                            ) VALUES (?, ?, ?, ?, 0, 'active', NOW(), NOW(), ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            // String format binding: i (int), s (string), s (string), d (double), s (string), s (string), s (string)
            mysqli_stmt_bind_param(
                $stmt, "issdsss", 
                $kategori_id,
                $judul_kampanye,
                $final_deskripsi_for_db, 
                $target_jumlah,
                $tanggal_mulai,
                $tanggal_akhir,
                $image_path_for_db
            );

            if (mysqli_stmt_execute($stmt)) {
                echo "<script>alert('Kampanye donasi berhasil ditambahkan!'); window.location.href='Manajemen.php?_t=" . time() . "';</script>";
            } else {
                $message = "Error saat menyimpan data: " . mysqli_error($conn) . " | STMT Error: " . mysqli_stmt_error($stmt);
            }

            mysqli_stmt_close($stmt);
        } else {
            $message = "Error saat menyiapkan statement: " . mysqli_error($conn);
        }
    }

    // Tutup koneksi database setelah semua proses selesai
    mysqli_close($conn); 
    // Menggunakan exit() setelah redirect dengan JavaScript untuk memastikan script berhenti.
    // Ini penting agar tidak ada output HTML lain yang dikirim setelah alert dan redirect.
    if (!empty($message) && strpos($message, 'berhasil') === false) {
        // Hanya tampilkan pesan error jika memang ada error
    } else {
        exit(); // Hentikan eksekusi script setelah redirect berhasil
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kampanye Donasi</title>
    <link rel="stylesheet" href="Tambah.css">
    <style>
        /* Styling Anda */
        body { font-family: 'Poppins', sans-serif; background-color: #f4f4f9; margin: 0; padding: 0; display: flex; flex-direction: column; align-items: center; min-height: 100vh; }
        .header { background-color: #1E3A8A; color: white; padding: 20px; width: 100%; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 90%; max-width: 800px; margin: 20px auto; }
        .form-container h2 { text-align: center; color: #333; margin-bottom: 20px; }
        .form-row { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 15px; }
        .form-group { flex: 1; min-width: 300px; margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        .form-group input[type="text"], .form-group input[type="number"], .form-group input[type="date"], .form-group input[type="file"], .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .form-group textarea { resize: vertical; }
        .submit-button { background-color: #4CAF50; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; width: 100%; transition: background-color 0.3s ease; }
        .submit-button:hover { background-color: #45a049; }
        .form-group small { color: #777; font-size: 0.9em; margin-top: 5px; display: block; }
        p.error-message { color: red; text-align: center; margin-top: 10px; }
    </style>
</head>

<body>
    <header class="header">
        <h1>Tambah Kampanye Donasi Baru</h1>
    </header>

    <div class="form-container">
        <form method="POST" action="" enctype="multipart/form-data">
            <h2>Informasi Kampanye Donasi</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="judul-donasi">Judul Kampanye Donasi</label>
                    <input type="text" id="judul-donasi" name="judul" placeholder="Contoh: Donasi Buku Pendidikan" required>
                </div>
                <div class="form-group">
                    <label for="jumlah-donasi">Target Dana (Rupiah)</label>
                    <input type="number" id="jumlah-donasi" name="jumlah" placeholder="Contoh: 5000000" required min="0">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tujuan-donasi">Tujuan Penerima Donasi</label>
                    <input type="text" id="tujuan-donasi" name="tujuan" placeholder="Contoh: Yayasan Panti Asuhan" required>
                </div>
                <div class="form-group">
                    <label for="kategori-donasi">Kategori Donasi</label>
                    <select id="kategori-donasi" name="kategori" required>
                        <?php if (empty($categories)): ?>
                            <option value="">Tidak ada kategori tersedia</option>
                        <?php else: ?>
                            <option value="">Pilih kategori yang sesuai.</option> <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['id_kategori']) ?>">
                                    <?= htmlspecialchars($cat['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small>Pilih kategori yang sesuai.</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tanggal-mulai">Tanggal Mulai Kampanye</label>
                    <input type="date" id="tanggal-mulai" name="tanggal_mulai" required>
                </div>
                <div class="form-group">
                    <label for="tanggal-akhir">Tanggal Berakhir Kampanye (Opsional)</label>
                    <input type="date" id="tanggal-akhir" name="tanggal_akhir">
                </div>
            </div>

            <div class="form-group">
                <label for="gambar-kampanye">Gambar Kampanye</label>
                <input type="file" id="gambar-kampanye" name="gambar_kampanye" accept="image/*">
                <small>Pilih gambar untuk kampanye Anda (JPG, JPEG, PNG, GIF). Maks 5MB.</small>
            </div>

            <div class="form-group">
                <label for="deskripsi-donasi">Deskripsi Kampanye Donasi</label>
                <textarea id="deskripsi-donasi" name="deskripsi" placeholder="Tambahkan catatan atau keterangan detail tentang kampanye..." rows="5"></textarea>
            </div>

            <button type="submit" class="submit-button">Tambah Kampanye Donasi</button>
        </form>
        <?php if (!empty($message)): ?>
            <p class="error-message"><?php echo $message; ?></p>
        <?php endif; ?>
    </div>
</body>

</html>