<?php
require '../_base.php';

// Check if user is logged in
if (!isset($_SESSION['cust_id']) || empty($_SESSION['cust_id'])) {
    header("Location: ../page/login.php");
    exit();
}

$custId = $_SESSION['cust_id'];

try {
    // Fetch pending orders
    $pendingStmt = $_db->prepare("
        SELECT o.*, sp.payment_intent_id, sp.status as payment_status
        FROM orders o
        LEFT JOIN stripe_payments sp ON o.order_id = sp.order_id
        WHERE o.cust_id = ? AND o.status = 'Pending'
        ORDER BY o.order_date DESC
    ");
    $pendingStmt->execute([$_SESSION['cust_id']]);
    $pendingOrders = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch confirmed orders (including Processing, Shipped, Delivered)
    $confirmedStmt = $_db->prepare("
        SELECT o.*, sp.payment_intent_id, sp.status as payment_status
        FROM orders o
        LEFT JOIN stripe_payments sp ON o.order_id = sp.order_id
        WHERE o.cust_id = ? AND o.status IN ('Confirmed', 'Processing', 'Shipped', 'Delivered')
        ORDER BY o.order_date DESC
    ");
    $confirmedStmt->execute([$_SESSION['cust_id']]);
    $confirmedOrders = $confirmedStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch completed orders (status = Received)
    $completedStmt = $_db->prepare("
        SELECT o.*, sp.payment_intent_id, sp.status as payment_status
        FROM orders o
        LEFT JOIN stripe_payments sp ON o.order_id = sp.order_id
        WHERE o.cust_id = ? AND o.status = 'Received'
        ORDER BY o.order_date DESC
    ");
    $completedStmt->execute([$_SESSION['cust_id']]);
    $completedOrders = $completedStmt->fetchAll(PDO::FETCH_ASSOC);

    $cancelledStmt = $_db->prepare("
        SELECT o.*, sp.payment_intent_id, sp.status as payment_status
        FROM orders o
        LEFT JOIN stripe_payments sp ON o.order_id = sp.order_id
        WHERE o.cust_id = ? AND o.status = 'Cancelled'
        ORDER BY o.order_date DESC
    ");
    $cancelledStmt->execute([$_SESSION['cust_id']]);
    $cancelledOrders = $cancelledStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log("My Purchase Error: " . $e->getMessage());
    $pendingOrders = [];
    $confirmedOrders = [];
    $completedOrders = [];
    $cancelledOrders = [];
}

// Handle Mark as Received
if (isset($_POST['mark_received'])) {
    try {
        $orderId = $_POST['order_id'];
        $stmt = $_db->prepare("UPDATE orders SET status = 'Received' WHERE order_id = ? AND cust_id = ?");
        $stmt->execute([$orderId, $_SESSION['cust_id']]);
        header("Location: mypurchase.php?tab=completed");
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

        // Redirect to payment page (e.g., Stripe)
        header("Location: stripe.php");
        exit();
    } catch (Exception $e) {
        error_log("Pay Now Error: " . $e->getMessage());
    }
}

if (isset($_POST['submit_review'])) {
    try {
        $orderId = $_POST['order_id'];
        $prodId = $_POST['prod_id'];
        $rating = $_POST['rating'];
        $review = $_POST['review'];

        $stmt = $_db->prepare("
            INSERT INTO prod_reviews (order_id, prod_id, cust_id, rating, review)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$orderId, $prodId, $_SESSION['cust_id'], $rating, $review]);

        header("Location: mypurchase.php?tab=completed");
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

$returnRefundStmt = $_db->prepare("
    SELECT rr.*, o.total_amount, o.order_date
    FROM return_refund_requests rr
    JOIN orders o ON rr.order_id = o.order_id
    WHERE rr.cust_id = ?
    ORDER BY rr.created_at DESC
");
$returnRefundStmt->execute([$custId]);
$returnRefundOrders = $returnRefundStmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['request_return_refund'])) {
    try {
        $orderId = $_POST['order_id'];
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

        header("Location: mypurchase.php?tab=return_refund");
        exit();
    } catch (Exception $e) {
        error_log("Return/Refund Request Error: " . $e->getMessage());
    }
}

if (isset($_POST['update_refund_status'])) {
    try {
        $requestId = $_POST['request_id'];
        $newStatus = $_POST['status'];

        $stmt = $_db->prepare("UPDATE return_refund_requests SET status = ? WHERE request_id = ?");
        $stmt->execute([$newStatus, $requestId]);

        header("Location: mypurchase.php?tab=return_refund");
        exit();
    } catch (Exception $e) {
        error_log("Refund Status Update Error: " . $e->getMessage());
    }
}

$_title = 'My Purchases';
include '../_head.php';
?>

<div class="mypurchase-page">
    <nav class="purchase-nav">
        <a href="?tab=pending" class="<?= ($_GET['tab'] ?? 'pending') === 'pending' ? 'active' : '' ?>">Pending</a>
        <a href="?tab=confirmed" class="<?= ($_GET['tab'] ?? '') === 'confirmed' ? 'active' : '' ?>">Confirmed</a>
        <a href="?tab=completed" class="<?= ($_GET['tab'] ?? '') === 'completed' ? 'active' : '' ?>">Completed</a>
        <a href="?tab=cancelled" class="<?= ($_GET['tab'] ?? '') === 'cancelled' ? 'active' : '' ?>">Cancelled</a>
        <a href="?tab=return_refund" class="<?= ($_GET['tab'] ?? '') === 'return_refund' ? 'active' : '' ?>">Return/Refund</a>
    </nav>

    <?php if (($_GET['tab'] ?? 'pending') === 'pending'): ?>
        <h2>Pending Orders</h2>
        <?php if (empty($pendingOrders)): ?>
            <p>No pending orders found.</p>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($pendingOrders as $order): ?>
                    <div class="order-card">
                        <h3>Order #<?= $order['order_id'] ?></h3>
                        <p>Placed on <?= date('F j, Y', strtotime($order['order_date'])) ?></p>
                        <p>Status: <?= htmlspecialchars($order['status']) ?></p>
                        <p>Total Amount: RM <?= number_format($order['total_amount'], 2) ?></p>

                        <form method="POST" class="order-actions">
                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                            <input type="hidden" name="total_amount" value="<?= $order['total_amount'] ?>">
                            <button type="submit" name="cancel_order" class="btn-cancel">Cancel</button>
                            <button type="submit" name="pay_now" class="btn-pay">Pay Now</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php elseif (($_GET['tab'] ?? '') === 'confirmed'): ?>
        <h2>Confirmed Orders</h2>
        <?php if (empty($confirmedOrders)): ?>
            <p>No confirmed orders found.</p>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($confirmedOrders as $order): ?>
                    <div class="order-card">
                        <h3>Order #<?= $order['order_id'] ?></h3>
                        <p>Placed on <?= date('F j, Y', strtotime($order['order_date'])) ?></p>
                        <p>Status: <?= htmlspecialchars($order['status']) ?></p>
                        <p>Total Amount: RM <?= number_format($order['total_amount'], 2) ?></p>

                        <?php if ($order['status'] === 'Delivered'): ?>
                            <form method="POST" class="order-actions">
                                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                <button type="submit" name="mark_received" class="btn-received">Mark as Received</button>
                            </form>
                            <form method="POST" enctype="multipart/form-data" class="order-actions">
                                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                <textarea name="reason" placeholder="Reason for return/refund" required></textarea>
                                <input type="file" name="photo" accept="image/*" required>
                                <button type="submit" name="request_return_refund" class="btn-return-refund">Return/Refund</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php elseif (($_GET['tab'] ?? '') === 'completed'): ?>
    <h2>Completed Orders</h2>
    <?php if (empty($completedOrders)): ?>
        <p>No completed orders found.</p>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($completedOrders as $order): ?>
                <div class="order-card">
                    <h3>Order #<?= $order['order_id'] ?></h3>
                    <p>Placed on <?= date('F j, Y', strtotime($order['order_date'])) ?></p>
                    <p>Status: <?= htmlspecialchars($order['status']) ?></p>
                    <p>Total Amount: RM <?= number_format($order['total_amount'], 2) ?></p>

                    <!-- Fetch products for this order -->
                    <?php
                    $itemStmt = $_db->prepare("
                        SELECT oi.*, p.prod_name
                        FROM order_items oi
                        JOIN product p ON oi.prod_id = p.prod_id
                        WHERE oi.order_id = ?
                    ");
                    $itemStmt->execute([$order['order_id']]);
                    $orderItems = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <div class="order-products">
                        <?php foreach ($orderItems as $item): ?>
                            <div class="product-review">
                                <p><strong>Product:</strong> <?= htmlspecialchars($item['prod_name']) ?></p>
                                <?php if (!hasReview($order['order_id'], $item['prod_id'], $_SESSION['cust_id'], $_db)): ?>
                                    <form method="POST" class="review-form">
                                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                        <input type="hidden" name="prod_id" value="<?= $item['prod_id'] ?>">

                                        <label for="rating-<?= $item['prod_id'] ?>">Rating:</label>
                                        <select name="rating" id="rating-<?= $item['prod_id'] ?>" required>
                                            <option value="">Select</option>
                                            <option value="1">1 - Poor</option>
                                            <option value="2">2 - Fair</option>
                                            <option value="3">3 - Good</option>
                                            <option value="4">4 - Very Good</option>
                                            <option value="5">5 - Excellent</option>
                                        </select>

                                        <label for="review-<?= $item['prod_id'] ?>">Review:</label>
                                        <textarea name="review" id="review-<?= $item['prod_id'] ?>" rows="3" required></textarea>

                                        <button type="submit" name="submit_review" class="btn-submit-review">Submit Review</button>
                                    </form>
                                <?php else: ?>
                                    <p><strong>Review Submitted:</strong> Thank you for your feedback!</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php elseif (($_GET['tab'] ?? '') === 'cancelled'): ?>
        <h2>Cancelled Orders</h2>
        <?php if (empty($cancelledOrders)): ?>
            <p>No cancelled orders found.</p>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($cancelledOrders as $order): ?>
                    <div class="order-card">
                        <h3>Order #<?= $order['order_id'] ?></h3>
                        <p>Placed on <?= date('F j, Y', strtotime($order['order_date'])) ?></p>
                        <p>Status: <?= htmlspecialchars($order['status']) ?></p>
                        <p>Total Amount: RM <?= number_format($order['total_amount'], 2) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>    
    <?php elseif (($_GET['tab'] ?? '') === 'return_refund'): ?>
    <h2>Return/Refund Requests</h2>
    <?php if (empty($returnRefundOrders)): ?>
        <p>No return/refund requests found.</p>
    <?php else: ?>
        <div class="orders-list">
            <?php 
            foreach ($returnRefundOrders as $request): ?>
                <div class="order-card">
                    <h3>Order #<?= $request['order_id'] ?></h3>
                    <p>Requested on <?= date('F j, Y', strtotime($request['created_at'])) ?></p>
                    <p><strong>Total Amount:</strong> RM <?= number_format($request['total_amount'], 2) ?></p>
                    <p>Status: <?= htmlspecialchars($request['status']) ?></p>
                    <p>Reason: <?= htmlspecialchars($request['reason']) ?></p>
                    <img src="<?= htmlspecialchars($request['photo']) ?>" alt="Return/Refund Photo" style="max-width: 200px;">
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
</div>

<style>
/* Add styles for the Cancel, Pay Now, and Mark as Received buttons */
.order-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.btn-cancel {
    background: #f44336;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-cancel:hover {
    background: #d32f2f;
}

.btn-pay {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-pay:hover {
    background: #45a049;
}

.btn-received {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-received:hover {
    background: #45a049;
}
</style>

<style>
/* Add styles for the Cancel, Pay Now, and Mark as Received buttons */
.order-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.btn-cancel {
    background: #f44336;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-cancel:hover {
    background: #d32f2f;
}

.btn-pay {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-pay:hover {
    background: #45a049;
}

.btn-received {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-received:hover {
    background: #45a049;
}
.btn-return-refund {
    background: #FF9800;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-return-refund:hover {
    background: #F57C00;
}

textarea {
    width: 100%;
    margin-bottom: 10px;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

input[type="file"] {
    margin-bottom: 10px;
}
</style>

<?php include '../_foot.php'; ?>