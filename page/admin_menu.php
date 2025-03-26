<?php
require_once '../_base.php';

// TODO: Display admin role on top right of header


// Check if user is logged in and has admin privileges
if (!isset($_SESSION["admin"]) || $_SESSION["admin"] !== true) {
    header('Location: login.php');
    exit;
}

// Get the action from the URL
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Page header
?>
    <div class="admin-container">
        <header>
            <h1>Product Administration</h1>
            <nav>
                <ul>
                    <li><a href="admin_menu.php">Products</a></li>
                    <li><a href="admin_categories.php">Categories</a></li>
                    <li><a href="admin_orders.php">Orders</a></li>
                    <li><a href="admin_users.php">Users</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <div class="actions">
                <a href="admin_menu.php" class="btn">List All Products</a>
                <a href="admin_menu.php?action=add" class="btn btn-primary">Add New Product</a>
            </div>
            
            <?php
            // Handle different actions
            switch ($action) {
                case 'add':
                    include 'add_product.php';
                    break;  
                    
                case 'edit':
                    include 'edit_product.php';
                    break;
                    
                case 'delete':
                    include 'delete_product.php';
                    break;
                    
                case 'view':
                    include 'view_product.php';
                    break;
                    
                default:
                    // List all products
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
                                            <a href="admin_menu.php?action=view&id=<?= $product['prod_id'] ?>" class="btn btn-sm">View</a>
                                            <a href="admin_menu.php?action=edit&id=<?= $product['prod_id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                                            <a href="admin_menu.php?action=delete&id=<?= $product['prod_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
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
                    break;
            }
            ?>
        </main>
    </div>
    
    <script src="../js/admin.js"></script>
</body>
</html>