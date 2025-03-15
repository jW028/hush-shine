<?php
session_start();

// Dummy credentials for demonstration
$valid_username = "1";
$valid_password = "1";



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION["user"] = $username;
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

    <br>
    <h1>Create an Account</h1>
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
            <h3>Already have an account?<a href="login.php">Login here ></a></h3>
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
            <img src="images/Hush & Shine.svg">
        </div>
        
    </div>
</body>
</html>
