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
    <title>Welcome</title>
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
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 30px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dropdown-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .dropdown-btn::after {
            content: '▼';
            font-size: 12px;
            transition: transform 0.3s ease;
        }

        .dropdown.active .dropdown-btn::after {
            transform: rotate(180deg);
        }

        .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            min-width: 200px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
            overflow: hidden;
            z-index: 1001;
            margin-top: 8px;
        }

        .dropdown.active .dropdown-content {
            display: block;
            animation: fadeInDown 0.3s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-content a {
            color: #333;
            padding: 15px 20px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            border-bottom: 1px solid #f0f0f0;
        }

        .dropdown-content a:last-child {
            border-bottom: none;
        }

        .dropdown-content a:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateX(5px);
        }

        .dropdown-content a::before {
            font-size: 18px;
        }

        .profile-link::before {
            content: '👤';
        }

        .logout-link::before {
            content: '🚪';
        }

        .main-content {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 100px 20px 20px;
        }

        .welcome-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 60px 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .welcome-icon {
            font-size: 4em;
            margin-bottom: 20px;
            display: block;
        }

        .welcome-title {
            font-size: 2.5em;
            color: #2d3748;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .welcome-subtitle {
            font-size: 1.2em;
            color: #4a5568;
            line-height: 1.6;
        }

        .user-name {
            color: #667eea;
            font-weight: 700;
        }

        @media (max-width: 600px) {
            .header {
                padding: 15px 20px;
            }
            
            .welcome-card {
                padding: 40px 30px;
                margin: 20px;
            }
            
            .welcome-title {
                font-size: 2em;
            }
            
            .welcome-subtitle {
                font-size: 1.1em;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="dropdown" id="userDropdown">
            <button class="dropdown-btn" onclick="toggleDropdown()">
                Menu
            </button>
            <div class="dropdown-content">
                <a href="profile.php" class="profile-link">Profile</a>
                <a href="?logout=1" class="logout-link">Logout</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="welcome-card">
            <span class="welcome-icon">👋</span>
            <h1 class="welcome-title">Welcome, <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>!</h1>
            <p class="welcome-subtitle">
                Thank you for joining us. We're excited to have you here!
            </p>
        </div>
    </div>

    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('active');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            const dropdownBtn = dropdown.querySelector('.dropdown-btn');
            
            if (!dropdown.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });

        // Close dropdown when pressing Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const dropdown = document.getElementById('userDropdown');
                dropdown.classList.remove('active');
            }
        });
    </script>
</body>
</html>
