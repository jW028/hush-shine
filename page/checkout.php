<?php
session_start();

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "Your cart is empty.";
    exit();
}

// Calculate total price
$totalPrice = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalPrice += floatval($item['price']);
}

// Redirect to Stripe payment page (Example)
header("Location: stripe.php?amount=$totalPrice");
exit();
?>
