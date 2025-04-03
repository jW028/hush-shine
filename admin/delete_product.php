<?php
require_once '../_base.php';
include '../_head.php';

auth('admin');

$prod_id = $_GET['id'] ?? null;

if (empty($prod_id)) {
    header('Location: /admin/admin_menu.php');
    exit;
}

if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    try {
        $stmt = $_db->prepare("SELECT prod_name FROM product WHERE prod_id = ?");
        $stmt->execute([$prod_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            echo '<div class="alert alert-danger">Product not found.</div>';
            echo '<a href="admin_products.php" class="btn">Back to Product List</a>';
            exit;
        }
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        echo '<a href="admin_products.php" class="btn">Back to Product List</a>';
        exit;
    }

?>

<div class="delete-confirmation">
    <h2>Confirm Deletion</h2>
    <p>Are you sure you want to delete the product <strong><?= htmlspecialchars($product['prod_name']) ?></strong>?</p>
    <p>This action cannot be undone.</p>

    <div class="button-group">
        <a href="delete_product.php?id=<?= htmlspecialchars($prod_id) ?>&confirm=yes" class="btn btn-danger">Delete</a>
        <a href="admin_products.php" class="btn">Cancel</a>
    </div>
</div>

<?php
    include '../_foot.php';
    exit;
}

try {
    $stmt = $_db->prepare("SELECT image FROM product WHERE prod_id = ?");
    $stmt->execute([$prod_id]);;
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product && !empty($product['image'])) {
        $image_paths = json_decode($product['image'], true);
        if (!$image_paths) {
            $image_paths = [$product['image']];;
        }

        foreach($image_paths as $image_path) {
            $full_path = '../images/products/' . basename($image_path);
            if (file_exists($full_path)) {
                unlink($full_path);
            }
        }
    }

    $stmt = $_db->prepare("DELETE FROM product WHERE prod_id = ?");
    $stmt->execute([$prod_id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['success'] = 'Product deleted successfully';  
    } else {
        $_SESSION['error'] = 'Product not found or already deleted';
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error: ' . htmlspecialchars($e->getMessage());
} finally {
    header('Location: admin_products.php');
    exit;
}
?>