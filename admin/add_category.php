<?php
// Include database connection
require_once '../_base.php';
include '../_head.php';

$_adminContext = true;

// Initialize variables
$category_name = '';
$material_type = '';
$errors = [];
$success = '';

// Check if form is submitted
if (is_post()) {
    // Validate form data
    $category_name = trim($_POST['category_name'] ?? '');
    $material_type = trim($_POST['material_type'] ?? '');

    if (empty($_POST['category_name'])) {
        $errors[] = 'Category name is required';
    } 
    if (empty($_POST['material_type'])) {
        $errors[] = 'Material type is required';
    }
        
    // Check if category already exists
    $stmt = $_db->prepare("SELECT cat_id FROM category WHERE cat_name = ?");
    $stmt->execute([$category_name]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($result) > 0) {
        $errors[] = 'Category already exists';
    } else {
        // Insert new category
        try {
            $query = "SELECT cat_id FROM category ORDER BY cat_id DESC LIMIT 1";
            $stmt = $_db->query($query);
            $last_category = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($last_category) {
                $last_id = intval(substr($last_category['cat_id'], 2));
                $cat_id = 'CT' . str_pad($last_id + 1, 2, '0', STR_PAD_LEFT);
            } else {
                $cat_id = 'CT01';
            }
            $stmt = $_db->prepare("INSERT INTO category (cat_id, cat_name, material_type)
                                  VALUES (?, ?, ?)");
            $stmt->execute([$cat_id, $category_name, $material_type]);
            $success = 'Category added successfully';
            header('Location: admin_dashboard.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<body>
    <div class="container">
        <h1>Add New Category</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="category_name">Category Name:</label>
                <input type="text" id="category_name" name="category_name" 
                       value="<?php echo htmlspecialchars($category_name); ?>" required>
            </div>
            <div class="form-group">
                <label for="material_type">Material Type:</label>
                <input type="text" id="material_type" name="material_type" 
                       value="<?php echo htmlspecialchars($material_type); ?>" required>
            </div>
            
            <div class="form-group">
                <button type="submit">Add Category</button>
                <a href="manage_categories.php" class="btn-secondary">Back to Categories</a>
            </div>
        </form>
    </div>
</body>
</html>