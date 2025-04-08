<?php
require '../_base.php';
require_once '../vendor/autoload.php';

// Set your Stripe secret key
\Stripe\Stripe::setApiKey('sk_test_51R6kNpFNb65u1viGxsiDLhrmT5wfQNQtzlOhGp6Ldu7uMbQ577pvupwdb1D1dzcYdtvD2O28QevBeriOyNBaOoyJ00DgX8TQNp');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout");
    exit();
}

$userId = $_SESSION['user_id'];
$orderItems = [];
$totalAmount = 0;

// If the request is POST, process payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $orderId = $_SESSION['order_id']; // Get order_id from session

        if (empty($orderId)) {
            throw new Exception("Order ID is missing in the session.");
            exit();
        }
        // Fetch the order items
        $stmt = $_db->prepare("
            SELECT oi.prod_id, oi.quantity, oi.price, p.prod_name
            FROM order_items oi
            JOIN product p ON oi.prod_id = p.prod_id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Check if there are items in the order
        if (empty($orderItems)) {
            throw new Exception("No items found for this order.");
            exit();
        }

        $totalAmount = 0;
        foreach ($orderItems as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }

        $totalAmountInCents = $totalAmount * 100;

        // Create a payment intent with the order total
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $totalAmountInCents,  // Amount in cents
            'currency' => 'myr',  // Set your currency (MYR for Malaysian Ringgit)
            'description' => 'Order #' . $orderId,
            'metadata' => ['order_id' => $orderId],
        ]);

        // Get the client secret to be used in Stripe Elements on the client side
        $clientSecret = $paymentIntent->client_secret;

        // Send the client secret and order details to the front end for further processing
        echo json_encode([
            'clientSecret' => $clientSecret,
            'orderItems' => $orderItems,
            'totalAmount' => $totalAmount,
        ]);

        exit();

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$_title = 'Stripe Payment';
include '../_head.php';
?>

<div class="stripe-container">
    <h1>Complete Payment</h1>
    <div class="order-summary">
        <h2>Order Summary</h2>
        <div class="order-items">
            <?php if (!empty($orderItems)): ?>
                <?php foreach ($orderItems as $item): ?>
                    <div class="order-item">
                        <p>Product: <?= htmlspecialchars($item['prod_name']) ?></p>
                        <p>Quantity: <?= $item['quantity'] ?></p>
                        <p>Price: RM <?= number_format($item['price'], 2) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No items in the order.</p>
            <?php endif; ?>
        </div>
        <div class="order-total">
            <p>Total: RM <?= number_format($totalAmount, 2) ?></p>
        </div>
    </div>

    <div class="payment-form">
        <h2>Payment Information</h2>
        <form id="payment-form">
            <div id="card-element">
                <!-- A Stripe Element will be inserted here. -->
            </div>
            <!-- Used to display form errors. -->
            <div id="card-errors" role="alert"></div>
            <button type="submit" id="submit">Pay Now</button>
        </form>
    </div>

<?php include '../_foot.php'; ?>
