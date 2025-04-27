<?php
require_once '../_base.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_log("Delete product operation started.");
unset($_SESSION['message']);
unset($_SESSION['message_type']);

// Check if user is logged in and has admin privileges
auth('admin');

$_adminContext = true;

function isProductInCart($prodId) {
    global $_db;

    try {
        $stmt = $_db->prepare(
            "SELECT COUNT(*) as count
            FROM cart_item ci
            WHERE ci.prod_id = ?"
        );
        $stmt->execute([$prodId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($result['count'] > 0);
    } catch (PDOException $e) {
        error_log("Error checking if product is in cart: " . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prod_id'])) {
    $prodId = $_POST['prod_id'] ?? '';

    // Check if product is in any cart
    if (isProductInCart($prodId)) {
        $_SESSION['message'] = "Product (ID: $prodId) exists in one or more customer carts and cannot be deleted.";
        $_SESSION['message_type'] = 'error';
        header("Location: admin_products.php");
        exit;
    }

    try {
        // Check if the product exists in any orders
        $stmt = $_db->prepare("
            SELECT COUNT(*) as count 
            FROM order_items 
            WHERE prod_id = ?
        ");
        $stmt->execute([$prodId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If product is in orders, prevent deletion
        if ($result['count'] > 0) {
            $_SESSION['message'] = "Product (ID: $prodId) has existing orders and cannot be deleted to maintain order history.";
            $_SESSION['message_type'] = "error";
            header("Location: admin_products.php");
            exit;
        }
        
        // No orders and not in cart - safe to delete
        // Get product details for image deletion
        $stmt = $_db->prepare("SELECT image FROM product WHERE prod_id = ?");
        $stmt->execute([$prodId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete product 
        $stmt = $_db->prepare("DELETE FROM product WHERE prod_id = ?");
        $stmt->execute([$prodId]);
        
        // Delete associated images if they exist
        if (!empty($product['image'])) {
            $images = json_decode($product['image'], true);
            if (is_array($images)) {
                foreach ($images as $image) {
                    $imagePath = "../images/products/" . $image;
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
            }
        }
        
        $_SESSION['message'] = "Product deleted successfully.";
        $_SESSION['message_type'] = "success";
        
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error deleting product: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }
    
    if ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        // If AJAX request, return JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => ($_SESSION['message_type'] === 'success'), 
            'message' => $_SESSION['message']
        ]);
        
        // Clear session messages since we've returned them in JSON
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        exit;
    } else {
        // Regular form submission - redirect with session message
        header("Location: admin_products.php");
        exit;
    }
} else {
    $_SESSION['message'] = "Invalid request.";
    $_SESSION['message_type'] = "error";
    header("Location: admin_products.php");
    exit;
}