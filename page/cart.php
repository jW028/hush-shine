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
            <th>Remove</th>
        </tr>
        <?php foreach ($_SESSION['cart'] as $index => $item): ?>
        <tr>
            <td><img src="/images/product_img/<?= htmlspecialchars($item['image']) ?>" width="50"></td>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= number_format($item['price'], 2) ?></td>
            <td>
                <a href="remove_from_cart.php?index=<?= $index ?>">Remove</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <br>
    <form action="checkout.php" method="post">
        <button type="submit">Checkout</button>
    </form>

<?php else: ?>
    <p>Your cart is empty.</p>
<?php endif; ?>
