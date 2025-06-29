<?php
session_start();

// --- AJAX endpoint for checking email existence ---
if (isset($_GET['check_email'])) {
    header('Content-Type: application/json');
    
    $host = "localhost";
    $username = "root";
    $password = "";
    $dbname = "enrollment_db2";
    
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        echo json_encode(['exists' => false, 'error' => 'Database connection failed']);
        exit();
    }
    
    $email = trim($_GET['check_email']);
    $sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo json_encode(['exists' => $row['count'] > 0]);
    $stmt->close();
    $conn->close();
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
        // Get and validate form data
        $name = trim($_POST['name'] ?? '');
        $surname = trim($_POST['surname'] ?? '');
        $full_name = trim($name . ' ' . $surname);
        $email = trim($_POST['email'] ?? '');
        $password_plain = $_POST['password'] ?? '';
        $nationality = $_POST['nationality'] ?? '';
        $countryCode = $_POST['countryCode'] ?? '';
        $phoneNumber = trim($_POST['phone'] ?? '');
        $phone = $countryCode . $phoneNumber;
        $agreed_terms = isset($_POST['agreed_terms']) ? 1 : 0;

        // Server-side validation
        $errors = [];
        
        if (empty($name)) $errors[] = "First name is required";
        if (empty($surname)) $errors[] = "Last name is required";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
        if (empty($password_plain) || strlen($password_plain) < 5 || preg_match_all('/\d/', $password_plain) < 2) {
            $errors[] = "Password must be at least 5 characters with at least 2 numbers";
        }
        if (empty($nationality)) $errors[] = "Nationality is required";
        if (empty($phoneNumber) || !preg_match('/^[\d\+\-\s\(\)]+$/', $phoneNumber)) $errors[] = "Valid phone number is required";
        if (!$agreed_terms) $errors[] = "You must agree to the terms and conditions";

        // Check if email already exists
        if (empty($errors)) {
            $sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                $errors[] = "Email already exists. Please use a different email.";
            }
            $stmt->close();
        }

        if (empty($errors)) {
            // Hash password and insert user
            $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (full_name, email, password, nationality, phone, agreed_terms)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $full_name, $email, $password_hashed, $nationality, $phone, $agreed_terms);

            if ($stmt->execute()) {
                $success_message = "✅ Account created successfully! You can now login.";
            } else {
                $error_message = "❌ Error creating account: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "❌ " . implode("<br>", $errors);
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
    <title>Create Account</title>
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
            padding: 20px;
        }
        
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 28px;
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
        
        input[type="text"], input[type="email"], input[type="password"], input[type="tel"], select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .phone-group {
            display: flex;
            gap: 10px;
        }
        
        .country-code {
            flex: 0 0 80px;
            background-color: #f8f9fa;
        }
        
        .phone-input {
            flex: 1;
        }
        
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin-top: 3px;
        }
        
        .checkbox-group label {
            margin: 0;
            flex: 1;
            font-weight: normal;
        }
        
        .terms-link {
            color: #667eea;
            text-decoration: underline;
            cursor: pointer;
        }
        
        .terms-link:hover {
            color: #764ba2;
        }
        
        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .validation-error {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
            display: block;
        }
        
        /* Modal Styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
        }
        
        .modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        .modal-close:hover {
            color: #000;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                padding: 20px;
            }
            
            h2 {
                font-size: 24px;
            }
            
            .phone-group {
                flex-direction: column;
            }
            
            .country-code {
                flex: none;
                width: 100%;
            }
            
            .modal-content {
                margin: 10px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container" x-data="signupForm()" x-init="init()">
        <h2>Create Account</h2>
        
        <?php if (isset($success_message)): ?>
            <div class="message success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="message error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" @submit="validateForm">
            <div class="form-group">
                <label for="name">First Name *</label>
                <input type="text" id="name" name="name" x-model="form.name" required>
                <span x-show="errors.name" class="validation-error" x-text="errors.name"></span>
            </div>
            
            <div class="form-group">
                <label for="surname">Last Name *</label>
                <input type="text" id="surname" name="surname" x-model="form.surname" required>
                <span x-show="errors.surname" class="validation-error" x-text="errors.surname"></span>
            </div>
            
            <div class="form-group">
                <label for="nationality">Nationality *</label>
                <select id="nationality" name="nationality" x-model="form.nationality" @change="updateCountryCode()" required>
                    <option value="">Select your nationality</option>
                    <option value="United States">United States</option>
                    <option value="United Kingdom">United Kingdom</option>
                    <option value="Canada">Canada</option>
                    <option value="Germany">Germany</option>
                    <option value="France">France</option>
                    <option value="Italy">Italy</option>
                    <option value="Spain">Spain</option>
                    <option value="Netherlands">Netherlands</option>
                    <option value="Belgium">Belgium</option>
                    <option value="Switzerland">Switzerland</option>
                    <option value="Austria">Austria</option>
                    <option value="Sweden">Sweden</option>
                    <option value="Norway">Norway</option>
                    <option value="Denmark">Denmark</option>
                    <option value="Finland">Finland</option>
                    <option value="Poland">Poland</option>
                    <option value="Czech Republic">Czech Republic</option>
                    <option value="Hungary">Hungary</option>
                    <option value="Romania">Romania</option>
                    <option value="Bulgaria">Bulgaria</option>
                    <option value="Greece">Greece</option>
                    <option value="Portugal">Portugal</option>
                    <option value="Ireland">Ireland</option>
                    <option value="Australia">Australia</option>
                    <option value="New Zealand">New Zealand</option>
                    <option value="Japan">Japan</option>
                    <option value="South Korea">South Korea</option>
                    <option value="China">China</option>
                    <option value="India">India</option>
                    <option value="Brazil">Brazil</option>
                    <option value="Mexico">Mexico</option>
                    <option value="Argentina">Argentina</option>
                    <option value="Chile">Chile</option>
                    <option value="Colombia">Colombia</option>
                    <option value="Peru">Peru</option>
                    <option value="Venezuela">Venezuela</option>
                    <option value="South Africa">South Africa</option>
                    <option value="Egypt">Egypt</option>
                    <option value="Nigeria">Nigeria</option>
                    <option value="Kenya">Kenya</option>
                    <option value="Morocco">Morocco</option>
                    <option value="Tunisia">Tunisia</option>
                    <option value="Algeria">Algeria</option>
                    <option value="Libya">Libya</option>
                    <option value="Sudan">Sudan</option>
                    <option value="Ethiopia">Ethiopia</option>
                    <option value="Uganda">Uganda</option>
                    <option value="Tanzania">Tanzania</option>
                    <option value="Ghana">Ghana</option>
                    <option value="Senegal">Senegal</option>
                    <option value="Ivory Coast">Ivory Coast</option>
                    <option value="Cameroon">Cameroon</option>
                </select>
                <span x-show="errors.nationality" class="validation-error" x-text="errors.nationality"></span>
            </div>
            
            <div class="form-group">
                <label for="phone">Mobile Number *</label>
                <div class="phone-group">
                    <input type="text" class="country-code" name="countryCode" x-model="form.countryCode" readonly required>
                    <input type="tel" class="phone-input" id="phone" name="phone" x-model="form.phone" required>
                </div>
                <span x-show="errors.phone" class="validation-error" x-text="errors.phone"></span>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" x-model="form.email" @blur="checkEmailExists()" required>
                <span x-show="errors.email" class="validation-error" x-text="errors.email"></span>
            </div>
            
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" x-model="form.password" @input="validatePassword()" required>
                <span x-show="errors.password" class="validation-error" x-text="errors.password"></span>
                <small style="color: #666;">Minimum 5 characters with at least 2 numbers</small>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="agreed_terms" name="agreed_terms" x-model="form.agreed_terms" required>
                <label for="agreed_terms">
                    I agree to the <span class="terms-link" @click="showTermsModal = true">terms and conditions</span> *
                </label>
            </div>
            <span x-show="errors.agreed_terms" class="validation-error" x-text="errors.agreed_terms"></span>
            
            <button type="submit" class="submit-btn" :disabled="isSubmitting">
                <span x-show="!isSubmitting">Create Account</span>
                <span x-show="isSubmitting">Creating Account...</span>
            </button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
        
        <!-- Terms and Conditions Modal -->
        <div x-show="showTermsModal" class="modal" x-transition>
            <div class="modal-content">
                <button class="modal-close" @click="showTermsModal = false">&times;</button>
                <h3>Terms and Conditions</h3>
                <div style="margin-top: 20px; line-height: 1.6;">
                    <h4>1. Acceptance of Terms</h4>
                    <p>By creating an account, you agree to comply with and be bound by these terms and conditions.</p>
                    
                    <h4>2. User Responsibilities</h4>
                    <p>You are responsible for maintaining the confidentiality of your account information and password.</p>
                    
                    <h4>3. Privacy Policy</h4>
                    <p>We respect your privacy and will protect your personal information in accordance with applicable laws.</p>
                    
                    <h4>4. Account Usage</h4>
                    <p>You agree to use your account only for legitimate educational enrollment purposes.</p>
                    
                    <h4>5. Data Accuracy</h4>
                    <p>You agree to provide accurate and complete information during the registration process.</p>
                    
                    <h4>6. Termination</h4>
                    <p>We reserve the right to terminate accounts that violate these terms.</p>
                    
                    <p><strong>Last updated:</strong> <?php echo date('F j, Y'); ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
