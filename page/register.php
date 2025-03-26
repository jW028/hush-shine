<?php

require '../_base.php';

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
                $stmt = $_db->prepare("INSERT INTO customer (cust_id, cust_name, cust_contact, cust_email, cust_gender, cust_password) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$customer_id, $name, $contact, $email, $gender, $hashed_password]);
                
                // Redirect to login page
                $_SESSION['success_message'] = "Registration successful! You can now log in.";
                header("Location: login.php");
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
    <br>
    <div class="registration-container">
        <?php if (!empty($errors)): ?>
            <div class="error-container">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form action="register.php" method="post">
            <h1>Create an Account</h1>
            <h3>Already have an account?</h3><a href="/page/login.php">Login here ></a>
            <div class="form-group">
                <label class="form-label" for="name">Full Name</label>
                <input class="form-input" type="text" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input class="form-input" type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="contact">Contact Number</label>
                <input class="form-input" type="tel" id="contact" name="contact" value="<?= htmlspecialchars($contact ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input class="form-input" type="password" id="password" name="password">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm Password</label>
                <input class="form-input" type="password" id="confirm_password" name="confirm_password">
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
            
            <div class="form-group">
                <button class="submit-button" type="submit" class="btn">Register</button>
            </div>

            
        </form>
        <div class="img-wrapper">
            <img src="../images/Hush & Shine.svg">
        </div>
        
    </div>
</body>
</html>

<?php
include '../_foot.php';