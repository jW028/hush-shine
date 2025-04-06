<!--<?php
session_start();
require '../_base.php'; // Database connection

// Fetch cart items from the database
/*$cust_id = $_SESSION['cust_id'] ?? 0;
$cart_items = [];
$total_price = 0;
$total_quantity = 0;

if ($cust_id) {
    $stmt = $pdo->prepare("SELECT ci.prod_id, ci.quantity, p.name, p.price, p.image 
                           FROM cart_item ci
                           JOIN products p ON ci.prod_id = p.id
                           WHERE ci.cust_id = ?");
    $stmt->execute([$cust_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    $_SESSION['order'] = [
        'cart_items' => $cart_items,
        'total_price' => $total_price,
        'total_quantity' => $total_quantity,
        'address' => $_POST['address'],
        'payment_method' => $_POST['payment_method']
    ];
    
    if ($_POST['payment_method'] === 'stripe') {
        header("Location: stripe.php");
        exit();
    }
}
?>

<h2>Checkout</h2>

<?php if (!empty($cart_items)): ?>
    <table border="1">
        <tr>
            <th>Image</th>
            <th>Product</th>
            <th>Price (RM)</th>
            <th>Quantity</th>
            <th>Total (RM)</th>
        </tr>
        <?php foreach ($cart_items as $item): 
            $item_total = $item['price'] * $item['quantity'];
            $total_price += $item_total;
            $total_quantity += $item['quantity'];
        ?>
        <tr>
            <td><img src="/images/product_img/<?= htmlspecialchars($item['image']) ?>" width="50"></td>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= number_format($item['price'], 2) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td><?= number_format($item_total, 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <p><strong>Total Price:</strong> RM <?= number_format($total_price, 2) ?></p>
    
    <form method="post">
        <label>Shipping Address:</label>
        <input type="text" name="address" required>
        <br>
        <label>Payment Method:</label>
        <select name="payment_method" required>
            <option value="stripe">Stripe</option>
        </select>
        <br>
        <button type="submit" name="checkout">Proceed to Payment</button>
    </form>

<?php else: ?>
    <p>Your cart is empty.</p>
<?php endif; ?>*/?>-->

<?php
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "Your cart is empty.";
    exit();
}

$totalPrice = 0;
?>

<h2>Checkout</h2>

<?php if (!empty($_SESSION['cart'])): ?>
    <table border="1">
        <tr>
            <th>Image</th>
            <th>Product</th>
            <th>Price (RM)</th>
            <th>Quantity</th>
            <th>Total (RM)</th>
        </tr>
        <?php 
        foreach ($_SESSION['cart'] as $item): 
            $quantity = isset($item['quantity']) ? $item['quantity'] : 1;
            $itemTotal = $item['price'] * $quantity;
            $totalPrice += $itemTotal;
        ?>
        <tr>
            <td><img src="/images/product_img/<?= htmlspecialchars($item['image']) ?>" width="50"></td>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= number_format($item['price'], 2) ?></td>
            <td><?= $quantity ?></td>
            <td><?= number_format($itemTotal, 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <p><strong>Total Price:</strong> RM <?= number_format($totalPrice, 2) ?></p>

    <br>
    <form action="payment.php" method="post">
        <input type="hidden" name="total" value="<?= $totalPrice ?>">
        <button type="submit">Proceed to Payment</button>
    </form>

<?php else: ?>
    <p>Your cart is empty.</p>
<?php endif; ?>

