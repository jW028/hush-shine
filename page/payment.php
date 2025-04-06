<?php
require '../_base.php';
session_start();

$total_price = $_POST['total'] ?? 0;  // Ensure total is passed via POST
if ($total_price <= 0) {
    die("Invalid Request: Total price is required.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';

    if ($payment_method === 'stripe') {
        header("Location: stripe.php?total=" . number_format($total_price, 2, '.', ''));
        exit();
    } elseif ($payment_method === 'qrcode') {
        header("Location: qrcode.php?total=" . urlencode($total_price));
        exit();
    } else {
        $error = "Please select a valid payment method.";
    }
}

$_title = "Payment";
include '../_head.php';
?>

<h2>Choose Payment Method</h2>
<?php if (!empty($error)) echo "<p style='color: red;'>$error</p>"; ?>

<form method="post">
    <input type="hidden" name="total" value="<?= htmlspecialchars($total_price) ?>">

    <label>
        <input type="radio" name="payment_method" value="stripe"> Pay with Stripe
    </label><br>
    <label>
        <input type="radio" name="payment_method" value="qrcode"> Pay with QR Code
    </label><br>

    <button type="submit">Proceed to Payment</button>
</form>

<a href="checkout.php">Back to Checkout</a>

<?php include '../_foot.php'; ?>