<?php

require '../_base.php';

$_title = 'Create an Account';
include '../_head.php';



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $pdo->prepare("SELECT cust_id, cust_password FROM customer WHERE cust_email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["cust_password"])) {
        // Password is correct, start a session
        $_SESSION["cust_id"] = $user["cust_id"];
        $_SESSION["cust_email"] = $email;

        header("Location: menu.php");
        exit();
    } else {
        // Invalid credentials
        $errors[] = "Invalid email or password.";
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