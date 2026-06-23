<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $status = $_POST['status'];
    $table = $_POST['table'];
    
    // Allowed tables
    $allowed_tables = ['job_applications', 'event_bookings', 'table_reservations', 'sponsorships', 'komuniti_events'];
    
    if (!in_array($table, $allowed_tables)) {
        echo json_encode(['success' => false, 'message' => 'Invalid table']);
        exit();
    }
    
    // Check if status column exists (you can skip this if you know it exists)
    $check_column = $conn->query("SHOW COLUMNS FROM $table LIKE 'status'");
    if ($check_column->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Status column not found in ' . $table]);
        exit();
    }
    
    $stmt = $conn->prepare("UPDATE $table SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>