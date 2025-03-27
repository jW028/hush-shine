<?php
require '../vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_51R6kNpFNb65u1viGxsiDLhrmT5wfQNQtzlOhGp6Ldu7uMbQ577pvupwdb1D1dzcYdtvD2O28QevBeriOyNBaOoyJ00DgX8TQNp'); // Use your Secret Key

$session_id = $_GET['session_id'];
$session = \Stripe\Checkout\Session::retrieve($session_id);
$customer_email = $session->customer_details->email;

echo "<h1>Payment Successful!</h1>";
echo "<p>Thank you for your purchase. A receipt has been sent to $customer_email.</p>";
?>
