<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: welcome.php");
    exit();
}

// --- Handle form submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = "localhost";
    $username = "root";
    $password = "";
    $dbname = "enrollment_db2";

    $conn = new mysqli($host, $username, $password, $dbname);

    if ($conn->connect_error) {
        $error_message = "❌ Connection failed: " . $conn->connect_error;
    } else {
        // Get form data safely
        $email = trim($_POST['email'] ?? '');
        $password_plain = trim($_POST['password'] ?? '');

        // Basic validation
        if (empty($email) || empty($password_plain)) {
            $error_message = "❌ Please fill in both email and password";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "❌ Please enter a valid email address";
        } else {
            // Check if user exists and password is correct
            $sql = "SELECT id, full_name, email, password FROM users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Verify password
                if (password_verify($password_plain, $user['password'])) {
                    // Login successful - store user data in session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['logged_in'] = true;
                    
                    // Redirect to welcome page
                    header("Location: welcome.php");
                    exit();
                } else {
                    $error_message = "❌ Invalid email or password";
                }
            } else {
                $error_message = "❌ Invalid email or password";
            }
            $stmt->close();
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Enrollment</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-title {
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .login-subtitle {
            color: #666;
            font-size: 16px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }
        
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }
        
        input[type="email"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }
        
        .validation-error {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
            display: block;
        }
        
        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .create-account-btn {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.8);
            color: #667eea;
            border: 2px solid #667eea;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .create-account-btn:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }
        
        .error-message {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 10px;
            color: #721c24;
            text-align: center;
            font-weight: 500;
        }
        
        .divider {
            text-align: center;
            margin: 20px 0;
            color: #999;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(to right, transparent, #ddd, transparent);
        }
        
        .divider span {
            background: rgba(255, 255, 255, 0.95);
            padding: 0 20px;
            position: relative;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .login-container {
                padding: 30px 20px;
                margin: 10px;
            }
            
            .login-title {
                font-size: 28px;
            }
            
            .login-subtitle {
                font-size: 14px;
            }
            
            input[type="email"], input[type="password"] {
                padding: 12px;
                font-size: 16px; /* Prevents zoom on iOS */
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .login-container {
                padding: 25px 15px;
            }
            
            .login-title {
                font-size: 24px;
            }
        }
        
        /* Loading state */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login-container" x-data="loginForm()" x-init="init()">
        <div class="login-header">
            <h1 class="login-title">Welcome Back</h1>
            <p class="login-subtitle">Please sign in to your account</p>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" @submit="validateForm">
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" x-model="form.email" required>
                <span x-show="errors.email" class="validation-error" x-text="errors.email"></span>
            </div>
            
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" x-model="form.password" required>
                <span x-show="errors.password" class="validation-error" x-text="errors.password"></span>
            </div>
            
            <button type="submit" class="submit-btn" :disabled="isSubmitting">
                <span x-show="isSubmitting" class="loading"></span>
                <span x-show="!isSubmitting">Sign In</span>
                <span x-show="isSubmitting">Signing In...</span>
            </button>
        </form>
        
        <div class="divider">
            <span>Don't have an account?</span>
        </div>
        
        <a href="signup2.php" class="create-account-btn">Create New Account</a>
    </div>
</body>
</html>
