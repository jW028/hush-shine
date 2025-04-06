<?php
require_once '../_base.php';
include '../_head.php';

// TODO: Display admin role on top right of header
// This page should be a dashboard in the future

// Check if user is logged in and has admin privileges
auth('admin');

// Get the action from the URL
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Page header
?>
    <div class="admin-container">
        
        <main>
            <div class="actions">
                <a href="admin_menu.php" class="btn">List All Products</a>
                <a href="add_product.php" class="btn btn-primary">Add New Product</a>
            </div>
            
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
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="message <?= $_SESSION['message_type'] ?>">
                        <?= $_SESSION['message'] ?>
                    </div>
                    <?php 
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                    ?>
                <?php endif; ?> 
                
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Category</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($products) > 0): ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['prod_id']) ?></td>
                                    <td><?= htmlspecialchars($product['prod_name']) ?></td>
                                    <td>$<?= number_format($product['price'], 2) ?></td>
                                    <td><?= htmlspecialchars($product['quantity']) ?></td>
                                    <td><?= htmlspecialchars($product['category']) ?></td>
                                    <td><?=htmlspecialchars($product['image']) ?></td>
                                    <td class="actions">
                                        <a href="view_product.php?id=<?= $product['prod_id'] ?>" class="btn btn-sm">View</a>
                                        <a href="edit_product.php?id=<?= $product['prod_id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                                        <a href="delete_product.php?id=<?= $product['prod_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
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
                <?php
            
            ?>
        </main>
    </div>
</body>
</html>