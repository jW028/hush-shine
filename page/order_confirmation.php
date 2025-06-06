<?php
require '../_base.php';

if (!isset($_SESSION['cust_id'])) {
    header("Location: ../index.php");
    exit();
}

// Get order ID from either session or URL parameter
$orderId = isset($_GET['id']) ? $_GET['id'] : (isset($_SESSION['order_id']) ? $_SESSION['order_id'] : null);
$custId = $_SESSION['cust_id'];

if (!isset($_SESSION['applied_reward_points'])) {
    $_SESSION['applied_reward_points'] = 0; 
}

try {
    // Debug session values
    if (!$orderId || !$custId) {
        throw new Exception("Missing session values: orderId or userId");
    }

    // Update order status
    $stmt = $_db->prepare("UPDATE orders SET payment_status = 'Paid', status = 'Confirmed' WHERE order_id = ? AND cust_id = ?");
    if (!$stmt->execute([$orderId, $custId])) {
        throw new Exception("Failed to update order status.");
    }
    
    // Fetch order items
    $stmt = $_db->prepare("
        SELECT oi.prod_id, oi.quantity, p.prod_name, p.price, p.image
        FROM order_items oi
        JOIN product p ON oi.prod_id = p.prod_id
        WHERE oi.order_id = ?
    ");
    if (!$stmt->execute([$orderId])) {
        throw new Exception("Failed to fetch order items.");
    }

    // $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // if (!$items) {
    //     throw new Exception("No items found for order ID: " . $orderId);
    // }

    // Fetch order details including shipping address
    $stmt = $_db->prepare("SELECT shipping_address FROM orders WHERE order_id = ? AND cust_id = ?");
    if (!$stmt->execute([$orderId, $custId])) {
        throw new Exception("Failed to fetch order details.");
    }
    $orderDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$orderDetails || !isset($orderDetails['shipping_address'])) {
        throw new Exception("Shipping address not found for order ID: " . $orderId);
    }
    $shippingAddress = $orderDetails['shipping_address'];
    // Calculate delivery date (today + 5 days)
    $deliveryDate = (new DateTime())->modify('+5 days')->format('l, d M Y');

    $orderQuery = $_db->prepare("SELECT total_amount, reward_used FROM orders WHERE order_id = ? AND cust_id = ?");
    $orderQuery->execute([$orderId, $custId]);
    $order = $orderQuery->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order not found.");
    }

    $totalAmount = $order['total_amount'];
    if ($totalAmount < 0.01) {
        $totalAmount = 0.01;
    }
    $appliedRewardPoints = $order['reward_used'];

    // Fetch items from order_items
    $stmt = $_db->prepare("SELECT oi.prod_id, oi.quantity, p.prod_name, oi.price, p.image 
                            FROM order_items oi
                            JOIN product p ON oi.prod_id = p.prod_id
                            WHERE oi.order_id = ?");
    $stmt->execute([$orderId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $subtotal = 0;
    foreach ($cartItems as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $tax = $subtotal * 0.06;

    // Remove confirmed items from cart
    $deleteStmt = $_db->prepare("
        DELETE ci FROM cart_item ci
        JOIN shopping_cart sc ON ci.cart_id = sc.cart_id
        WHERE sc.cust_id = ? AND ci.prod_id IN (SELECT prod_id FROM order_items WHERE order_id = ?)
    ");
    $deleteStmt->execute([$custId, $orderId]);


} catch (Exception $e) {
    error_log("Order Confirmation Error: " . $e->getMessage());
    echo "<p><strong>DEBUG:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    header("Location: products.php");
    exit();
}

if (isset($_SESSION['applied_reward_points']) && $_SESSION['applied_reward_points'] > 0) {
    $pointsUsed = $_SESSION['applied_reward_points'];

    try {
        // Insert a negative entry into the reward_points table to track the deduction
        $deductStmt = $_db->prepare("
            INSERT INTO reward_points (cust_id, order_id, points, description, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $deductStmt->execute([
            $custId,
            $orderId,
            -$pointsUsed, // Negative points to deduct
            "Redeemed for Order #" . $orderId
        ]);

        // Clear the applied reward points from session
        unset($_SESSION['applied_reward_points']);
    } catch (Exception $e) {
        error_log("Reward Points Deduction Error: " . $e->getMessage());
    }
}

$_title = 'Order Confirmation';
include '../_head.php';
?>
<div class="confirmation-page">
    <div class="confirmation-container">
        <div class="checkout-header">
                <h1><i class="fas fa-shopping-bag"></i> Checkout</h1>
                <div class="checkout-steps">
                    <div class="step"><span>1</span> Shipping</div>
                    <div class="step"><span>2</span> Payment</div>
                    <div class="step active"><span>3</span> Confirmation</div>
                </div>
            </div>
        <h2>Thank you for your payment!</h2>
        <p>Your order <strong><?= htmlspecialchars($orderId) ?></strong> has been confirmed.</p>

        <div class="order-summary">
            <h3>Order Summary</h3>
            <div class="shipping-address">
                <h4>Shipping Address</h4>
                <p><?= nl2br(htmlspecialchars($shippingAddress)) ?></p>
                <p><strong>Estimated Delivery Date:</strong> <?= htmlspecialchars($deliveryDate) ?></p>
            </div>

            <div class="items-list">
                <?php foreach ($cartItems as $item): ?>
                    <?php
                        // Decode JSON image data
                        $productImages = json_decode($item['image'], true) ?: [];
                        $firstImage = !empty($productImages) ? $productImages[0] : 'default.jpg';
                    ?>
                    <div class="item">
                        <img src="/images/products/<?= htmlspecialchars($firstImage) ?>" 
                            alt="<?= htmlspecialchars($item['prod_name']) ?>">
                        <div class="item-details">
                            <h4><?= htmlspecialchars($item['prod_name']) ?></h4>
                            <p>Quantity: <?= $item['quantity'] ?></p>
                            <p>Price: RM <?= number_format($item['price'], 2) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <div class="total-summary">
            <div class="row">
                <span>Subtotal:</span>
                <span>RM <?= number_format($subtotal, 2) ?></span>
            </div>
            <?php if ($appliedRewardPoints > 0): ?>
                <div class="row">
                    <span>Reward Points Applied</span>
                    <span>-RM <?= number_format($appliedRewardPoints, 2) ?></span>
                </div>
            <?php endif; ?>

            <div class="row">
                <span>Tax (6%)</span>
                <span>RM <?= number_format($tax, 2) ?></span>
            </div>
            <div class="row total">
                <span>Total:</span>
                <span>RM <?= number_format($totalAmount, 2) ?></span>
            </div>
        </div>
        <div class="confirmation-actions" style="margin-top: 30px; text-align: center;">
            <a href="products.php" class="action-button" style="display: inline-block; margin: 0 10px; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">
                Continue Shopping
            </a>
            <a href="mypurchase.php" class="action-button" style="display: inline-block; margin: 0 10px; padding: 10px 20px; background-color: #2196F3; color: white; text-decoration: none; border-radius: 5px;">
                View My Orders
            </a>
            <a href="custGenerate_invoice.php?id=<?= $orderId ?>" class="admin-submit-btn" target="_blank">
                <i class="fas fa-file-pdf"></i> Download Invoice
            </a>

            <a href="custGenerate_invoice.php?id=<?= $orderId ?>&email=1" class="admin-submit-btn secondary">
                <i class="fas fa-envelope"></i> Send Invoice to Email
            </a>
        </div>
    </div>
</div>

<?php include '../_foot.php'; ?>