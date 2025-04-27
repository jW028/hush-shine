<?php
require '../_base.php';

// Check if user is logged in
if (!isset($_SESSION['cust_id'])) {
    header("Location: ../login.php?redirect=checkout");
    exit();
}

$custId = $_SESSION['cust_id'];
$error = null;
$success = null;
$formData = [];

// Get user details
try {
    $stmt = $_db->prepare("SELECT * FROM customer WHERE cust_id = ?");
    $stmt->execute([$custId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("User Fetch Error: " . $e->getMessage());
    $user = [];
}

// Get reward points
try {
    $rewardStmt = $_db->prepare("
        SELECT
            (
                -- Total earned reward points from the reward_points table
                (SELECT COALESCE(SUM(rp.points), 0)
                FROM reward_points rp
                INNER JOIN orders o ON rp.order_id = o.order_id
                WHERE rp.cust_id = ? AND o.status NOT IN ('Pending', 'Cancelled'))
                +
                -- Total earned reward points from the reward_get column in the orders table
                (SELECT COALESCE(SUM(o.reward_get), 0)
                FROM orders o
                WHERE o.cust_id = ? AND o.status NOT IN ('Pending', 'Cancelled'))
            ) AS total_earned,
            -- Total used reward points from the reward_used column in the orders table
            (SELECT COALESCE(SUM(o.reward_used), 0)
            FROM orders o
            WHERE o.cust_id = ? AND o.status != 'Cancelled') AS total_used
    ");
    $rewardStmt->execute([$custId, $custId,$custId]);
    $rewardResult = $rewardStmt->fetch(PDO::FETCH_ASSOC);
    // Calculate available points (earned minus used)
    $rewardPoints = floatval($rewardResult['total_earned']) - floatval($rewardResult['total_used']);
} catch (Exception $e) {
    error_log("Reward Points Error: " . $e->getMessage());
    $rewardPoints = 0;
}

// Get cart items for display
try {
    // Get selected items from query string if present
    $selectedItems = isset($_GET['items']) ? explode(',', $_GET['items']) : [];
    if (empty($selectedItems) && isset($_SESSION['selected_cart_items'])) {
        $selectedItems = explode(',', $_SESSION['selected_cart_items']);
    }

    $query = "
        SELECT ci.prod_id, ci.quantity, p.prod_name, p.price, p.image 
        FROM cart_item ci
        JOIN product p ON ci.prod_id = p.prod_id
        JOIN shopping_cart sc ON ci.cart_id = sc.cart_id
        WHERE sc.cust_id = ?
    ";

    if (!empty($selectedItems)) {
        $query .= " AND ci.prod_id IN (" . implode(',', array_fill(0, count($selectedItems), '?')) . ")";
        $params = array_merge([$custId], $selectedItems);
    } else {
        $params = [$custId];
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

// Initialize applied points
$appliedPoints = isset($_SESSION['applied_reward_points']) ? $_SESSION['applied_reward_points'] : 0;
$afterPointsTotal = $total;

// Handle reward points application
if (isset($_POST['apply_reward_points']) && !empty($_POST['points'])) {

    $pointsToUse = floatval($_POST['points']);
    
    // Validate reward points
    if ($pointsToUse <= 0) {
        $error = "Reward points must be greater than 0.";
    } elseif ($pointsToUse > $rewardPoints) {
        $error = "You cannot use more reward points than you have.";
    } elseif ($pointsToUse > $total) {
        $error = "Reward points cannot exceed the total amount.";
    } else {
        // Deduct reward points from the total
        $afterPointsTotal = $total - $pointsToUse;

        // Ensure the total is not less than the minimum payable amount
        if ($afterPointsTotal < 0.01) {
            $afterPointsTotal = 0.01; // Set the minimum payable amount
        }

        // Store applied reward points in the session
        $_SESSION['applied_reward_points'] = $pointsToUse;
        $_SESSION['checkout_total'] = $afterPointsTotal; // Update the session total

        // Preserve selected items
        if (isset($_GET['items'])) {
            $_SESSION['selected_cart_items'] = $_GET['items'];
        }
        $_SESSION['successMessage'] = "Reward points applied successfully."; 

        // Redirect back with items parameter if it exists
        $redirectUrl = 'checkout.php';
        if (isset($_GET['items'])) {
            $redirectUrl .= '?items=' . urlencode($_GET['items']);
        }
        
        header("Location: " . $redirectUrl);
        exit();
    }
}

$formData = $_SESSION['checkout_form_data'] ?? [];

if (isset($_POST['remove_reward_points'])) {
    unset($_SESSION['applied_reward_points']);
    $appliedPoints = 0;
    $_SESSION['checkout_total'] = $total;
    $_SESSION['successMessage'] = "Reward points removed.";

    // Redirect back with items parameter if it exists
    $redirectUrl = 'checkout.php';
    if (isset($_GET['items'])) {
        $redirectUrl .= '?items=' . urlencode($_GET['items']);
    }
    
    header("Location: " . $redirectUrl);
    exit();
}

// Process checkout form submission when the complete order button is clicked
if (isset($_POST['complete_order'])) {
    try {
        // Clear applied reward points for the new order
        unset($_SESSION['applied_reward_points']);
        unset($_SESSION['selected_cart_items']);

        // Validate required fields
        $required = ['name', 'email', 'address', 'payment_method'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields");
            }
            // Extra validation for address
            if ($field === 'address') {
                $address = trim($_POST['address']);
                if (strlen($address) < 10) {
                    throw new Exception("Shipping address must be at least 10 characters long");
                }
            }
        }

        // Payment Method
        $paymentMethod = $_POST['payment_method'];
        $validPaymentMethods = ['Debit/Credit Card', 'DuitNow QR'];
        if (!in_array($paymentMethod, $validPaymentMethods)) {
            throw new Exception("Invalid payment method selected");
        }

        // Calculate final total with applied points
        if ($appliedPoints > 0) {
            $afterPointsTotal = $total - $appliedPoints;
            if ($afterPointsTotal < 0.01) {
                $afterPointsTotal = 0.01; // Minimum payable amount
            }
        } else {
            $afterPointsTotal = $total;
        }

        foreach ($cartItems as $item) {
            $stockCheck = $_db->prepare("
                SELECT prod_id, prod_name, quantity
                FROM product
                WHERE prod_id = ?");
            $stockCheck->execute([$item['prod_id']]);
            $productStock = $stockCheck->fetch(PDO::FETCH_ASSOC);

            if ($productStock['quantity'] < $item['quantity']) {
                throw new Exception("Insufficient stock for " . htmlspecialchars($item['prod_name']));
            }
        }

        // Store checkout total in session
        $_SESSION['checkout_total'] = $afterPointsTotal;

        // Start transaction
        $_db->beginTransaction();

        // Calculate reward points 
        $rewardPointsget = $afterPointsTotal * 0.01;

        // Insert the order into the database
        $orderStmt = $_db->prepare("
            INSERT INTO orders (cust_id, order_date, total_amount, reward_used, reward_get, status, payment_id, payment_status, shipping_address, payment_method)
            VALUES (?, NOW(), ?, ?, ?, 'Pending', NULL, 'Unpaid', ?, ?)
        ");
        $orderStmt->execute([
            $custId,
            $afterPointsTotal, // Total after applying reward points
            $appliedPoints,    // Reward points used
            $rewardPointsget,     // Reward points earned
            $_POST['address'],
            $paymentMethod
        ]);
        $orderId = $_db->lastInsertId();
        $_SESSION['order_id'] = $orderId;

        // Insert order items
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

            // Update product stock 
            $updateStockStmt = $_db->prepare("
                UPDATE product
                SET quantity = quantity - ?
                WHERE prod_id = ? AND quantity >= ? 
            ");
            $updateStockStmt->execute([
                $item['quantity'],
                $item['prod_id'],
                $item['quantity']
            ]);

            $updateCheck = $_db->prepare("
                SELECT prod_name, quantity
                FROM product
                WHERE prod_id = ?
            ");
            $updateCheck->execute([$item['prod_id']]);
            $productInfo = $updateCheck->fetch(PDO::FETCH_ASSOC);

            error_log("Updated stock for product ID: " . $item['prod_id'] . " to " . $productInfo['quantity'] . "\n");
        }

        // Commit transaction before redirecting
        $_db->commit();

        if (!empty($selectedItems)) {
            // If specific items were selected
            $placeholders = implode(',', array_fill(0, count($selectedItems), '?'));
            $clearCartStmt = $_db->prepare("
                DELETE ci FROM cart_item ci
                JOIN shopping_cart sc ON ci.cart_id = sc.cart_id
                WHERE sc.cust_id = ? AND ci.prod_id IN ($placeholders)
            ");
            $clearCartParams = array_merge([$custId], $selectedItems);
            $clearCartStmt->execute($clearCartParams);
        } else {
            // If all items were purchased
            $clearCartStmt = $_db->prepare("
                DELETE ci FROM cart_item ci
                JOIN shopping_cart sc ON ci.cart_id = sc.cart_id
                WHERE sc.cust_id = ?
            ");
            $clearCartStmt->execute([$custId]);
        }

        // Handle different payment methods
        if ($paymentMethod === 'Debit/Credit Card') {
            header("Location: stripe.php");
            exit();
        } elseif ($paymentMethod === 'DuitNow QR') {
            header("Location: duitnow.php");
            exit();
        }

    } catch (Exception $e) {
        if ($_db->inTransaction()) {
            $_db->rollBack();
        }
        $error = $e->getMessage();
    }
}
// Calculate total after points deduction for display
if ($appliedPoints > 0) {
    $afterPointsTotal = $total - $appliedPoints;
    if ($afterPointsTotal < 0.01) {
        $afterPointsTotal = 0.01; // Minimum payable amount
    }
} else {
    $afterPointsTotal = $total;
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

        <?php if (isset($_SESSION['successMessage'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['successMessage']) ?>
            </div>
            <?php unset($_SESSION['successMessage']); ?>
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
                                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($formData['name'] ?? ($user['cust_name'] ?? '')) ?>" required>

                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" id="email" name="email" 
                                    value="<?= htmlspecialchars($formData['email'] ?? ($user['email'] ?? '')) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="address">Shipping Address</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <textarea id="address" name="address" required minlength="10" 
                                    placeholder="Enter your full shipping address"><?= htmlspecialchars($formData['address'] ?? ($user['address'] ?? '')) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="form-section">
                        <h2><i class="fas fa-credit-card"></i> Payment Method</h2>
                        
                        <div class="payment-cards">
                            <label class="payment-option">
                            <input type="radio" name="payment_method" value="Debit/Credit Card" required 
                            <?= (!isset($formData['payment_method']) || $formData['payment_method'] == 'Debit/Credit Card') ? 'checked' : '' ?>>
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
                            <input type="radio" name="payment_method" value="DuitNow QR" 
                            <?= (isset($formData['payment_method']) && $formData['payment_method'] == 'DuitNow QR') ? 'checked' : '' ?>> <div class="payment-content">
                                    <div class="payment-icon">
                                        <i class="fas fa-qrcode"></i>
                                    </div>
                                    <span>DuitNow QR</span>
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
                                <?php
                                    // Decode JSON image data
                                    $productImages = json_decode($item['image'], true) ?: [];
                                    $firstImage = !empty($productImages) ? $productImages[0] : 'no-image.png';
                                ?>
                                <div class="order-item">
                                    <div class="item-image">
                                        <img src="../images/products/<?= htmlspecialchars($firstImage) ?>" 
                                            alt="<?= htmlspecialchars($item['prod_name']) ?>">
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
                        </div>
                        <div class="reward-points-section">
                            <h4>Reward Points</h4>
                            <p>You have <strong><?= number_format($rewardPoints, 0) ?></strong> reward points available</p>
                                
                            <?php if ($rewardPoints > 0): ?>
                                <div class="points-form">
                                    <input type="number" name="points" id="points" min="0" max="<?= min($rewardPoints, $total) ?>" 
                                        value="<?= $appliedPoints ?>" placeholder="Points to use">
                                    
                                    <?php if ($appliedPoints > 0): ?>
                                        <span class="points-applied">-RM <?= number_format($appliedPoints, 2) ?></span>
                                        <button type="submit" name="remove_reward_points" class="btn-remove-points">Remove</button>
                                    <?php else: ?>
                                        <button type="submit" name="apply_reward_points" class="btn-apply-points">Apply</button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="total-row grand-total">
                            <span>Total</span>
                            <span>RM <?= number_format($afterPointsTotal, 2) ?></span>
                        </div>

                        
                        <button type="submit" name="complete_order" class="btn-checkout">
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
<audio id="buttonSound" preload="auto">
    <source src="../sound/buy_1.mp3" type="audio/mpeg">
</audio>
<?php include '../_foot.php'; ?>