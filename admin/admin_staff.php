<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'main_admin') {
    die('Access denied.');
}

require_once '../config/database.php';

// Handle Add Staff
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_staff'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $role = $_POST['role'];
    
    $stmt = $conn->prepare("INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $password, $name, $role);
    if ($stmt->execute()) { $success = "Staff added successfully!"; }
    else { $error = "Error adding staff: " . $conn->error; }
    $stmt->close();
}

// Handle Delete Staff
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $success = "Staff deleted successfully!";
    } else { $error = "You cannot delete your own account!"; }
}

// Handle Update Staff Role
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_role'])) {
    $id = $_POST['id'];
    $role = $_POST['role'];
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $id);
    $stmt->execute();
    $stmt->close();
    $success = "Staff role updated successfully!";
}

// Fetch all staff
$result = $conn->query("SELECT id, username, name, role, created_at FROM users ORDER BY created_at DESC");

// Get counts for sidebar badges
$job_count = $conn->query("SELECT COUNT(*) as count FROM job_applications")->fetch_assoc()['count'];
$event_count = $conn->query("SELECT COUNT(*) as count FROM event_bookings")->fetch_assoc()['count'];
$reservation_count = $conn->query("SELECT COUNT(*) as count FROM table_reservations")->fetch_assoc()['count'];
$sponsorship_count = $conn->query("SELECT COUNT(*) as count FROM komuniti_events")->fetch_assoc()['count'];
$new_jobs = $conn->query("SELECT COUNT(*) as count FROM job_applications WHERE is_read = 0")->fetch_assoc()['count'];
$new_events = $conn->query("SELECT COUNT(*) as count FROM event_bookings WHERE is_read = 0")->fetch_assoc()['count'];
$new_reservations = $conn->query("SELECT COUNT(*) as count FROM table_reservations WHERE is_read = 0")->fetch_assoc()['count'];
$new_sponsorships = $conn->query("SELECT COUNT(*) as count FROM komuniti_events WHERE is_read = 0")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Management - Tuah Cafe</title>
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

        /* Profile Dropdown - FIXED with click */
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

        /* Messages */
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

        /* Table Card */
        .table-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .card-header h2 {
            color: #2d1f16;
            font-size: 24px;
            font-weight: 700;
        }

        /* Buttons */
        .add-btn {
            background: linear-gradient(135deg, #c49b66, #a87d4a);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(196, 155, 102, 0.4);
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
        }

        .delete-btn {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .delete-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4);
        }

        /* Table Styles */
        .data-table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f9f5f0;
            color: #2d1f16;
            font-weight: 700;
            padding: 15px;
            text-align: left;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f0e6d8;
            color: #5a4a3a;
            font-size: 14px;
        }

        tr:hover {
            background: #fef9f4;
        }

        /* Role Badges */
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

        /* Role Select Dropdown */
        .role-select {
            padding: 6px 12px;
            border-radius: 50px;
            border: 1px solid #e0d5c8;
            font-size: 12px;
            cursor: pointer;
            background: white;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .role-select:hover {
            border-color: #c49b66;
            box-shadow: 0 0 0 2px rgba(196, 155, 102, 0.2);
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

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.show {
            display: flex;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background: white;
            border-radius: 24px;
            padding: 35px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0e6d8;
        }

        .modal-header h2 {
            color: #2d1f16;
            font-size: 24px;
            font-weight: 700;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
            transition: all 0.2s ease;
        }

        .close-modal:hover {
            color: #dc3545;
            transform: scale(1.1);
        }

        /* Form Styles */
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

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #f0e6d8;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #c49b66;
            box-shadow: 0 0 0 3px rgba(196, 155, 102, 0.1);
        }

        .btn-submit {
            background: linear-gradient(135deg, #c49b66, #a87d4a);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(196, 155, 102, 0.4);
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="logo.png" alt="Tuah Cafe Logo" style="width: 80px; height: auto; margin-bottom: 10px;">
        <h2>Tuah Cafe</h2>
        <p>Admin Panel</p>
    </div>
    <ul class="sidebar-nav">
        <li onclick="location.href='dashboard.php'">
            <a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        </li>
        <li onclick="location.href='dashboard.php#applications'">
            <a href="#"><i class="fas fa-briefcase"></i> Job Applications</a>
            <?php if ($new_jobs > 0): ?><span class="badge"><?php echo $new_jobs; ?></span><?php endif; ?>
        </li>
        <li onclick="location.href='dashboard.php#event-bookings'">
            <a href="#"><i class="fas fa-calendar"></i> Event Bookings</a>
            <?php if ($new_events > 0): ?><span class="badge"><?php echo $new_events; ?></span><?php endif; ?>
        </li>
        <li onclick="location.href='dashboard.php#table-reservation'">
            <a href="#"><i class="fas fa-chair"></i> Table Reservation</a>
            <?php if ($new_reservations > 0): ?><span class="badge"><?php echo $new_reservations; ?></span><?php endif; ?>
        </li>
        <li onclick="location.href='dashboard.php#sponsorships'">
            <a href="#"><i class="fas fa-handshake"></i> Sponsorship</a>
            <?php if ($new_sponsorships > 0): ?><span class="badge"><?php echo $new_sponsorships; ?></span><?php endif; ?>
        </li>
        <li class="active">
            <a href="#"><i class="fas fa-users"></i> Staff Management</a>
        </li>
    </ul>
</div>

<div class="main-content">
    <div class="top-header">
        <div>
            <h1>Staff Management</h1>
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
        <?php if (isset($success)): ?>
            <div class="success-msg">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="table-card">
            <div class="card-header">
                <h2><i class="fas fa-users"></i> Staff Members</h2>
                <button class="add-btn" onclick="openModal()">
                    <i class="fas fa-plus"></i> Add New Staff
                </button>
            </div>
            
            <div class="data-table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td>
                                        <span class="role-badge role-<?php echo $row['role']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $row['role'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <select name="role" class="role-select" onchange="this.form.submit()">
                                                <option value="staff" <?php echo $row['role'] == 'staff' ? 'selected' : ''; ?>>Staff</option>
                                                <option value="admin" <?php echo $row['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                <option value="main_admin" <?php echo $row['role'] == 'main_admin' ? 'selected' : ''; ?>>Main Admin</option>
                                            </select>
                                            <input type="hidden" name="update_role" value="1">
                                        </form>
                                        <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                            <a href="?delete=<?php echo $row['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Delete this staff?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center; padding: 40px; color: #999;">
                                    <i class="fas fa-user-slash"></i> No staff found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Simple Footer at bottom -->
    <footer class="simple-footer">
        <p>© 2026 Tuah Cafe. Hak cipta terpelihara.</p>
    </footer>
</div>

<!-- Add Staff Modal -->
<div id="addStaffModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-user-plus"></i> Add New Staff</h2>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Username *</label>
                <input type="text" name="username" placeholder="Enter username" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password *</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-id-card"></i> Full Name *</label>
                <input type="text" name="name" placeholder="Enter full name" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-tag"></i> Role *</label>
                <select name="role" required>
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                    <option value="main_admin">Main Admin</option>
                </select>
            </div>
            <button type="submit" name="add_staff" class="btn-submit">
                <i class="fas fa-save"></i> Add Staff
            </button>
        </form>
    </div>
</div>

<script>
function toggleDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    dropdown.classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('profileDropdown');
    const profileBtn = document.querySelector('.profile-btn');
    if (dropdown && profileBtn && !profileBtn.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});

function openModal() {
    document.getElementById('addStaffModal').classList.add('show');
}

function closeModal() {
    document.getElementById('addStaffModal').classList.remove('show');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('addStaffModal');
    if (event.target === modal) {
        modal.classList.remove('show');
    }
}
</script>

</body>
</html>