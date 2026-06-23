<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if ($password == $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            header("Location: dashboard.php");
            exit();
        } else { $error = "Invalid password."; }
    } else { $error = "User not found."; }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Login - Tuah Cafe Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2d1f16 0%, #4a372e 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        /* Background decoration */
        body::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(196,155,102,0.1) 0%, transparent 70%);
            top: -150px;
            right: -150px;
            border-radius: 50%;
        }

        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(196,155,102,0.08) 0%, transparent 70%);
            bottom: -200px;
            left: -200px;
            border-radius: 50%;
        }

        /* Login Card */
        .login-card {
            background: white;
            border-radius: 32px;
            padding: 45px 40px;
            width: 420px;
            max-width: 90%;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
            animation: fadeInUp 0.6s ease;
            position: relative;
            z-index: 1;
        }

        /* Logo Section */
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-section img {
            width: 80px;
            height: auto;
            margin-bottom: 15px;
        }

        .logo-section h1 {
            font-size: 28px;
            color: #2d1f16;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .logo-section p {
            font-size: 13px;
            color: #8b7355;
            margin-top: 5px;
        }

        /* Welcome Text */
        .welcome-text {
            text-align: center;
            margin-bottom: 30px;
        }

        .welcome-text h2 {
            font-size: 24px;
            color: #2d1f16;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .welcome-text p {
            font-size: 14px;
            color: #8b7355;
        }

        /* Form Group */
        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: #2d1f16;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group label i {
            color: #c49b66;
            margin-right: 8px;
            width: 20px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #c49b66;
            font-size: 16px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 2px solid #f0e6d8;
            border-radius: 16px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
            background: #fefcf9;
        }

        .form-group input:focus {
            outline: none;
            border-color: #c49b66;
            box-shadow: 0 0 0 3px rgba(196,155,102,0.2);
            background: white;
        }

        /* Login Button */
        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #c49b66, #a87d4a);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(196,155,102,0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        /* Error Message */
        .error-message {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.3s ease;
        }

        .error-message i {
            font-size: 16px;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 30px 25px;
            }
            
            .logo-section img {
                width: 60px;
            }
            
            .logo-section h1 {
                font-size: 24px;
            }
            
            .welcome-text h2 {
                font-size: 20px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<div class="login-card">
    <div class="logo-section">
        <img src="logo.png" alt="Tuah Cafe Logo">
        <h1>Tuah Cafe</h1>
        <p>Admin Portal</p>
    </div>

    <div class="welcome-text">
        <h2>Welcome Back!</h2>
        <p>Please login to access the admin dashboard</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $error; ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label><i class="fas fa-user"></i> Username</label>
            <div class="input-wrapper">
                <i class="fas fa-user-circle"></i>
                <input type="text" name="username" placeholder="Enter your username" required autocomplete="off">
            </div>
        </div>

        <div class="form-group">
            <label><i class="fas fa-lock"></i> Password</label>
            <div class="input-wrapper">
                <i class="fas fa-key"></i>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
        </div>

        <button type="submit" class="login-btn">
            <i class="fas fa-sign-in-alt"></i> Login
        </button>
    </form>
</div>
</body>
</html>