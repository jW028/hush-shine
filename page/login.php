<?php

require '../_base.php';

$_title = 'Create an Account';
include '../_head.php';



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? '');
    $password = trim($_POST["password"] ?? '');

    try {
        $stmt = $_db->prepare("SELECT * FROM admin WHERE admin_email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            if (password_verify($password, $admin["admin_password"])) {
                $_SESSION['user'] = "admin";
                $_SESSION["admin_id"] = $admin["admin_id"];
                $_SESSION["admin_email"] = $admin["admin_email"];
                header("Location: ../admin/admin_menu.php");
                exit();
            } 
        }

        $stmt = $_db->prepare("SELECT cust_id, cust_password FROM customer WHERE cust_email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["cust_password"])) {
            // Password is correct, start a session
            $_SESSION['user'] = "customer";
            $_SESSION["cust_id"] = $user["cust_id"];
            $_SESSION["cust_email"] = $email;
            $_SESSION["admin"] = false;

            header("Location: ../index.php");
            exit();
        } else {
            // Invalid credentials
            $errors[] = "Invalid email or password.";
        }
    } catch (PDOException $e) {
        $errors[] = "Database error: " . $e->getMessage();
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
        
        <form action="login.php" method="post">
            <h1>Sign In</h1>

            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input class="form-input" type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input class="form-input" type="password" id="password" name="password">
            </div>

            
            <div class="form-group">
                <button class="submit-button" type="submit" class="btn">Sign in</button>
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