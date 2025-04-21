<?php
include '../_base.php';

auth();

$_title = 'Update Password';
include '../_head.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: /page/login.php");
    exit();
}

// Fetch user data
$stmt = $_db->prepare("SELECT * FROM customer WHERE cust_id = ?");
$stmt->execute([$_SESSION['cust_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: /page/login.php");
    exit();
}

if (is_post()) {
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'update_password') {
        // Handle password update
        $current_password = req('current_password');
        $new_password = req('new_password');
        $confirm_password = req('confirm_password');

        // Validate current password
        if ($current_password == '') {
            $_err['current_password'] = 'Required';
        } else if (strlen($current_password) < 6) {
            $_err['current_password'] = 'Minimum 6 characters';
        } else if (!password_verify($current_password, $user['cust_password'])) {
            $_err['current_password'] = 'Incorrect current password';
        }

        // Validate new password
        if ($new_password == '') {
            $_err['new_password'] = 'Required';
        } else if (strlen($new_password) < 6) {
            $_err['new_password'] = 'Minimum  characters';
        } 

        if ($confirm_password == '') {
            $_err['confirm_password'] = 'Required';
        } else if (strlen($confirm_password) < 6) {
            $_err['confirm_password'] = 'Minimum 6 characters';
        } else if ($new_password == $current_password) {
            $_err['new_password'] = 'New password cannot be the same as current password';
        } else if ($new_password != $confirm_password) {
            $_err['confirm_password'] = 'Passwords do not match';
        }



        // If no errors, update password
        if (!$_err) {
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $_db->prepare("UPDATE customer SET cust_password = ? WHERE cust_id = ?");
            $stmt->execute([$hashed_new_password, $_SESSION['cust_id']]);

            // Redirect to profile page with success message
            header("Location: /page/profile.php?success=Password updated successfully");
            exit();
        }
    }


}

?>

<div class="page-wrapper">
    <div class="content profile">
        <div class="container margin-top">
            
            <button class="btn btn-secondary" onclick="history.back()">Back</button>

            <h1>Update Password</h1>
            <form method="post" action="" class="form">
                <input type="hidden" name="form_type" value="update_password">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <?= html_password('current_password', 'maxlength="100"') ?> 
                    <?= err('current_password') ?>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <?= html_password('new_password', 'maxlength="100"') ?> 
                    <?= err('new_password') ?>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <?= html_password('confirm_password', 'maxlength="100"') ?> 
                    <?= err('confirm_password') ?>
                </div> 

                <section>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </section>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../_foot.php'; ?>
</div>