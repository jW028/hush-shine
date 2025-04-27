<?php
require '../_base.php';
require_once '../vendor/autoload.php';

if (!isset($_SESSION['cust_id']) || !isset($_SESSION['checkout_total']) || !isset($_SESSION['order_id'])) {
    header("Location: ../page/checkout.php");
    exit();
}

// $totalAmount = $_SESSION['checkout_total'];
$custId = $_SESSION['cust_id'];
$orderId = $_SESSION['order_id'];
$isExistingOrder = isset($_SESSION['is_existing_order']) && $_SESSION['is_existing_order'] === true;

if (!isset($_SESSION['applied_reward_points'])) {
    $_SESSION['applied_reward_points'] = 0; 
}

try {
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

try {
    \Stripe\Stripe::setApiKey('sk_test_51R6kNpFNb65u1viGxsiDLhrmT5wfQNQtzlOhGp6Ldu7uMbQ577pvupwdb1D1dzcYdtvD2O28QevBeriOyNBaOoyJ00DgX8TQNp');

    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => round($totalAmount * 100),
        'currency' => 'myr',
        'metadata' => [
            'customer_id' => $custId,
            'order_id' => $orderId
        ]
    ]);

    $clientSecret = $paymentIntent->client_secret;
} catch (Exception $e) {
    error_log("Payment Error: " . $e->getMessage());
    header("Location: ../page/checkout.php?error=payment");
    exit();
}

$_title = 'Complete Payment';
include '../_head.php';
?>

<div class="stripe-page">
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

            <!-- Payment Form -->
            <div class="payment-form">
                <h2><i class="fas fa-credit-card"></i> Card Payment</h2>
                <form id="payment-form">
                    <div id="payment-element"></div>
                    <button id="submit-button" class="payment-button">
                        <i class="fas fa-lock"></i>
                        <span id="button-text">Pay RM <?= number_format($totalAmount, 2) ?></span>
                        <div id="spinner" class="spinner hidden"></div>
                    </button>
                    <div id="payment-message" class="hidden"></div>
                </form>
                <div class="secure-checkout">
                    <i class="fas fa-shield-alt"></i> Secure payment powered by Stripe
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://js.stripe.com/v3/"></script>
<script>
$(document).ready(function() {
    const stripe = Stripe('pk_test_51R6kNpFNb65u1viG9vJUDnoNiYpdXkNhX5r9NdMMu22THPzkyP87EJZRojWzdENqeNX18A6X3FdkdOv7wqFZXlDZ00utrSGvkV');
    const elements = stripe.elements({
        clientSecret: '<?= $clientSecret ?>'
    });

    const paymentElement = elements.create('payment');
    paymentElement.mount('#payment-element');

    $('#payment-form').on('submit', function(e) {
        e.preventDefault();
        
        $('#button-text').hide();
        $('#spinner').show();
        $('#submit-button').prop('disabled', true);

        stripe.confirmPayment({
            elements,
            confirmParams: {
                return_url: '<?= "http://" . $_SERVER["HTTP_HOST"] ?>/page/order_confirmation.php'
            }
        }).then(function(result) {
            if (result.error) {
                $('#payment-message')
                    .text(result.error.message)
                    .removeClass('hidden');
                
                $('#button-text').show();
                $('#spinner').hide();
                $('#submit-button').prop('disabled', false);
            }
        });
    });
});
</script>

<?php include '../_foot.php'; ?>