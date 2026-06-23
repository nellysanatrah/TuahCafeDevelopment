<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT * FROM event_bookings WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    header('Location: dashboard.php#event-bookings');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Event Booking</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5efe6; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        h1 { color: #6f4e37; margin-bottom: 20px; }
        .info-group { margin-bottom: 15px; padding: 10px; background: #f9f5f0; border-radius: 8px; }
        .info-label { font-weight: bold; color: #4A372E; width: 180px; display: inline-block; }
        .info-value { color: #666; }
        .btn { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; margin-right: 10px; text-decoration: none; display: inline-block; }
        .btn-back { background: #6f4e37; color: white; }
        .btn-delete { background: #a94442; color: white; }
        .status-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-completed { background: #cce5ff; color: #004085; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="container">
    <h1>Event Booking Details</h1>
    
    <div class="info-group">
        <span class="info-label">Status:</span>
        <span class="status-badge status-<?php echo $booking['status'] ?? 'pending'; ?>"><?php echo ucfirst($booking['status'] ?? 'Pending'); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Customer Name:</span>
        <span class="info-value"><?php echo htmlspecialchars($booking['nama_penuh'] ?? $booking['full_name'] ?? 'N/A'); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Phone Number:</span>
        <span class="info-value"><?php echo htmlspecialchars($booking['no_tel'] ?? $booking['phone_number'] ?? 'N/A'); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Membership:</span>
        <span class="info-value"><?php echo htmlspecialchars($booking['membership_status'] ?? 'N/A'); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Event Type:</span>
        <span class="info-value"><?php echo htmlspecialchars($booking['jenis_acara'] ?? $booking['event_type'] ?? 'N/A'); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Event Name:</span>
        <span class="info-value"><?php echo htmlspecialchars($booking['event_name'] ?? 'N/A'); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Event Date:</span>
        <span class="info-value"><?php echo htmlspecialchars($booking['tarikh_acara'] ?? $booking['event_date'] ?? 'N/A'); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Time:</span>
        <span class="info-value"><?php echo htmlspecialchars($booking['start_time'] ?? 'N/A'); ?> - <?php echo htmlspecialchars($booking['end_time'] ?? 'N/A'); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Venue:</span>
        <span class="info-value"><?php echo htmlspecialchars($booking['venue'] ?? 'N/A'); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Package:</span>
        <span class="info-value"><?php echo htmlspecialchars($booking['pakej_pilihan'] ?? $booking['package_selected'] ?? 'N/A'); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Number of Pax:</span>
        <span class="info-value"><?php echo $booking['pax_count'] ?? 'N/A'; ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Menu Details:</span>
        <span class="info-value"><?php echo nl2br(htmlspecialchars($booking['menu_details'] ?? 'N/A')); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Add-ons:</span>
        <span class="info-value"><?php echo nl2br(htmlspecialchars($booking['addons_details'] ?? 'N/A')); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Special Remarks:</span>
        <span class="info-value"><?php echo nl2br(htmlspecialchars($booking['special_remarks'] ?? 'N/A')); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Submitted:</span>
        <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($booking['created_at'] ?? 'now')); ?></span>
    </div>
    
    <div style="margin-top: 30px;">
        <a href="dashboard.php#event-bookings" class="btn btn-back">← Back to Event Bookings</a>
        <a href="delete_event_booking.php?id=<?php echo $id; ?>" class="btn btn-delete" onclick="return confirm('Delete this booking?')">Delete</a>
    </div>
</div>
</body>
</html>