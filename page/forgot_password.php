<?php
include '../_base.php';

if(is_post()){
    $email = req('email');
    
    // Validate email
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (strlen($email) > 100) {
        $_err['email'] = 'Maximum 100 characters';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    } else {
        $stm = $_db->prepare('SELECT * FROM customer WHERE cust_email = ?');
        $stm->execute([$email]);
        $user= $stm->fetch(PDO::FETCH_ASSOC);

        $user_type = 'customer';

        if (!$user) {
            $stm = $_db->prepare('SELECT * FROM `admin` WHERE admin_email = ?');
            $stm->execute([$email]);
            $user= $stm->fetch(PDO::FETCH_ASSOC);
            $user_type = 'admin';
        }


        if (!$user) {
            $_err['email'] = 'Email not found';
        } 
    }
    
    // If no errors, send reset token
    if (!$_err) {

        // Generate a unique token
        $id = sha1(uniqid().rand());

        $stm = $_db->prepare('DELETE FROM token WHERE id = ? AND user_type = ?');
        $stm->execute([$user['cust_id'] ?? $user['admin_id'], $user_type]);

        $stm = $_db->prepare('INSERT INTO token (token_id, expire, id, user_type) VALUES (?, ADDTIME(NOW(), "00:10:00"), ?, ?)');
        $stm->execute([$id, $user['cust_id'] ?? $user['admin_id'], $user_type]);

        $url = base("page/token.php?id=$id");

        $m = get_mail();
        $m->addAddress($user['cust_email'] ?? $user['admin_email'], $user['cust_name'] ?? $user['admin_name']);
        $m->addEmbeddedImage("../images/Hush & Shine.png", 'photo');
        $m->isHTML(true);
        $m->Subject = 'Reset Password';
        $m->Body = "
            <img src='cid:photo'
                 style='width: 200px; height: 200px;'>
            <p>Dear " . ($user['cust_name'] ?? $user['admin_name']) . ",</p>
            <h1>Reset Password</h1>
            <p>
                Please click <a href='$url'>here</a>
                to reset your password.
            </p>
            <p>From, Admin</p>
            <p>Hush & Shine</p>
            <p>Note: This link will expire in 10 minutes.</p>
            <p>If you did not request this, please ignore this email.</p>
            <p>Thank you for using our service!</p>
        ";
        $m->send();
        $_SESSION['reset_email_sent'] = true;
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_time'] = time();

        redirect('../index.php');
    }
    
}


$_title = 'Reset Password';
include '../_head.php';
?>

<div class="reset-password-container">
    <div class="reset-form-wrapper">
        <div class="logo-container">
            <img src="../images/Hush & Shine.svg" alt="Hush & Shine Logo">
        </div>
        
        <div class="form-card">
            <h1>Reset Password</h1>
            <p class="instruction-text">Enter your email address and we'll send you a link to reset your password.</p>
            
            <?php if (isset($_err) && !empty($_err)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($_err as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="post" class="reset-password-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-with-icon reset-password">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="admin-submit-btn password-reset">
                        <i class="fas fa-paper-plane"></i> Send Reset Link
                    </button>
                    <a href="login.php" class="secondary-link">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                </div>
            </form>
        </div>
        
        <div class="help-text">
            <p>Need assistance? <a href="contact.php">Contact support</a></p>
        </div>
    </div>
</div>


    <?php
    include '../_foot.php';?>
</div>