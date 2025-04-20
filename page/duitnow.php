<?php
require '../_base.php';

// Check if order_id and total exist in session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['checkout_total']) || !isset($_SESSION['order_id'])) {
    header("Location: ../checkout.php");
    exit();
}
$userId = $_SESSION['user_id']; 
$orderId = $_SESSION['order_id'];
$total = $_SESSION['checkout_total'];

$_title = 'DuitNow Payment';
include '../_head.php';
?>

<div class="duitnow-page">
    <div class="duitnow-container">
        <h1><i class="fas fa-qrcode"></i> DuitNow QR Payment</h1>

        <div class="duitnow-box">
            <p>Please scan the QR code below with your mobile banking app to simulate payment.</p>
            
            <div class="qr-box">
                <img src="/images/payment/duitnow-qr-placeholder.png" alt="DuitNow QR Code" class="qr-image">
                <p class="qr-total">Amount: <strong>RM <?= number_format($total, 2) ?></strong></p>
            </div>

            <form method="POST" class="duitnow-form">
                <button type="submit" class="btn btn-confirm">
                    <i class="fas fa-check-circle"></i> I've Completed Payment
                </button>
            </form>
        </div>
    </div>
</div>

<?php include '../_foot.php'; ?>

<?php
// When the user clicks "I've Completed Payment"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Location: order_confirmation.php?id=" . urlencode($orderId));
    exit();
}
?>
