<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) { // disamakan dengan proses_login.php
    echo "Anda harus login terlebih dahulu.";
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM donations WHERE user = ? ORDER BY donated_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Donasi</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #888;
            text-align: center;
        }
        th {
            background-color: #f0f0f0;
        }
        img {
            max-width: 100px;
            max-height: 100px;
        }
        .btn {
            padding: 5px 10px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background: #0056b3;
        }

        /* Modal style */
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            padding-top: 60px;
            left: 0; top: 0;
            width: 100%; height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fff;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 400px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>

<h2 style="text-align: center;">Riwayat Donasi Anda</h2>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Order ID</th>
            <th>Jumlah Donasi</th>
            <th>Status</th>
            <th>Bukti Pembayaran</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        while ($data = $result->fetch_assoc()) {
        ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($data['order_id']); ?></td>
                <td>Rp<?= number_format($data['amount'], 0, ',', '.'); ?></td>
                <td><?= htmlspecialchars($data['payment_status']); ?></td>
                <td>
                    <?php if (!empty($data['bukti_upload'])): ?>
                        <a class="btn" href="../payment/<?= $data['bukti_upload']; ?>" target="_blank">Lihat Bukti</a>
                    <?php else: ?>
                        <button class="btn" onclick="openModal('<?= $data['order_id']; ?>')">Upload Bukti</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<!-- MODAL Upload Bukti -->
<div id="uploadModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Upload Bukti Pembayaran</h3>
        <form action="../payment/upload_bukti.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="order_id" id="modalOrderId">
            <input type="file" name="bukti" required accept=".jpg,.jpeg,.png,.pdf"><br><br>
            <button type="submit" class="btn">Kirim Bukti</button>
        </form>
    </div>
</div>

<!-- SweetAlert jika ada notifikasi -->
<?php if (isset($_SESSION['upload_status'])):
    $msg = $_SESSION['upload_status'];
    unset($_SESSION['upload_status']);
?>
<script>
    Swal.fire({
        icon: '<?= $msg === "success" ? "success" : "error" ?>',
        title: '<?= $msg === "success" ? "Berhasil!" : "Gagal!" ?>',
        text: '<?= $msg === "success" ? "Bukti berhasil dikirim! Tunggu verifikasi dari admin." : $msg ?>',
        confirmButtonText: 'OK'
    });
</script>
<?php endif; ?>

<!-- Script Modal -->
<script>
function openModal(orderId) {
    document.getElementById("modalOrderId").value = orderId;
    document.getElementById("uploadModal").style.display = "block";
}

function closeModal() {
    document.getElementById("uploadModal").style.display = "none";
}
</script>

</body>
</html>