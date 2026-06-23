<?php
require_once '../config/database.php';

$query = "SELECT id, name, phone, proposal, status FROM komuniti_events ORDER BY id DESC";
$result = $conn->query($query);
?>

<div class="container">
    <h1>Sponsorship Applications</h1>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; background: white;">
            <thead>
                <tr>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; background: #f9f5f0;">Name</th>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; background: #f9f5f0;">Phone</th>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; background: #f9f5f0;">Proposal</th>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; background: #f9f5f0;">Status</th>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; background: #f9f5f0;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars(substr($row['proposal'], 0, 50)) . (strlen($row['proposal']) > 50 ? '...' : ''); ?></td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;">
                                <select class="status-select" data-id="<?php echo $row['id']; ?>" data-table="komuniti_events" onchange="updateStatus(this)">
                                    <option value="pending" <?php echo ($row['status'] ?? 'pending') == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo ($row['status'] ?? '') == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo ($row['status'] ?? '') == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;">
                                <a href="view_komuniti.php?id=<?php echo $row['id']; ?>" class="btn view-btn" style="text-decoration: none; display: inline-block;">View</a>
                                <button class="btn delete-btn" onclick="deleteSponsorship(<?php echo $row['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="padding: 12px; text-align: center;">No sponsorship applications found</td
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
function viewApplication(id) {
    window.location.href = 'view_komuniti.php?id=' + id;
}
function deleteSponsorship(id) {
    if (confirm('Delete this application?')) {
        window.location.href = 'delete_komuniti.php?id=' + id;
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