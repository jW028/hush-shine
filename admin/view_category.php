<?php
require_once '../_base.php';

auth('admin');

$_adminContext = true;

$title = 'Category Details';
include '../_head.php';

$cat_id = $_GET['id'] ?? null;

if (!$cat_id) {
    header('Location: /admin/admin_dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $name = trim($_POST['cat_name'] ?? '');
    $material = trim($_POST['material_type'] ?? '');

    if (empty($name)) {
        $errors[] = "Category name is required";
    }

    

    if (empty($errors)) {
        try {
            
                $stmt = $_db->prepare("UPDATE category SET
                    cat_name = ?,
                    material_type = ?
                    WHERE cat_id = ?");
                $stmt->execute([$name, $material, $cat_id]);
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

try {
    $stmt = $_db->prepare("SELECT * FROM category WHERE cat_id = ?");
    $stmt->execute([$cat_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        header('Location: /admin/admin_dashboard.php');
        exit;
    }

} catch (PDOException $e) {
    $errors[] = "Database error: " . $e->getMessage();
    exit;
}
?>

<div class="admin-main">
<div class="admin-content">
    <div class="admin-title">
        <h2>Category Details: <?= htmlspecialchars($category['cat_name']) ?></h2>
        <a href="admin_category.php" class="category-btn back">
            <i class="fas fa-arrow-left"></i> Back to Categories
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

    <form action="view_category.php?id=<?= htmlspecialchars($cat_id)?>" method="POST" class="product-form">
    <div class="form-group">
        <label for="cat_id">Category ID</label>
        <input type="text" id="cat_id" class="form-control" value="<?= htmlspecialchars($category['cat_id']) ?>" readonly>
    </div>

    <div class="form-group">
        <label for="cat_name">Category Name</label>
        <input type="text" id="cat_name" name="cat_name" class="form-control" value="<?= htmlspecialchars($category['cat_name']) ?>" required>
    </div>

    <div class="form-group">
        <label for="material_type">Material Type</label>
        <input type="text" id="material_type" name="material_type" class="form-control" value="<?= htmlspecialchars($category['material_type']) ?>" required>
    </div>
    <hr>
    
    <div class="form-group">
        <button type="submit" name="update_category" class="admin-submit-btn primary">Update Category</button>
    </div>
    </form>
</div>
</div>