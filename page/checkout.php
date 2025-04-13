<?php
require '../_base.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=checkout");
    exit();
}

$userId = $_SESSION['user_id'];

// Process checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required = ['name', 'email', 'address', 'payment_method'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields");
            }
        }

        //PaymentMethod
        $paymentMethod = $_POST['payment_method'];
        $validPaymentMethods = ['Credit Card', 'PayPal', 'Bank Transfer'];
        if (!in_array($paymentMethod, $validPaymentMethods)) {
            throw new Exception("Invalid payment method selected");
        }

        // Get cart items
        $stmt = $_db->prepare("
            SELECT ci.prod_id, ci.quantity, p.prod_name, p.price 
            FROM cart_item ci
            JOIN product p ON ci.prod_id = p.prod_id
            JOIN shopping_cart sc ON ci.cart_id = sc.cart_id
            WHERE sc.cust_id = ?
        ");
        $stmt->execute([$userId]);
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($cartItems)) {
            throw new Exception("Your cart is empty");
        }

        // Calculate totals
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $tax = $subtotal * 0.06; // Example 6% tax
        $total = $subtotal + $tax;

        //Stripe
        if ($paymentMethod === 'Credit Card') {
            $_SESSION['checkout_total'] = $total;
            $_SESSION['order_id'] = $orderId;

            header("Location: stripe.php");
            exit();
        }

        // Start transaction
        $_db->beginTransaction();

        // Create order
        $orderStmt = $_db->prepare("
            INSERT INTO orders (cust_id, order_date, total_amount, status, 
                               shipping_address, payment_method)
            VALUES (?, NOW(), ?, 'Processing', ?, ?)
        ");
        $orderStmt->execute([
            $userId,
            $total,
            $_POST['address'],
            $payment_method
        ]);
        $orderId = $_db->lastInsertId();

        // Add order items
        $itemStmt = $_db->prepare("
            INSERT INTO order_items (order_id, prod_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($cartItems as $item) {
            $itemStmt->execute([
                $orderId,
                $item['prod_id'],
                $item['quantity'],
                $item['price']
            ]);
        }

        //Handle different payment methods
        if ($paymentMethod === 'Credit Card') {
            $_SESSION['checkout_total'] = $total;
            $_SESSION['order_id'] = $orderId;
            
            // Commit transaction before redirecting
            $_db->commit();

            header("Location: stripe.php");
            exit();
        }
        // Clear cart
        $cartStmt = $_db->prepare("
            DELETE ci FROM cart_item ci
            JOIN shopping_cart sc ON ci.cart_id = sc.cart_id
            WHERE sc.cust_id = ?
        ");
        $cartStmt->execute([$userId]);

        // Commit transaction
        $_db->commit();

        // Redirect to order confirmation
        header("Location: order_confirmation.php?id=" . $orderId);
        exit();

    } catch (Exception $e) {
        if ($_db->inTransaction()) {
            $_db->rollBack();
        }
        $error = $e->getMessage();
    }
}

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

// Get user details
try {
    $stmt = $_db->prepare("SELECT * FROM customer WHERE cust_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("User Fetch Error: " . $e->getMessage());
    $user = [];
}

$_title = 'Checkout';
include '../_head.php';
?>

<div class="checkout-page">
    <h1>Checkout</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="checkout-container">
        <div class="checkout-summary">
            <h2>Order Summary</h2>
            <div class="order-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="order-item">
                        <img src="/images/prod_img/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['prod_name']) ?>">
                        <div class="item-details">
                            <h4><?= htmlspecialchars($item['prod_name']) ?></h4>
                            <p>Quantity: <?= $item['quantity'] ?></p>
                            <p>RM <?= number_format($item['price'], 2) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="order-totals">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>RM <?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="total-row">
                    <span>Tax (6%):</span>
                    <span>RM <?= number_format($tax, 2) ?></span>
                </div>
                <div class="total-row grand-total">
                    <span>Total:</span>
                    <span>RM <?= number_format($total, 2) ?></span>
                </div>
            </div>
        </div>

        <div class="checkout-form">
            <h2>Shipping & Payment</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" 
                           value="<?= htmlspecialchars($user['cust_name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="address">Shipping Address</label>
                    <textarea id="address" name="address" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Payment Method</label>
                    <div class="payment-options">
                        <label>
                            <input type="radio" name="payment_method" value="Credit Card" required>
                            <span>Credit Card</span>
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="PayPal">
                            <span>PayPal</span>
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="Bank Transfer">
                            <span>Bank Transfer</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-checkout">Complete Order</button>
            </form>
        </div>
    </div>
</div>

<?php include '../_foot.php'; ?>