<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle AJAX request for user data
if (isset($_GET['get_user_data'])) {
    header('Content-Type: application/json');
    
    $host = "localhost";
    $username = "root";
    $password = "";
    $dbname = "enrollment_db2";
    
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        echo json_encode(['error' => 'Database connection failed']);
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT id, full_name, email, nationality, phone, created_at, agreed_terms FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Format the creation date
        $user['created_at'] = date('F j, Y \a\t g:i A', strtotime($user['created_at']));
        // Format phone number
        if ($user['phone']) {
            $user['phone'] = $user['phone'];
        }
        echo json_encode($user);
    } else {
        echo json_encode(['error' => 'User not found']);
    }
    
    $stmt->close();
    $conn->close();
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
    <title>My Profile - Student Enrollment</title>
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
        
        .back-btn {
            background: none;
            border: none;
            color: #667eea;
            font-size: 16px;
            cursor: pointer;
            display: none;
        }
        
        .main-content {
            padding: 40px 20px;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .profile-title {
            font-size: 36px;
            color: white;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .profile-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 18px;
        }
        
        .profile-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .loading-spinner {
            text-align: center;
            padding: 40px;
        }
        
        .spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .info-section {
            background: rgba(248, 249, 250, 0.8);
            padding: 25px;
            border-radius: 15px;
            border-left: 4px solid #667eea;
        }
        
        .section-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 15px 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #555;
            min-width: 120px;
        }
        
        .info-value {
            color: #333;
            flex: 1;
            text-align: right;
            word-break: break-word;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-verified {
            background: #cce5ff;
            color: #004085;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
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
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        /* Mobile Menu - Same as welcome page */
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
        
        .menu-items a:hover, .menu-items a.active {
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
            
            .back-btn {
                display: block;
            }
            
            .profile-card {
                padding: 30px 20px;
                margin: 10px;
            }
            
            .profile-title {
                font-size: 28px;
            }
            
            .profile-subtitle {
                font-size: 16px;
            }
            
            .profile-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .info-section {
                padding: 20px;
            }
            
            .info-row {
                flex-direction: column;
                gap: 5px;
                align-items: flex-start;
            }
            
            .info-value {
                text-align: left;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 250px;
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
            
            .profile-card {
                padding: 20px 15px;
            }
            
            .profile-title {
                font-size: 24px;
            }
            
            .info-section {
                padding: 15px;
            }
        }
    </style>
</head>
<body x-data="profilePage()" x-init="init()">
    <!-- Header -->
    <div class="header">
        <div class="logo">Student Enrollment</div>
        <button class="menu-toggle" @click="toggleMenu()">☰</button>
        <button class="back-btn" onclick="history.back()">← Back</button>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="profile-header">
            <h1 class="profile-title">My Profile</h1>
            <p class="profile-subtitle">Manage your account information</p>
        </div>
        
        <div class="profile-card">
            <!-- Loading State -->
            <div x-show="loading" class="loading-spinner">
                <div class="spinner"></div>
                <p style="margin-top: 20px; color: #666;">Loading your profile...</p>
            </div>
            
            <!-- Profile Content -->
            <div x-show="!loading">
                <div class="profile-grid">
                    <!-- Personal Information -->
                    <div class="info-section">
                        <h3 class="section-title">
                            👤 Personal Information
                        </h3>
                        <div class="info-row">
                            <span class="info-label">Full Name:</span>
                            <span class="info-value" x-text="user.full_name || 'Not provided'"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value" x-text="user.email || 'Not provided'"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Nationality:</span>
                            <span class="info-value" x-text="user.nationality || 'Not provided'"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Phone:</span>
                            <span class="info-value" x-text="user.phone || 'Not provided'"></span>
                        </div>
                    </div>
                    
                    <!-- Account Information -->
                    <div class="info-section">
                        <h3 class="section-title">
                            🏛️ Account Details
                        </h3>
                        <div class="info-row">
                            <span class="info-label">User ID:</span>
                            <span class="info-value" x-text="user.id || 'N/A'"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Member Since:</span>
                            <span class="info-value" x-text="user.created_at || 'Not available'"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Account Status:</span>
                            <span class="info-value">
                                <span class="status-badge status-active">Active</span>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Terms Agreed:</span>
                            <span class="info-value">
                                <span x-show="user.agreed_terms == '1'" class="status-badge status-verified">Yes</span>
                                <span x-show="user.agreed_terms != '1'" class="status-badge" style="background: #ffebee; color: #c62828;">No</span>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="welcome.php" class="btn btn-primary">← Back to Home</a>
                    <button class="btn btn-secondary" onclick="alert('Edit functionality coming soon!')">Edit Profile</button>
                    <a href="?logout=1" class="btn btn-danger">Logout</a>
                </div>
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
            <li><a href="profile.php" @click="closeMenu()" class="active">👤 My Profile</a></li>
            <li><a href="#" @click="closeMenu()">📚 Enrollments</a></li>
            <li><a href="#" @click="closeMenu()">⚙️ Settings</a></li>
            <li><a href="?logout=1" @click="closeMenu()">🚪 Logout</a></li>
        </ul>
    </div>
</body>
</html>
