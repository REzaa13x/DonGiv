<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bentuk Kerja Sama</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f2f5; /* Warna latar belakang body secara umum */
        }

        .section-white-background {
            background-color: white;
            padding: 50px 20px; /* Padding atas dan bawah untuk section putih */
            text-align: center;
        }

        .section-title {
            font-size: 2.5em;
            color: #212121;
            margin-bottom: 40px; /* Jarak antara judul dan kotak biru */
            font-weight: bold;
        }

        .content-box-container {
            display: flex;
            justify-content: center;
            align-items: center;
            max-width: 1000px; /* Lebar maksimum container kotak biru */
            margin: 0 auto;
            background-color: #B2EBF2; /* Warna biru muda untuk kotak biru */
            border-radius: 15px;
            padding: 30px 40px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden; /* Penting untuk gambar */
        }

        .content-text {
            flex: 1;
            padding-right: 30px;
            text-align: left; /* Teks di dalam kotak biru rata kiri */
        }

        .content-icon {
            background-color: #00BCD4; /* Warna biru lebih gelap untuk ikon */
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px;
        }
        .content-icon svg {
            fill: white;
            width: 30px;
            height: 30px;
        }

        .content-text h3 {
            font-size: 2em; /* Ukuran judul di dalam kotak biru */
            color: #212121;
            margin-top: 0;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .content-text p {
            font-size: 1.1em;
            color: #424242;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .content-button {
            display: inline-flex;
            align-items: center;
            background-color: #00BCD4; /* Warna tombol */
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .content-button:hover {
            background-color: #008C9E; /* Warna hover tombol */
        }
        .content-button svg {
            fill: white;
            width: 15px;
            height: 15px;
            margin-left: 10px;
        }

        .content-image {
            flex: 1;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-left: -20px; /* Sedikit geser ke kiri agar tangan terlihat "keluar" */
        }
        .content-image img {
            max-width: 120%; /* Membuat gambar sedikit lebih besar dari container */
            height: auto;
            border-radius: 10px; /* Border radius pada gambar jika diinginkan */
        }

        /* Responsif */
        @media (max-width: 768px) {
            .section-title {
                font-size: 2em;
                margin-bottom: 30px;
            }
            .content-box-container {
                flex-direction: column;
                padding: 20px;
                text-align: center;
            }
            .content-text {
                padding-right: 0;
                margin-bottom: 20px;
                text-align: center;
            }
            .content-icon {
                margin: 0 auto 15px auto;
            }
            .content-image {
                margin-left: 0;
                justify-content: center;
            }
            .content-image img {
                max-width: 100%;
            }
            .content-text h3 {
                font-size: 1.8em;
            }
            .content-text p {
                font-size: 1em;
            }
        }
    </style>
</head>
<body>

    <section class="section-white-background" >
        <h2 class="section-title" id="Volunter">Bentuk Kerja Sama yang kita Lakukan Bersama</h2>
        <div class="content-box-container">
            <div class="content-text">
                <div class="content-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 9h-3.5c-.32 0-.58-.1-.76-.28-.18-.18-.27-.43-.27-.72 0-.29.09-.54.27-.72.18-.18.44-.27.76-.27H18c.55 0 1-.45 1-1s-.45-1-1-1h-3.5c-.88 0-1.63.3-2.26.9-.63.6-1.02 1.34-1.16 2.2l-.24 1.5-.1 1.5c.01.27-.08.53-.27.72-.18.18-.44.27-.76.27H6c-.55 0-1 .45-1 1s.45 1 1 1h3.5c.88 0 1.63-.3 2.26-.9.63-.6 1.02-1.34 1.16-2.2l.24-1.5.1-1.5c-.01-.27.08-.53.27-.72.18-.18.44-.27.76-.27H18c.55 0 1-.45 1-1s-.45-1-1-1zM12 15.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                </div>
                <h3>Volunteering</h3>
                <p>Pilih Program yang ingin Anda Dukung</p>
                <a href="../Relawan/program_relawan.php" class="content-button">
                    Selengkapnya
                    <svg viewBox="0 0 24 24">
                        <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
                    </svg>
                </a>
            </div>
            <div class="content-image">
                <img src="../foto/Volunters.jpg" alt="Relawan bekerja sama">
            </div>
        </div>
    </section>

</body>
</html>