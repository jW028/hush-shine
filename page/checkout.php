<?php
require '../vendor/autoload.php'; // Stripe SDK

\Stripe\Stripe::setApiKey('sk_test_51R6kNpFNb65u1viGxsiDLhrmT5wfQNQtzlOhGp6Ldu7uMbQ577pvupwdb1D1dzcYdtvD2O28QevBeriOyNBaOoyJ00DgX8TQNp'); // Use your Secret Key
$price_in_dollars = 6969; // Example from DB
$unit_amount = $price_in_dollars * 100; // Convert to cents

// Create a Checkout Session
$session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => [[
        'price_data' => [
            'currency' => 'usd',
            'product_data' => [
                'name' => 'Hush & Shine Jewelry Order',
            ],
            'unit_amount' => $unit_amount,
        ],
        'quantity' => 1,
    ]],
    'mode' => 'payment',
    'success_url' => 'http://localhost:8000/page/success.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => 'http://localhost:8000/page/cancel.php',
]);

// Redirect to Stripe
header("Location: " . $session->url);
exit;
?>
