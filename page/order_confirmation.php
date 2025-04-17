<?php
require '../_base.php';
require_once '../vendor/autoload.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

try {
    // Get payment intent ID from URL
    if (!isset($_GET['payment_intent'])) {
        throw new Exception("No payment information found");
    }

    // Initialize Stripe
    \Stripe\Stripe::setApiKey('sk_test_51R6kNpFNb65u1viGxsiDLhrmT5wfQNQtzlOhGp6Ldu7uMbQ577pvupwdb1D1dzcYdtvD2O28QevBeriOyNBaOoyJ00DgX8TQNp');

    // Retrieve payment intent
    $paymentIntent = \Stripe\PaymentIntent::retrieve($_GET['payment_intent']);
    
    if ($paymentIntent->status !== 'succeeded') {
        throw new Exception("Payment was not successful");
    }

    // Start transaction
    $_db->beginTransaction();

    // Create order
    $orderStmt = $_db->prepare("
        INSERT INTO orders (
            cust_id, 
            order_date, 
            total_amount, 
            status, 
            payment_id,
            payment_status
        ) VALUES (?, NOW(), ?, 'Confirmed', ?, 'Paid')
    ");

    $orderStmt->execute([
        $_SESSION['user_id'],
        $paymentIntent->amount / 100, // Convert from cents
        $paymentIntent->id
    ]);
    $orderId = $_db->lastInsertId();

    // Get cart items
    $cartStmt = $_db->prepare("
        SELECT ci.prod_id, ci.quantity, p.price
        FROM cart_item ci
        JOIN product p ON ci.prod_id = p.prod_id
        JOIN shopping_cart sc ON ci.cart_id = sc.cart_id
        WHERE sc.cust_id = ?
    ");
    $cartStmt->execute([$_SESSION['user_id']]);
    $cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

    // Add order items
    $itemStmt = $_db->prepare("
        INSERT INTO order_items (
            order_id, 
            prod_id, 
            quantity, 
            price
        ) VALUES (?, ?, ?, ?)
    ");
    foreach ($cartItems as $item) {
        $itemStmt->execute([
            $orderId,
            $item['prod_id'],
            $item['quantity'],
            $item['price']
        ]);
    }

    // Clear cart
    $clearCartStmt = $_db->prepare("
        DELETE ci FROM cart_item ci
        JOIN shopping_cart sc ON ci.cart_id = sc.cart_id
        WHERE sc.cust_id = ?
    ");
    $clearCartStmt->execute([$_SESSION['user_id']]);

    // Commit transaction
    $_db->commit();

    // Get order details for display
    $orderStmt = $_db->prepare("
        SELECT o.*, oi.*, p.prod_name, p.image
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN product p ON oi.prod_id = p.prod_id
        WHERE o.order_id = ?
    ");
    $orderStmt->execute([$orderId]);
    $orderItems = $orderStmt->fetchAll(PDO::FETCH_ASSOC);

    $success = true;
    $message = "Payment successful! Your order has been confirmed.";

} catch (Exception $e) {
    if ($_db->inTransaction()) {
        $_db->rollBack();
    }
    $success = false;
    $message = $e->getMessage();
    error_log("Order Confirmation Error: " . $e->getMessage());
}

$_title = 'Order Confirmation';
include '../_head.php';
?>

<div class="confirmation-container">
    <?php if ($success): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <h1>Thank You for Your Order!</h1>
            <p><?= htmlspecialchars($message) ?></p>
        </div>

        <div class="order-details">
            <h2>Order Summary</h2>
            <div class="order-info">
                <p>Order ID: #<?= $orderId ?></p>
                <p>Date: <?= date('F j, Y') ?></p>
                <p>Payment Status: Paid</p>
            </div>

            <div class="order-items">
                <?php foreach ($orderItems as $item): ?>
                    <div class="item">
                        <img src="/images/prod_img/<?= htmlspecialchars($item['image']) ?>" 
                             alt="<?= htmlspecialchars($item['prod_name']) ?>">
                        <div class="item-details">
                            <h3><?= htmlspecialchars($item['prod_name']) ?></h3>
                            <p>Quantity: <?= $item['quantity'] ?></p>
                            <p>Price: RM <?= number_format($item['price'], 2) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="order-total">
                <div class="total-row">
                    <span>Total Paid:</span>
                    <span>RM <?= number_format($paymentIntent->amount / 100, 2) ?></span>
                </div>
            </div>

            <div class="actions">
                <a href="../index.php" class="btn-primary">Continue Shopping</a>
                <a href="order_history.php?tab=orders" class="btn-secondary">View Orders</a>
            </div>
        </div>
    <?php else: ?>
        <div class="error-message">
            <i class="fas fa-times-circle"></i>
            <h1>Payment Failed</h1>
            <p><?= htmlspecialchars($message) ?></p>
            <div class="actions">
                <a href="checkout.php" class="btn-primary">Try Again</a>
                <a href="cart.php" class="btn-secondary">Return to Cart</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.confirmation-container {
    max-width: 800px;
    margin: 150px auto 50px;
    padding: 30px;
}

.success-message,
.error-message {
    text-align: center;
    margin-bottom: 40px;
}

.success-message i,
.error-message i {
    font-size: 48px;
    color: #4CAF50;
    margin-bottom: 20px;
}

.error-message i {
    color: #dc3545;
}

.order-details {
    background: #f9f9f9;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
}

.order-info {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.order-items .item {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
    align-items: center;
}

.item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 4px;
}

.order-total {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #eee;
}

.total-row {
    display: flex;
    justify-content: space-between;
    font-size: 1.2em;
    font-weight: bold;
}

.actions {
    margin-top: 30px;
    display: flex;
    gap: 15px;
    justify-content: center;
}

.btn-primary,
.btn-secondary {
    padding: 12px 24px;
    border-radius: 4px;
    text-decoration: none;
    text-align: center;
}

.btn-primary {
    background: #4CAF50;
    color: white;
}

.btn-secondary {
    background: #f8f9fa;
    color: #333;
    border: 1px solid #ddd;
}

.btn-primary:hover,
.btn-secondary:hover {
    opacity: 0.9;
}
</style>

<?php include '../_foot.php'; ?>