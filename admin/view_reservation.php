<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT * FROM table_reservations WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$reservation) {
    header('Location: dashboard.php#table-reservation');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Table Reservation</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5efe6; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        h1 { color: #6f4e37; margin-bottom: 20px; }
        .info-group { margin-bottom: 15px; padding: 10px; background: #f9f5f0; border-radius: 8px; }
        .info-label { font-weight: bold; color: #4A372E; width: 150px; display: inline-block; }
        .info-value { color: #666; }
        .btn { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; margin-right: 10px; text-decoration: none; display: inline-block; }
        .btn-back { background: #6f4e37; color: white; }
        .btn-delete { background: #a94442; color: white; }
        .status-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-seated { background: #cce5ff; color: #004085; }
        .status-completed { background: #d1ecf1; color: #0c5460; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-no_show { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="container">
    <h1>Table Reservation Details</h1>
    
    <div class="info-group">
        <span class="info-label">Status:</span>
        <span class="status-badge status-<?php echo $reservation['status'] ?? 'pending'; ?>"><?php echo ucfirst($reservation['status'] ?? 'Pending'); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Customer Name:</span>
        <span class="info-value"><?php echo htmlspecialchars($reservation['full_name']); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Phone Number:</span>
        <span class="info-value"><?php echo htmlspecialchars($reservation['phone_number']); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Venue:</span>
        <span class="info-value"><?php echo htmlspecialchars($reservation['venue']); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Reservation Date:</span>
        <span class="info-value"><?php echo htmlspecialchars($reservation['reservation_date']); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Time Slot:</span>
        <span class="info-value"><?php echo htmlspecialchars($reservation['time_slot']); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Number of Guests:</span>
        <span class="info-value"><?php echo htmlspecialchars($reservation['guests_count']); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Special Requests:</span>
        <span class="info-value"><?php echo nl2br(htmlspecialchars($reservation['special_requests'] ?? 'None')); ?></span>
    </div>
    
    <div style="margin-top: 30px;">
        <a href="dashboard.php#table-reservation" class="btn btn-back">← Back to Table Reservations</a>
        <a href="delete_reservation.php?id=<?php echo $id; ?>" class="btn btn-delete" onclick="return confirm('Delete this reservation?')">Delete</a>
    </div>
</div>
</body>
</html>