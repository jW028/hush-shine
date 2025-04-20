<?php
require_once '../_base.php';

    // Check if user is logged in and has admin privileges
auth('admin');

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cat_id = $_POST['cat_id'] ?? '';
    
    if (empty($cat_id)) {
        $response['message'] = 'Category ID is required';
        echo json_encode($response);
        exit;
    }
    
    try {
        // First check if there are any products using this category
        $stmt = $_db->prepare("SELECT COUNT(*) FROM product WHERE cat_id = ?");
        $stmt->execute([$cat_id]);
        $product_count = $stmt->fetchColumn();
        
        if ($product_count > 0) {
            $response['message'] = 'Cannot delete this category because it is used by ' . $product_count . ' products. Please reassign these products to another category first.';
            echo json_encode($response);
            exit;
        }
        
        // Delete the category
        $stmt = $_db->prepare("DELETE FROM category WHERE cat_id = ?");
        $stmt->execute([$cat_id]);
        
        if ($stmt->rowCount() > 0) {
            $response['success'] = true;
            $response['message'] = 'Category deleted successfully';
        } else {
            $response['message'] = 'Failed to delete category';
        }
    } catch (PDOException $e) {
        // Check if it's a foreign key constraint violation
        if ($e->getCode() == 23000) {
            $response['message'] = 'Cannot delete this category because it is being used by products. Please reassign these products to another category first.';
        } else {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
}

echo json_encode($response);
?>