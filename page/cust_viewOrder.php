<?php
require '../_base.php';

// Check if user is logged in
if (!isset($_SESSION['cust_id']) || empty($_SESSION['cust_id'])) {
    header("Location: ../page/login.php");
    exit();
}

$custId = $_SESSION['cust_id'];

// Get order ID from URL
$orderId = $_GET['id'] ?? null;

if (!$orderId) {
    // Redirect if no order ID provided
    header('Location: mypurchase.php');
    exit;
}

try {
    // Verify this order belongs to the logged-in customer
    $orderStmt = $_db->prepare("
        SELECT o.*, sp.payment_intent_id, sp.status as payment_status
        FROM orders o
        LEFT JOIN stripe_payments sp ON o.order_id = sp.order_id
        WHERE o.order_id = ? AND o.cust_id = ?
    ");
    $orderStmt->execute([$orderId, $custId]);
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        // Order not found or doesn't belong to this customer
        header('Location: mypurchase.php');
        exit;
    }
    
    // Get order items
    $itemStmt = $_db->prepare("
        SELECT oi.*, p.prod_name, p.image
        FROM order_items oi
        JOIN product p ON oi.prod_id = p.prod_id
        WHERE oi.order_id = ?
    ");
    $itemStmt->execute([$orderId]);
    $orderItems = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Parse tracking information from shipping_address if available
    $trackingInfo = [
        'courier' => '',
        'tracking_number' => ''
    ];
    
    if (!empty($order['shipping_address']) && strpos($order['shipping_address'], '--TRACKING INFO--') !== false) {
        $parts = explode('--TRACKING INFO--', $order['shipping_address']);
        $shippingAddress = trim($parts[0]);
        
        // Extract tracking info
        if (isset($parts[1])) {
            if (preg_match('/Courier:\s*(.*?)\s*\n/i', $parts[1], $match)) {
                $trackingInfo['courier'] = $match[1];
            }
            if (preg_match('/Tracking Number:\s*(.*?)\s*(\n|$)/i', $parts[1], $match)) {
                $trackingInfo['tracking_number'] = $match[1];
            }
        }
    } else {
        $shippingAddress = $order['shipping_address'];
    }

    // Get customer information
    $custStmt = $_db->prepare("
        SELECT cust_name, cust_email, cust_contact
        FROM customer
        WHERE cust_id = ?
    ");
    $custStmt->execute([$custId]);
    $customer = $custStmt->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("View Order Error: " . $e->getMessage());
    header('Location: mypurchase.php');
    exit;
}
// Handle Mark as Received
if (isset($_POST['mark_received'])) {
    try {
        $stmt = $_db->prepare("UPDATE orders SET status = 'Received' WHERE order_id = ? AND cust_id = ?");
        $stmt->execute([$orderId, $custId]);
        header("Location: cust_viewOrder.php?id=" . $orderId . "&status=updated");
        exit();
    } catch (Exception $e) {
        error_log("Mark Received Error: " . $e->getMessage());
    }
}
// Handle Cancel Order
if (isset($_POST['cancel_order'])) {
    try {
        $orderId = $_POST['order_id'];
        $cancelStmt = $_db->prepare("UPDATE orders SET status = 'Cancelled' WHERE order_id = ? AND cust_id = ?");
        $cancelStmt->execute([$orderId, $_SESSION['cust_id']]);
        header("Location: mypurchase.php?tab=pending");
        exit();
    } catch (Exception $e) {
        error_log("Cancel Order Error: " . $e->getMessage());
    }
}

// Handle Pay Now
if (isset($_POST['pay_now'])) {
    try {
        $orderId = $_POST['order_id'];
        $_SESSION['checkout_total'] = $_POST['total_amount'];
        $_SESSION['order_id'] = $orderId;
        $_SESSION['is_existing_order'] = true; // Add this line
        header("Location: stripe.php");
        exit();
    } catch (Exception $e) {
        error_log("Pay Now Error: " . $e->getMessage());
    }
}

// Handle Return/Refund Request
if (isset($_POST['request_return_refund'])) {
    try {
        $reason = $_POST['reason'];
        $photo = $_FILES['photo'];

        $photoPath = 'uploads/return_refund/' . basename($photo['name']);
        move_uploaded_file($photo['tmp_name'], $photoPath);

        $stmt = $_db->prepare("
            INSERT INTO return_refund_requests (order_id, cust_id, reason, photo)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$orderId, $custId, $reason, $photoPath]);

        $updateStmt = $_db->prepare("UPDATE orders SET status = 'Request Pending' WHERE order_id = ? AND cust_id = ?");
        $updateStmt->execute([$orderId, $custId]);

        header("Location: cust_viewOrder.php?id=" . $orderId . "&status=return_requested");
        exit();
    } catch (Exception $e) {
        error_log("Return/Refund Request Error: " . $e->getMessage());
    }
}

// Handle Submit Review
if (isset($_POST['submit_review'])) {
    try {
        $prodId = $_POST['prod_id'];
        $rating = $_POST['rating'];
        $review = $_POST['review'];

        $stmt = $_db->prepare("
            INSERT INTO prod_reviews (order_id, prod_id, cust_id, rating, review)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$orderId, $prodId, $custId, $rating, $review]);

        header("Location: cust_viewOrder.php?id=" . $orderId . "&status=review_submitted");
        exit();
    } catch (Exception $e) {
        error_log("Review Submission Error: " . $e->getMessage());
    }
}

function hasReview($orderId, $prodId, $custId, $db) {
    $stmt = $db->prepare("
        SELECT COUNT(*) AS review_count
        FROM prod_reviews
        WHERE order_id = ? AND prod_id = ? AND cust_id = ?
    ");
    $stmt->execute([$orderId, $prodId, $custId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['review_count'] > 0;
}

$_title = 'Order Details #' . $orderId;
include '../_head.php';
?>

<div class="custView-order-details-page">
    <div class="custView-page-header">
        <h2>Order #<?= $orderId ?></h2>
        <a href="mypurchase.php" class="custView-back-link">Back to My Purchases</a>
    </div>

    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] === 'updated'): ?>
            <div class="alert alert-success">Order status has been updated successfully.</div>
        <?php elseif ($_GET['status'] === 'return_requested'): ?>
            <div class="alert alert-success">Return/refund request has been submitted successfully.</div>
        <?php elseif ($_GET['status'] === 'review_submitted'): ?>
            <div class="alert alert-success">Your review has been submitted successfully.</div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="custView-order-summary-section">
        <div class="custView-order-status">
            <h3>Order Status: <span class="custView-status-badge status-<?= strtolower($order['status']) ?>"><?= htmlspecialchars($order['status']) ?></span></h3>
            <div class="custView-status-progression-container">
        <div class="custView-status-track">
            <?php
            $statuses = ['Confirmed', 'Processing', 'Shipped', 'Delivered'];
            if (in_array($order['status'], $statuses)):
                $currentStatusIndex = array_search($order['status'], $statuses);
                foreach ($statuses as $index => $status): ?>
                    <div class="custView-status-step <?= $index <= $currentStatusIndex ? 'active' : '' ?>">
                        <div class="custView-status-dot"></div>
                        <div class="custView-status-label"><?= htmlspecialchars($status) ?></div>
                    </div>
                    <?php if ($index < count($statuses) - 1): ?>
                        <div class="custView-status-line"></div>
                    <?php endif; ?>
                <?php endforeach;
            endif; ?>
        </div>
        </div>
            <p>Order Date: <?= date('F j, Y g:i A', strtotime($order['order_date'])) ?></p>
        </div>
        
        <?php if ($order['status'] === 'Pending'): ?>
            <div class="custView-order-actions">
                <form method="POST" class="custView-action-form">
                    <input type="hidden" name="order_id" value="<?= $orderId ?>">
                    <button type="submit" name="cancel_order" class="custView-btn btn-danger">Cancel Order</button>
                </form>
                <form method="POST" class="custView-action-form">
                    <input type="hidden" name="order_id" value="<?= $orderId ?>">
                    <input type="hidden" name="total_amount" value="<?= $order['total_amount'] ?>">
                    <button type="submit" name="pay_now" class="custView-btn btn-primary">Pay Now</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($order['status'] === 'Delivered'): ?>
            <div class="custView-order-actions">
                <form method="POST" class="custView-action-form">
                    <input type="hidden" name="order_id" value="<?= $orderId ?>">
                    <button type="submit" name="mark_received" class="custView-btn btn-primary">Mark as Received</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <div class="custView-order-details-grid">
        <!-- Customer Information -->
        <div class="custView-detail-card">
            <h3>Customer Information</h3>
            <div class="custView-card-content">
                <p><strong>Name:</strong> <?= htmlspecialchars($customer['cust_name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($customer['cust_email']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($customer['cust_contact']) ?></p>
            </div>
        </div>
        
        <!-- Shipping Address -->
        <div class="custView-detail-card">
            <h3>Shipping Address</h3>
            <div class="custView-card-content">
                <address>
                    <?= nl2br(htmlspecialchars($shippingAddress ?? 'No shipping address provided')) ?>
                </address>
            </div>
        </div>
        
        <?php if (!empty($trackingInfo['tracking_number'])): ?>
        <!-- Tracking Information -->
        <div class="custView-detail-card">
            <h3>Tracking Information</h3>
            <div class="custView-card-content">
                <p><strong>Courier:</strong> <?= htmlspecialchars($trackingInfo['courier']) ?></p>
                <p><strong>Tracking Number:</strong> <?= htmlspecialchars($trackingInfo['tracking_number']) ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Order Items -->
        <div class="custView-detail-card full-width">
            <h3>Order Items</h3>
            <div class="custView-card-content">
                <table class="custView-order-items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $subtotal = 0;
                        foreach ($orderItems as $item): 
                            $subtotal += $item['price'] * $item['quantity'];
                        ?>
                            <?php 
                                $productImage = '../images/no-image.png'; // Default image
                                if (!empty($item['image'])) {
                                    $imageData = json_decode($item['image'], true);
                                    if ($imageData) {
                                        $imageFile = is_array($imageData) ? $imageData[0] : $imageData;
                                        $productImage = '../images/products/' . $imageFile;
                                        if (!file_exists($productImage)) {
                                            $productImage = '../images/no-image.png';
                                        }
                                    }
                                }
                            ?>
                            <tr>
                                <td class="custView-product-cell">
                                    <div class="custView-product-info">
                                        <img src="<?= $productImage ?>" alt="<?= htmlspecialchars($item['prod_name']) ?>" class="custView-product-thumb">
                                        <span><?= htmlspecialchars($item['prod_name']) ?></span>
                                    </div>
                                </td>
                                <td>RM <?= number_format($item['price'], 2) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td>RM <?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="custView-text-right"><strong>Subtotal:</strong></td>
                            <td>RM <?= number_format($subtotal, 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="custView-text-right"><strong>Tax (6%):</strong></td>
                            <td>RM <?= number_format($subtotal * 0.06, 2) ?></td>
                        </tr>
                        <tr>    
                            <td colspan="3" class="custView-text-right"><strong>(-) Reward Points:</strong></td>
                            <td>RM <?= number_format($order['reward_used']) ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="custView-text-right"><strong>Total:</strong></td>
                            <td><strong>RM <?= number_format($order['total_amount'], 2) ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <?php if ($order['status'] === 'Delivered'): ?>
        <!-- Return/Refund Request Form -->
        <div class="custView-detail-card full-width">
            <h3>Request Return/Refund</h3>
            <div class="custView-card-content">
                <form method="POST" enctype="multipart/form-data" class="custView-return-form">
                    <div class="custView-form-group">
                        <label for="reason">Reason for return/refund:</label>
                        <textarea name="reason" id="reason" rows="3" required></textarea>
                    </div>
                    <div class="custView-form-group">
                        <label for="photo">Upload photo evidence:</label>
                        <input type="file" name="photo" id="photo" accept="image/*" required>
                    </div>
                    <button type="submit" name="request_return_refund" class="custView-btn btn-warning">Submit Request</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($order['status'] === 'Received'): ?>
        <!-- Product Reviews -->
        <div class="custView-detail-card full-width">
            <h3>Product Reviews</h3>
            <div class="custView-card-content">
                <?php foreach ($orderItems as $item): ?>
                    <div class="custView-product-review-section">
                        <h4><?= htmlspecialchars($item['prod_name']) ?></h4>
                        
                        <?php if (!hasReview($orderId, $item['prod_id'], $custId, $_db)): ?>
                            <form method="POST" class="custView-review-form">
                                <input type="hidden" name="prod_id" value="<?= $item['prod_id'] ?>">
                                
                                <div class="custView-form-group">
                                    <label for="rating-<?= $item['prod_id'] ?>">Rating:</label>
                                    <select name="rating" id="rating-<?= $item['prod_id'] ?>" required>
                                        <option value="">Select</option>
                                        <option value="1">1 - Poor</option>
                                        <option value="2">2 - Fair</option>
                                        <option value="3">3 - Good</option>
                                        <option value="4">4 - Very Good</option>
                                        <option value="5">5 - Excellent</option>
                                    </select>
                                </div>
                                
                                <div class="custView-form-group">
                                    <label for="review-<?= $item['prod_id'] ?>">Review:</label>
                                    <textarea name="review" id="review-<?= $item['prod_id'] ?>" rows="3" required></textarea>
                                </div>
                                
                                <button type="submit" name="submit_review" class="custView-btn btn-primary">Submit Review</button>
                            </form>
                        <?php else: ?>
                            <p class="custView-review-submitted">
                                <i class="custView-fas fa-check-circle"></i> 
                                Thank you for your feedback!
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../_foot.php'; ?>