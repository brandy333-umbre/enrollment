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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Student Enrollment</title>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="form.js" defer></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 20px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #667eea;
        }
        
        .main-content {
            padding: 40px 20px;
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .welcome-title {
            font-size: 42px;
            color: #333;
            margin-bottom: 10px;
            font-weight: 300;
        }
        
        .welcome-name {
            font-size: 32px;
            color: #667eea;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .welcome-message {
            font-size: 18px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.8);
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-secondary:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }
        
        /* Mobile Menu */
        .mobile-menu {
            position: fixed;
            top: 0;
            left: -300px;
            width: 300px;
            height: 100vh;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            z-index: 1000;
            padding: 20px;
            transition: left 0.3s ease;
            box-shadow: 2px 0 20px rgba(0,0,0,0.1);
        }
        
        .mobile-menu.show {
            left: 0;
        }
        
        .menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .menu-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        .menu-items {
            list-style: none;
        }
        
        .menu-items li {
            margin-bottom: 15px;
        }
        
        .menu-items a {
            display: block;
            padding: 15px 20px;
            color: #333;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .menu-items a:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateX(5px);
        }
        
        .menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .menu-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .user-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }
        
        .user-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .user-email {
            font-size: 14px;
            opacity: 0.9;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
            
            .welcome-card {
                padding: 30px 20px;
                margin: 10px;
            }
            
            .welcome-title {
                font-size: 32px;
            }
            
            .welcome-name {
                font-size: 24px;
            }
            
            .welcome-message {
                font-size: 16px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }
            
            .btn {
                width: 100%;
                max-width: 250px;
                padding: 15px 20px;
            }
        }
        
        @media (max-width: 480px) {
            .header {
                padding: 10px 15px;
            }
            
            .logo {
                font-size: 20px;
            }
            
            .main-content {
                padding: 20px 10px;
            }
            
            .welcome-card {
                padding: 20px 15px;
            }
            
            .welcome-title {
                font-size: 28px;
            }
            
            .welcome-name {
                font-size: 20px;
            }
        }
    </style>
</head>
<body x-data="welcomePage()">
    <!-- Header -->
    <div class="header">
        <div class="logo">Student Enrollment</div>
        <button class="menu-toggle" @click="toggleMenu()">☰</button>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="welcome-card">
            <h1 class="welcome-title">Welcome</h1>
            <div class="welcome-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
            <p class="welcome-message">
                Great to see you! Your account is now active and ready to use. 
                You can access your profile, manage your enrollment information, 
                and explore all the features available to you.
            </p>
            <div class="action-buttons">
                <a href="profile.php" class="btn btn-primary">View My Profile</a>
                <a href="?logout=1" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Overlay -->
    <div class="menu-overlay" :class="{ 'show': showMenu }" @click="closeMenu()"></div>

    <!-- Mobile Menu -->
    <div class="mobile-menu" :class="{ 'show': showMenu }">
        <div class="menu-header">
            <div class="logo">Menu</div>
            <button class="menu-close" @click="closeMenu()">&times;</button>
        </div>
        
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
            <div class="user-email"><?php echo htmlspecialchars($_SESSION['user_email']); ?></div>
        </div>
        
        <ul class="menu-items">
            <li><a href="welcome.php" @click="closeMenu()">🏠 Home</a></li>
            <li><a href="profile.php" @click="closeMenu()">👤 My Profile</a></li>
            <li><a href="#" @click="closeMenu()">📚 Enrollments</a></li>
            <li><a href="#" @click="closeMenu()">⚙️ Settings</a></li>
            <li><a href="?logout=1" @click="closeMenu()">🚪 Logout</a></li>
        </ul>
    </div>
</body>
</html>