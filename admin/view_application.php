<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT * FROM job_applications WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$app = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$app) {
    header('Location: dashboard.php#applications');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Job Application Details</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5efe6; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .header h1 { color: #4A372E; font-size: 24px; }
        .back-btn { background: #6f4e37; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; }
        .status-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: bold; margin-left: 10px; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-reviewed { background: #d4edda; color: #155724; }
        .status-interviewed { background: #d1ecf1; color: #0c5460; }
        .status-hired { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        
        .section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .section h2 {
            color: #4A372E;
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0e6d8;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .info-item.full-width {
            grid-column: span 2;
        }
        .info-label {
            font-weight: bold;
            color: #4A372E;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .info-value {
            color: #333;
            font-size: 14px;
            background: #f9f5f0;
            padding: 10px;
            border-radius: 8px;
        }
        .btn-action {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            margin-left: 10px;
        }
        .btn-review { background: #FFEE8C; color: white; }
        .btn-interview { background: #17a2b8; color: white; }
        .btn-hire { background: #ffc107; color: #333; }
        .btn-reject { background: #dc3545; color: white; }
        
        @media (max-width: 768px) {
            .info-grid { grid-template-columns: 1fr; }
            .info-item.full-width { grid-column: span 1; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Job Application Details #<?php echo $app['id']; ?>
            <span class="status-badge status-<?php echo $app['status']; ?>"><?php echo ucfirst($app['status']); ?></span>
        </h1>
        <a href="dashboard.php#applications" class="back-btn">← Back to Job Applications</a>
    </div>

    <div class="section">
        <h2>Personal Information</h2>
        <div class="info-grid">
            <div class="info-item"><div class="info-label">Full Name</div><div class="info-value"><?php echo htmlspecialchars($app['full_name'] ?? 'N/A'); ?></div></div>
            <div class="info-item"><div class="info-label">NRIC</div><div class="info-value"><?php echo htmlspecialchars($app['nric'] ?? 'N/A'); ?></div></div>
            <div class="info-item"><div class="info-label">Email</div><div class="info-value"><?php echo htmlspecialchars($app['email'] ?? 'N/A'); ?></div></div>
            <div class="info-item"><div class="info-label">Phone</div><div class="info-value"><?php echo htmlspecialchars($app['phone'] ?? 'N/A'); ?></div></div>
            <div class="info-item"><div class="info-label">Gender</div><div class="info-value"><?php echo ($app['gender'] == 'Male') ? 'Lelaki' : 'Perempuan'; ?></div></div>
            <div class="info-item"><div class="info-label">Marital Status</div><div class="info-value"><?php echo htmlspecialchars($app['marital_status'] ?? 'N/A'); ?></div></div>
            <div class="info-item"><div class="info-label">Nationality</div><div class="info-value"><?php echo htmlspecialchars($app['nationality'] ?? 'N/A'); ?></div></div>
            <div class="info-item"><div class="info-label">Religion</div><div class="info-value"><?php echo htmlspecialchars($app['religion'] ?? 'N/A'); ?></div></div>
            <div class="info-item"><div class="info-label">City</div><div class="info-value"><?php echo htmlspecialchars($app['city'] ?? 'N/A'); ?></div></div>
            <div class="info-item"><div class="info-label">State</div><div class="info-value"><?php echo htmlspecialchars($app['state'] ?? 'N/A'); ?></div></div>
            <div class="info-item"><div class="info-label">Postal Code</div><div class="info-value"><?php echo htmlspecialchars($app['postal_code'] ?? 'N/A'); ?></div></div>
            <div class="info-item"><div class="info-label">Own Vehicle</div><div class="info-value"><?php echo ($app['own_vehicle'] == 'Yes') ? 'Ya' : 'Tidak'; ?></div></div>
        </div>
    </div>

    <div class="section">
        <h2>Position Applied</h2>
        <div class="info-grid">
            <div class="info-item"><div class="info-label">Applying Position</div><div class="info-value"><?php echo htmlspecialchars($app['applying_position'] ?? 'N/A'); ?></div></div>
            <div class="info-item"><div class="info-label">Outlet</div><div class="info-value"><?php echo htmlspecialchars($app['apply_outlet'] ?? 'N/A'); ?></div></div>
            <div class="info-item"><div class="info-label">Notice Period</div><div class="info-value"><?php echo htmlspecialchars($app['notice_period'] ?? 'N/A'); ?></div></div>
            <div class="info-item"><div class="info-label">Expected Salary</div><div class="info-value">RM <?php echo number_format($app['expected_salary'] ?? 0, 2); ?></div></div>
        </div>
    </div>

    <div class="section">
        <h2>Employment & Skills</h2>
        <div class="info-grid">
            <div class="info-item"><div class="info-label">Current Status</div><div class="info-value"><?php echo htmlspecialchars($app['current_employment_status'] ?? 'N/A'); ?></div></div>
            <div class="info-item"><div class="info-label">F&B Experience</div><div class="info-value"><?php echo ($app['fb_experience'] == 'Yes') ? 'Ya' : 'Tidak'; ?></div></div>
            <div class="info-item full-width"><div class="info-label">Skills</div><div class="info-value"><?php echo nl2br(htmlspecialchars($app['skills'] ?? 'N/A')); ?></div></div>
        </div>
    </div>

    <div class="section">
        <h2>Company Knowledge</h2>
        <div class="info-grid">
            <div class="info-item"><div class="info-label">How did you know about us?</div><div class="info-value"><?php echo htmlspecialchars($app['company_name_knowledge'] ?? 'N/A'); ?></div></div>
            <div class="info-item"><div class="info-label">Job Source</div><div class="info-value"><?php echo htmlspecialchars($app['job_source'] ?? 'N/A'); ?></div></div>
            <div class="info-item full-width"><div class="info-label">Why should we hire you?</div><div class="info-value"><?php echo nl2br(htmlspecialchars($app['why_hire_you'] ?? 'N/A')); ?></div></div>
            <div class="info-item full-width"><div class="info-label">Why join Tuah Cafe?</div><div class="info-value"><?php echo nl2br(htmlspecialchars($app['why_join_company'] ?? 'N/A')); ?></div></div>
        </div>
    </div>

    <div class="section">
        <h2>Emergency Contact</h2>
        <div class="info-grid">
            <div class="info-item"><div class="info-label">Contact Person</div><div class="info-value"><?php echo htmlspecialchars($app['emergency_contact_person'] ?? 'N/A'); ?></div></div>
            <div class="info-item"><div class="info-label">Phone</div><div class="info-value"><?php echo htmlspecialchars($app['emergency_contact_phone'] ?? 'N/A'); ?></div></div>
            <div class="info-item full-width"><div class="info-label">Address</div><div class="info-value"><?php echo nl2br(htmlspecialchars($app['emergency_address'] ?? 'N/A')); ?></div></div>
        </div>
    </div>

    <div class="section">
        <h2>Submission Info</h2>
        <div class="info-grid">
            <div class="info-item"><div class="info-label">Submitted At</div><div class="info-value"><?php echo date('d/m/Y H:i:s', strtotime($app['submitted_at'])); ?></div></div>
        </div>
        <div class="action-buttons" style="margin-top: 20px; text-align: right;">
            <?php if ($app['status'] == 'pending'): ?>
                <button class="btn-action btn-review" onclick="updateStatus('reviewed')">Review</button>
                <button class="btn-action btn-interview" onclick="updateStatus('interviewed')">Interview</button>
                <button class="btn-action btn-reject" onclick="updateStatus('rejected')">Reject</button>
            
            <?php elseif ($app['status'] == 'reviewed'): ?>
                <button class="btn-action btn-interview" onclick="updateStatus('interviewed')">Interview</button>
                <button class="btn-action btn-reject" onclick="updateStatus('rejected')">Reject</button>
            
            <?php elseif ($app['status'] == 'interviewed'): ?>
                <button class="btn-action btn-hire" onclick="updateStatus('hired')">Hire</button>
                <button class="btn-action btn-reject" onclick="updateStatus('rejected')">Reject</button>
            
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function updateStatus(status) {
    fetch('update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=<?php echo $id; ?>&status=' + status + '&table=job_applications'
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              location.reload();
          } else {
              alert('Error updating status');
          }
      });
}
</script>
</body>
</html>