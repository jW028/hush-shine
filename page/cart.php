<!--<?php
session_start();
require '../_base.php'; // Database connection

// Fetch cart items from the database
$cust_id = $_SESSION['cust_id'] ?? 0; // Assuming cust_id is stored in session
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
?>

<h2>Shopping Cart</h2>

<?php if (!empty($cart_items)): ?>
    <table border="1">
        <tr>
            <th>Image</th>
            <th>Product</th>
            <th>Price (RM)</th>
            <th>Quantity</th>
            <th>Total (RM)</th>
            <th>Remove</th>
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
            <td>
                <a href="remove_from_cart.php?prod_id=<?= $item['prod_id'] ?>">Remove</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <p><strong>Total Price:</strong> RM <?= number_format($total_price, 2) ?></p>

    <br>
    <form action="checkout.php" method="post">
        <button type="submit">Proceed to Checkout</button>
    </form>

<?php else: ?>
    <p>Your cart is empty.</p>
<?php endif; ?>-->

<?php
session_start();
?>

<h2>Shopping Cart</h2>

<?php if (!empty($_SESSION['cart'])): ?>
    <table border="1">
        <tr>
            <th>Image</th>
            <th>Product</th>
            <th>Price (RM)</th>
            <th>Quantity</th>
            <th>Total (RM)</th>
            <th>Remove</th>
        </tr>
        <?php 
        $totalPrice = 0;
        foreach ($_SESSION['cart'] as $index => $item): 
            $quantity = isset($item['quantity']) ? $item['quantity'] : 1; // Default quantity = 1
            $itemTotal = $item['price'] * $quantity;
            $totalPrice += $itemTotal;
        ?>
        <tr>
            <td><img src="/images/product_img/<?= htmlspecialchars($item['image']) ?>" width="50"></td>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= number_format($item['price'], 2) ?></td>
            <td><?= $quantity ?></td>
            <td><?= number_format($itemTotal, 2) ?></td>
            <td>
                <a href="remove_from_cart.php?index=<?= $index ?>">Remove</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <p><strong>Total Price:</strong> RM <?= number_format($totalPrice, 2) ?></p>

    <br>
    <form action="checkout.php" method="post">
        <button type="submit">Checkout</button>
    </form>

<?php else: ?>
    <p>Your cart is empty.</p>
<?php endif; ?>