<?php
require '../_base.php';
require_once '../vendor/autoload.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=checkout");
    exit();
}

try {
    $selectedItems = $_SESSION['selected_items'];
    if (empty($selectedItems)) {
        throw new Exception("No items selected for payment.");
    }
    $placeholders = implode(',', array_fill(0, count($selectedItems), '?'));
    $query = "
        SELECT ci.prod_id, ci.quantity, p.prod_name, p.price, p.image
        FROM cart_item ci
        JOIN product p ON ci.prod_id = p.prod_id
        JOIN shopping_cart sc ON ci.cart_id = sc.cart_id
        WHERE sc.cust_id = ? AND ci.prod_id IN ($placeholders)
    ";

    $params = array_merge([$_SESSION['user_id']], $selectedItems);

    $stmt = $_db->prepare($query);
    $stmt->execute($params);

    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total
    $totalAmount = 0;
    foreach ($cartItems as $item) {
        $totalAmount += $item['price'] * $item['quantity'];
    }
    
    // Add tax
    $tax = $totalAmount * 0.06;
    $totalAmount += $tax;

    // Create Stripe payment intent
    \Stripe\Stripe::setApiKey('sk_test_51R6kNpFNb65u1viGxsiDLhrmT5wfQNQtzlOhGp6Ldu7uMbQ577pvupwdb1D1dzcYdtvD2O28QevBeriOyNBaOoyJ00DgX8TQNp');
    
    if ($orderId) {
        $stmt = $_db->prepare("UPDATE orders SET payment_status = 'Paid' WHERE order_id = ?");
        $stmt->execute([$orderId]);

        // Optional: clear cart
        $clearStmt = $_db->prepare("
            DELETE FROM cart_item 
            WHERE cart_id = (SELECT cart_id FROM shopping_cart WHERE cust_id = ?)
        ");
        $clearStmt->execute([$_SESSION['user_id']]);
    }
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => round($totalAmount * 100), // in cents
        'currency' => 'myr',
        'metadata' => [
            'customer_id' => $_SESSION['user_id'],
            'order_id' => $orderId
        ]
    ]);

    $clientSecret = $paymentIntent->client_secret;

} catch (Exception $e) {
    error_log("Payment Error: " . $e->getMessage());
    header("Location: checkout.php?error=payment");
    exit();
}

$_title = 'Complete Payment';
include '../_head.php';
?>

<div class="stripe-container">
    
    <div class="order-summary">
        <h2>Order Summary</h2>
        <div class="items-list">
            <?php foreach ($cartItems as $item): ?>
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
        
        <div class="total-summary">
            <div class="row">
                <span>Subtotal:</span>
                <span>RM <?= number_format($totalAmount - $tax, 2) ?></span>
            </div>
            <div class="row">
                <span>Tax (6%):</span>
                <span>RM <?= number_format($tax, 2) ?></span>
            </div>
            <div class="row total">
                <span>Total:</span>
                <span>RM <?= number_format($totalAmount, 2) ?></span>
            </div>
        </div>
    </div>

    <div class="payment-form">
        <form id="payment-form">
            <div id="payment-element"></div>
            <button id="submit-button" class="payment-button">
                <span id="button-text">Pay RM <?= number_format($totalAmount, 2) ?></span>
                <span id="spinner" class="spinner hidden"></span>
            </button>
            <div id="payment-message" class="hidden"></div>
        </form>
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