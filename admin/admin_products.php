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
                try {
                    $stmt = $_db->query("SELECT * FROM category ORDER BY cat_name");
                    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    echo "Error loading categories: " . $e->getMessage();
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
                                        <button data-get="view_product.php?id=<?= $product['prod_id'] ?>" class="btn btn-sm"><i class="fas fa-plus"></i> </button>
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
                <div class="button-group">
                    <button id="openAddProductModal" class="category-btn">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                </div>
        </main>
    </div>

    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h1>Add New Product</h1>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">

                <form action="add_product.php" method="POST" enctype="multipart/form-data" class="product-form">
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="5"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (RM)</label>
                        <input type="number" id="price" name="price" class="form-control" min="0" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" min="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category['cat_id']) ?>"> 
                                <?= htmlspecialchars($category['cat_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="images">Product Image</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="images" name="images[]" accept="image/*" multiple>
                        <label class="custom-file-label" for="images">Choose file</label>
                        <small class="form-text">Accepted formats: JPG, JPEG, PNG, WEBP. Max size: 2MB</small>
                    </div>
                </div>
                
                <div class="image-previews" id="imagePreview"></div>
                
                <div class="form-group">
                    <button type="submit" class="submit-button">Add Product</button>
                    <button id="close-modal" type="button" class="cancel-button">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>