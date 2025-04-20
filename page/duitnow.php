<?php
require '../_base.php';

session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['checkout_total']) || !isset($_SESSION['order_id'])) {
    header("Location: ../checkout.php");
    exit();
}

$orderId = $_SESSION['order_id'];
$total = $_SESSION['checkout_total'];
$userId = $_SESSION['user_id']; 

// Get cart items for display
try {
    // Get selected items from query string if present
    $selectedItems = isset($_GET['items']) ? explode(',', $_GET['items']) : [];

    $query = "
        SELECT ci.prod_id, ci.quantity, p.prod_name, p.price, p.image 
        FROM cart_item ci
        JOIN product p ON ci.prod_id = p.prod_id
        JOIN shopping_cart sc ON ci.cart_id = sc.cart_id
        WHERE sc.cust_id = ?
    ";

    if (!empty($selectedItems)) {
        $query .= " AND ci.prod_id IN (" . implode(',', array_fill(0, count($selectedItems), '?')) . ")";
        $params = array_merge([$userId], $selectedItems);
    } else {
        $params = [$userId];
    }

    $stmt = $_db->prepare($query);
    $stmt->execute($params);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate totals
    $subtotal = 0;
    foreach ($cartItems as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $tax = $subtotal * 0.06; // Example 6% tax
    $total = $subtotal + $tax;

} catch (Exception $e) {
    error_log("Checkout Error: " . $e->getMessage());
    $cartItems = [];
    $subtotal = $tax = $total = 0;
}
// Simulate payment after QR is scanned
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Update payment status in the database
        $stmt = $_db->prepare("
            UPDATE orders 
            SET payment_status = 'Paid', status = 'Confirmed' 
            WHERE order_id = ?
        ");
        $stmt->execute([$orderId]);

        // Clear session
        unset($_SESSION['checkout_total'], $_SESSION['order_id'], $_SESSION['pending_order_id']);

        // Redirect to confirmation
        header("Location: order_confirmation.php?id=$orderId");
        exit();

    } catch (Exception $e) {
        $error = "Failed to update payment: " . $e->getMessage();
    }
}

?>

<?php include '../_head.php'; ?>
<div class="duitnow-container">
    <h1><i class="fas fa-qrcode"></i> Scan to Pay with DuitNow QR</h1>

    <div class="qr-section">
        <p>Please scan the QR code below with your banking app:</p>
        <img src="/images/duitnow-qr-placeholder.png" alt="DuitNow QR Code" style="max-width:300px">

        <form method="POST">
            <button type="submit" class="btn-confirm">I have paid</button>
        </form>

        <?php if (isset($error)): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
    </div>
</div>
<?php include '../_foot.php'; ?>
