<?php
require '../_base.php';
//-----------------------------------------------------------------------------

if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
    // User is logged in as admin
    header("Location: ../index.php");
    exit();
}

// Handle deletion of cart item
// Handle AJAX requests for deleting items
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_item') {
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    error_log("Received POST request: " . print_r($_POST, true)); // Debugging line

    // Handle quantity updates
    if (isset($_POST['action']) && $_POST['action'] === 'update_quantity') {
        

        // Check if user is logged in
        if (!isset($_SESSION['cust_id']) || empty($_SESSION['cust_id'])) {
            header("Location: ../page/login.php");
            exit();
        } 


        // Check required parameters
        if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            exit();
        }

        $productId = $_POST['product_id'];
        $newQuantity = (int)$_POST['quantity'];
        $custId = $_SESSION['cust_id'];

        // Validate quantity
        if ($newQuantity < 1 || $newQuantity > 99) {
            echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
            exit();
        }

        try {
            // Verify product exists in user's cart
            $verifyStmt = $_db->prepare("
                SELECT ci.cart_id 
                FROM cart_item ci
                JOIN shopping_cart sc ON ci.cart_id = sc.cart_id
                WHERE ci.prod_id = ? AND sc.cust_id = ?
            ");
            $verifyStmt->execute([$productId, $custId]);
            
            if ($verifyStmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Product not in cart']);
                exit();
            }
            
            // Update quantity
            $updateStmt = $_db->prepare("
                UPDATE cart_item ci
                JOIN shopping_cart sc ON ci.cart_id = sc.cart_id
                SET ci.quantity = ?
                WHERE ci.prod_id = ? AND sc.cust_id = ?
            ");
            $updateStmt->execute([$newQuantity, $productId, $custId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Quantity updated',
                'newQuantity' => $newQuantity
            ]);
        } catch (Exception $e) {
            error_log("Quantity Update Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Update failed']);
        }
        exit();
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete_item') {
        error_log("Delete action detected"); // Debugging line

        // Check if user is logged in
        if (!isset($_SESSION['cust_id']) || empty($_SESSION['cust_id'])) {
            header("Location: ../page/login.php");
            exit();
        }

        // Check if required parameters are provided
        if (!isset($_POST['cart_id']) || !isset($_POST['prod_id'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit();
        }

        $cartId = $_POST['cart_id'];
        $prodId = $_POST['prod_id'];
        $custId = $_SESSION['cust_id'];

        try {
            // First verify that this cart item belongs to the current user
            $verifyStmt = $_db->prepare("
                SELECT ci.cart_id 
                FROM cart_item ci
                JOIN shopping_cart sc ON ci.cart_id = sc.cart_id
                WHERE ci.cart_id = ? AND ci.prod_id = ? AND sc.cust_id = ?
            ");
            $verifyStmt->execute([$cartId, $prodId, $custId]);
            
            if ($verifyStmt->rowCount() === 0) {
                // No matching cart item found for this user
                echo json_encode(['success' => false, 'message' => 'Cart item not found or does not belong to current user']);
                exit();
            }
            
            // Delete the cart item
            $deleteStmt = $_db->prepare("DELETE FROM cart_item WHERE cart_id = ? AND prod_id = ?");
            $deleteStmt->execute([$cartId, $prodId]);
            
            if ($deleteStmt->rowCount() > 0) {
                // Check if this was the last item in the cart
                $checkCartStmt = $_db->prepare("SELECT COUNT(*) FROM cart_item WHERE cart_id = ?");
                $checkCartStmt->execute([$cartId]);
                $itemCount = $checkCartStmt->fetchColumn();
                
                echo json_encode(['success' => true, 'message' => 'Item removed successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
            }
        } catch (Exception $e) {
            error_log("Delete Cart Item Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred']);
        }
        exit(); // Stop further execution after handling the AJAX request
    }

    if (isset($_POST['action']) && $_POST['action'] === 'get_count') {
        try {
            $stmt = $_db->prepare("
                SELECT SUM(ci.quantity) as count 
                FROM cart_item ci
                JOIN shopping_cart sc ON ci.cart_id = sc.cart_id
                WHERE sc.cust_id = ?
            ");
            $stmt->execute([$_SESSION['cust_id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'count' => $result['count'] ?? 0
            ]);
        } catch (Exception $e) {
            error_log("Count Fetch Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'count' => 0
            ]);
        }
        exit();
    }
}


//Normal cart
if (!isset($_SESSION['cust_id']) || empty($_SESSION['cust_id'])) {
    header("Location: ../page/login.php");
    exit();
}

$custId = $_SESSION['cust_id'];

// Retrieve cart item data from database (based on which users logged in)
try {
    $stmt = $_db->prepare("
        SELECT ci.cart_id, ci.prod_id, ci.quantity, p.prod_name, p.price, p.image 
        FROM cart_item ci
        JOIN product p ON ci.prod_id = p.prod_id
        JOIN shopping_cart sc ON ci.cart_id = sc.cart_id
        WHERE sc.cust_id = ?
    ");
    $stmt->execute([$custId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $subtotal = 0;
    foreach ($cartItems as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
} catch (Exception $e) {
    error_log("Cart Fetch Error: " . $e->getMessage());
    $cartItems = [];
    $subtotal = 0;
}


// Fetch saved cart items from the database

$savedCartItems = $cartItems;
// $savedCartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);



// ----------------------------------------------------------------------------
$_title = '';
include '../_head.php';
?>

<div class="cart-page-container">

    <h1>Shopping Cart</h1>
    <form id="checkout-selected-form" method="POST" action="checkout.php">
        <input type="hidden" name="selected_items" id="selected-items-input">
    </form>

    <section class="cart-section">
        <h2>Your Cart</h2>
        <div class="cart-container">
            <?php if (count($cartItems) > 0): ?>
                <table class="cart-list">
                    <thead>
                        <tr>
                            <th>Select</th>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <?php
                                // Extract the first image
                                if ($item && !empty($item['image'])) {
                                    $productImages = json_decode($item['image']) ?: [];
                                    $firstImage = !empty($productImages) ? $productImages[0] : 'default-product-image.jpg';                            
                                } else {
                                    $firstImage = 'default_image.webp'; // Fallback image if no images are found
                                }
                            ?>
                            <tr>                           
                                <td><input type="checkbox" value="<?= $item['prod_id'] ?>" class="item-checkbox"></td>
                                <td><img src="/images/products/<?= htmlspecialchars($firstImage) ?>" class="cart-product-img"></td>
                                <td><?= htmlspecialchars($item['prod_name']) ?></td>
                                <td> 
                                    <div class="quantity-control"> 
                                        <button class="qty-btn minus" data-id="<?= $item['prod_id'] ?>">-</button> 
                                        <span class="qty-value"><?= $item['quantity'] ?></span> 
                                        <button class="qty-btn plus" data-id="<?= $item['prod_id'] ?>">+</button> 
                                    </div> 
                                </td> 
                                <td>RM <?= number_format($item['price'], 2) ?></td>
                                <td class="item-total">RM <?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                <td>
                                    <button class="remove-btn" data-cart-id="<?= $item['cart_id'] ?>" data-prod-id="<?= $item['prod_id'] ?>">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right">Subtotal (Selected Items):</td>
                            <td id="selected-subtotal">RM <?= number_format($subtotal, 2) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="cart-actions">
                    <a href="../index.php"><button class="continue-shopping">Continue Shopping</button></a>
                    <!-- <button class="checkout-btn">Proceed to Checkout</button> -->
                    <!-- TODO (on click validate) -->
                    <button type="button" class="checkout-selected">Proceed to Checkout</button>
                    <button class="checkout-all">Checkout All Items</button>
                    <!-- <button class="save-cart">Save Cart</button> -->
                </div>
                </div>
            <?php else: ?>
                <p>Your cart is empty.</p>
                <a href="../index.php"><button class="continue-shopping">Continue Shopping</button></a>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php
include '../_foot.php';

