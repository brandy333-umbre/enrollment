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

// Fetch user data from database
$host = "localhost";
$username = "root";
$password = ""; // Set this if your MySQL has a password
$dbname = "enrollment_db2";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// Get user data using the session user ID
$user_id = $_SESSION['user_id'];
$sql = "SELECT id, full_name, email, nationality, phone, agreed_terms, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
} else {
    // Fallback to session data if database query fails
    $user_data = [
        'id' => $_SESSION['user_id'],
        'full_name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'nationality' => 'Not available',
        'phone' => 'Not available',
        'agreed_terms' => 'Not available',
        'created_at' => 'Not available'
    ];
}

$stmt->close();
$conn->close();
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
                <span class="info-value"><?php echo htmlspecialchars($user_data['id']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Full Name:</span>
                <span class="info-value"><?php echo htmlspecialchars($user_data['full_name']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email Address:</span>
                <span class="info-value"><?php echo htmlspecialchars($user_data['email']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Nationality:</span>
                <span class="info-value"><?php echo htmlspecialchars($user_data['nationality']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Phone:</span>
                <span class="info-value"><?php echo htmlspecialchars($user_data['phone']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Agreed Terms:</span>
                <span class="info-value">
                    <?php 
                    if ($user_data['agreed_terms'] == 1) {
                        echo "✅ Yes - Agreed to Terms and Conditions";
                    } elseif ($user_data['agreed_terms'] == 0) {
                        echo "❌ No - Did not agree to Terms and Conditions";
                    } else {
                        echo htmlspecialchars($user_data['agreed_terms']);
                    }
                    ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Account Created:</span>
                <span class="info-value">
                    <?php 
                    if ($user_data['created_at'] != 'Not available') {
                        echo date('F j, Y \a\t g:i A', strtotime($user_data['created_at']));
                    } else {
                        echo htmlspecialchars($user_data['created_at']);
                    }
                    ?>
                </span>
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
