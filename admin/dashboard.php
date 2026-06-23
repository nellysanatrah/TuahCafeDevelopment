<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$role = $_SESSION['role'];
$name = $_SESSION['name'];
require_once '../config/database.php';

// Get counts for summaries (total counts)
$job_count = $conn->query("SELECT COUNT(*) as count FROM job_applications")->fetch_assoc()['count'];
$event_count = $conn->query("SELECT COUNT(*) as count FROM event_bookings")->fetch_assoc()['count'];
$reservation_count = $conn->query("SELECT COUNT(*) as count FROM table_reservations")->fetch_assoc()['count'];
$sponsorship_count = $conn->query("SELECT COUNT(*) as count FROM komuniti_events")->fetch_assoc()['count'];

// Get UNREAD counts for badges
$new_jobs = $conn->query("SELECT COUNT(*) as count FROM job_applications WHERE is_read = 0")->fetch_assoc()['count'];
$new_events = $conn->query("SELECT COUNT(*) as count FROM event_bookings WHERE is_read = 0")->fetch_assoc()['count'];
$new_reservations = $conn->query("SELECT COUNT(*) as count FROM table_reservations WHERE is_read = 0")->fetch_assoc()['count'];
$new_sponsorships = $conn->query("SELECT COUNT(*) as count FROM komuniti_events WHERE is_read = 0")->fetch_assoc()['count'];

// Get recent unread items for announcements
$recent_jobs = $conn->query("SELECT id, full_name, applying_position, submitted_at FROM job_applications WHERE is_read = 0 ORDER BY submitted_at DESC LIMIT 5");
$recent_events = $conn->query("SELECT id, nama_penuh as customer_name, jenis_acara as event_type, tarikh_acara as event_date FROM event_bookings WHERE is_read = 0 ORDER BY tarikh_acara DESC LIMIT 5");
$recent_reservations = $conn->query("SELECT id, full_name as customer_name, guests_count as guests, reservation_date FROM table_reservations WHERE is_read = 0 ORDER BY reservation_date DESC LIMIT 5");
$recent_sponsorships = $conn->query("SELECT id, name, proposal FROM komuniti_events WHERE is_read = 0 ORDER BY id DESC LIMIT 5");

$total_new = $new_jobs + $new_events + $new_reservations + $new_sponsorships;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Tuah Cafe Admin Dashboard</title>
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

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 35px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #c49b66, #e8c49a);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 42px;
            font-weight: 800;
            background: linear-gradient(135deg, #c49b66, #a87d4a);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #8b7355;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Announcements Section */
        .announcements-section {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 35px;
        }

        .announcements-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f0e6d8;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .announcements-header h3 {
            color: #2d1f16;
            font-size: 20px;
            font-weight: 700;
        }

        .new-badge {
            background: linear-gradient(135deg, #c49b66, #a87d4a);
            color: white;
            font-size: 11px;
            padding: 5px 12px;
            border-radius: 50px;
            font-weight: 600;
        }

        .announcement-item {
            padding: 15px 0;
            border-bottom: 1px solid #f0e6d8;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s ease;
        }

        .announcement-item:hover {
            background: #fef9f4;
            padding-left: 10px;
        }

        .announcement-content {
            flex: 1;
        }

        .announcement-title {
            font-weight: 700;
            color: #2d1f16;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .announcement-title i {
            color: #c49b66;
        }

        .announcement-detail {
            font-size: 13px;
            color: #8b7355;
        }

        .announcement-time {
            font-size: 11px;
            color: #c49b66;
            margin-right: 15px;
            font-weight: 500;
        }

        .view-link {
            background: linear-gradient(135deg, #c49b66, #a87d4a);
            color: white;
            padding: 6px 15px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-block;
        }

        .view-link:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 8px rgba(196, 155, 102, 0.4);
        }

        /* Content Sections */
        .content-section {
            display: none;
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            animation: fadeIn 0.3s ease;
        }

        .content-section.active {
            display: block;
        }

        .content-section h2 {
            color: #2d1f16;
            font-size: 24px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0e6d8;
            font-weight: 700;
        }

        #dashboard-view {
            display: block;
        }

        #dashboard-view.hide {
            display: none;
        }

        /* Tables */
        .container {
            background: transparent;
            padding: 0;
        }

        .data-table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        th {
            background: #f9f5f0;
            color: #2d1f16;
            font-weight: 700;
            padding: 15px;
            text-align: left;
            font-size: 14px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f0e6d8;
            color: #5a4a3a;
        }

        tr:hover {
            background: #fef9f4;
        }

        /* Buttons */
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 50px;
            color: white;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }

        .view-btn { background: linear-gradient(135deg, #17a2b8, #138496); }
        .delete-btn { background: linear-gradient(135deg, #dc3545, #c82333); }
        .approve-btn { background: linear-gradient(135deg, #28a745, #218838); }
        .reject-btn { background: linear-gradient(135deg, #dc3545, #c82333); }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        /* Status Badges */
        .status {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-interviewed { background: #d1ecf1; color: #0c5460; }
        .status-hired { background: #d4edda; color: #155724; }

        .status-select {
            padding: 6px 12px;
            border-radius: 50px;
            border: 1px solid #e0d5c8;
            font-size: 12px;
            cursor: pointer;
            background: white;
            transition: all 0.2s ease;
        }

        .status-select:hover {
            border-color: #c49b66;
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

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
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
    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profileDropdown');
            const profileBtn = document.querySelector('.profile-btn');
            if (!profileBtn.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        function markAsRead(id, table, sectionId, element) {
            fetch('mark_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'table=' + table + '&id=' + id
            });
            
            const announcementItem = event.target.closest('.announcement-item');
            if (announcementItem) {
                announcementItem.remove();
            }
            
            const announcementsContainer = document.querySelector('.announcements-section .announcement-item');
            if (!announcementsContainer) {
                document.querySelector('.announcements-section .new-badge').style.display = 'none';
                document.querySelector('.announcements-section .announcements-header').innerHTML = '<h3><i class="fas fa-bullhorn"></i> Announcements</h3><span class="new-badge" style="display:none;">0 new</span>';
                const emptyMsg = document.createElement('div');
                emptyMsg.className = 'announcement-item';
                emptyMsg.innerHTML = '<div class="announcement-content"><div class="announcement-detail" style="text-align:center; color:#999;">No new announcements. Everything is up to date!</div></div>';
                document.querySelector('.announcements-section').appendChild(emptyMsg);
            }
            
            showSection(sectionId, element, table);
        }
        
        function markAllAsRead(table, element) {
            fetch('mark_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'table=' + table
            });
            
            const badge = element.querySelector('.badge');
            if (badge) {
                badge.remove();
            }
        }
        
        function showSection(sectionId, element, tableName) {
            if (tableName) {
                markAllAsRead(tableName, element);
            }
            
            const dashboardView = document.getElementById('dashboard-view');
            const contentSections = document.querySelectorAll('.content-section');
            if (sectionId === 'dashboard') {
                dashboardView.classList.remove('hide');
                contentSections.forEach(section => section.classList.remove('active'));
            } else {
                dashboardView.classList.add('hide');
                contentSections.forEach(section => section.classList.remove('active'));
                document.getElementById(sectionId).classList.add('active');
            }
            document.querySelectorAll('.sidebar-nav li').forEach(li => li.classList.remove('active'));
            if (element) element.classList.add('active');
            
            history.pushState(null, null, '#' + sectionId);
        }
        
        function showSectionFromHash() {
            var hash = window.location.hash;
            if (hash && hash !== '#') {
                var sectionId = hash.substring(1);
                var sectionMap = {
                    'applications': 1,
                    'event-bookings': 2,
                    'table-reservation': 3,
                    'sponsorships': 4
                };
                var index = sectionMap[sectionId];
                if (index !== undefined) {
                    var navItems = document.querySelectorAll('.sidebar-nav li');
                    var navElement = navItems[index];
                    if (navElement) {
                        showSection(sectionId, navElement, '');
                    }
                }
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            showSectionFromHash();
        });
    </script>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="logo.png" alt="Tuah Cafe Logo" style="width: 80px; height: auto; margin-bottom: 10px;">
        <h2>Tuah Cafe</h2>
        <p>Admin Panel</p>
    </div>
    <ul class="sidebar-nav">
        <li class="active" onclick="showSection('dashboard', this, '')">
            <a href="#"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        </li>
        <li onclick="showSection('applications', this, 'job_applications')">
            <a href="#"><i class="fas fa-briefcase"></i> Job Applications</a>
            <?php if ($new_jobs > 0): ?><span class="badge"><?php echo $new_jobs; ?></span><?php endif; ?>
        </li>
        <li onclick="showSection('event-bookings', this, 'event_bookings')">
            <a href="#"><i class="fas fa-calendar"></i> Event Bookings</a>
            <?php if ($new_events > 0): ?><span class="badge"><?php echo $new_events; ?></span><?php endif; ?>
        </li>
        <li onclick="showSection('table-reservation', this, 'table_reservations')">
            <a href="#"><i class="fas fa-chair"></i> Table Reservation</a>
            <?php if ($new_reservations > 0): ?><span class="badge"><?php echo $new_reservations; ?></span><?php endif; ?>
        </li>
        <li onclick="showSection('sponsorships', this, 'komuniti_events')">
            <a href="#"><i class="fas fa-handshake"></i> Sponsorship</a>
            <?php if ($new_sponsorships > 0): ?><span class="badge"><?php echo $new_sponsorships; ?></span><?php endif; ?>
        </li>
        <?php if ($role == 'admin' || $role == 'main_admin'): ?>
        <li onclick="location.href='admin_staff.php'">
            <a href="#"><i class="fas fa-users"></i> Staff Management</a>
        </li>
        <?php endif; ?>
    </ul>
</div>

<div class="main-content">
    <div class="top-header">
        <div>
            <h1>Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($name); ?> </p>
        </div>
        <div class="profile-dropdown">
            <button class="profile-btn" onclick="toggleDropdown()">
                <i class="fas fa-user-circle"></i> 
                <?php echo htmlspecialchars($name); ?> 
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="profile-dropdown-content" id="profileDropdown">
                <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        <div id="dashboard-view">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $job_count; ?></div>
                    <div class="stat-label">Job Applications</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $event_count; ?></div>
                    <div class="stat-label">Event Bookings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $reservation_count; ?></div>
                    <div class="stat-label">Table Reservations</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $sponsorship_count; ?></div>
                    <div class="stat-label">Sponsorships</div>
                </div>
            </div>

            <div class="announcements-section">
                <div class="announcements-header">
                    <h3><i class="fas fa-bullhorn"></i> Recent Announcements</h3>
                    <?php if ($total_new > 0): ?>
                    <span class="new-badge"><?php echo $total_new; ?> new</span>
                    <?php endif; ?>
                </div>
                
                <?php if ($recent_jobs && $recent_jobs->num_rows > 0): ?>
                    <?php while($job = $recent_jobs->fetch_assoc()): ?>
                    <div class="announcement-item">
                        <div class="announcement-content">
                            <div class="announcement-title">
                                <i class="fas fa-briefcase"></i> New Job Application
                            </div>
                            <div class="announcement-detail">
                                <?php echo htmlspecialchars($job['full_name']); ?> applied for <?php echo htmlspecialchars($job['applying_position']); ?>
                            </div>
                        </div>
                        <div>
                            <span class="announcement-time"><?php echo date('d/m/Y', strtotime($job['submitted_at'])); ?></span>
                            <a href="#" onclick="markAsRead(<?php echo $job['id']; ?>, 'job_applications', 'applications', document.querySelector('.sidebar-nav li:nth-child(2)')); return false;" class="view-link">View →</a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
                
                <?php if ($recent_events && $recent_events->num_rows > 0): ?>
                    <?php while($event = $recent_events->fetch_assoc()): ?>
                    <div class="announcement-item">
                        <div class="announcement-content">
                            <div class="announcement-title">
                                <i class="fas fa-calendar"></i> New Event Booking
                            </div>
                            <div class="announcement-detail">
                                <?php echo htmlspecialchars($event['customer_name']); ?> booked <?php echo htmlspecialchars($event['event_type']); ?>
                            </div>
                        </div>
                        <div>
                            <span class="announcement-time"><?php echo date('d/m/Y', strtotime($event['event_date'])); ?></span>
                            <a href="#" onclick="markAsRead(<?php echo $event['id']; ?>, 'event_bookings', 'event-bookings', document.querySelector('.sidebar-nav li:nth-child(3)')); return false;" class="view-link">View →</a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
                
                <?php if ($recent_reservations && $recent_reservations->num_rows > 0): ?>
                    <?php while($reservation = $recent_reservations->fetch_assoc()): ?>
                    <div class="announcement-item">
                        <div class="announcement-content">
                            <div class="announcement-title">
                                <i class="fas fa-chair"></i> New Table Reservation
                            </div>
                            <div class="announcement-detail">
                                <?php echo htmlspecialchars($reservation['customer_name']); ?> reserved for <?php echo $reservation['guests']; ?> guests
                            </div>
                        </div>
                        <div>
                            <span class="announcement-time"><?php echo date('d/m/Y', strtotime($reservation['reservation_date'])); ?></span>
                            <a href="#" onclick="markAsRead(<?php echo $reservation['id']; ?>, 'table_reservations', 'table-reservation', document.querySelector('.sidebar-nav li:nth-child(4)')); return false;" class="view-link">View →</a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
                
                <?php if ($recent_sponsorships && $recent_sponsorships->num_rows > 0): ?>
                    <?php while($sponsor = $recent_sponsorships->fetch_assoc()): ?>
                    <div class="announcement-item">
                        <div class="announcement-content">
                            <div class="announcement-title">
                                <i class="fas fa-handshake"></i> New Sponsorship Application
                            </div>
                            <div class="announcement-detail">
                                <?php echo htmlspecialchars($sponsor['name']); ?> submitted an application
                            </div>
                        </div>
                        <div>
                            <span class="announcement-time">New</span>
                            <a href="#" onclick="markAsRead(<?php echo $sponsor['id']; ?>, 'komuniti_events', 'sponsorships', document.querySelector('.sidebar-nav li:nth-child(5)')); return false;" class="view-link">View →</a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php endif; ?>
                
                <?php if ($total_new == 0): ?>
                    <div class="announcement-item">
                        <div class="announcement-content">
                            <div class="announcement-detail" style="text-align:center; color:#c49b66;">
                                All caught up! No new announcements.
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="applications" class="content-section"><h2>Job Applications</h2><?php include 'admin_job_application.php'; ?></div>
        <div id="event-bookings" class="content-section"><h2>Event Bookings</h2><?php include 'admin_event_bookings.php'; ?></div>
        <div id="table-reservation" class="content-section"><h2>Table Reservations</h2><?php include 'admin_table_reservation.php'; ?></div>
        <div id="sponsorships" class="content-section"><h2>Sponsorship Applications</h2><?php include 'admin_sponsorships.php'; ?></div>
    </div>

    <!-- Simple Footer at bottom -->
    <footer class="simple-footer">
        <p>© 2026 Tuah Cafe. Hak cipta terpelihara.</p>
    </footer>
</div>

<script>
function showSectionFromHash() {
    var hash = window.location.hash;
    if (hash && hash !== '#') {
        var sectionId = hash.substring(1);
        var sectionMap = {
            'applications': 1,
            'event-bookings': 2,
            'table-reservation': 3,
            'sponsorships': 4
        };
        var index = sectionMap[sectionId];
        if (index !== undefined) {
            var navItems = document.querySelectorAll('.sidebar-nav li');
            var navElement = navItems[index];
            if (navElement) {
                showSection(sectionId, navElement, '');
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    showSectionFromHash();
});
</script>

</body>
</html>