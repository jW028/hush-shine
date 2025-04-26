<?php

require '../_base.php';

define('MAX_LOGIN_ATTEMPTS', 3);
define('LOCKOUT_TIME_MINUTES', 1);

$_title = 'Sign In';
include '../_head.php';

if (isset($_SESSION['user'])) {
    if ($_SESSION['user'] == "admin") {
        header("Location: ../admin/admin_dashboard.php");
        exit();
    } else {
        header("Location: ../index.php");
        exit();
    }
}

$errors = [];
$email = '';
$is_locked = false;
$remaining_attempts = MAX_LOGIN_ATTEMPTS;
$lockout_time = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? '');
    $password = trim($_POST["password"] ?? '');

    if (empty($email) || empty($password)) {
        $errors[] = "Email and password are required.";
    } else {
        try {
            // Check if this email has been locked out
            $stmt = $_db->prepare("SELECT 
                login_attempts, 
                last_attempt_time,
                TIMESTAMPDIFF(MINUTE, last_attempt_time, NOW()) as minutes_since_last_attempt
                FROM login_attempts 
                WHERE email = ?");
            $stmt->execute([$email]);
            $attempt_info = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($attempt_info) {
                $attempts = (int)$attempt_info['login_attempts'];
                $minutes_passed = (int)$attempt_info['minutes_since_last_attempt'];
                
                // Check if account is locked
                if ($attempts >= MAX_LOGIN_ATTEMPTS && $minutes_passed < LOCKOUT_TIME_MINUTES) {
                    $is_locked = true;
                    $lockout_time = LOCKOUT_TIME_MINUTES - $minutes_passed;
                    $errors[] = "Account is temporarily locked. Please try again in {$lockout_time} minutes.";
                }
                
                // Reset attempts if lockout period has passed
                if ($attempts >= MAX_LOGIN_ATTEMPTS && $minutes_passed >= LOCKOUT_TIME_MINUTES) {
                    $reset_stmt = $_db->prepare("UPDATE login_attempts SET login_attempts = 0 WHERE email = ?");
                    $reset_stmt->execute([$email]);
                    $attempts = 0;
                }
                
                $remaining_attempts = MAX_LOGIN_ATTEMPTS - $attempts;
            }
            
            // Proceed with login if account is not locked
            if (!$is_locked) {
                // First check admin credentials
                $stmt = $_db->prepare("SELECT * FROM admin WHERE admin_email = ?");
                $stmt->execute([$email]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($admin && password_verify($password, $admin["admin_password"])) {
                    // Successful admin login - reset any login attempts
                    resetLoginAttempts($email);
                    
                    $_SESSION['user'] = "admin";
                    $_SESSION["admin_id"] = $admin["admin_id"];
                    $_SESSION["admin_email"] = $admin["admin_email"];
                    $_SESSION["admin_name"] = $admin["admin_name"] ?? "Admin";
                    header("Location: ../admin/admin_dashboard.php");
                    exit();
                } 
                
                // If not admin, check customer credentials
                $stmt = $_db->prepare("SELECT cust_id, cust_name, cust_email, cust_password FROM customer WHERE cust_email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user["cust_password"])) {
                    // Successful customer login - reset any login attempts
                    resetLoginAttempts($email);
                    
                    $_SESSION['user'] = "customer";
                    $_SESSION["cust_id"] = $user["cust_id"];
                    $_SESSION["cust_email"] = $user["cust_email"];
                    $_SESSION["cust_name"] = $user["cust_name"];
                    $_SESSION["admin"] = false;

                    header("Location: ../index.php");
                    exit();
                } else {
                    // Failed login attempt - increment the counter
                    incrementLoginAttempts($email);
                    
                    if ($remaining_attempts <= 1) {
                        $errors[] = "Invalid email or password. Your account will be temporarily locked after this final attempt.";
                    } else {
                        $errors[] = "Invalid email or password. You have {$remaining_attempts} attempts remaining.";
                    }
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }     
    }
}

function resetLoginAttempts($email) {
    global $_db;
    try {
        $stmt = $_db->prepare("DELETE FROM login_attempts WHERE email = ?");
        $stmt->execute([$email]);
    } catch (PDOException $e) {
        // Don't do anything here
    }
}

function incrementLoginAttempts($email) {
    global $_db;
    try {
        $stmt = $_db->prepare("SELECT COUNT(*) FROM login_attempts WHERE email = ?");
        $stmt->execute([$email]);
        $exists = $stmt->fetchColumn() > 0;
        
        if ($exists) {
            $stmt = $_db->prepare("UPDATE login_attempts
                                SET login_attempts = login_attempts + 1, 
                                last_attempt_time = NOW()
                                WHERE email = ?");
            $stmt->execute([$email]);
        } else {
            $stmt = $_db->prepare("INSERT INTO login_attempts (email, login_attempts, last_attempt_time) 
                                VALUES (?, 1, NOW())");
            $stmt->execute([$email]);
        }
    } catch (PDOException $e) {
        // Don't do anything here
    }
}

?>

    <br>
    <div class="registration-container">
        <form action="login.php" method="post">
            <h1>Sign In</h1>
            <h3>Don't have an account yet?</h3><a href="/page/register.php">Register here</a>

            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input class="form-input" type="email" id="email" name="email" 
                    value="<?= htmlspecialchars($email ?? '') ?>" 
                    <?= $is_locked ? 'disabled' : '' ?>>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input class="form-input" type="password" id="password" name="password" 
                    <?= $is_locked ? 'disabled' : '' ?>>
            </div>
            
            <?php if (!$is_locked): ?>
                <div class="form-group">
                    <button class="submit-button" type="submit" class="btn">Sign in</button>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <div class="account-locked">
                        <i class="fas fa-lock"></i> 
                        Account temporarily locked. Please try again in <?= $lockout_time ?> minutes.
                    </div>
                </div>
            <?php endif; ?>
        </form>
        <div class="img-wrapper">
            <img src="../images/Hush & Shine.svg">
        </div>
        
    </div>
    <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
</body>
</html>
<?php
include '../_foot.php';