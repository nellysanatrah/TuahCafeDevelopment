<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $table = $_POST['table'];
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $mark_all = isset($_POST['mark_all']) ? true : false;
    
    // Allowed tables
    $allowed_tables = ['job_applications', 'event_bookings', 'table_reservations', 'sponsorships', 'komuniti_events'];
    
    if (in_array($table, $allowed_tables)) {
        if ($mark_all || $id == 0) {
            // Mark all as read
            $conn->query("UPDATE $table SET is_read = 1");
        } else {
            // Mark specific item as read
            $conn->query("UPDATE $table SET is_read = 1 WHERE id = $id");
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
?>