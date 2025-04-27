<?php
include '../_base.php';


if (!isset($_SESSION['user'])) {
    redirect('login.php');
}

$_err = [];
$success = false;

if(is_post()){
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password)) {
        $_err[] = 'Current password is required';
    }

    if (empty($new_password)) {
        $_err[] = 'New password is required';
    } else if (strlen($new_password) < 6) {
        $_err[] = 'New password must be at least 6 characters';
    } 
    
    if ($new_password != $confirm_password) {
        $_err[] = 'Passwords do not match';
    }

    if ($new_password === $current_password && !empty($new_password)) {
        $_err[] = "New password cannot be the same as current password";
    }
    
    // If no errors, proceed with password change
    if (empty($_err)) {
        try {
            // Check if user is admin or customer
            $user_type = $_SESSION['user'];
            $user_id = $_SESSION[$user_type === 'admin' ? 'admin_id' : 'cust_id'];
            
            if ($user_type === 'admin') {
                // Verify admin's current password
                $stmt = $_db->prepare("SELECT admin_password FROM admin WHERE admin_id = ?");
                $stmt->execute([$user_id]);
                $current_hash = $stmt->fetchColumn();
                
                if (!password_verify($current_password, $current_hash)) {
                    $_err[] = "Current password is incorrect";
                } else {
                    // Update admin password
                    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $_db->prepare("UPDATE admin SET admin_password = ? WHERE admin_id = ?");
                    $stmt->execute([$new_hash, $user_id]);
                    $success = true;
                }
            } else {
                // Verify customer's current password
                $stmt = $_db->prepare("SELECT cust_password FROM customer WHERE cust_id = ?");
                $stmt->execute([$user_id]);
                $current_hash = $stmt->fetchColumn();
                
                if (!password_verify($current_password, $current_hash)) {
                    $_err[] = "Current password is incorrect";
                } else {
                    // Update customer password
                    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $_db->prepare("UPDATE customer SET cust_password = ? WHERE cust_id = ?");
                    $stmt->execute([$new_hash, $user_id]);
                    $success = true;
                }
            }
        } catch (PDOException $e) {
            $_err[] = "Database error: " . $e->getMessage();
        }
    }
}


$_title = 'Reset Password';
include '../_head.php';
?>

<div class="reset-password-container">
    <div class="reset-form-wrapper">
        <div class="form-card">
            <h1>Reset Your Password</h1>
            
            <?php if ($success): ?>
                <div class="success-container">
                    <i class="fas fa-check-circle"></i>
                    <p>Password changed successfully!</p>
                    <div class="button-container">
                        <a href="<?= $_SESSION['user'] === 'admin' ? '../admin/admin_dashboard.php' : '../index.php' ?>" class="primary-button">
                            <i class="fas fa-home"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <p class="instruction-text">To reset your password, please enter your current password for verification, then choose a new password.</p>
                
                <?php if (!empty($_err)): ?>
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
                        <label for="current_password">Current Password</label>
                        <div class="input-with-icon reset-password">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="current_password" name="current_password" placeholder="Enter your current password" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <div class="input-with-icon reset-password">
                            <i class="fas fa-key"></i>
                            <input type="password" id="new_password" name="new_password" placeholder="Enter your new password" required>
                        </div>
                        <div class="password-requirements">
                            <small>Password must be at least 6 characters long</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="input-with-icon reset-password">
                            <i class="fas fa-check-circle"></i>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your new password" required>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="admin-submit-btn password-reset">
                            <i class="fas fa-save"></i> Update Password
                        </button>
                        <a href="profile.php" class="secondary-link">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include '../_foot.php';?>
</div>