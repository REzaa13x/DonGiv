<?php
// File: admin/Edit.php

error_reporting(E_ALL); // Melaporkan semua jenis error PHP
ini_set('display_errors', 1); // Menampilkan error di layar

// Include file koneksi database Anda
include '../users/koneksi.php'; // Asumsi: koneksi.php ada di folder 'users' satu tingkat di atas 'admin'

$kampanye = null; // Variabel untuk menyimpan data kampanye yang akan diedit
$message = ''; // Untuk pesan sukses atau error

// Ambil daftar kategori dari database untuk dropdown
$categories = [];
// PASTIKAN NAMA TABEL DAN KOLOM DI BAWAH INI SESUAI DENGAN DATABASE ANDA
$stmt_categories = mysqli_query($conn, "SELECT id_kategori, nama_kategori FROM kategori_donasi ORDER BY nama_kategori ASC");
if ($stmt_categories) {
    while ($cat_row = mysqli_fetch_assoc($stmt_categories)) {
        $categories[] = $cat_row;
    }
} else {
    // Pesan error ini akan muncul di $message jika ada masalah dengan query kategori
    $message .= "Error mengambil kategori: " . mysqli_error($conn) . "<br>";
}

// ==============================================================================
// BAGIAN 1: Mengambil data kampanye yang akan diedit (GET request)
// ==============================================================================
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_kampanye = (int)$_GET['id']; // Konversi ke integer untuk keamanan

    // Query untuk mengambil data kampanye yang akan diedit
    // Lakukan JOIN dengan kategori_donasi agar nama kategori juga tersedia (jika diperlukan untuk tampilan lain)
    $sql_kampanye = "SELECT kd.*, k.nama_kategori 
                     FROM kampanye_donasi kd
                     LEFT JOIN kategori_donasi k ON kd.id_kategori = k.id_kategori
                     WHERE kd.id_donasi = ?";
    $stmt_kampanye = mysqli_prepare($conn, $sql_kampanye);

    if ($stmt_kampanye) {
        mysqli_stmt_bind_param($stmt_kampanye, "i", $id_kampanye); // Bind ID sebagai integer
        mysqli_stmt_execute($stmt_kampanye);
        $result_kampanye = mysqli_stmt_get_result($stmt_kampanye);

        if (mysqli_num_rows($result_kampanye) > 0) {
            $kampanye = mysqli_fetch_assoc($result_kampanye);
            
            // --- Logika untuk memisahkan Deskripsi & Tujuan ---
            // Asumsi format saat menyimpan: "Tujuan: [tujuan]\n\n[deskripsi]"
            $db_description = $kampanye['deskripsi'];
            $tujuan_penerima_edited = ''; // Variabel untuk diisi ke input 'tujuan'
            $deskripsi_kampanye_edited = ''; // Variabel untuk diisi ke textarea 'deskripsi'

            if (strpos($db_description, 'Tujuan:') === 0) {
                // Jika deskripsi dimulai dengan "Tujuan:", coba pisahkan
                $parts = explode("\n\n", $db_description, 2); // Pecah menjadi maksimal 2 bagian pada "\n\n"
                if (count($parts) > 0) {
                    $tujuan_line = trim($parts[0]);
                    if (strpos($tujuan_line, 'Tujuan:') === 0) { // Pastikan baris pertama memang "Tujuan:"
                        $tujuan_penerima_edited = substr($tujuan_line, strlen('Tujuan:')); // Ambil setelah "Tujuan:"
                        $tujuan_penerima_edited = trim($tujuan_penerima_edited); // Hapus spasi di awal/akhir
                    }
                    if (count($parts) > 1) { // Jika ada bagian deskripsi setelah "\n\n"
                        $deskripsi_kampanye_edited = trim($parts[1]);
                    }
                }
            } else {
                // Jika deskripsi tidak diawali dengan "Tujuan:", berarti seluruhnya adalah deskripsi
                $deskripsi_kampanye_edited = $db_description;
            }

            // Jika nilai yang terpisah adalah "Tidak ada deskripsi rinci.", kosongkan untuk form input
            if ($deskripsi_kampanye_edited === 'Tidak ada deskripsi rinci.') {
                $deskripsi_kampanye_edited = '';
            }
            if ($tujuan_penerima_edited === 'Tidak ada deskripsi rinci.') {
                $tujuan_penerima_edited = '';
            }
            // --- Akhir Logika pemisahan ---

        } else {
            $message = "Kampanye donasi tidak ditemukan.";
            // Anda bisa mengarahkan pengguna ke halaman manajemen jika kampanye tidak ditemukan
            // header('Location: Manajemen.php');
            // exit();
        }
        mysqli_stmt_close($stmt_kampanye);
    } else {
        $message = "Error saat menyiapkan statement pengambilan kampanye: " . mysqli_error($conn);
    }
} else {
    $message = "ID kampanye tidak disediakan.";
    // Anda bisa mengarahkan pengguna ke halaman manajemen jika ID tidak ada
    // header('Location: Manajemen.php');
    // exit();
}

// ==============================================================================
// BAGIAN 2: Memproses UPDATE data kampanye (POST request)
// ==============================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_donasi'])) {
    // Pastikan ID yang akan diupdate valid
    $id_donasi_update = (int)$_POST['id_donasi'];

    // Ambil data dari formulir yang disubmit
    $judul_kampanye = mysqli_real_escape_string($conn, $_POST['judul']);
    $target_jumlah = (double) $_POST['jumlah'];
    $tujuan_penerima_post = mysqli_real_escape_string($conn, $_POST['tujuan']); // Ambil dari form 'tujuan'
    $kategori_id = (int) $_POST['kategori'];
    $deskripsi_kampanye_input_post = mysqli_real_escape_string($conn, $_POST['deskripsi']); // Ambil dari form 'deskripsi'
    $tanggal_mulai = mysqli_real_escape_string($conn, $_POST['tanggal_mulai']);
    $tanggal_akhir = !empty($_POST['tanggal_akhir']) ? mysqli_real_escape_string($conn, $_POST['tanggal_akhir']) : NULL;
    $status_kampanye = mysqli_real_escape_string($conn, $_POST['status']); // Ambil nilai status dari form

    // Inisialisasi path gambar, gunakan gambar lama jika tidak ada upload baru
    $image_path_for_db = $kampanye['gambar'] ?? ''; // Jika $kampanye masih null karena error GET, pastikan default kosong

    // Gabungkan tujuan dan deskripsi kembali untuk disimpan ke kolom 'deskripsi' di DB
    $final_deskripsi_for_db = "";
    if (!empty(trim($tujuan_penerima_post))) {
        $final_deskripsi_for_db .= "Tujuan: " . $tujuan_penerima_post;
    }
    if (!empty(trim($deskripsi_kampanye_input_post))) {
        if (!empty($final_deskripsi_for_db)) { 
            $final_deskripsi_for_db .= "\n\n"; // Tambahkan 2 newline sebagai pemisah
        }
        $final_deskripsi_for_db .= $deskripsi_kampanye_input_post;
    }
    // Jika keduanya kosong, berikan placeholder agar tidak menjadi "0" di DB
    if (empty(trim($final_deskripsi_for_db))) {
        $final_deskripsi_for_db = "Tidak ada deskripsi rinci.";
    }


    // Logika upload gambar baru (mirip dengan tambah.php)
    if (isset($_FILES['gambar_kampanye']) && $_FILES['gambar_kampanye']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../uploads/campaign_images/"; // Folder untuk menyimpan gambar kampanye
        if (!is_dir($target_dir)) { 
            mkdir($target_dir, 0777, true); // Buat folder jika belum ada
        }

        $image_name = basename($_FILES["gambar_kampanye"]["name"]);
        $target_file = $target_dir . uniqid() . "_" . $image_name; // Buat nama file unik
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Cek apakah file adalah gambar asli
        $check = getimagesize($_FILES["gambar_kampanye"]["tmp_name"]);
        if($check === false) { // Jika bukan gambar
            $message = "File yang diunggah bukan gambar.";
        } elseif ($_FILES["gambar_kampanye"]["size"] > 5000000) { // Ukuran file (5MB)
            $message = "Maaf, ukuran file gambar terlalu besar (maks 5MB).";
        } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) { // Format file
            $message = "Maaf, hanya format JPG, JPEG, PNG & GIF yang diizinkan.";
        } else {
            // Jika semua validasi lolos, coba upload
            if (move_uploaded_file($_FILES["gambar_kampanye"]["tmp_name"], $target_file)) {
                $image_path_for_db = 'uploads/campaign_images/' . basename($target_file); // Path relatif untuk DB
                // Opsional: Hapus gambar lama jika ada dan berhasil upload gambar baru
                if (!empty($kampanye['gambar']) && file_exists('../' . $kampanye['gambar'])) {
                    unlink('../' . $kampanye['gambar']); // Hapus file fisik gambar lama
                }
            } else {
                $message = "Maaf, terjadi kesalahan saat mengunggah gambar Anda.";
                error_log("File upload error (Edit.php): " . $_FILES["gambar_kampanye"]["error"]); // Log error ke file
            }
        }
    } elseif (isset($_FILES['gambar_kampanye']) && $_FILES['gambar_kampanye']['error'] != UPLOAD_ERR_NO_FILE) {
        // Tangani error upload lainnya (misalnya, file terlalu besar dari php.ini)
        $message = "Terjadi error saat upload file: " . $_FILES['gambar_kampanye']['error'];
        switch ($_FILES['gambar_kampanye']['error']) {
            case UPLOAD_ERR_INI_SIZE: $message .= " (Ukuran file melebihi batas upload server)."; break;
            case UPLOAD_ERR_FORM_SIZE: $message .= " (Ukuran file melebihi batas form HTML)."; break;
            case UPLOAD_ERR_PARTIAL: $message .= " (File hanya terunggah sebagian)."; break;
            case UPLOAD_ERR_NO_TMP_DIR: $message .= " (Folder sementara tidak ada)."; break;
            case UPLOAD_ERR_CANT_WRITE: $message .= " (Gagal menulis file ke disk)."; break;
            case UPLOAD_ERR_EXTENSION: $message .= " (Ekstensi PHP menghentikan upload file)."; break;
        }
    }
    // Jika tidak ada file yang diupload ($image_path_for_db akan tetap menggunakan path lama), tidak ada pesan error dari sini

    // Hanya lanjutkan proses UPDATE jika tidak ada error dari upload gambar (atau jika tidak ada gambar yang diupload)
    if (empty($message)) { // Periksa kembali $message
        $query = "UPDATE kampanye_donasi SET
                            id_kategori = ?,
                            nama_donasi = ?,
                            deskripsi = ?,
                            target_dana = ?,
                            status = ?,
                            updated_at = NOW(),
                            tanggal_mulai = ?,
                            tanggal_akhir = ?,
                            gambar = ?
                  WHERE id_donasi = ?";

        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            // String format binding:
            // i (id_kategori)
            // s (nama_donasi)
            // s (deskripsi)
            // d (target_dana)
            // s (status)
            // s (tanggal_mulai)
            // s (tanggal_akhir)
            // s (gambar)
            // i (id_donasi untuk WHERE clause)
            mysqli_stmt_bind_param(
                $stmt, "issdssssi", 
                $kategori_id,
                $judul_kampanye,
                $final_deskripsi_for_db,
                $target_jumlah,
                $status_kampanye, // Status baru
                $tanggal_mulai,
                $tanggal_akhir,
                $image_path_for_db,
                $id_donasi_update
            );

            if (mysqli_stmt_execute($stmt)) {
                // Redirect ke halaman manajemen dengan pesan sukses
                echo "<script>alert('Kampanye donasi berhasil diperbarui!'); window.location.href='Manajemen.php?status=success&msg=Kampanye+berhasil+diperbarui&_t=" . time() . "';</script>";
            } else {
                $message = "Error saat memperbarui data: " . mysqli_error($conn) . " | STMT Error: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $message = "Error saat menyiapkan statement update: " . mysqli_error($conn);
        }
    }
    // Tutup koneksi database setelah semua proses selesai
    mysqli_close($conn); 
    // Menggunakan exit() setelah redirect dengan JavaScript untuk memastikan script berhenti.
    // Ini penting agar tidak ada output HTML lain yang dikirim setelah alert dan redirect.
    if (!empty($message) && strpos($message, 'berhasil') === false) {
        // Jika ada pesan error (dan bukan pesan sukses), biarkan tampil di halaman.
    } else {
        exit(); // Hentikan eksekusi script setelah redirect berhasil
    }
}

// Tutup koneksi jika ini adalah GET request dan tidak ada POST yang terjadi
// Atau jika proses POST selesai dan sudah redirect/exit.
// Pastikan $conn masih ada sebelum mencoba menutupnya.
if (isset($conn) && $conn) {
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kampanye Donasi</title>
    <link rel="stylesheet" href="Tambah.css"> 
    <style>
        /* Styling Anda, bisa disesuaikan atau dipisah ke file CSS lain */
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
        <h1>Edit Kampanye Donasi</h1>
    </header>

    <div class="form-container">
        <?php if ($kampanye): // Pastikan data kampanye ditemukan sebelum menampilkan form ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="id_donasi" value="<?= htmlspecialchars($kampanye['id_donasi']) ?>">

            <h2>Informasi Kampanye Donasi</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="judul-donasi">Judul Kampanye Donasi</label>
                    <input type="text" id="judul-donasi" name="judul" placeholder="Contoh: Donasi Buku Pendidikan" 
                           value="<?= htmlspecialchars($kampanye['nama_donasi']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="jumlah-donasi">Target Dana (Rupiah)</label>
                    <input type="number" id="jumlah-donasi" name="jumlah" placeholder="Contoh: 5000000" 
                           value="<?= htmlspecialchars($kampanye['target_dana']) ?>" required min="0">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tujuan-donasi">Tujuan Penerima Donasi</label>
                    <input type="text" id="tujuan-donasi" name="tujuan" placeholder="Contoh: Yayasan Panti Asuhan" 
                           value="<?= htmlspecialchars($tujuan_penerima_edited) ?>" required> 
                </div>
                <div class="form-group">
                    <label for="kategori-donasi">Kategori Donasi</label>
                    <select id="kategori-donasi" name="kategori" required>
                        <option value="">Pilih kategori yang sesuai.</option>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['id_kategori']) ?>"
                                    <?= ($cat['id_kategori'] == $kampanye['id_kategori']) ? 'selected' : '' ?>> 
                                    <?= htmlspecialchars($cat['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Tidak ada kategori tersedia</option>
                        <?php endif; ?>
                    </select>
                    <small>Pilih kategori yang sesuai.</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="status-kampanye">Status Kampanye</label>
                    <select id="status-kampanye" name="status" required>
                        <option value="active" <?= ($kampanye['status'] == 'active') ? 'selected' : '' ?>>Aktif</option>
                        <option value="closed" <?= ($kampanye['status'] == 'closed') ? 'selected' : '' ?>>Ditutup</option>
                        <option value="completed" <?= ($kampanye['status'] == 'completed') ? 'selected' : '' ?>>Selesai</option>
                    </select>
                    <small>Atur status kampanye.</small>
                </div>
                <div class="form-group">
                    <label for="tanggal-mulai">Tanggal Mulai Kampanye</label>
                    <input type="date" id="tanggal-mulai" name="tanggal_mulai" 
                           value="<?= htmlspecialchars($kampanye['tanggal_mulai']) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tanggal-akhir">Tanggal Berakhir Kampanye (Opsional)</label>
                    <input type="date" id="tanggal-akhir" name="tanggal_akhir" 
                           value="<?= htmlspecialchars($kampanye['tanggal_akhir'] ?? '') ?>"> 
                </div>
                <div class="form-group">
                    <label for="gambar-kampanye">Gambar Kampanye</label>
                    <input type="file" id="gambar-kampanye" name="gambar_kampanye" accept="image/*">
                    <small>Biarkan kosong untuk mempertahankan gambar lama. Maks 5MB.</small>
                    <?php if (!empty($kampanye['gambar'])): ?>
                        <br>
                        <img src="../<?= htmlspecialchars($kampanye['gambar']) ?>" alt="Gambar Saat Ini" style="max-width: 150px; margin-top: 10px; border-radius: 5px;">
                        <small>Gambar saat ini.</small>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="deskripsi-donasi">Deskripsi Kampanye Donasi</label>
                <textarea id="deskripsi-donasi" name="deskripsi" placeholder="Tambahkan catatan atau keterangan detail tentang kampanye..." rows="5"><?= htmlspecialchars($deskripsi_kampanye_edited) ?></textarea> 
            </div>

            <button type="submit" class="submit-button">Simpan Perubahan</button>
        </form>
        <?php else: // Jika kampanye tidak ditemukan, tampilkan pesan error ?>
            <p class="error-message"><?php echo $message; ?></p>
        <?php endif; ?>

        <?php if (!empty($message) && strpos($message, 'berhasil') === false): // Tampilkan pesan error setelah form jika ada error (bukan pesan berhasil) ?>
            <p class="error-message"><?php echo $message; ?></p>
        <?php endif; ?>
    </div>
</body>

</html>