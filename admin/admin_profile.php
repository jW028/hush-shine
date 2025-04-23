<?php
require '../_base.php';

auth();

$_title = 'Profile';
include '../_head.php';

// Check if user is logged in  
if (!isset($_SESSION['user']) && !$_SESSION['user'] == 'admin' && !$_adminContext) {
    header("Location: /page/login.php");
    exit();
}

// Fetch user data
if (is_get()){
    $stmt = $_db->prepare("SELECT * FROM `admin` WHERE admin_id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        header("Location: /page/login.php");
        exit();
    }

    extract((array)$admin);
}

if (is_post()) {
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'update_profile') {
        // Handle profile update
        $name = req('name');
        $email = req('email');
        $contact = req('contact');


        // Validate name
        if ($name == '') {
            $_err['name'] = 'Required';
        } else if (strlen($name) > 100) {
            $_err['name'] = 'Maximum 100 characters';
        }

        // Validate contact
        if ($contact == '') {
            $_err['contact'] = 'Required';
        } else if (strlen($contact) > 13) {
            $_err['contact'] = 'Maximum 13 characters';
        } else if (!is_phone($contact)) {
            $_err['contact'] = 'Invalid phone number format. Must be in the format XXX-XXXXXXXX.';
        }

        // DB operation
        if (!$_err) {

            $stm = $_db->prepare('
                UPDATE admin
                SET admin_name = ?, admin_email = ?, admin_contact = ?
                WHERE admin_id = ?
            ');
            $stm->execute([$name, $email, $contact, $_SESSION['admin_id']]);

            $_SESSION['admin_name'] = $name;
            $_SESSION['admin_contact'] = $contact;
            $_SESSION['admin_email'] = $email;

            temp('info', 'Record updated');
            redirect('/admin/admin_profile.php');
            exit();
        }
    }
}
?>

<div class="page-wrapper">
    <div class="content admin-profile">
        <div class="container admin-margin-top">
        <!-- Form for updating profile -->
        <form method="POST" class="form" enctype="multipart/form-data">
            <input type="hidden" name="form_type" value="update_profile">
            <h2>Profile</h2>
            <div class="profile-container">
                <div class="profile-picture">
                    <h3>Profile Picture</h3>
                    <i class="fas fa-user-shield admin-icon"  id="photo"></i>
                </div>

                <div class="profile-form">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($admin['admin_name']) ?>" required>
                        <?php err('name') ?>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($admin['admin_email']) ?>" readonly>
                        <?php err('email') ?>
                    </div>
                    <div class="form-group">
                        <label for="contact">Contact:</label>
                        <input type="text" id="contact" name="contact" value="<?= htmlspecialchars($admin['admin_contact']) ?>" required>
                        <?php err('contact') ?>
                    </div>

                    <section>
                        <button type="submit">Update Profile</button>
                    </section>

                </div>

                <div class="change-password-container">
                    <h3>Change Password</h3>
                    <div>
                        <a href="/page/reset_password.php" class="change-password-link"><i class="fas fa-key"></i>  Change Password</a>
                    </div>
                </div>
            </div>
        </form>

        </div>
    </div>
    <?php include '../_foot.php'; ?>
</div>
