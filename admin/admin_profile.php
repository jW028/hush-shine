<?php
require '../_base.php';

auth('admin');

$_title = 'Profile';
include '../_head.php';

// Check if user is logged in  
if (!isset($_SESSION['user']) || $_SESSION['user'] != 'admin') {
    header("Location: /page/login.php");
    exit();
}

// Fetch user data
if (is_get()) {
    $stmt = $_db->prepare("SELECT * FROM `admin` WHERE admin_id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        header("Location: /page/login.php");
        exit();
    }
}

if (is_post()) {
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'update_profile') {
        // Handle profile update
        $name = req('name');
        $email = req('email');
        $contact = req('contact');
        $f = get_file('photo');
        
        // Always fetch current admin data for validation
        $stmt = $_db->prepare("SELECT * FROM `admin` WHERE admin_id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$admin) {
            header("Location: /page/login.php");
            exit();
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
                try {
                    // Save the new photo 
                    ob_start();
                    $photo = save_photo($f, '../images/admin_img');
                    ob_end_clean();
                } catch (Exception $e) {
                    $_err['photo'] = 'Error uploading image: ' . $e->getMessage();
                }
            }
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
        if (empty($_err)) {
            try {
                if (isset($photo) && $f) {
                    // Delete old photo if it exists and isn't the default
                    if ($admin['admin_photo'] && $admin['admin_photo'] !== 'default.png' && file_exists("../images/admin_img/{$admin['admin_photo']}")) {
                        unlink("../images/admin_img/{$admin['admin_photo']}");
                    }
                    
                    // Update including the photo
                    $stm = $_db->prepare('
                        UPDATE admin
                        SET admin_name = ?, admin_email = ?, admin_contact = ?, admin_photo = ?
                        WHERE admin_id = ?
                    ');
                    $stm->execute([$name, $email, $contact, $photo, $_SESSION['admin_id']]);
                    
                    // Update session with new photo
                    $_SESSION['admin_photo'] = $photo;
                } else {
                    // Update without changing the photo
                    $stm = $_db->prepare('
                        UPDATE admin
                        SET admin_name = ?, admin_email = ?, admin_contact = ?
                        WHERE admin_id = ?
                    ');
                    $stm->execute([$name, $email, $contact, $_SESSION['admin_id']]);
                }

                $_SESSION['admin_name'] = $name;
                $_SESSION['admin_email'] = $email;
                $_SESSION['admin_contact'] = $contact;

                temp('info', 'Profile updated successfully');
                redirect('/admin/admin_profile.php');
                exit();
            } catch (PDOException $e) {
                $_err['general'] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="profile-page-container">
    <div class="profile-header">
        <h1>My Profile</h1>
        <p>Manage your personal information and account settings</p>
    </div>

    <?php if (isset($_SESSION['info'])): ?>
        <div class="profile-alert success">
            <i class="fas fa-check-circle"></i> 
            <span><?= htmlspecialchars($_SESSION['info']) ?></span>
            <button type="button" class="close-alert" onclick="this.parentElement.style.display='none';">×</button>
        </div>
    <?php endif; ?>

    <div class="profile-content">
        <div class="profile-sidebar">
            <div class="profile-image-container">
                <img src="/images/admin_img/<?= htmlspecialchars($admin['admin_photo']) ?>" alt="Profile Picture" id="profile-image-preview">
                <div class="profile-image-overlay">
                    <label for="photo" class="change-photo-btn">
                        <i class="fas fa-camera"></i>
                        <span>Change Photo</span>
                    </label>
                </div>
            </div>
            <div class="profile-user-info">
                <h2><?= htmlspecialchars($admin['admin_name']) ?></h2>
                <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($admin['admin_email']) ?></p>
                <p><i class="fas fa-phone"></i> <?= htmlspecialchars($admin['admin_contact']) ?></p>
            </div>
            <div class="profile-menu">
                <a href="/page/profile.php" class="active"><i class="fas fa-user"></i> Personal Information</a>
                <a href="/page/reset_password.php"><i class="fas fa-key"></i> Change Password</a>
                <a href="/page/logout.php"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
            </div>
        </div>

        <div class="profile-main">
            <div class="profile-card">
                <div class="profile-card-header">
                    <h2>Personal Information</h2>
                    <p>Update your personal details</p>
                </div>

                <form method="POST" class="profile-form" enctype="multipart/form-data">
                    <input type="hidden" name="form_type" value="update_profile">
                    <input type="file" id="photo" name="photo" accept="image/*" class="hidden-file-input" onchange="previewProfileImage(event)">
                    
                    <?php if (isset($_err['photo'])): ?>
                        <div class="form-error photo-error">
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_err['photo']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <div class="input-with-icon profile ">
                            <i class="fas fa-user"></i>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($admin['admin_name']) ?>" placeholder="Enter your full name" required>
                        </div>
                        <?php if (isset($_err['name'])): ?>
                            <div class="form-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_err['name']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-with-icon profile">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($admin['admin_email']) ?>" readonly>
                            <span class="readonly-badge">Cannot be changed</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contact">Contact Number</label>
                        <div class="input-with-icon profile">
                            <i class="fas fa-phone"></i>
                            <input type="text" id="contact" name="contact" value="<?= htmlspecialchars($admin['admin_contact']) ?>" placeholder="Format: XXX-XXXXXXXX" required>
                        </div>
                        <?php if (isset($_err['contact'])): ?>
                            <div class="form-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_err['contact']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="primary-button">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <div class="profile-card security-section">
                <div class="profile-card-header">
                    <h2>Account Security</h2>
                    <p>Manage your password and account security</p>
                </div>
                <div class="security-options">
                    <div class="security-option">
                        <div class="security-option-icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <div class="security-option-details">
                            <h3>Password</h3>
                            <p>It's a good idea to change your password regularly</p>
                        </div>
                    </div>
                    <a href="/page/reset_password.php" class="security-option-action">Change</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewProfileImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile-image-preview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
}
</script>