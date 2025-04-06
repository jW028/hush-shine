<?php
require '../_base.php';
require '../vendor/autoload.php'; // Include Stripe PHP SDK

// Set your Stripe Secret Key
\Stripe\Stripe::setApiKey('sk_test_51R6kNpFNb65u1viGxsiDLhrmT5wfQNQtzlOhGp6Ldu7uMbQ577pvupwdb1D1dzcYdtvD2O28QevBeriOyNBaOoyJ00DgX8TQNp');

// Start session
session_start();

// Retrieve the total price 
$total_price = $_POST['total'] ?? 0;

// Validate the total price
if ($total_price <= 0) {
    die("Invalid Request: Total price is required.");
}

// Create a new Stripe Checkout session
try {
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'myr',
                'product_data' => [
                    'name' => 'Hush & Shine Jewelry Order', // You can dynamically fetch the product name here
                ],
                'unit_amount' => intval($total_price * 100), // Amount in cents
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => 'http://localhost:8000/page/success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://localhost:8000/page/cancel.php',
    ]);

    // Redirect to Stripe Checkout page
    header("Location: " . $session->url);
    exit;
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
