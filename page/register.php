<?php

require '../_base.php';

define('RECAPTCHA_SITE_KEY', '6LciLSYrAAAAALYKM41_yhNAUWWcOmuTnhQ8Bxsb');
define('RECAPTCHA_SECRET_KEY', '6LciLSYrAAAAAOewB9gBZDFRLhITz2z3fC_tz-w5');

$_title = 'Create an Account';
include '../_head.php';

// Error messages
$errors = [];

$referrer = $_SERVER['HTTP_REFERER'] ?? '';
$is_returning = strpos($referrer, 'login.php') !== false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_returning) {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $password = $_POST['password'] ?? '';
    $gender = trim($_POST['gender'] ?? '');
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate input
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    if ($gender == 'radMale') {
        $gender = 'M';
    } else {
        $gender = 'F';
    }

    if (empty($contact)) {
        $errors[] = "Contact number is required";
    } elseif (!preg_match('/^\d{3}-?\d{7,8}$/', $contact)) {
        $errors[] = "Invalid contact number format. Must be in the format XXX-XXXXXXX or XXX-XXXXXXXX.";
    }
    

    
    // If no errors, proceed with registration
    if (empty($errors)) {
        try {
            // Check if email already exists
            $stmt = $_db->prepare("SELECT cust_id FROM customer WHERE cust_email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $errors[] = "Email already exists";
            } else {
                // Generate customer_id
                $stmt = $_db->query("SELECT cust_id FROM customer ORDER BY cust_id DESC LIMIT 1");
                if ($stmt->rowCount() > 0) {
                    $last_id = $stmt->fetchColumn();
                    $numeric_part = intval(substr($last_id, 1));
                    $new_numeric_part = $numeric_part + 1;
                    $customer_id = 'C' . str_pad($new_numeric_part, 4, '0', STR_PAD_LEFT);
                } else {
                    $customer_id = 'C0001';
                }
                
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $_db->prepare("INSERT INTO customer (cust_id, cust_name, cust_contact, cust_email, cust_gender, cust_password, cust_photo) VALUES (?, ?, ?, ?, ?, ?,?)");
                $stmt->execute([$customer_id, $name, $contact, $email, $gender, $hashed_password, 'default.png']);
                
                // Redirect to login page
                $_SESSION['success_message'] = "Registration successful! You can now log in.";
                $_SESSION['register_success'] = true;
                $_SESSION['register_time'] = time();
                header("Location: login.php");
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }

    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    if (empty($recaptcha_response)) {
        $errors[] = "Please complete the CAPTCHA verification";
    } else {
        $verify_url = "https://www.google.com/recaptcha/api/siteverify";
        $data = [
            'secret' => RECAPTCHA_SECRET_KEY,
            'response' => $recaptcha_response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);
        $verify_response = file_get_contents($verify_url, false, $context);
        $response_data = json_decode($verify_response);

        if (!$response_data->success) {
            $errors[] = "CAPTCHA verification failed";
        }
    }
}
?>
    <br>
    <div class="registration-container">
        <form action="register.php" method="post">
            <h1>Create an Account</h1>
            <h3>Already have an account?</h3><a href="/page/login.php">Login here</a>
            <div class="form-group">
                <label class="form-label" for="name">Full Name</label>
                <input class="form-input" type="text" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input class="form-input" type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="contact">Contact Number</label>
                <input class="form-input" type="tel" id="contact" name="contact" value="<?= htmlspecialchars($contact ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input class="form-input" type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm Password</label>
                <input class="form-input" type="password" id="confirm_password" name="confirm_password" required>   
            </div>

            <fieldset>
                <legend>Gender</legend>
                <div class="radio">
                    <input value="radFemale" name="gender" type="radio" id="radFemale">
                    <label for="radFemale">Female</label>
                </div>

                <div class="radio">
                    <input value="radMale" name="gender" type="radio" id="radMale">
                    <label for="radMale">Male</label>
                </div>
            </fieldset>

            <div class="form-group captcha-wrapper">
                <div class="g-recaptcha" data-sitekey="<?= RECAPTCHA_SITE_KEY ?>"></div>
            </div>
    
            <div class="form-group">
                <button class="submit-button" type="submit" class="btn">Register</button>
            </div>

            
        </form>
        <div class="img-wrapper">
            <img src="../images/Hush & Shine.svg">
        </div>
        
    </div>
    <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
</body>
</html>

<?php
include '../_foot.php';