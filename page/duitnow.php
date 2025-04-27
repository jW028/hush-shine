<?php
require '../_base.php';

// Check if order_id and total exist in session
if (!isset($_SESSION['cust_id']) || !isset($_SESSION['checkout_total']) || !isset($_SESSION['order_id'])) {
    header("Location: ../page/checkout.php");
    exit();
}

// When the user clicks "I've Completed Payment"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_SESSION['order_id'];
    header("Location: order_confirmation.php?id=" . urlencode($orderId));
    exit();
}

$totalAmount = $_SESSION['checkout_total'];
$custId = $_SESSION['cust_id'];
$orderId = $_SESSION['order_id'];

try {
    // Always fetch from existing order to avoid recalculation
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
} catch (Exception $e) {
    error_log("Stripe Init Error: " . $e->getMessage());
    header("Location: ../page/checkout.php?error=init");
    exit();
}

$_title = 'DuitNow Payment';
include '../_head.php';
?>

<div class="stripe-page"> <!-- Using stripe-page class for consistency -->
    <div class="stripe-container">
        <div class="checkout-header">
            <h1><i class="fas fa-shopping-bag"></i> Checkout</h1>
            <div class="checkout-steps">
                <div class="step"><span>1</span> Shipping</div>
                <div class="step active"><span>2</span> Payment</div>
                <div class="step"><span>3</span> Confirmation</div>
            </div>
        </div>
        <div class="payment-grid">
            <!-- Order Summary -->
            <div class="order-summary">
                <div class="summary-card">
                    <h2><i class="fas fa-receipt"></i> Order Summary</h2>
                    
                    <div class="order-items">
                        <?php foreach ($cartItems as $item): ?>
                            <?php
                                // Decode JSON image data
                                $productImages = json_decode($item['image'], true) ?: [];
                                $firstImage = !empty($productImages) ? $productImages[0] : 'default.jpg';
                            ?>
                            <div class="order-item">
                                <div class="item-image">
                                    <img src="/images/products/<?= htmlspecialchars($firstImage) ?>" 
                                        alt="<?= htmlspecialchars($item['prod_name']) ?>">
                                    <span class="item-quantity"><?= $item['quantity'] ?></span>
                                </div>
                                <div class="item-details">
                                    <h4><?= htmlspecialchars($item['prod_name']) ?></h4>
                                    <p>RM <?= number_format($item['price'], 2) ?></p>
                                </div>
                                <div class="item-total">
                                    RM <?= number_format($item['price'] * $item['quantity'], 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-totals">
                        <div class="total-row">
                            <span>Subtotal</span>
                            <span>RM <?= number_format($subtotal, 2) ?></span>
                        </div>
                        <div class="total-row">
                            <span>Tax (6%)</span>
                            <span>RM <?= number_format($tax, 2) ?></span>
                        </div>
                        
                        <?php if ($appliedRewardPoints > 0): ?>
                            <div class="total-row discount-row">
                                <span>Reward Points Applied</span>
                                <span>-RM <?= number_format($appliedRewardPoints, 2) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="total-row grand-total">
                            <span>Total</span>
                            <span>RM <?= number_format($totalAmount, 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DuitNow Payment Section -->
            <div class="payment-form"> <!-- Using payment-form class for consistency -->
                <h2><i class="fas fa-qrcode"></i> DuitNow QR Payment</h2>
                <div class="qr-box">
                    <img src="/images/payment/duitnow-qr-placeholder.png" alt="DuitNow QR Code" class="qr-image">
                    <p class="qr-total">Amount: <strong>RM <?= number_format($totalAmount, 2) ?></strong></p>
                </div>
                <form method="POST" class="duitnow-form">
                    <button type="submit" class="payment-button">
                        <i class="fas fa-check-circle"></i> I've Completed Payment
                    </button>
                </form>
                <div class="secure-checkout">
                    <i class="fas fa-shield-alt"></i> Scan with your banking app
                </div>
            </div>
        </div>
    </div>
</div>


<?php include '../_foot.php'; ?>
