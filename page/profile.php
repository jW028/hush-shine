<?php
require '../_base.php';

auth();

$_title = 'Profile';
include '../_head.php';

// Check if user is logged in  
if (!isset($_SESSION['user'])) {
    header("Location: /page/login.php");
    exit();
}

// Fetch user data
if (is_get()){
    $stmt = $_db->prepare("SELECT * FROM customer WHERE cust_id = ?");
    $stmt->execute([$_SESSION['cust_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header("Location: /page/login.php");
        exit();
    }

    extract((array)$user);
    $_SESSION['cust_photo'] = $user['cust_photo'];
}

if (is_post()) {
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'update_profile') {
        // Handle profile update
        $name = req('name');
        $email = req('email');
        $contact = req('contact');
        $f = get_file('photo');

        // Validate and update profile information
        if ($email == '') {
            $_err['email'] = 'Required';
        } else if (strlen($email) > 100) {
            $_err['email'] = 'Maximum 100 characters';
        } else if (!is_email($email)) {
            $_err['email'] = 'Invalid email';
        } else {
            $stm = $_db->prepare('
                SELECT COUNT(*) FROM customer
                WHERE cust_email = ? AND cust_id != ?
            ');
            $stm->execute([$email, $_SESSION['cust_id']]);

            if ($stm->fetchColumn() > 0) {
                $_err['email'] = 'Duplicated';
            }
        }

        // Validate name
        if ($name == '') {
            $_err['name'] = 'Required';
        } else if (strlen($name) > 100) {
            $_err['name'] = 'Maximum 100 characters';
        }

        // Validate photo (file) --> optional
        if ($f) {
            if (!str_starts_with($f->type, 'image/')) {
                $_err['photo'] = 'Must be an image';
            } else if ($f->size > 1 * 1024 * 1024) {
                $_err['photo'] = 'Maximum 1MB';
            } else {
                // Save the new photo
                $photo = save_photo($f, '../images/customer_img');
            }
        } else {
            // If no new photo is uploaded, keep the existing photo
            $photo = $user['cust_photo'];
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
            if ($f && $user['cust_photo'] && file_exists("../images/customer_img/{$user['cust_photo']}")) {
                unlink("../images/customer_img/{$user['cust_photo']}");
            }

            $stm = $_db->prepare('
                UPDATE customer
                SET cust_name = ?, cust_email = ?, cust_contact = ?, cust_photo = ?
                WHERE cust_id = ?
            ');
            $stm->execute([$name, $email, $contact, $photo, $_SESSION['cust_id']]);

            $_SESSION['cust_name'] = $name;
            $_SESSION['cust_email'] = $email;
            $_SESSION['cust_contact'] = $contact;
            $_SESSION['cust_photo'] = $photo;

            temp('info', 'Record updated');
            redirect('/page/profile.php');
            exit();
        }
    } elseif (isset($_POST['form_type']) && $_POST['form_type'] === 'change_password') {
        // Handle password change
        $password = req('password');
        $new_password = req('new_password');
        $confirm_password = req('confirm_password');

        if ($password == '') {
            $_err['old_password'] = 'Required';
        } else {
            $stm = $_db->prepare('SELECT cust_password FROM customer WHERE cust_id = ?');
            $stm->execute([$_SESSION['cust_id']]);
            $hashed_password = $stm->fetchColumn();

            if (!password_verify($password, $hashed_password)) {
                $_err['old_password'] = 'Incorrect password';
            }
        }

        if ($new_password == '') {
            $_err['password'] = 'Required';
        } else if (strlen($new_password) < 6) {
            $_err['password'] = 'Minimum 6 characters';
        } else if ($new_password !== $confirm_password) {
            $_err['confirm_password'] = 'Passwords do not match';
        }

        if (!$_err) {
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stm = $_db->prepare('UPDATE customer SET cust_password = ? WHERE cust_id = ?');
            $stm->execute([$hashed_new_password, $_SESSION['cust_id']]);

            temp('info', 'Password updated');
            redirect('/page/profile.php');
            exit();
        }
    }
}
?>

<div class="page-wrapper">
    <div class="content profile">
        <div class="container margin-top">
        <!-- Form for updating profile -->
        <form method="POST" class="form" enctype="multipart/form-data">
            <input type="hidden" name="form_type" value="update_profile">
            <h2>Profile</h2>
            <div class="profile-container">
                <div class="profile-picture">
                    <label for="photo">Profile Picture:</label>
                    <img id="preview" src="/images/customer_img/<?= htmlspecialchars($user['cust_photo']) ?>" alt="Profile Picture" class="profile-pic">
                    <input type="file" id="photo" name="photo" accept="image/*" onchange="previewImage(event)">
                    <?php err('photo') ?>
                </div>

                <div class="profile-form">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['cust_name']) ?>" required>
                        <?php err('name') ?>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['cust_email']) ?>" required>
                        <?php err('email') ?>
                    </div>
                    <div class="form-group">
                        <label for="contact">Contact:</label>
                        <input type="text" id="contact" name="contact" value="<?= htmlspecialchars($user['cust_contact']) ?>" required>
                        <?php err('contact') ?>
                    </div>

                    <section>
                        <button type="submit">Update Profile</button>
                    </section>

                </div>

                <div class="change-password-container">
                    <h2 style="margin-bottom: 5px;">Change Password</h2>
                    <div>
                        <a href="/page/update_password.php" class="change-password-link">-> Change Password</a>
                    </div>
                </div>
            </div>
        </form>

        </div>
    </div>
    <?php include '../_foot.php'; ?>
</div>

