<?php
session_start();
include 'koneksi.php'; // Sesuaikan path

header('Content-Type: application/json'); // Respons dalam format JSON

$response = ['status' => 'error', 'message' => 'Invalid request.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id']) && isset($_POST['user_id'])) {
    $notificationId = (int)$_POST['notification_id'];
    $userId = (int)$_POST['user_id'];

    if ($notificationId > 0 && $userId > 0) {
        // Gunakan INSERT IGNORE untuk menghindari error jika sudah ada data unique constraint
        // Atau gunakan INSERT ... ON DUPLICATE KEY UPDATE jika ingin update timestamp
        $stmt = $conn->prepare("INSERT IGNORE INTO user_notification_reads (user_id, notification_id) VALUES (?, ?)");
        
        if ($stmt) {
            $stmt->bind_param("ii", $userId, $notificationId);
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Notification marked as read.';
            } else {
                $response['message'] = 'Failed to execute query: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'Failed to prepare statement: ' . $conn->error;
        }
    } else {
        $response['message'] = 'Invalid notification_id or user_id.';
    }
}

echo json_encode($response);
$conn->close();
?>