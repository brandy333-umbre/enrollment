<?php
session_start();

// --- Run this block when the form is submitted ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Connect to the database
    $host = "localhost";
    $username = "root";
    $password = ""; // Set this if your MySQL has a password
    $dbname = "enrollment_db2";

    $conn = new mysqli($host, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("❌ Connection failed: " . $conn->connect_error);
    }

    // Get form data safely and normalize email
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password_plain = $_POST['password'] ?? '';

    // Add debugging (remove these lines after fixing)
    error_log("Login attempt for email: " . $email);
    
    // Check if user exists and password is correct
    $sql = "SELECT id, full_name, email, password FROM users WHERE LOWER(email) = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        $error_message = "❌ Database error occurred";
    } else {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Add debugging (remove after fixing)
            error_log("User found in database");
            error_log("Stored password hash: " . substr($user['password'], 0, 10) . "...");
            
            // Verify password
            if (password_verify($password_plain, $user['password'])) {
                // Login successful - store user data in session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['logged_in'] = true;
                
                error_log("Login successful for user: " . $user['email']);
                
                // Redirect to profile page
                header("Location: profile.php");
                exit();
            } else {
                error_log("Password verification failed");
                $error_message = "❌ Invalid email or password";
            }
        } else {
            error_log("No user found with email: " . $email);
            $error_message = "❌ Invalid email or password";
        }

        $stmt->close();
    }
    $conn->close();
}
?>

<!-- --- Login Form --- -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="form.js" defer></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input[type="email"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0,123,255,0.3);
        }
        .submit-btn {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 15px;
        }
        .submit-btn:hover {
            background-color: #0056b3;
        }
        .create-account-btn {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            box-sizing: border-box;
        }
        .create-account-btn:hover {
            background-color: #218838;
        }
        .error-message {
            margin-bottom: 20px;
            padding: 12px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            color: #721c24;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container" x-data="loginForm()">
        <h2>Login</h2>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" x-model="form.email" required>
            </div>
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" x-model="form.password" required>
            </div>
            <button type="submit" class="submit-btn">Login</button>
        </form>
        
        <a href="signup2.php" class="create-account-btn">Create Account</a>
    </div>

    <script>
    function loginForm() {
        return {
            form: {
                email: '',
                password: ''
            }
        }
    }
    </script>
</body>
</html>
