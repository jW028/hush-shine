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
            // Extra validation for address
            $address = trim($_POST['address']);
            if (strlen($address) < 10) {
                throw new Exception("Shipping address must be at least 10 characters long");
            }
        }

        //PaymentMethod
        $paymentMethod = $_POST['payment_method'];
        $validPaymentMethods = ['Debit/Credit Card', 'PayPal', 'Bank Transfer'];
        if (!in_array($paymentMethod, $validPaymentMethods)) {
            throw new Exception("Invalid payment method selected");
        }

        // Get selected items from query string
        $selectedItems = isset($_GET['items']) ? explode(',', $_GET['items']) : [];

        // Get cart items
        $stmt = $_db->prepare("
            SELECT ci.cart_id, ci.prod_id, ci.quantity, p.prod_name, p.price
            FROM cart_item ci
            JOIN product p ON ci.prod_id = p.prod_id
            JOIN shopping_cart sc ON ci.cart_id = sc.cart_id
            WHERE sc.cust_id = ?
        ");

        // Prepare the base SQL query
        $query = "
            SELECT ci.cart_id, ci.prod_id, ci.quantity, p.prod_name, p.price
            FROM cart_item ci
            JOIN product p ON ci.prod_id = p.prod_id
            JOIN shopping_cart sc ON ci.cart_id = sc.cart_id
            WHERE sc.cust_id = ?
        ";

        // Modify the query if there are selected items
        if (!empty($selectedItems)) {
        $query .= " AND ci.prod_id IN (" . implode(',', array_fill(0, count($selectedItems), '?')) . ")";
        }

        // Prepare the statement using the DB connection
        $stmt = $_db->prepare($query);

        // Merge the user ID with selected items as parameters
        $params = array_merge([$userId], $selectedItems);

        // Execute the query
        $stmt->execute($params);

        // Fetch the cart items
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['price'] * $item['quantity'];
            
        }
        $tax = $subtotal * 0.06; // Example 6% tax
        $total = $subtotal + $tax;

        // Start transaction
        $_db->beginTransaction();

        $orderStmt = $_db->prepare("
            INSERT INTO orders (cust_id, order_date, total_amount, status, payment_id, payment_status, shipping_address)
            VALUES (?, NOW(), ?, 'Pending', NULL, 'Unpaid', ?)
        ");
        $orderStmt->execute([
            $userId, 
            $total, 
            $_POST['address']
        ]);
        $orderId = $_db->lastInsertId();
        $_SESSION['pending_order_id'] = $orderId;
        error_log("Order ID {$_db->lastInsertId()} inserted with payment_status 'Unpaid'");
        
        
        // Optional: Insert order_items from cart
        foreach ($cartItems as $item) {
            $itemStmt = $_db->prepare("
                INSERT INTO order_items (order_id, prod_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            $itemStmt->execute([
                $orderId, 
                $item['prod_id'], 
                $item['quantity'],
                $item['price']
            ]);
        }
        $_SESSION['pending_order_id'] = $_db->lastInsertId();

        //Handle different payment methods
        if ($paymentMethod === 'Debit/Credit Card') {
            $_SESSION['checkout_total'] = $total;
            $_SESSION['order_id'] = $orderId;
            
            // Commit transaction before redirecting
            $_db->commit();

            header("Location: stripe.php");
            exit();
        }

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
    <div class="checkout-container">
        <div class="checkout-header">
            <h1><i class="fas fa-shopping-bag"></i> Checkout</h1>
            <div class="checkout-steps">
                <div class="step active"><span>1</span> Shipping</div>
                <div class="step"><span>2</span> Payment</div>
                <div class="step"><span>3</span> Confirmation</div>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="checkout-post-method">
            <div class="checkout-grid">
                <!-- Shipping Information -->
                <div class="checkout-form">
                    <div class="form-section">
                        <h2><i class="fas fa-truck"></i> Shipping Information</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['cust_name'] ?? '') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="address">Shipping Address</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <textarea id="address" name="address" required minlength="10" 
                                        placeholder="Enter your full shipping address"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="form-section">
                        <h2><i class="fas fa-credit-card"></i> Payment Method</h2>
                        
                        <div class="payment-cards">
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="Debit/Credit Card" required checked>
                                <div class="payment-content">
                                    <div class="payment-icon">
                                        <i class="fab fa-cc-stripe"></i>
                                    </div>
                                    <span>Debit/Credit Card</span>
                                    <div class="payment-brands">
                                        <i class="fab fa-cc-visa"></i>
                                        <i class="fab fa-cc-mastercard"></i>
                                        <i class="fab fa-cc-amex"></i>
                                    </div>
                                </div>
                            </label>

                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="PayPal">
                                <div class="payment-content">
                                    <div class="payment-icon">
                                        <i class="fab fa-paypal"></i>
                                    </div>
                                    <span>PayPal</span>
                                </div>
                            </label>

                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="Bank Transfer">
                                <div class="payment-content">
                                    <div class="payment-icon">
                                        <i class="fas fa-university"></i>
                                    </div>
                                    <span>Bank Transfer</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="summary-card">
                        <h2><i class="fas fa-receipt"></i> Order Summary</h2>
                        
                        <div class="order-items">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="order-item">
                                    <div class="item-image">
                                        <img src="/images/prod_img/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['prod_name']) ?>">
                                        <span class="item-quantity"><?= $item['quantity'] ?></span>
                                    </div>
                                    <div class="item-details">
                                        <h4><?= htmlspecialchars($item['prod_name']) ?></h4>
                                        <p>RM <?= number_format($item['price'], 2) ?></p>
                                    </div>
                                    <div class="item-total">
                                        RM <?= number_format($item['price'] * $item['quantity'], 2) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="order-totals">
                            <div class="total-row">
                                <span>Subtotal</span>
                                <span>RM <?= number_format($subtotal, 2) ?></span>
                            </div>
                            <div class="total-row">
                                <span>Tax (6%)</span>
                                <span>RM <?= number_format($tax, 2) ?></span>
                            </div>
                            <div class="total-row grand-total">
                                <span>Total</span>
                                <span>RM <?= number_format($total, 2) ?></span>
                            </div>
                        </div>

                        <button type="submit" class="btn-checkout">
                            <i class="fas fa-lock"></i> Complete Order
                        </button>
                        
                        <div class="secure-checkout">
                            <i class="fas fa-shield-alt"></i> Secure checkout
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../_foot.php'; ?>