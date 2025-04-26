<?php
require_once '../_base.php';
include '../_head.php';

auth('admin');

$_adminContext = true;

$title = 'Product Details';

$prod_id = $_GET['id'] ?? null;

if (!$prod_id) {
    header('Location: /admin/admin_dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $name = trim($_POST['prod_name'] ?? '');
    $description = trim($_POST['prod_desc'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $quantity_action = $_POST['quantity_action'] ?? 'set';
    $edit_desc = trim($_POST['edit_desc'] ?? '');
    $category_id = $_POST['cat_id'] ?? '';
    $image_paths = [];
    $errors = [];

    if (empty($name)) {
        $errors[] = "Product name is required";
    }

    if ($price === false || $price < 0) {
        $errors[] = "Price must be a valid positive number";
    }

     // Handle quantity based on on type
     if ($quantity_action === 'set') {
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        if ($quantity === false || $quantity < 0) {
            $errors[] = "Quantity must be a valid positive number";
        }
    } else { // 'add'
        $add_stock = filter_input(INPUT_POST, 'add_stock', FILTER_VALIDATE_INT);
        if ($add_stock === false || $add_stock < 1) {
            $errors[] = "Added stock must be at least 1";
        }
        
        // Get current quantity from database to ensure we have the latest value
        $stmt = $_db->prepare("SELECT quantity FROM product WHERE prod_id = ?");
        $stmt->execute([$prod_id]);
        $current_quantity = (int)$stmt->fetchColumn();
        
        // Calculate new quantity
        $quantity = $current_quantity + $add_stock;
    }

    if (empty($category_id)) {
        $errors[] = "Category is required";
    }

    $image_paths = [];
    if (!empty($_FILES['images']['name'][0])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $upload_dir = '../images/products/';
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        for ($i=0; $i<count($_FILES['images']['name']); $i++) {
            if (isset($_FILES['images']['error'][$i]) && $_FILES['images']['error'][$i] == 0) {
                $filename = $_FILES['images']['name'][$i];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if (in_array($ext, $allowed)) {
                    $seq_num = $i + 1;
                    $new_filename = uniqid() . "_$seq_num." . $ext;
                    $upload_path = $upload_dir . $new_filename;

                    if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $upload_path)) {
                        $image_paths[] = $new_filename;
                    } else {
                        $errors[] = "Failed to upload image #" . ($i+1);
                    }
                } else {
                    $errors[] = "Invalid format for image #" . ($i+1);
                }
            }
        }
    }

    if (isset($_POST['existing_images'])) {
        foreach ($_POST['existing_images'] as $existing_image) {
            $image_paths[] = $existing_image;
        }
    }

    $image_path = !empty($image_paths) ? json_encode($image_paths) : null;

    if (empty($errors)) {
        try {
            $_db->beginTransaction();
            if (!empty($image_paths)) {
                $stmt = $_db->prepare("UPDATE product SET 
                    prod_name = ?,
                    prod_desc = ?,
                    price = ?,
                    quantity = ?, 
                    cat_id = ?,
                    image = ?
                    WHERE prod_id = ?");
                $stmt->execute([$name, $description, $price, $quantity, $category_id, $image_path, $prod_id]);
            } else {
                $stmt = $_db->prepare("UPDATE product SET
                    prod_name = ?,
                    prod_desc = ?,
                    price = ?,
                    quantity = ?, 
                    cat_id = ?
                    WHERE prod_id = ?");
                $stmt->execute([$name, $description, $price, $quantity, $category_id, $prod_id]);
            }

            // Log product update in update_prod table
            $admin_id = $_SESSION['admin_id'];
            $log_stmt = $_db->prepare("INSERT INTO update_prod (prod_id, admin_id, edit_date, edit_desc) 
                                        VALUES (?, ?, NOW(), ?)");
            $log_stmt->execute([$prod_id, $admin_id, $edit_desc]);

            $_db->commit();

            $action_msg = $quantity_action === 'set' ? "set to $quantity" : 
                            "increased by " . $_POST['add_stock'] . " (new total: $quantity)";
            $success = "Product updated successfully! Stock was $action_msg.";
        } catch (PDOException $e) {
            if ($_db->inTransaction()) {
                $_db->rollBack();
            }
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

try {
    $stmt = $_db->prepare("SELECT * FROM product WHERE prod_id = ?");
    $stmt->execute([$prod_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header('Location: /admin/admin_dashboard.php');
        exit;
    }

    $stmt = $_db->query("SELECT * FROM category ORDER BY cat_name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $images = [];
    if (!empty($product['image'])) {
        $images = json_decode($product['image'], true) ?: [$product['image']];
    }
} catch (PDOException $e) {
    $errors[] = "Database error: " . $e->getMessage();
    exit;
}
?>
    
    <div class="admin-main">
        <div class="admin-content">
            <div class="admin-title">
                <h2>Product Details: <?= htmlspecialchars($product['prod_name']) ?></h2>
                <a href="admin_products.php" class="category-btn back">
                    <i class="fas fa-arrow-left"></i>
                    Back to Products
                </a>
        </div>


            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="view_product.php?id=<?= htmlspecialchars($prod_id)?>" method="POST" enctype="multipart/form-data" class="product-form">
            <div class="form-group">
                <label for="prod_id">Product ID</label>
                <input type="text" id="prod_id" class="form-control" value="<?= htmlspecialchars($product['prod_id']) ?>" readonly>
            </div>

            <div class="form-group">
                <label for="prod_name">Product Name</label>
                <input type="text" id="prod_name" name="prod_name" class="form-control" value="<?= htmlspecialchars($product['prod_name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="prod_desc">Description</label>
                <textarea id="prod_desc" name="prod_desc" class="form-control" rows="4"><?= htmlspecialchars($product['prod_desc']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" id="price" name="price" class="form-control" value="<?= htmlspecialchars($product['price']) ?>" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="quantity_type">Stock Management</label>
                <div class="stock-management-container">
                    <div class="stock-option">
                        <input type="radio" id="set_quantity" name="quantity_action" value="set" checked>
                        <label for="set_quantity">Set exact quantity</label>
                        
                        <div class="quantity-input-wrapper">
                            <input type="number" id="quantity" name="quantity" class="form-control" min="0" 
                                value="<?= htmlspecialchars($product['quantity'] ?? 0) ?>" required>
                            <span class="input-note">Current stock: <?= htmlspecialchars($product['quantity'] ?? 0) ?></span>
                        </div>
                    </div>
                    
                    <div class="stock-option">
                        <input type="radio" id="add_quantity" name="quantity_action" value="add">
                        <label for="add_quantity">Restock</label>
                        
                        <div class="quantity-input-wrapper">
                            <input type="number" id="add_stock" name="add_stock" class="form-control" min="1" value="1">
                            <span class="input-note">Will be added to current stock (<?= htmlspecialchars($product['quantity'] ?? 0) ?>)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="edit_desc">Update Description</label>
                <textarea id="edit_desc" name="edit_desc" class="form-control" rows="2" 
                        placeholder="Brief description of the changes made (required)"
                        required></textarea>
                <small class="form-text text-muted">Enter a brief explanation of why you're updating this product.</small>
            </div>

            <div class="form-group">
                <label for="cat_id">Category</label>
                <select id="cat_id" name="cat_id" class="form-control" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['cat_id']) ?>" <?= $product['cat_id'] == $category['cat_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['cat_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Current Images</label>
                <div class="current-images">
                    <?php if (!empty($images)): ?>
                        <?php foreach($images as $image): ?>
                            <div class="current-image-item">
                                <img src="../images/products/<?= htmlspecialchars($image) ?>" alt="Product Image" class="img-thumbnail">
                                <input type="checkbox" name="existing_images[]" value="<?= htmlspecialchars($image) ?>" checked>    
                                <label>Keep this image</label>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No images available for this product.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="images">Upload New Images</label>
                <input type="file" id="images" name="images[]" class="form-control-file" accept="image/*" multiple>
                <small class="form-text text-muted">Select multiple images by holding Ctrl (or Cmd on Mac) while clicking.</small>
            </div>

            <div class="image-previews" id="imagePreview"></div>
            <hr>
            
            <div class="form-group">
                <button type="submit" name="update_product" class="admin-submit-btn primary">Update Product</button>
            </div>
            </form>
        </div>

        <!-- Update History -->
        <div class="update-history">
            <h3>Update History</h3>
            
            <?php
            try {
                // Get the last 5 updates for this product
                $historyStmt = $_db->prepare("
                    SELECT up.*, a.admin_name
                    FROM update_prod up
                    JOIN admin a ON up.admin_id = a.admin_id
                    WHERE up.prod_id = ?
                    ORDER BY up.edit_date DESC
                    LIMIT 5
                ");
                $historyStmt->execute([$prod_id]);
                $updateHistory = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($updateHistory) > 0):
            ?>
                <?php foreach ($updateHistory as $update): ?>
                    <div class="update-record">
                        <div class="update-date">
                            <?= date('F j, Y g:i A', strtotime($update['edit_date'])) ?>
                        </div>
                        <div class="update-admin">
                            Updated by: <?= htmlspecialchars($update['admin_name']) ?>
                        </div>
                        <div class="update-desc">
                            <?= htmlspecialchars($update['edit_desc']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No update history available for this product.</p>
            <?php 
                endif;
            } catch (PDOException $e) {
                echo "<p>Error loading update history: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            ?>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enable/disable quantity fields based on selected option
    const setQuantityRadio = document.getElementById('set_quantity');
    const addQuantityRadio = document.getElementById('add_quantity');
    const quantityInput = document.getElementById('quantity');
    const addStockInput = document.getElementById('add_stock');
    
    function updateInputStates() {
        if (setQuantityRadio.checked) {
            quantityInput.disabled = false;
            addStockInput.disabled = true;
        } else {
            quantityInput.disabled = true;
            addStockInput.disabled = false;
        }
    }
    
    setQuantityRadio.addEventListener('change', updateInputStates);
    addQuantityRadio.addEventListener('change', updateInputStates);
    
    // Initialize state
    updateInputStates();
});
</script>