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
    $email = $_POST['email'] ?? '';
    $password_plain = $_POST['password'] ?? '';
    $nationality = $_POST['nationality'] ?? '';
    $countryCode = $_POST['countryCode'] ?? '';
    $phoneNumber = $_POST['phone'] ?? '';
    $phone = $countryCode . $phoneNumber;
    $agreed_terms = isset($_POST['agreed_terms']) ? 1 : 0;

    // Hash the password securely
    $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

    // Insert using prepared statement
    $sql = "INSERT INTO users (full_name, email, password, nationality, phone, agreed_terms)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $full_name, $email, $password_hashed, $nationality, $phone, $agreed_terms);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>✅ Signup successful!</p>";
    } else {
        echo "<p style='color:red;'>❌ Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
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
        
        <div class="terms-section" style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #e9ecef;">
            <h3 style="margin-top: 0; color: #495057;">Terms and Conditions</h3>
            <p style="margin-bottom: 15px; line-height: 1.6; color: #6c757d;">
                By creating an account, you agree to our Terms of Service and Privacy Policy. 
                You acknowledge that you have read, understood, and agree to be bound by these terms. 
                You also consent to the collection and processing of your personal data as described 
                in our Privacy Policy.
            </p>
            <div class="form-group" style="margin-bottom: 0;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" name="agreed_terms" x-model="form.agreed_terms" required style="margin-right: 10px; transform: scale(1.2);">
                    <span style="color: #495057; font-weight: 500;">I agree to the Terms and Conditions *</span>
                </label>
            </div>
        </div>
        
        <form method="POST">
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
                    <input type="tel" class="phone-input" id="phone" name="phone" x-model="form.phone" required>
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" x-model="form.email" required>
            </div>
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" x-model="form.password" required>
            </div>
            <button type="submit" class="submit-btn">Create Account</button>
    </form>
        <?php if ($showResult): ?>
            <div class="result-box">
                <?php echo $resultMsg; ?>
            </div>
        <?php endif; ?>
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
