<?php
$_title = "Payment";
include '../_head.php';
?>

<div class="payment-container">
    <h2>Choose Payment Method</h2>
    <form action="stripe.php" method="POST">
        <label><input type="radio" name="payment_method" value="Credit Card" required> Credit Card</label><br>
        <label><input type="radio" name="payment_method" value="FPX"> FPX</label><br>
        <label><input type="radio" name="payment_method" value="Bank Transfer"> Bank Transfer</label><br>
        <button type="submit">Confirm Payment</button>
    </form>
</div>

<?php
include '../_foot.php';
?>