<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
require_once '../config/database.php';

$query = "SELECT id, full_name, email, phone, applying_position, apply_outlet, 
          fb_experience, status, submitted_at 
          FROM job_applications 
          ORDER BY submitted_at DESC";
$result = $conn->query($query);
?>

<div class="container">
    <h1>Job Applications</h1>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; background: white;">
            <thead>
                <tr>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; background: #f9f5f0;">Name</th>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; background: #f9f5f0;">Position</th>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; background: #f9f5f0;">Email</th>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; background: #f9f5f0;">Status</th>
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; background: #f9f5f0;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($row['applying_position']); ?></td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($row['email']); ?></td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;">
                                <span class="status status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span>
                            </td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;">
                                <a href="view_application.php?id=<?php echo $row['id']; ?>" class="btn view-btn" style="text-decoration: none; display: inline-block;">View</a>
                                
                                <?php if ($row['status'] == 'pending'): ?>
                                    <button class="btn approve-btn" onclick="updateJobAppStatus(<?php echo $row['id']; ?>, 'reviewed')">Review</button>
                                    <button class="btn reject-btn" onclick="updateJobAppStatus(<?php echo $row['id']; ?>, 'rejected')">Reject</button>
                                
                                <?php elseif ($row['status'] == 'reviewed'): ?>
                                    <button class="btn interview-btn" onclick="updateJobAppStatus(<?php echo $row['id']; ?>, 'interviewed')">Interview</button>
                                    <button class="btn reject-btn" onclick="updateJobAppStatus(<?php echo $row['id']; ?>, 'rejected')">Reject</button>
                                
                                <?php elseif ($row['status'] == 'interviewed'): ?>
                                    <button class="btn hire-btn" onclick="updateJobAppStatus(<?php echo $row['id']; ?>, 'hired')">Hire</button>
                                    <button class="btn reject-btn" onclick="updateJobAppStatus(<?php echo $row['id']; ?>, 'rejected')">Reject</button>
                                
                                <?php endif; ?>
                                
                                <button class="btn delete-btn" onclick="deleteJobApplication(<?php echo $row['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="padding: 12px; text-align: center;">No job applications found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.btn { padding: 8px 12px; border: none; border-radius: 8px; color: white; cursor: pointer; font-size: 12px; margin: 0 2px; display: inline-block; text-align: center; }
.view-btn { background: #17a2b8; text-decoration: none; }
.approve-btn { background: #28a745; }
.reject-btn { background: #dc3545; }
.delete-btn { background: #a94442; }
.interview-btn { background: #17a2b8; }
.hire-btn { background: #ffc107; color: #333; }

.status { padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; display: inline-block; }
.status-pending { background: #fff3cd; color: #856404; }
.status-reviewed { background: #FFEE8C; color: #155724; }
.status-interviewed { background: #d1ecf1; color: #0c5460; }
.status-hired { background: #d4edda; color: #155724; }
.status-rejected { background: #f8d7da; color: #721c24; }
</style>

<script>
function updateJobAppStatus(id, status) {
    fetch('update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id + '&status=' + status + '&table=job_applications'
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              location.reload();
          } else {
              alert('Error updating status');
          }
      });
}

function deleteJobApplication(id) { 
    if (confirm('Delete this application?')) { 
        window.location.href = 'delete_application.php?id=' + id; 
    } 
}
</script>