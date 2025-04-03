<?php
require_once '../_base.php';
include '../_head.php';

auth('admin');

$cat_id = $_GET['id'] ?? null;

if (empty($cat_id)) {
    header('Location: /admin/admin_category.php');
    exit;
}

if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    try {
        $stmt = $_db->prepare("SELECT cat_name FROM category WHERE cat_id = ?");
        $stmt->execute([$cat_id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$category) {
            echo '<div class="alert alert-danger">Category not found.</div>';
            echo '<a href="admin_category.php" class="btn">Back to Category List</a>';
            exit;
        }

        $stmt = $_db->prepare("SELECT COUNT(*) as product_count FROM product WHERE cat_id = ?");
        $stmt->execute([$cat_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['product_count'] > 0) {
            echo '<div class="alert alert-warning">
                <h4>Cannot Delete Category</h4.>
                <p>This category has ' . $result['product_count'] . ' products associated with it.</p>
                <p>Please delete or reassign these products before deleting this category.</p>
                </div>';

            $stmt = $_db->prepare("SELECT prod_id, prod_name FROM product WHERE cat_id = ?");
            $stmt->execute([$cat_id]);;
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo '<h3>Products in this Category</h3>';
            echo '<ul>';
            foreach ($products as $product) {
                echo '<li>' . htmlspecialchars($product['prod_name']) . ' (' . htmlspecialchars($product['prod_id']) . ')</li>';
            }
            
            if ($result['product_count'] > 10) {
                echo '<li>...and ' . ($result['product_count'] - 10) . ' more</li>';
            }
            
            echo '</ul>';
        }
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        echo '<a href="admin_category.php" class="btn">Back to Category List</a>';
        exit;
    }

?>

<div class="delete-confirmation">
    <h2>Confirm Deletion</h2>
    <p>Are you sure you want to delete the category <strong><?= htmlspecialchars($category['cat_name']) ?></strong>?</p>
    <p>This action cannot be undone.</p>

    <div class="button-group">
        <a href="delete_category.php?id=<?= htmlspecialchars($cat_id) ?>&confirm=yes" class="btn btn-danger">Delete</a>
        <a href="admin_category.php" class="btn">Cancel</a>
    </div>
</div>

<?php
    include '../_foot.php';
    exit;
}

try {
    $stmt = $_db->prepare("DELETE FROM category WHERE cat_id = ?");
    $stmt->execute([$cat_id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['success'] = 'Category deleted successfully';  
    } else {
        $_SESSION['error'] = 'Category not found or already deleted';
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error: ' . htmlspecialchars($e->getMessage());
} finally {
    header('Location: admin_category.php');
    exit;
}
?>