<?php
// Simple test script to debug login issues
// Delete this file after fixing the login problem

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$dbname = "enrollment_db2";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

echo "<h2>Login Debug Test</h2>";

// Test 1: Check if users table exists and show structure
echo "<h3>1. Database Table Structure:</h3>";
$result = $conn->query("DESCRIBE users");
if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Users table not found or error: " . $conn->error;
}

// Test 2: Show all users (without showing actual passwords)
echo "<h3>2. Users in Database:</h3>";
$result = $conn->query("SELECT id, full_name, email, LEFT(password, 10) as password_preview FROM users");
if ($result && $result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Full Name</th><th>Email</th><th>Password Hash Preview</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['full_name'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['password_preview'] . "...</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ No users found or error: " . $conn->error;
}

// Test 3: Test password verification with a known user
echo "<h3>3. Password Verification Test:</h3>";
if (isset($_POST['test_email']) && isset($_POST['test_password'])) {
    $test_email = strtolower(trim($_POST['test_email']));
    $test_password = $_POST['test_password'];
    
    $sql = "SELECT id, full_name, email, password FROM users WHERE LOWER(email) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $test_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "<p>✅ User found: " . $user['full_name'] . " (" . $user['email'] . ")</p>";
        
        if (password_verify($test_password, $user['password'])) {
            echo "<p style='color: green;'>✅ Password verification SUCCESSFUL!</p>";
        } else {
            echo "<p style='color: red;'>❌ Password verification FAILED!</p>";
            echo "<p>Hash from DB: " . substr($user['password'], 0, 20) . "...</p>";
            
            // Test if it's a plain password stored by mistake
            if ($test_password === $user['password']) {
                echo "<p style='color: orange;'>⚠️ WARNING: Password appears to be stored as plain text!</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ User not found with email: " . htmlspecialchars($test_email) . "</p>";
    }
    $stmt->close();
}

echo "<h3>Test Login:</h3>";
echo "<form method='POST'>";
echo "<p>Email: <input type='email' name='test_email' required></p>";
echo "<p>Password: <input type='password' name='test_password' required></p>";
echo "<p><button type='submit'>Test Login</button></p>";
echo "</form>";

echo "<p><strong>Instructions:</strong></p>";
echo "<ol>";
echo "<li>First, create a test account using signup2.php</li>";
echo "<li>Then use this form to test if login works</li>";
echo "<li>Check the error logs in your web server for detailed debugging info</li>";
echo "<li>Delete this file after fixing the login issue</li>";
echo "</ol>";

$conn->close();
?>