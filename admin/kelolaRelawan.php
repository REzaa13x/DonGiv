<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel Relawan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #00BCD4; /* Biru terang */
            --primary-dark: #008C9E; /* Biru gelap untuk hover */
            --secondary-color: #4CAF50; /* Hijau */
            --danger-color: #F44336; /* Merah untuk hapus */
            --warning-color: #FFC107; /* Kuning untuk pending */
            --info-color: #2196F3; /* Biru untuk completed */
            --text-dark: #212121;
            --text-medium: #424242;
            --text-light: #757575;
            --bg-light: #f0f2f5;
            --bg-white: #ffffff;
            --border-color: #e0e0e0;
            --shadow-light: 0 2px 5px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
            display: flex;
            min-height: 100vh;
            color: var(--text-medium);
        }

        /* Sidebar */
        .sidebar {
            width: 220px; /* Diperbarui: Lebar sidebar lebih ramping */
            background-color: var(--text-dark);
            color: var(--bg-white);
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100%;
            z-index: 1000;
        }

        .sidebar .logo {
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.8em;
            font-weight: 700;
            color: var(--primary-color);
        }

        .sidebar nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar nav ul li {
            margin-bottom: 10px;
        }

        .sidebar nav ul li a {
            color: var(--bg-white);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 15px 20px; /* Padding diperbesar */
            border-radius: 8px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .sidebar nav ul li a:hover,
        .sidebar nav ul li a.active {
            background-color: var(--primary-color);
            color: var(--text-dark);
        }

        .sidebar nav ul li a svg {
            margin-right: 12px; /* Jarak ikon dari teks diperbesar */
            fill: currentColor; /* Agar warna ikon mengikuti warna teks */
            width: 20px;
            height: 20px;
        }

        /* Main Content */
        .main-content {
            flex-grow: 1;
            margin-left: 220px; /* Diperbarui: Sesuaikan dengan lebar sidebar yang baru */
            padding: 30px;
            background-color: var(--bg-light);
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .header h1 {
            font-size: 2em;
            color: var(--text-dark);
            margin: 0;
        }

        .user-profile {
            display: flex;
            align-items: center;
        }

        .user-profile span {
            margin-right: 15px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-color);
        }

        /* Cards/Widgets */
        .cards-container {
            display: grid;
            /* Diperbarui: Cards akan mencoba sebaris, dengan lebar minimum 200px */
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .card {
            background-color: var(--bg-white);
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow-light);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card .icon {
            font-size: 2.5em;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .card h3 {
            font-size: 1.2em;
            color: var(--text-light);
            margin-top: 0;
            margin-bottom: 5px;
            font-weight: 400;
        }

        .card .value {
            font-size: 2.2em;
            font-weight: 700;
            color: var(--text-dark);
        }

        /* Charts Section */
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .chart-card {
            background-color: var(--bg-white);
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow-medium);
            height: 350px; /* Tinggi tetap untuk kartu diagram */
            display: flex;
            flex-direction: column;
            justify-content: center; /* Pusatkan konten vertikal */
            align-items: center; /* Pusatkan konten horizontal */
        }

        .chart-card h2 {
            font-size: 1.5em;
            color: var(--text-dark);
            margin-top: 0;
            margin-bottom: 20px;
        }

        /* Pastikan canvas mengisi ruang yang tersedia di dalam chart-card */
        canvas {
            max-width: 100% !important;
            max-height: 100% !important;
            width: 100% !important; /* Penting untuk responsif */
            height: 100% !important; /* Penting untuk responsif */
        }


        /* Table Section */
        .section-table {
            background-color: var(--bg-white);
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow-medium);
            margin-bottom: 30px;
        }

        .section-table h2 {
            font-size: 1.8em;
            color: var(--text-dark);
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
        }

        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .table-controls .search-box {
            display: flex;
            align-items: center;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 5px 10px;
            background-color: var(--bg-white);
        }

        .table-controls .search-box input {
            border: none;
            outline: none;
            padding: 8px;
            font-size: 1em;
            width: 200px;
        }

        .table-controls .search-box svg {
            fill: var(--text-light);
            width: 20px;
            height: 20px;
            margin-right: 5px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .data-table th,
        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
            text-align: left;
        }

        .data-table th {
            background-color: var(--bg-light);
            color: var(--text-dark);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr:hover {
            background-color: #f5f5f5;
        }

        .data-table .status {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85em;
            font-weight: 600;
            text-align: center;
            display: inline-block;
        }

        .status.active { background-color: #e8f5e9; color: var(--secondary-color); } /* Light Green */
        .status.pending { background-color: #fffde7; color: var(--warning-color); } /* Light Yellow */
        .status.inactive { background-color: #ffebee; color: var(--danger-color); } /* Light Red */
        .status.completed { background-color: #e3f2fd; color: var(--info-color); } /* Light Blue */
        .status.draft { background-color: #F3E5F5; color: #9C27B0; } /* Light Purple */


        .actions-buttons button {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            margin: 0 3px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .actions-buttons button svg {
            width: 18px;
            height: 18px;
            fill: var(--text-light);
        }

        .actions-buttons button:hover {
            background-color: var(--bg-light);
        }

        .actions-buttons button.edit:hover svg { fill: var(--primary-color); }
        .actions-buttons button.delete:hover svg { fill: var(--danger-color); }
        .actions-buttons button.view:hover svg { fill: var(--info-color); }


        /* Form Section */
        .section-form {
            background-color: var(--bg-white);
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow-medium);
            margin-bottom: 30px;
        }

        .section-form h2 {
            font-size: 1.8em;
            color: var(--text-dark);
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="date"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select {
            width: calc(100% - 20px);
            padding: 12px 10px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1em;
            color: var(--text-medium);
            transition: border-color 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="email"]:focus,
        .form-group input[type="tel"]:focus,
        .form-group input[type="date"]:focus,
        .form-group input[type="number"]:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .button-group {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
            text-decoration: none; /* Untuk link sebagai button */
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--bg-white);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: var(--bg-light);
            color: var(--text-dark);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background-color: var(--border-color);
        }

        .btn-add {
            background-color: var(--secondary-color);
            color: var(--bg-white);
            margin-bottom: 20px;
        }

        .btn-add:hover {
            background-color: #388E3C; /* Darker Green */
        }
        .btn-add svg {
            margin-right: 8px;
            fill: currentColor;
            width: 18px;
            height: 18px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 90px; /* Diperbarui: Lebar sidebar lebih ramping untuk mobile */
                padding: 15px 0;
            }
            .sidebar .logo {
                font-size: 1.2em;
                word-break: break-all;
                margin-bottom: 20px;
                padding: 0 5px;
            }
            .sidebar nav ul li a {
                justify-content: center;
                padding: 10px 0;
            }
            .sidebar nav ul li a span {
                display: none;
            }
            .sidebar nav ul li a svg {
                margin-right: 0;
            }
            .main-content {
                margin-left: 90px; /* Diperbarui: Sesuaikan dengan lebar sidebar mobile */
                padding: 20px;
            }
            .header h1 {
                font-size: 1.8em;
            }
            .user-profile span {
                display: none;
            }
            .cards-container {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            .charts-container {
                grid-template-columns: 1fr; /* Di layar lebih kecil, grafik menumpuk */
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                box-shadow: none;
                flex-direction: row;
                justify-content: space-around;
                padding: 15px 0;
            }
            .sidebar .logo {
                display: none;
            }
            .sidebar nav {
                width: 100%;
            }
            .sidebar nav ul {
                display: flex;
                justify-content: space-around;
            }
            .sidebar nav ul li {
                margin-bottom: 0;
            }
            .sidebar nav ul li a {
                padding: 10px;
            }
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            .header h1 {
                margin-bottom: 15px;
            }
            .user-profile {
                width: 100%;
                justify-content: flex-end;
            }
            .cards-container {
                grid-template-columns: 1fr;
            }
            .table-controls {
                flex-direction: column;
                align-items: flex-start;
            }
            .table-controls .search-box input {
                width: 100%;
            }
            .data-table th, .data-table td {
                padding: 8px 10px;
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo">Admin Panel</div>
        <nav>
            <ul>
                <li><a href="#dashboard" class="active">
                    <svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V7h-8v14zm0-18v4h8V3h-8z"/></svg>
                    <span>Dashboard</span>
                </a></li>
                <li><a href="#volunteers">
                    <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    <span>Relawan</span>
                </a></li>
                <li><a href="#programs">
                    <svg viewBox="0 0 24 24"><path d="M14 10H2v2h12v-2zm0-4H2v2h12V6zm4 8v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zM2 16h8v-2H2v2z"/></svg>
                    <span>Program</span>
                </a></li>
                <li><a href="#messages">
                    <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>
                    <span>Pesan</span>
                </a></li>
                <li><a href="#reports">
                    <svg viewBox="0 0 24 24"><path d="M19 19H5V5h14v14zm-2-10h-4V7h4v2zm-6 4H7v-2h4v2zm2 4h-4v-2h4v2z"/></svg>
                    <span>Laporan</span>
                </a></li>
                <li><a href="#settings">
                    <svg viewBox="0 0 24 24"><path d="M19.43 12.98c.04-.32.07-.64.07-.98s-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.09-.76-1.71-1.05l-.34-2.5c-.05-.24-.27-.42-.5-.42h-4c-.23 0-.45.18-.5.42l-.34 2.5c-.62.29-1.19.65-1.71 1.05l-2.49-1c-.22-.09-.49 0-.61.22l-2 3.46c-.12.22-.07.49.12.64l2.11 1.65c-.04.32-.07.64-.07.98s.03.66.07.98l-2.11 1.65c-.19.15-.24-.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.09.76 1.71 1.05l.34 2.5c.05.24.27.42.5.42h4c.23 0 .45-.18.5-.42l-.34-2.5c.62-.29 1.19-.65 1.71-1.05l2.49 1c.22.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z"/></svg>
                    <span>Pengaturan</span>
                </a></li>
            </ul>
        </nav>
    </aside>

    <div class="main-content">
        <header class="header">
            <h1>Dashboard</h1>
            <div class="user-profile">
                <span>Halo, Admin!</span>
                <img src="https://via.placeholder.com/40/00BCD4/FFFFFF?text=A" alt="Admin Profile Picture">
            </div>
        </header>

        <section id="dashboard-content">
            <div class="cards-container">
                <div class="card">
                    <div class="icon">&#128100;</div> <h3>Total Relawan</h3>
                    <div class="value">1,250</div>
                </div>
                <div class="card">
                    <div class="icon">&#128221;</div> <h3>Pendaftar Baru</h3>
                    <div class="value">15</div>
                </div>
                <div class="card">
                    <div class="icon">&#128200;</div> <h3>Program Aktif</h3>
                    <div class="value">8</div>
                </div>
                <div class="card">
                    <div class="icon">&#128172;</div> <h3>Pesan Baru</h3>
                    <div class="value">5</div>
                </div>
            </div>

            <div class="charts-container">
                <div class="chart-card">
                    <h2>Relawan Berdasarkan Minat</h2>
                    <canvas id="interestDoughnutChart"></canvas>
                </div>
                <div class="chart-card">
                    <h2>Partisipasi Program Bulanan</h2>
                    <canvas id="monthlyBarChart"></canvas>
                </div>
            </div>
        </section>

        <section class="section-table" id="volunteers" style="display: none;">
            <div class="table-controls">
                <h2>Daftar Relawan</h2>
                <div class="search-box">
                    <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                    <input type="text" id="volunteerSearch" placeholder="Cari relawan...">
                </div>
                <button class="btn btn-add" onclick="showForm('add-volunteer-form', 'Tambah Relawan Baru')">
                    <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                    Tambah Relawan
                </button>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Minat</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>VOL001</td>
                            <td>Andi Pratama</td>
                            <td>andi.pratama@email.com</td>
                            <td>08123456789</td>
                            <td>Lingkungan</td>
                            <td><span class="status active">Aktif</span></td>
                            <td class="actions-buttons">
                                <button class="view" title="Lihat"><svg viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zm0 13c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg></button>
                                <button class="edit" title="Edit"><svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg></button>
                                <button class="delete" title="Hapus"><svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg></button>
                            </td>
                        </tr>
                        <tr>
                            <td>VOL002</td>
                            <td>Budi Santoso</td>
                            <td>budi.santoso@email.com</td>
                            <td>08765432109</td>
                            <td>Pendidikan</td>
                            <td><span class="status pending">Pending</span></td>
                            <td class="actions-buttons">
                                <button class="view" title="Lihat"><svg viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zm0 13c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg></button>
                                <button class="edit" title="Edit"><svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg></button>
                                <button class="delete" title="Hapus"><svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg></button>
                            </td>
                        </tr>
                        <tr>
                            <td>VOL003</td>
                            <td>Citra Dewi</td>
                            <td>citra.dewi@email.com</td>
                            <td>08551234567</td>
                            <td>Kesehatan</td>
                            <td><span class="status inactive">Tidak Aktif</span></td>
                            <td class="actions-buttons">
                                <button class="view" title="Lihat"><svg viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zm0 13c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg></button>
                                <button class="edit" title="Edit"><svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg></button>
                                <button class="delete" title="Hapus"><svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg></button>
                            </td>
                        </tr>
                        <tr>
                            <td>VOL004</td>
                            <td>Dewi Lestari</td>
                            <td>dewi.lestari@email.com</td>
                            <td>08112233445</td>
                            <td>Lingkungan</td>
                            <td><span class="status active">Aktif</span></td>
                            <td class="actions-buttons">
                                <button class="view" title="Lihat"><svg viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zm0 13c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg></button>
                                <button class="edit" title="Edit"><svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg></button>
                                <button class="delete" title="Hapus"><svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="section-form" id="add-volunteer-form" style="display: none;">
            <h2>Tambah Relawan Baru</h2>
            <form>
                <div class="form-group">
                    <label for="fullName">Nama Lengkap</label>
                    <input type="text" id="fullName" name="fullName" placeholder="Masukkan nama lengkap" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Masukkan alamat email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Telepon</label>
                    <input type="tel" id="phone" name="phone" placeholder="Masukkan nomor telepon">
                </div>
                <div class="form-group">
                    <label for="dob">Tanggal Lahir</label>
                    <input type="date" id="dob" name="dob">
                </div>
                <div class="form-group">
                    <label for="address">Alamat Lengkap</label>
                    <textarea id="address" name="address" placeholder="Masukkan alamat relawan"></textarea>
                </div>
                <div class="form-group">
                    <label for="interest">Minat Utama</label>
                    <select id="interest" name="interest">
                        <option value="">Pilih minat</option>
                        <option value="lingkungan">Lingkungan</option>
                        <option value="pendidikan">Pendidikan</option>
                        <option value="kesehatan">Kesehatan</option>
                        <option value="sosial">Sosial & Kemanusiaan</option>
                        <option value="senibudaya">Seni & Budaya</option>
                        <option value="teknologi">Teknologi</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="skills">Keterampilan (Pisahkan dengan koma)</label>
                    <textarea id="skills" name="skills" placeholder="Contoh: Desain Grafis, Public Speaking, Penulisan"></textarea>
                </div>
                 <div class="form-group">
                    <label for="emergencyContactName">Nama Kontak Darurat</label>
                    <input type="text" id="emergencyContactName" name="emergencyContactName" placeholder="Nama kontak darurat">
                </div>
                <div class="form-group">
                    <label for="emergencyContactPhone">Telepon Kontak Darurat</label>
                    <input type="tel" id="emergencyContactPhone" name="emergencyContactPhone" placeholder="Nomor telepon kontak darurat">
                </div>
                <div class="button-group">
                    <button type="button" class="btn btn-secondary" onclick="hideForm('add-volunteer-form', 'volunteers', 'Relawan')">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Relawan</button>
                </div>
            </form>
        </section>

        <section class="section-table" id="programs" style="display: none;">
             <div class="table-controls">
                <h2>Daftar Program</h2>
                <div class="search-box">
                    <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                    <input type="text" id="programSearch" placeholder="Cari program...">
                </div>
                <button class="btn btn-add" onclick="showForm('add-program-form', 'Tambah Program Baru')">
                    <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                    Tambah Program
                </button>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Program</th>
                            <th>Tanggal</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Relawan Dibutuhkan</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>PROG001</td>
                            <td>Bersih Pantai Lestari</td>
                            <td>10-12 Juli 2025</td>
                            <td>Pantai Indah</td>
                            <td><span class="status active">Aktif</span></td>
                            <td>50</td>
                            <td>25</td>
                            <td class="actions-buttons">
                                <button class="view" title="Lihat"><svg viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zm0 13c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg></button>
                                <button class="edit" title="Edit"><svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg></button>
                                <button class="delete" title="Hapus"><svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg></button>
                            </td>
                        </tr>
                        <tr>
                            <td>PROG002</td>
                            <td>Bimbingan Belajar Anak</td>
                            <td>1 Ags - 31 Des 2025</td>
                            <td>Panti Asuhan Bahagia</td>
                            <td><span class="status active">Aktif</span></td>
                            <td>15</td>
                            <td>10</td>
                            <td class="actions-buttons">
                                <button class="view" title="Lihat"><svg viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zm0 13c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg></button>
                                <button class="edit" title="Edit"><svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg></button>
                                <button class="delete" title="Hapus"><svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg></button>
                            </td>
                        </tr>
                        <tr>
                            <td>PROG003</td>
                            <td>Donor Darah Massal</td>
                            <td>20 Juni 2025</td>
                            <td>Balai Kota</td>
                            <td><span class="status completed">Selesai</span></td>
                            <td>50</td>
                            <td>50</td>
                            <td class="actions-buttons">
                                <button class="view" title="Lihat"><svg viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zm0 13c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg></button>
                                <button class="edit" title="Edit"><svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg></button>
                                <button class="delete" title="Hapus"><svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg></button>
                            </td>
                        </tr>
                        <tr>
                            <td>PROG004</td>
                            <td>Kampanye Lingkungan Digital</td>
                            <td>1 Sept - 30 Sept 2025</td>
                            <td>Online</td>
                            <td><span class="status draft">Draf</span></td>
                            <td>30</td>
                            <td>0</td>
                            <td class="actions-buttons">
                                <button class="view" title="Lihat"><svg viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zm0 13c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg></button>
                                <button class="edit" title="Edit"><svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg></button>
                                <button class="delete" title="Hapus"><svg viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="section-form" id="add-volunteer-form" style="display: none;">
            <h2>Tambah Relawan Baru</h2>
            <form>
                <div class="form-group">
                    <label for="fullName">Nama Lengkap</label>
                    <input type="text" id="fullName" name="fullName" placeholder="Masukkan nama lengkap" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Masukkan alamat email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Telepon</label>
                    <input type="tel" id="phone" name="phone" placeholder="Masukkan nomor telepon">
                </div>
                <div class="form-group">
                    <label for="dob">Tanggal Lahir</label>
                    <input type="date" id="dob" name="dob">
                </div>
                <div class="form-group">
                    <label for="address">Alamat Lengkap</label>
                    <textarea id="address" name="address" placeholder="Masukkan alamat relawan"></textarea>
                </div>
                <div class="form-group">
                    <label for="interest">Minat Utama</label>
                    <select id="interest" name="interest">
                        <option value="">Pilih minat</option>
                        <option value="lingkungan">Lingkungan</option>
                        <option value="pendidikan">Pendidikan</option>
                        <option value="kesehatan">Kesehatan</option>
                        <option value="sosial">Sosial & Kemanusiaan</option>
                        <option value="senibudaya">Seni & Budaya</option>
                        <option value="teknologi">Teknologi</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="skills">Keterampilan (Pisahkan dengan koma)</label>
                    <textarea id="skills" name="skills" placeholder="Contoh: Desain Grafis, Public Speaking, Penulisan"></textarea>
                </div>
                 <div class="form-group">
                    <label for="emergencyContactName">Nama Kontak Darurat</label>
                    <input type="text" id="emergencyContactName" name="emergencyContactName" placeholder="Nama kontak darurat">
                </div>
                <div class="form-group">
                    <label for="emergencyContactPhone">Telepon Kontak Darurat</label>
                    <input type="tel" id="emergencyContactPhone" name="emergencyContactPhone" placeholder="Nomor telepon kontak darurat">
                </div>
                <div class="button-group">
                    <button type="button" class="btn btn-secondary" onclick="hideForm('add-volunteer-form', 'volunteers', 'Relawan')">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Relawan</button>
                </div>
            </form>
        </section>

        <section class="section-table" id="messages" style="display: none;">
            <h2>Pesan & Notifikasi</h2>
            <p>Di sini Anda bisa melihat pesan dari relawan atau koordinator, serta mengirim pengumuman massal.</p>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Dari</th>
                            <th>Subjek</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Andi Pratama</td>
                            <td>Pertanyaan Program Bersih Pantai</td>
                            <td>2025-06-03</td>
                            <td><span class="status pending">Baru</span></td>
                            <td class="actions-buttons">
                                <button class="view" title="Lihat Pesan"><svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM4 16V4h16v12H4z"/></svg></button>
                                <button class="edit" title="Balas"><svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg></button>
                            </td>
                        </tr>
                        <tr>
                            <td>Sistem</td>
                            <td>Pengumuman: Jadwal Pelatihan Relawan</td>
                            <td>2025-06-01</td>
                            <td><span class="status completed">Terbaca</span></td>
                            <td class="actions-buttons">
                                <button class="view" title="Lihat Pesan"><svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM4 16V4h16v12H4z"/></svg></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <button class="btn btn-primary" style="margin-top: 20px;">Kirim Pengumuman Baru</button>
        </section>

        <section class="section-table" id="reports" style="display: none;">
            <h2>Laporan & Analitik</h2>
            <p>Di sini Anda bisa melihat laporan detail tentang relawan, program, dan partisipasi.</p>
            <div class="cards-container">
                <div class="card">
                    <div class="icon">&#128202;</div> <h3>Laporan Partisipasi Relawan</h3>
                    <p>Unduh laporan detail partisipasi relawan dalam berbagai program.</p>
                    <a href="#" class="btn btn-secondary">Unduh Excel</a>
                </div>
                 <div class="card">
                    <div class="icon">&#128203;</div> <h3>Laporan Kinerja Program</h3>
                    <p>Unduh laporan ringkasan dan kinerja dari semua program yang telah berjalan.</p>
                    <a href="#" class="btn btn-secondary">Unduh Excel</a>
                </div>
            </div>
        </section>

        <section class="section-form" id="settings" style="display: none;">
            <h2>Pengaturan Sistem</h2>
            <p>Kelola pengaturan umum, akun pengguna, dan kategori di sini.</p>
            <h3>Manajemen Akun Admin/Koordinator</h3>
            <form>
                <div class="form-group">
                    <label for="adminName">Nama Admin</label>
                    <input type="text" id="adminName" name="adminName" value="Admin Utama" readonly>
                </div>
                <div class="form-group">
                    <label for="adminEmail">Email Admin</label>
                    <input type="email" id="adminEmail" name="adminEmail" value="admin@organisasi.com" readonly>
                </div>
                 <div class="form-group">
                    <label for="newPassword">Kata Sandi Baru</label>
                    <input type="password" id="newPassword" name="newPassword" placeholder="Biarkan kosong jika tidak ingin mengubah">
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Konfirmasi kata sandi baru">
                </div>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                </div>
            </form>

            <h3 style="margin-top: 40px;">Pengaturan Umum Organisasi</h3>
            <form>
                <div class="form-group">
                    <label for="orgName">Nama Organisasi</label>
                    <input type="text" id="orgName" name="orgName" value="Nama Organisasi Anda">
                </div>
                <div class="form-group">
                    <label for="orgAddress">Alamat Organisasi</label>
                    <textarea id="orgAddress" name="orgAddress">Jalan Contoh No. 123, Kota Contoh</textarea>
                </div>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">Perbarui Info Organisasi</button>
                </div>
            </form>
        </section>

    </div>

    <script>
        // JavaScript untuk mengelola tampilan section
        document.addEventListener('DOMContentLoaded', () => {
            const sidebarLinks = document.querySelectorAll('.sidebar nav ul li a');
            const sections = document.querySelectorAll('.main-content section');
            const dashboardContent = document.getElementById('dashboard-content');
            const addVolunteerForm = document.getElementById('add-volunteer-form');
            const volunteersTable = document.getElementById('volunteers');
            const addProgramForm = document.getElementById('add-program-form');
            const programsTable = document.getElementById('programs');

            // Inisialisasi Chart.js
            let interestDoughnutChart, monthlyBarChart;

            function initializeCharts() {
                // Pastikan chart sebelumnya dihancurkan sebelum membuat yang baru
                if (interestDoughnutChart) interestDoughnutChart.destroy();
                if (monthlyBarChart) monthlyBarChart.destroy();

                // Diagram Donat (Minat Relawan)
                const ctxDoughnut = document.getElementById('interestDoughnutChart');
                if (ctxDoughnut) {
                    interestDoughnutChart = new Chart(ctxDoughnut, {
                        type: 'doughnut',
                        data: {
                            labels: ['Lingkungan', 'Pendidikan', 'Kesehatan', 'Sosial', 'Seni & Budaya', 'Teknologi'],
                            datasets: [{
                                data: [300, 250, 150, 200, 100, 50], // Data contoh
                                backgroundColor: [
                                    '#00BCD4', // primary-color
                                    '#4CAF50', // secondary-color
                                    '#2196F3', // info-color
                                    '#FFC107', // warning-color
                                    '#9C27B0', // Purple
                                    '#FF5722'  // Deep Orange
                                ],
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false, // Penting agar chart mengisi container
                            plugins: {
                                legend: {
                                    position: 'right',
                                },
                                title: {
                                    display: false,
                                    text: 'Relawan Berdasarkan Minat'
                                }
                            }
                        }
                    });
                }

                // Diagram Batang (Partisipasi Program Bulanan)
                const ctxBar = document.getElementById('monthlyBarChart');
                if (ctxBar) {
                    monthlyBarChart = new Chart(ctxBar, {
                        type: 'bar',
                        data: {
                            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                            datasets: [{
                                label: 'Relawan Berpartisipasi',
                                data: [65, 59, 80, 81, 56, 55], // Data contoh
                                backgroundColor: [
                                    'rgba(0, 188, 212, 0.7)', // primary-color
                                    'rgba(0, 188, 212, 0.7)',
                                    'rgba(0, 188, 212, 0.7)',
                                    'rgba(0, 188, 212, 0.7)',
                                    'rgba(0, 188, 212, 0.7)',
                                    'rgba(0, 188, 212, 0.7)'
                                ],
                                borderColor: [
                                    'rgba(0, 188, 212, 1)',
                                    'rgba(0, 188, 212, 1)',
                                    'rgba(0, 188, 212, 1)',
                                    'rgba(0, 188, 212, 1)',
                                    'rgba(0, 188, 212, 1)',
                                    'rgba(0, 188, 212, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false, // Penting agar chart mengisi container
                            plugins: {
                                legend: {
                                    display: false
                                },
                                title: {
                                    display: false,
                                    text: 'Partisipasi Program Bulanan'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            }


            // Fungsi untuk menampilkan section yang aktif dan menyembunyikan yang lain
            function showSection(id) {
                sections.forEach(section => {
                    section.style.display = 'none';
                });
                const targetSection = document.getElementById(id);
                if (targetSection) {
                    targetSection.style.display = 'block';
                }

                // Hancurkan dan inisialisasi ulang chart jika itu dashboard
                if (id === 'dashboard-content') {
                    initializeCharts(); // Panggil inisialisasi chart saat dashboard ditampilkan
                }
            }

            // Fungsi untuk menampilkan form dan menyembunyikan tabel terkait
            window.showForm = function(formId, headerTitle) {
                const currentTable = document.querySelector('.main-content section[style*="display: block"]');
                if (currentTable) {
                    currentTable.style.display = 'none'; // Sembunyikan section yang sedang aktif
                }

                document.getElementById(formId).style.display = 'block'; // Tampilkan form
                document.querySelector('.header h1').textContent = headerTitle; // Ubah judul header
            };

            // Fungsi untuk menyembunyikan form dan menampilkan tabel kembali
            window.hideForm = function(formId, tableId, headerTitle) {
                document.getElementById(formId).style.display = 'none'; // Sembunyikan form
                document.getElementById(tableId).style.display = 'block'; // Tampilkan tabel
                document.querySelector('.header h1').textContent = headerTitle; // Kembalikan judul header
            };


            // Atur event listener untuk sidebar links
            sidebarLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const targetId = e.currentTarget.getAttribute('href').substring(1);

                    // Hapus kelas 'active' dari semua link dan tambahkan ke link yang diklik
                    sidebarLinks.forEach(l => l.classList.remove('active'));
                    e.currentTarget.classList.add('active');

                    // Tampilkan section yang sesuai
                    if (targetId === 'dashboard') {
                        showSection('dashboard-content');
                        document.querySelector('.header h1').textContent = 'Dashboard';
                    } else if (targetId === 'volunteers') {
                        showSection('volunteers');
                        document.querySelector('.header h1').textContent = 'Relawan';
                    } else if (targetId === 'programs') {
                        showSection('programs');
                        document.querySelector('.header h1').textContent = 'Program';
                    } else if (targetId === 'messages') {
                        showSection('messages');
                        document.querySelector('.header h1').textContent = 'Pesan';
                    } else if (targetId === 'reports') {
                        showSection('reports');
                        document.querySelector('.header h1').textContent = 'Laporan';
                    } else if (targetId === 'settings') {
                        showSection('settings');
                        document.querySelector('.header h1').textContent = 'Pengaturan';
                    }
                });
            });

            // Inisialisasi tampilan awal
            showSection('dashboard-content'); // Tampilkan dashboard saat pertama kali halaman dimuat
        });
    </script>
</body>
</html>