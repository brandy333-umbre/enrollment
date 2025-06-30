<?php
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

    // Get form data safely
    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $full_name = trim($name . ' ' . $surname);
    $email = strtolower(trim($_POST['email'] ?? '')); // Normalize email to lowercase
    $password_plain = $_POST['password'] ?? '';
    $nationality = $_POST['nationality'] ?? '';
    $countryCode = $_POST['countryCode'] ?? '';
    $phoneNumber = $_POST['phone'] ?? '';
    $phone = $countryCode . $phoneNumber;
    $agreed_terms = isset($_POST['agreed_terms']) ? 1 : 0;

    // Validate required fields
    if (empty($name) || empty($surname) || empty($email) || empty($password_plain)) {
        $signup_error = "❌ All required fields must be filled";
    } else {
        // Check if email already exists
        $check_sql = "SELECT id FROM users WHERE LOWER(email) = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $signup_error = "❌ An account with this email already exists";
        } else {
            // Hash the password securely
            $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);
            
            // Add debugging (remove after fixing)
            error_log("Creating account for email: " . $email);
            error_log("Password hash created: " . substr($password_hashed, 0, 10) . "...");

            // Insert using prepared statement
            $sql = "INSERT INTO users (full_name, email, password, nationality, phone, agreed_terms)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $full_name, $email, $password_hashed, $nationality, $phone, $agreed_terms);

            if ($stmt->execute()) {
                $signup_success = "✅ Signup successful! You can now login with your credentials.";
                error_log("Account created successfully for: " . $email);
            } else {
                $signup_error = "❌ Error: " . $stmt->error;
                error_log("Database error: " . $stmt->error);
            }

            $stmt->close();
        }
        $check_stmt->close();
    }
    $conn->close();
}
?>

<!-- --- Signup Form --- -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            position: relative;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 20px 20px 0 0;
        }

        h2 {
            text-align: center;
            color: #2d3748;
            font-size: 2.2em;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .terms-section {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 2px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }

        .terms-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, #667eea, #764ba2);
        }

        .terms-section h3 {
            color: #2d3748;
            font-size: 1.3em;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .terms-section p {
            color: #4a5568;
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2d3748;
            font-weight: 500;
            font-size: 0.95em;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .phone-group {
            display: flex;
            gap: 10px;
        }

        .country-code {
            width: 80px !important;
            text-align: center;
            font-weight: 600;
            color: #4a5568;
            background: #edf2f7 !important;
        }

        .phone-input {
            flex: 1;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 10px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .checkbox-container:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }

        .checkbox-container input[type="checkbox"] {
            width: auto;
            margin-right: 12px;
            transform: scale(1.3);
            accent-color: #667eea;
        }

        .checkbox-container span {
            color: #2d3748;
            font-weight: 500;
            font-size: 0.95em;
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .success-message {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: 2px solid #28a745;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
            color: #155724;
            font-weight: 500;
        }

        .error-message {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border: 2px solid #dc3545;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
            color: #721c24;
            font-weight: 500;
        }

        .password-error {
            color: #dc3545;
            font-size: 0.9em;
            margin-top: 8px;
            padding: 8px 12px;
            background: #f8d7da;
            border-radius: 6px;
            border-left: 3px solid #dc3545;
        }

        .login-link {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .login-link:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .container {
                padding: 30px 20px;
                margin: 10px;
            }
            
            h2 {
                font-size: 1.8em;
            }
            
            .phone-group {
                flex-direction: column;
                gap: 8px;
            }
            
            .country-code {
                width: 100% !important;
            }
        }
    </style>
</head>
<body>
<?php
$showResult = false;
$resultMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $full_name = $name . ' ' . $surname;
    $nationality = $_POST['nationality'] ?? '';
    $countryCode = $_POST['countryCode'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $agreed_terms = isset($_POST['agreed_terms']) ? 'Yes' : 'No';
    $fullPhone = $countryCode . $phone;
    $showResult = true;
    $resultMsg = "<strong>Submitted Data:</strong><br>" .
        "Name: $name $surname<br>Nationality: $nationality<br>Email: $email<br>Phone: $fullPhone<br>Password: $password (hashed in real DB)<br>Agreed to Terms: $agreed_terms";
}
?>
    <div class="container" x-data="signupForm()" x-init="init()">
        <h2>Create Account</h2>
        
        <?php if (isset($signup_success)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($signup_success); ?>
                <br><br>
                <a href="login.php" class="login-link">Go to Login Page</a>
            </div>
        <?php endif; ?>
        
        <?php if (isset($signup_error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($signup_error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="terms-section">
                <h3>Terms and Conditions</h3>
                <p>
                    By creating an account, you agree to our Terms of Service and Privacy Policy. 
                    You acknowledge that you have read, understood, and agree to be bound by these terms. 
                    You also consent to the collection and processing of your personal data as described 
                    in our Privacy Policy.
                </p>
                <div class="checkbox-container">
                    <input type="checkbox" name="agreed_terms" x-model="form.agreed_terms" required>
                    <span>I agree to the Terms and Conditions *</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="name">First Name *</label>
                <input type="text" id="name" name="name" x-model="form.name" required>
            </div>
            <div class="form-group">
                <label for="surname">Last Name *</label>
                <input type="text" id="surname" name="surname" x-model="form.surname" required>
            </div>
            <div class="form-group">
                <label for="nationality">Nationality *</label>
                <select id="nationality" name="nationality" x-model="form.nationality" @change="form.countryCode = countryCodes[form.nationality] || ''" required>
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
            </div>
            <div class="form-group">
                <label for="phone">Mobile Number *</label>
                <div class="phone-group">
                    <input type="text" class="country-code" name="countryCode" x-model="form.countryCode" readonly required>
                    <input type="tel" class="phone-input" id="phone" name="phone" x-model="form.phone" 
                           pattern="[0-9]+" 
                           title="Please enter only numbers"
                           @input="form.phone = form.phone.replace(/[^0-9]/g, '')"
                           required>
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" x-model="form.email" required>
            </div>
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" x-model="form.password" 
                       pattern="^(?=.*[0-9].*[0-9]).{5,}$"
                       title="Password must be at least 5 characters long and contain at least 2 numbers"
                       @input="validatePassword()"
                       required>
                <div x-show="passwordError" x-text="passwordError" class="password-error"></div>
            </div>
            <button type="submit" class="submit-btn">Create Account</button>
        </form>
    </div>
    <script>
    function signupForm() {
        return {
            form: {
                name: '',
                surname: '',
                nationality: '',
                countryCode: '',
                phone: '',
                email: '',
                password: '',
                agreed_terms: false
            },
            passwordError: '',
            validatePassword() {
                const password = this.form.password;
                if (password.length < 5) {
                    this.passwordError = 'Password must be at least 5 characters long';
                } else if ((password.match(/[0-9]/g) || []).length < 2) {
                    this.passwordError = 'Password must contain at least 2 numbers';
                } else {
                    this.passwordError = '';
                }
            },
            countryCodes: {
                "United Kingdom": "+44",
                "United States": "+1",
                "Canada": "+1",
                "Germany": "+49",
                "France": "+33",
                "Italy": "+39",
                "Spain": "+34",
                "Netherlands": "+31",
                "Belgium": "+32",
                "Switzerland": "+41",
                "Austria": "+43",
                "Sweden": "+46",
                "Norway": "+47",
                "Denmark": "+45",
                "Finland": "+358",
                "Poland": "+48",
                "Japan": "+81",
                "India": "+91",
                "Nigeria": "+234",
                "Australia": "+61",
                "China": "+86",
                "South Africa": "+27"
            }
        }
    }
</script>


</body>
</html>
