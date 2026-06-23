<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT * FROM komuniti_events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$app = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$app) {
    header('Location: dashboard.php#sponsorships');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Community Application</title>
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
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="container">
    <h1>Community Event / Sponsorship Application</h1>
    
    <div class="info-group">
        <span class="info-label">Status:</span>
        <span class="status-badge status-<?php echo $app['status'] ?? 'pending'; ?>"><?php echo ucfirst($app['status'] ?? 'Pending'); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Name:</span>
        <span class="info-value"><?php echo htmlspecialchars($app['name']); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Phone:</span>
        <span class="info-value"><?php echo htmlspecialchars($app['phone']); ?></span>
    </div>
    
    <div class="info-group">
        <span class="info-label">Proposal / Event:</span>
        <span class="info-value"><?php echo nl2br(htmlspecialchars($app['proposal'])); ?></span>
    </div>
    
    <div style="margin-top: 30px;">
        <a href="dashboard.php#sponsorships" class="btn btn-back">← Back to Sponsorship Applications</a>
        <a href="delete_komuniti.php?id=<?php echo $id; ?>" class="btn btn-delete" onclick="return confirm('Delete this application?')">Delete</a>
    </div>
</div>
</body>
</html>