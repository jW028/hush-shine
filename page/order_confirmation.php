<?php
require '../_base.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['order_id'])) {
    header("Location: ../index.php");
    exit();
}

$orderId = $_SESSION['order_id'];
$userId = $_SESSION['user_id'];

try {
    // Debug session values
    if (!$orderId || !$userId) {
        throw new Exception("Missing session values: orderId or userId");
    }

    // Update order status
    $stmt = $_db->prepare("UPDATE orders SET payment_status = 'Paid', status = 'Confirmed' WHERE order_id = ? AND cust_id = ?");
    if (!$stmt->execute([$orderId, $userId])) {
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

    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$items) {
        throw new Exception("No items found for order ID: " . $orderId);
    }

    // Fetch order details including shipping address
    $stmt = $_db->prepare("SELECT shipping_address FROM orders WHERE order_id = ? AND cust_id = ?");
    if (!$stmt->execute([$orderId, $userId])) {
        throw new Exception("Failed to fetch order details.");
    }
    $orderDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$orderDetails || !isset($orderDetails['shipping_address'])) {
        throw new Exception("Shipping address not found for order ID: " . $orderId);
    }
    $shippingAddress = $orderDetails['shipping_address'];
    // Calculate delivery date (today + 5 days)
    $deliveryDate = (new DateTime())->modify('+5 days')->format('l, d M Y');

    // Calculate totals
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $tax = $subtotal * 0.06;
    $total = $subtotal + $tax;

    // Clean up session
    unset($_SESSION['checkout_total']);
    unset($_SESSION['order_id']);

    // Remove confirmed items from cart
    $deleteStmt = $_db->prepare("
        DELETE ci FROM cart_item ci
        JOIN shopping_cart sc ON ci.cart_id = sc.cart_id
        WHERE sc.cust_id = ? AND ci.prod_id IN (SELECT prod_id FROM order_items WHERE order_id = ?)
    ");
    $deleteStmt->execute([$userId, $orderId]);


} catch (Exception $e) {
    error_log("Order Confirmation Error: " . $e->getMessage());
    echo "<p><strong>DEBUG:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    exit();
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
                <?php foreach ($items as $item): ?>
                    <div class="item">
                        <img src="/images/prod_img/<?= htmlspecialchars($item['image']) ?>" 
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
                <div class="row">
                    <span>Tax (6%):</span>
                    <span>RM <?= number_format($tax, 2) ?></span>
                </div>
                <div class="row total">
                    <span>Total:</span>
                    <span>RM <?= number_format($total, 2) ?></span>
                </div>
            </div>
            <div class="confirmation-actions" style="margin-top: 30px; text-align: center;">
                <a href="products.php" class="action-button" style="display: inline-block; margin: 0 10px; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">
                    Continue Shopping
                </a>
                <a href="order_history.php" class="action-button" style="display: inline-block; margin: 0 10px; padding: 10px 20px; background-color: #2196F3; color: white; text-decoration: none; border-radius: 5px;">
                    View My Orders
                </a>
            </div>
        </div>
    </div>
</div>
<?php include '../_foot.php'; ?>
