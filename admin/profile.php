<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/database.php';

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

$stmt = $conn->prepare("SELECT id, username, name, role, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $username = $_POST['username'];
    
    $check = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $check->bind_param("si", $username, $user_id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $error = "Username already taken!";
    } else {
        $update = $conn->prepare("UPDATE users SET name = ?, username = ? WHERE id = ?");
        $update->bind_param("ssi", $name, $username, $user_id);
        if ($update->execute()) {
            $_SESSION['name'] = $name;
            $success = "Profile updated successfully!";
            $user['name'] = $name;
            $user['username'] = $username;
        } else { $error = "Error updating profile."; }
        $update->close();
    }
    $check->close();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $pass_stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $pass_stmt->bind_param("i", $user_id);
    $pass_stmt->execute();
    $current_hash = $pass_stmt->get_result()->fetch_assoc()['password'];
    $pass_stmt->close();
    
    if ($current_password != $current_hash) {
        $password_error = "Current password is incorrect!";
    } elseif ($new_password != $confirm_password) {
        $password_error = "New passwords do not match!";
    } elseif (strlen($new_password) < 4) {
        $password_error = "Password must be at least 4 characters!";
    } else {
        $update_pass = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_pass->bind_param("si", $new_password, $user_id);
        if ($update_pass->execute()) { $password_success = "Password changed successfully!"; }
        else { $password_error = "Error changing password."; }
        $update_pass->close();
    }
}

// Get counts for sidebar badges
$job_count = $conn->query("SELECT COUNT(*) as count FROM job_applications")->fetch_assoc()['count'];
$event_count = $conn->query("SELECT COUNT(*) as count FROM event_bookings")->fetch_assoc()['count'];
$reservation_count = $conn->query("SELECT COUNT(*) as count FROM table_reservations")->fetch_assoc()['count'];
$sponsorship_count = $conn->query("SELECT COUNT(*) as count FROM sponsorships")->fetch_assoc()['count'];
$new_jobs = $conn->query("SELECT COUNT(*) as count FROM job_applications WHERE DATE(submitted_at) = CURDATE()")->fetch_assoc()['count'];
$new_events = $conn->query("SELECT COUNT(*) as count FROM event_bookings WHERE tarikh_acara = CURDATE()")->fetch_assoc()['count'];
$new_reservations = $conn->query("SELECT COUNT(*) as count FROM table_reservations WHERE reservation_date = CURDATE()")->fetch_assoc()['count'];
$new_sponsorships = $conn->query("SELECT COUNT(*) as count FROM sponsorships WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Tuah Cafe</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5efe6 0%, #e8ddd0 100%);
            display: flex;
        }

        /* ========== SIDEBAR ========== */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #2d1f16 0%, #1a120c 100%);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
        }

        .sidebar-header h2 {
            font-size: 24px;
            margin-bottom: 5px;
            letter-spacing: 2px;
            font-weight: 600;
        }

        .sidebar-header p {
            font-size: 12px;
            opacity: 0.7;
            letter-spacing: 1px;
        }

        .sidebar-nav {
            list-style: none;
            padding: 20px 0 30px 0;
        }

        .sidebar-nav li {
            padding: 14px 25px;
            transition: all 0.3s ease;
            cursor: pointer;
            margin: 5px 15px;
            border-radius: 12px;
        }

        .sidebar-nav li:hover {
            background: rgba(196, 155, 102, 0.3);
            transform: translateX(5px);
        }

        .sidebar-nav li.active {
            background: linear-gradient(135deg, #c49b66, #a87d4a);
            box-shadow: 0 4px 15px rgba(196, 155, 102, 0.3);
        }

        .sidebar-nav li a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 500;
        }

        .sidebar-nav li a i {
            width: 24px;
            font-size: 18px;
        }

        .badge {
            background: #c49b66;
            color: #2d1f16;
            font-size: 11px;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: auto;
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 25px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Content wrapper to push footer down */
        .content-wrapper {
            flex: 1;
        }

        /* Top Header */
        .top-header {
            background: white;
            padding: 20px 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-header h1 {
            font-size: 28px;
            color: #2d1f16;
            font-weight: 700;
        }

        .top-header p {
            color: #8b7355;
            font-size: 14px;
            margin-top: 5px;
        }

        /* Profile Dropdown */
        .profile-dropdown {
            position: relative;
            display: inline-block;
        }

        .profile-btn {
            background: linear-gradient(135deg, #c49b66, #a87d4a);
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
        }

        .profile-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(196, 155, 102, 0.4);
        }

        .profile-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            min-width: 200px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-radius: 12px;
            z-index: 1000;
            margin-top: 10px;
            overflow: hidden;
        }

        .profile-dropdown-content.show {
            display: block;
        }

        .profile-dropdown-content a {
            color: #4A372E;
            padding: 12px 20px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .profile-dropdown-content a:hover {
            background: #f5efe6;
            padding-left: 25px;
        }

        /* Messages - FIXED */
        .success-msg {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
            font-weight: 500;
        }

        .error-msg {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
            font-weight: 500;
        }

        /* Profile Card */
        .profile-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .profile-card h2 {
            color: #2d1f16;
            font-size: 24px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0e6d8;
            font-weight: 700;
        }

        .info-row {
            display: flex;
            margin-bottom: 15px;
            padding: 12px 15px;
            background: #f9f5f0;
            border-radius: 12px;
        }

        .info-label {
            width: 140px;
            font-weight: 600;
            color: #2d1f16;
        }

        .info-value {
            flex: 1;
            color: #5a4a3a;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #2d1f16;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #f0e6d8;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: #c49b66;
            box-shadow: 0 0 0 3px rgba(196, 155, 102, 0.1);
        }

        .btn-submit {
            background: linear-gradient(135deg, #c49b66, #a87d4a);
            color: white;
            padding: 14px 24px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(196, 155, 102, 0.4);
        }

        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .role-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
        }

        .role-admin {
            background: linear-gradient(135deg, #c49b66, #a87d4a);
            color: white;
        }

        .role-main_admin {
            background: linear-gradient(135deg, #6f4e37, #5a3d2e);
            color: white;
        }

        .role-staff {
            background: linear-gradient(135deg, #a5a5a5, #888);
            color: white;
        }

        /* Simple Footer */
        .simple-footer {
            text-align: center;
            padding: 30px 20px 15px 20px;
            margin-top: 30px;
        }

        .simple-footer p {
            color: #a08060;
            font-size: 12px;
            margin: 0;
            letter-spacing: 0.5px;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f0e6d8;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c49b66;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a87d4a;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('show');
        }

        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profileDropdown');
            const profileBtn = document.querySelector('.profile-btn');
            if (dropdown && profileBtn && !profileBtn.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });
    </script>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="../logo.png" alt="Tuah Cafe Logo" style="width: 60px; height: auto; margin-bottom: 10px;">
        <h2>Tuah Cafe</h2>
        <p>Admin Panel</p>
    </div>
    <ul class="sidebar-nav">
        <li onclick="location.href='dashboard.php'">
            <a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        </li>
        <li onclick="location.href='dashboard.php#applications'">
            <a href="#"><i class="fas fa-briefcase"></i> Job Applications</a>
            <?php if ($new_jobs > 0): ?><span class="badge">+<?php echo $new_jobs; ?></span><?php endif; ?>
        </li>
        <li onclick="location.href='dashboard.php#event-bookings'">
            <a href="#"><i class="fas fa-calendar"></i> Event Bookings</a>
            <?php if ($new_events > 0): ?><span class="badge">+<?php echo $new_events; ?></span><?php endif; ?>
        </li>
        <li onclick="location.href='dashboard.php#table-reservation'">
            <a href="#"><i class="fas fa-chair"></i> Table Reservation</a>
            <?php if ($new_reservations > 0): ?><span class="badge">+<?php echo $new_reservations; ?></span><?php endif; ?>
        </li>
        <li onclick="location.href='dashboard.php#sponsorships'">
            <a href="#"><i class="fas fa-handshake"></i> Sponsorship Applications</a>
            <?php if ($new_sponsorships > 0): ?><span class="badge">+<?php echo $new_sponsorships; ?></span><?php endif; ?>
        </li>
        <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'main_admin'): ?>
        <li onclick="location.href='admin_staff.php'">
            <a href="#"><i class="fas fa-users"></i> Staff Management</a>
        </li>
        <?php endif; ?>
    </ul>
</div>

<div class="main-content">
    <div class="top-header">
        <div>
            <h1>My Profile</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</p>
        </div>
        <div class="profile-dropdown">
            <button class="profile-btn" onclick="toggleDropdown()">
                <i class="fas fa-user-circle"></i> 
                <?php echo htmlspecialchars($_SESSION['name']); ?> 
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="profile-dropdown-content" id="profileDropdown">
                <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        <!-- Success Message - Only shows when there's actual content -->
        <?php if (isset($success) && !empty($success)): ?>
            <div class="success-msg">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <!-- Error Message - Only shows when there's actual content -->
        <?php if (isset($error) && !empty($error)): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="two-columns">
            <div class="profile-card">
                <h2><i class="fas fa-user"></i> Profile Information</h2>
                <div class="info-row">
                    <div class="info-label">Username:</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Full Name:</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['name']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Role:</div>
                    <div class="info-value">
                        <span class="role-badge role-<?php echo $user['role']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Member Since:</div>
                    <div class="info-value"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></div>
                </div>
                <form method="POST" style="margin-top: 20px;">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    <button type="submit" name="update_profile" class="btn-submit">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>

            <div class="profile-card">
                <h2><i class="fas fa-lock"></i> Change Password</h2>
                
                <!-- Password Success Message -->
                <?php if (isset($password_success) && !empty($password_success)): ?>
                    <div class="success-msg" style="margin-bottom: 20px;">
                        <i class="fas fa-check-circle"></i> <?php echo $password_success; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Password Error Message -->
                <?php if (isset($password_error) && !empty($password_error)): ?>
                    <div class="error-msg" style="margin-bottom: 20px;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $password_error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label><i class="fas fa-key"></i> Current Password</label>
                        <input type="password" name="current_password" placeholder="Enter current password" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> New Password</label>
                        <input type="password" name="new_password" placeholder="Enter new password" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-check"></i> Confirm New Password</label>
                        <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn-submit">
                        <i class="fas fa-exchange-alt"></i> Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Simple Footer at bottom -->
    <footer class="simple-footer">
        <p>© 2026 Tuah Cafe. Hak cipta terpelihara.</p>
    </footer>
</div>

</body>
</html>