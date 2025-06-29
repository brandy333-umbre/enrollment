<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .profile-info {
            margin-bottom: 30px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        .info-value {
            color: #333;
        }
        .logout-btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            margin-top: 20px;
        }
        .logout-btn:hover {
            background-color: #c82333;
        }
        .welcome-message {
            text-align: center;
            color: #28a745;
            font-size: 18px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>User Profile</h1>
        
        <div class="welcome-message">
            Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👋
        </div>
        
        <div class="profile-info">
            <div class="info-row">
                <span class="info-label">User ID:</span>
                <span class="info-value"><?php echo htmlspecialchars($_SESSION['user_id']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Full Name:</span>
                <span class="info-value"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email Address:</span>
                <span class="info-value"><?php echo htmlspecialchars($_SESSION['user_email']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Login Status:</span>
                <span class="info-value">✅ Logged In</span>
            </div>
        </div>
        
        <a href="?logout=1" class="logout-btn">Logout</a>
    </div>
</body>
</html>
