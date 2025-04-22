<?php
// session_start();
require '../_base.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
// For testing, use hardcoded user
$testUserId = "C0001"; // Hardcoded test user ID
$response = ['success' => false, 'message' => ''];

// Function to check if a product exists
function productExists($db, $productId) {
    $stmt = $db->prepare("SELECT prod_id FROM product WHERE prod_id = ?");
    $stmt->execute([$productId]);
    return $stmt->rowCount() > 0;
}

// Handle different actions
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'get_favorites':
            try {
                $stmt = $_db->prepare("SELECT prod_id FROM product_favorites WHERE cust_id = ?");
                $stmt->execute([$testUserId]);
                $favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                $response = [
                    'success' => true,
                    'favorites' => $favorites
                ];
            } catch (Exception $e) {
                $response['message'] = "Error retrieving favorites";
                error_log("Favorites error: " . $e->getMessage());
            }
            break;
            
        default:
            $response['message'] = "Invalid action";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['product_id'])) {
        $response['message'] = "Product ID is required";
        echo json_encode($response);
        exit;
    }
    
    $productId = $_POST['product_id'];
    
    // Verify product exists
    if (!productExists($_db, $productId)) {
        $response['message'] = "Product not found";
        echo json_encode($response);
        exit;
    }
    
    switch ($_POST['action']) {
        case 'add_favorite':
            try {
                // Check if already exists
                $stmt = $_db->prepare("SELECT favorite_id FROM product_favorites WHERE cust_id = ? AND prod_id = ?");
                $stmt->execute([$testUserId, $productId]);
                
                if ($stmt->rowCount() === 0) {
                    $stmt = $_db->prepare("INSERT INTO product_favorites (cust_id, prod_id) VALUES (?, ?)");
                    $stmt->execute([$testUserId, $productId]);
                }
                
                $response['success'] = true;
            } catch (Exception $e) {
                $response['message'] = "Error adding to favorites";
                error_log("Favorites error: " . $e->getMessage());
            }
            break;
            
        case 'remove_favorite':
            try {
                $stmt = $_db->prepare("DELETE FROM product_favorites WHERE cust_id = ? AND prod_id = ?");
                $stmt->execute([$testUserId, $productId]);
                
                $response['success'] = true;
            } catch (Exception $e) {
                $response['message'] = "Error removing from favorites";
                error_log("Favorites error: " . $e->getMessage());
            }
            break;
            
        default:
            $response['message'] = "Invalid action";
    }
}

echo json_encode($response);