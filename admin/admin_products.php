<?php
require_once '../_base.php';
include '../_head.php';

auth('admin');

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

?>

    <div class="admin-container">

        <main class="admin-main">
            <?php
                try {
                    $stmt = $_db->query("SELECT p.prod_id, p.prod_name, p.prod_desc, p.price, p.quantity, p.cat_id as category, p.image
                                        FROM product p 
                                        LEFT JOIN category c ON p.cat_id = c.cat_id 
                                        ORDER BY p.prod_id DESC");
                    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    echo "Database error: " . $e->getMessage();
                    exit;
                }
                ?>
                <h2>Product List</h2>

                <?php if (isset($_SESSION['message'])) : ?>
                    <div class="message <?= $_SESSION['message_type'] ?>">
                        <?= $_SESSION['message'] ?>
                    </div>
                    <?php
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                    ?>
                <?php endif; ?>

                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($products) > 0): ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['prod_id']) ?></td>
                                    <td><?= htmlspecialchars($product['prod_name']) ?></td>
                                    <td><?= number_format($product['price'], 2) ?></td>
                                    <td><?= htmlspecialchars($product['quantity']) ?></td>
                                    <td><?= htmlspecialchars($product['category']) ?></td>
                                    <td class="actions">
                                        <button data-get="view_product.php?id=<?= $product['prod_id'] ?>" class="btn btn-sm">View</button>
                                        <button data-get="edit_product.php?id=<?= $product['prod_id'] ?>" class="btn btn-sm btn-secondary">Edit</button>
                                        <button data-get="delete_product.php?id=<?= $product['prod_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No products found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
        </main>
    </div>
