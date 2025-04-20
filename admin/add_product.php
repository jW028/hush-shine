<?php
require_once '../_base.php';
include '../_head.php';

// Check if user is logged in and has admin privileges
auth('admin');

// Initialize variables
$name = '';
$description = '';
$price = '';
$quantity = '';
$category_id = '';
$errors = [];

// Handle form submission
if (is_post()) {
    // Validate inputs
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);  
    $category_id = $_POST['category_id'] ?? '';
    
    if (empty($name)) {
        $errors[] = 'Product name is required';
    }
    
    if ($price === false || $price <= 0) {
        $errors[] = 'Valid price is required';
    }
    
    if ($quantity === false || $quantity <= 0) {
        $errors[] = 'Valid quantity is required';
    }
    
    // Process image upload if exists
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

    $image_path = !empty($image_paths) ? json_encode($image_paths) : null;

    
    // If no errors, insert product
    if (empty($errors)) {
        try {
            $query = "SELECT prod_id FROM product ORDER BY prod_id DESC LIMIT 1";
            $stmt = $_db->query($query);
            $last_product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($last_product) {
                $last_id = intval(substr($last_product['prod_id'], 1));
                $prod_id = 'P' . str_pad($last_id + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $prod_id = 'P001';
            }

            $stmt = $_db->prepare("INSERT INTO product (prod_id, prod_name, prod_desc, price, quantity, cat_id, image) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$prod_id, $name, $description, $price, $quantity, $category_id, $image_path]);
            
            $_SESSION['success'] = 'Product added successfully';
            header('Location: admin_menu.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
            
            
        }
    }
}

// Get categories for dropdown
try {
    $stmt = $_db->query("SELECT * FROM category ORDER BY cat_id");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = 'Error loading categories: ' . $e->getMessage();
}

?>

<div class="admin-modal-content">
    <div class="modal-header">
        <h1>Add New Product</h1>
        <span class="close">&times;</span>
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
                <option value="<?= htmlspecialchars($category['cat_id']) ?>" 
                    <?= $category_id == $category['cat_id'] ? 'selected' : '' ?>>
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
        <button type="submit" class="admin-submit-btn">Add Product</button>
        <a href="admin_menu.php">Cancel</a>
    </div>
</form>
</div>
