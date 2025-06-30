<?php
// Script to check and fix database schema for password storage
// Delete this file after fixing the issue

$host = "localhost";
$username = "root";
$password = "";
$dbname = "enrollment_db2";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

echo "<h2>Database Schema Check & Fix</h2>";

// Check current table structure
echo "<h3>1. Current Table Structure:</h3>";
$result = $conn->query("DESCRIBE users");
if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Length</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        
        // Check password field specifically
        if ($row['Field'] === 'password') {
            preg_match('/\((\d+)\)/', $row['Type'], $matches);
            $length = isset($matches[1]) ? $matches[1] : 'Unknown';
            echo "<td style='color: " . ($length < 255 ? 'red' : 'green') . ";'>" . $length . "</td>";
            
            if ($length < 255) {
                echo "</tr><tr><td colspan='3' style='color: red; font-weight: bold;'>";
                echo "❌ PASSWORD FIELD TOO SHORT! Need at least 255 characters for bcrypt hashes.";
                echo "</td>";
            }
        } else {
            echo "<td>-</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Error: " . $conn->error;
}

// Check existing data
echo "<h3>2. Current Password Data:</h3>";
$result = $conn->query("SELECT id, email, LENGTH(password) as hash_length, LEFT(password, 30) as hash_preview FROM users");
if ($result && $result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Email</th><th>Hash Length</th><th>Hash Preview</th><th>Status</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td style='color: " . ($row['hash_length'] < 60 ? 'red' : 'green') . ";'>" . $row['hash_length'] . "</td>";
        echo "<td>" . $row['hash_preview'] . "...</td>";
        echo "<td>" . ($row['hash_length'] < 60 ? "❌ TRUNCATED" : "✅ OK") . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No users found.";
}

// Automatic fix
echo "<h3>3. Automatic Fix:</h3>";

// First, let's alter the table to increase password column size
$alter_sql = "ALTER TABLE users MODIFY COLUMN password VARCHAR(255)";
if ($conn->query($alter_sql)) {
    echo "<p style='color: green;'>✅ Password column size updated to VARCHAR(255)</p>";
    
    // Now we need to rehash any truncated passwords
    // Since we can't recover truncated hashes, we'll need to reset them
    $truncated_users = $conn->query("SELECT id, email FROM users WHERE LENGTH(password) < 60");
    
    if ($truncated_users && $truncated_users->num_rows > 0) {
        echo "<p style='color: orange;'>⚠️ Found " . $truncated_users->num_rows . " users with truncated password hashes.</p>";
        echo "<p><strong>These users will need to reset their passwords:</strong></p>";
        echo "<ul>";
        while ($user = $truncated_users->fetch_assoc()) {
            echo "<li>" . $user['email'] . " (ID: " . $user['id'] . ")</li>";
        }
        echo "</ul>";
        
        // For demonstration, let's fix the specific user mentioned (jack3@gmail.com)
        $correct_hash = password_hash('password123', PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $conn->prepare($update_sql);
        $email = 'jack3@gmail.com';
        $stmt->bind_param("ss", $correct_hash, $email);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✅ Fixed password for jack3@gmail.com</p>";
            echo "<p>New hash length: " . strlen($correct_hash) . " characters</p>";
        } else {
            echo "<p style='color: red;'>❌ Error updating password: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color: green;'>✅ All password hashes are proper length</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Error updating table: " . $conn->error . "</p>";
}

// Test the fix
echo "<h3>4. Test the Fix:</h3>";
$test_result = $conn->query("SELECT email, LENGTH(password) as hash_length FROM users WHERE email = 'jack3@gmail.com'");
if ($test_result && $test_result->num_rows > 0) {
    $user = $test_result->fetch_assoc();
    echo "<p>jack3@gmail.com password hash length: <strong>" . $user['hash_length'] . "</strong></p>";
    
    if ($user['hash_length'] >= 60) {
        echo "<p style='color: green;'>✅ Hash length is now correct! Try logging in.</p>";
    } else {
        echo "<p style='color: red;'>❌ Hash is still truncated.</p>";
    }
}

echo "<h3>5. Next Steps:</h3>";
echo "<ol>";
echo "<li>Try logging in with jack3@gmail.com and password123</li>";
echo "<li>If it works, delete this file for security</li>";
echo "<li>If other users have login issues, they may need to reset their passwords</li>";
echo "<li>For new signups, passwords should now be stored correctly</li>";
echo "</ol>";

$conn->close();
?>