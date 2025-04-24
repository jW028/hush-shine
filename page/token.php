<?php 
include '../_base.php';

$_db->query('DELETE FROM token WHERE expire < NOW() ');

$id = req('id');

$_title = 'Reset Password';
include '../_head.php';

$stm = $_db->prepare('SELECT * FROM token WHERE token_id = ?');
$stm->execute([$id]);
$token = $stm->fetch(PDO::FETCH_ASSOC);

// Check if token is valid
if (!is_exists($id, 'token', 'token_id')) {
    temp('error', 'Invalid token');
    redirect('/page/login.php');
}

if(is_post()) {
    $new_password = req('new_password');
    $confirm_password = req('confirm_password');
    
    // Validate new password
    if ($new_password == '') {
        $_err['new_password'] = 'Required';
    } else if (strlen($new_password) < 6) {
        $_err['new_password'] = 'Minimum 6 characters';
    } else if ($new_password != $confirm_password) {
        $_err['confirm_password'] = 'Passwords do not match';
    }
    
    // If no errors, update password
    if (!$_err) {
        if ($token['user_type'] == 'customer') {
            $stm = $_db->prepare('UPDATE customer SET cust_password = ? WHERE cust_id = ?');
            $stm->execute([password_hash($new_password, PASSWORD_DEFAULT), $token['id']]);
        } elseif ($token['user_type'] == 'admin'){
            $stm = $_db->prepare('UPDATE `admin` SET admin_password = ? WHERE admin_id = ?');
            $stm->execute([password_hash($new_password, PASSWORD_DEFAULT), $token['id']]);
        }

        // Delete token after use
        $stm = $_db->prepare('DELETE FROM token WHERE token_id = ? AND user_type = ?');
        $stm->execute([$id, $token['user_type']]);

        temp('info', 'Password updated successfully');
        redirect('/');
    }
}

?>

<div class="page-wrapper">
    <div class="container margin-top">
        <form method="post" class="reset-password-form">
            <h2>Reset Password</h2>
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <?= html_password('new_password', 'required') ?>
                <?php err(key: 'new_password') ?>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <?= html_password('confirm_password', 'required') ?>
                <?php err(key: 'confirm_password') ?>
            </div>

            <section class="form-group">
                <button type="submit" class="submit-button">Reset Password</button>
            </section>
        </form>
    </div>

    <?php  
    include '../_foot.php';?>
</div>

