<?php
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}
require_once '../config/database.php';

$query = "SELECT id, 
          CASE 
              WHEN nama_penuh IS NOT NULL AND nama_penuh != '' THEN nama_penuh
              WHEN full_name IS NOT NULL AND full_name != '' THEN full_name
              ELSE 'Unknown'
          END as customer_name, 
          jenis_acara as event_type, 
          tarikh_acara as event_date, 
          status 
          FROM event_bookings 
          ORDER BY tarikh_acara DESC";
$result = $conn->query($query);
?>

<div class="container">
    <h1>Event Bookings</h1>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; background: white;">
            <thead>
                <tr>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; background: #f9f5f0;">Customer</th>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; background: #f9f5f0;">Event</th>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; background: #f9f5f0;">Date</th>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; background: #f9f5f0;">Status</th>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; background: #f9f5f0;">Action</th>
                </tr>
            </thead>
            <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($row['event_type'] ?? 'N/A'); ?></td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($row['event_date'] ?? 'N/A'); ?></td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;">
                    <select class="status-select" data-id="<?php echo $row['id']; ?>" data-table="event_bookings" onchange="updateStatus(this)">
                        <option value="pending" <?php echo ($row['status'] ?? 'pending') == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo ($row['status'] ?? '') == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="completed" <?php echo ($row['status'] ?? '') == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo ($row['status'] ?? '') == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;">
                    <a href="view_event_booking.php?id=<?php echo $row['id']; ?>" class="btn view-btn" style="text-decoration: none; display: inline-block;">View</a>
                    <button class="btn delete-btn" onclick="deleteEventBooking(<?php echo $row['id']; ?>)">Delete</button>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="5" style="padding: 12px; text-align: center;">No event bookings found</td>
        </tr>
    <?php endif; ?>
</tbody>
        </table>
    </div>
</div>

<style>
.btn { padding: 8px 12px; border: none; border-radius: 8px; color: white; cursor: pointer; font-size: 12px; margin: 0 2px; }
.view-btn { background: #17a2b8; }
.delete-btn { background: #a94442; }
.status-select { padding: 5px 8px; border-radius: 5px; border: 1px solid #ddd; font-size: 12px; cursor: pointer; }
.status-select:hover { border-color: #17a2b8; }
</style>

<script>
function viewBooking(id) { 
    window.location.href = 'view_event_booking.php?id=' + id; 
}
function deleteEventBooking(id) { 
    if (confirm('Delete this booking?')) { 
        window.location.href = 'delete_event_booking.php?id=' + id; 
    } 
}
function updateStatus(selectElement) {
    var id = selectElement.getAttribute('data-id');
    var status = selectElement.value;
    var table = selectElement.getAttribute('data-table');
    
    fetch('update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id + '&status=' + status + '&table=' + table
    }).then(response => response.json())
      .then(data => {
          if (!data.success) {
              alert('Error updating status');
              location.reload();
          }
      });
}
</script>