<?php
require_once '../_base.php';
auth('admin');

$_title = 'Block Customer';
include '../_head.php';
$_adminContext = true;

$customer_id = $_GET['id'] ?? '';
$errors = [];
$success = false;
$customer = [];

// Fetch customer information
if (!empty($customer_id)) {
    try {
        $stmt = $_db->prepare("SELECT * FROM customer WHERE cust_id = ?");
        $stmt->execute([$customer_id]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$customer) {
            $errors[] = "Customer not found";
        }
    } catch (PDOException $e) {
        $errors[] = "Database error: " . $e->getMessage();
    }
}

// Process the block/unblock request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_block'])) {
    $action = $_POST['action'] ?? '';
    $block_duration = $_POST['block_duration'] ?? '';
    $block_reason = trim($_POST['block_reason'] ?? '');
    
    if ($action === 'block') {
        if (empty($block_duration)) {
            $errors[] = "Please select a block duration";
        }
        
        if (empty($block_reason)) {
            $errors[] = "Please provide a reason for blocking";
        }
        
        if (empty($errors)) {
            try {
                $blocked_until = null;
                
                // Calculate block end date based on duration
                switch ($block_duration) {
                    case '1_day':
                        $blocked_until = date('Y-m-d H:i:s', strtotime('+1 day'));
                        break;
                    case '1_week':
                        $blocked_until = date('Y-m-d H:i:s', strtotime('+1 week'));
                        break;
                    case '1_month':
                        $blocked_until = date('Y-m-d H:i:s', strtotime('+1 month'));
                        break;
                    case 'permanent':
                        $blocked_until = '2099-12-31 23:59:59'; // Essentially permanent
                        break;
                    default:
                        $errors[] = "Invalid block duration";
                        break;
                }
                
                if (empty($errors)) {
                    $stmt = $_db->prepare("UPDATE customer SET status = 'blocked', blocked_until = ?, block_reason = ? WHERE cust_id = ?");
                    $stmt->execute([$blocked_until, $block_reason, $customer_id]);
                    
                    $success = true;
                    $_SESSION['message'] = "Customer account has been blocked until " . date('F j, Y g:i A', strtotime($blocked_until));
                    $_SESSION['message_type'] = 'success';
                    
                    
                    header("Location: admin_customer.php");
                    exit;
                }
            } catch (PDOException $e) {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    } else if ($action === 'unblock') {
        try {
            $stmt = $_db->prepare("UPDATE customer SET status = 'active', blocked_until = NULL, block_reason = NULL WHERE cust_id = ?");
            $stmt->execute([$customer_id]);
            
            $success = true;
            $_SESSION['message'] = "Customer account has been unblocked";
            $_SESSION['message_type'] = 'success';
            
            header("Location: admin_customer.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<main class="admin-main">
    <div class="admin-title">
        <h2><?= $customer['status'] === 'blocked' ? 'Manage Block Status' : 'Block Customer Account' ?></h2>
        <a href="admin_customer.php" class="category-btn"><i class="fas fa-arrow-left"></i> Back to Customers</a>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="error-container">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success-container">
            <p>The operation was completed successfully.</p>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($customer)): ?>
        <div class="card">
            <div class="card-header">
                <h3>Customer Information</h3>
            </div>
            <div class="card-body">
                <div class="customer-info">
                    <p><strong>ID:</strong> <?= htmlspecialchars($customer['cust_id']) ?></p>
                    <p><strong>Name:</strong> <?= htmlspecialchars($customer['cust_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($customer['cust_email']) ?></p>
                    <p><strong>Contact:</strong> <?= htmlspecialchars($customer['cust_contact']) ?></p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge <?= $customer['status'] === 'blocked' ? 'blocked' : 'active' ?>">
                            <?= ucfirst(htmlspecialchars($customer['status'] ?? 'active')) ?>
                        </span>
                    </p>
                    
                    <?php if ($customer['status'] === 'blocked' && !empty($customer['blocked_until'])): ?>
                        <p><strong>Blocked Until:</strong> <?= date('F j, Y g:i A', strtotime($customer['blocked_until'])) ?></p>
                        <p><strong>Block Reason:</strong> <?= htmlspecialchars($customer['block_reason'] ?? 'N/A') ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h3><?= $customer['status'] === 'blocked' ? 'Update Block Status' : 'Block Account' ?></h3>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <?php if ($customer['status'] === 'blocked'): ?>
                        <div class="form-group">
                            <p class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                This customer is currently blocked until <?= date('F j, Y g:i A', strtotime($customer['blocked_until'])) ?>
                            </p>
                            <input type="hidden" name="action" value="unblock">
                            <button style="width:100%" type="submit" name="submit_block" class="admin-submit-btn">
                                <i class="fas fa-unlock"></i> Unblock Customer Account
                            </button>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="action" value="block">
                        <div class="form-group">
                            <label for="block_duration">Block Duration:</label>
                            <select id="block_duration" name="block_duration" class="form-control" required>
                                <option value="">Select duration</option>
                                <option value="1_day">1 Day</option>
                                <option value="1_week">1 Week</option>
                                <option value="1_month">1 Month</option>
                                <option value="permanent">Permanent Block</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="block_reason">Block Reason:</label>
                            <textarea id="block_reason" name="block_reason" class="form-control" rows="3" required></textarea>
                            <small class="form-text text-muted">Please provide a reason for blocking this customer's account.</small>
                        </div>
                        
                        <button style="width:100%" type="submit" name="submit_block" class="admin-submit-btn danger" onclick="return confirm('Are you sure you want to block this customer account?')">
                            <i class="fas fa-ban"></i> Block Customer Account
                        </button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="error-container">
            <p>No customer found with the provided ID.</p>
        </div>
    <?php endif; ?>
</main>