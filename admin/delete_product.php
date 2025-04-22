<?php
// filepath: /Users/jw/Documents/hushandshine/admin/process_delete_product.php
require_once '../_base.php';

// Check if user is logged in and has admin privileges
auth('admin');

$_adminContext = true;

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prod_id = $_POST['prod_id'] ?? '';
    
    if (empty($prod_id)) {
        $response['message'] = 'Product ID is required';
        echo json_encode($response);
        exit;
    }
    
    try {
        // Get product images first
        $stmt = $_db->prepare("SELECT image FROM product WHERE prod_id = ?");
        $stmt->execute([$prod_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            // Delete image files if they exist
            if (!empty($product['image'])) {
                $image_paths = json_decode($product['image'], true);
                if ($image_paths) {
                    foreach ($image_paths as $image_path) {
                        $full_path = '../images/products/' . $image_path;
                        if (file_exists($full_path)) {
                            unlink($full_path);
                        }
                    }
                }
            }
            
            // Delete the product
            $stmt = $_db->prepare("DELETE FROM product WHERE prod_id = ?");
            $stmt->execute([$prod_id]);
            
            if ($stmt->rowCount() > 0) {
                $response['success'] = true;
                $response['message'] = 'Product deleted successfully';
            } else {
                $response['message'] = 'Failed to delete product';
            }
        } else {
            $response['message'] = 'Product not found';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

echo json_encode($response);