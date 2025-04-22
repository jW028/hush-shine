<?php
require '../_base.php';
ob_clean();
header('Content-Type: application/json');

// Enable detailed error logging
error_log("Favorites handler called: " . $_SERVER['REQUEST_METHOD']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST data: " . print_r($_POST, true));
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    error_log("GET data: " . print_r($_GET, true));
}

$response = ['success' => false, 'message' => ''];

// Check if user is logged in
if (!isset($_SESSION['cust_id'])) {
    error_log("User not logged in - Session: " . print_r($_SESSION, true));
    echo json_encode(['success' => false, 'message' => 'login_required']);
    exit;
}

$custId = $_SESSION['cust_id'];
error_log("Customer ID: $custId");

// Function to check if a product exists
function productExists($db, $productId) {
    $stmt = $db->prepare("SELECT prod_id FROM product WHERE prod_id = ?");
    $stmt->execute([$productId]);
    return $stmt->rowCount() > 0;
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'get_favorites':
            try {
                // Updated to use prod_fav table name
                $stmt = $_db->prepare("SELECT prod_id FROM prod_fav WHERE cust_id = ?");
                $stmt->execute([$custId]);
                $favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                error_log("Favorites found: " . print_r($favorites, true));
                
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
            error_log("Invalid action: " . $_GET['action']);
    }

// Handle POST requests
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['product_id'])) {
        $response['message'] = "Product ID is required";
        error_log("Missing product ID");
        echo json_encode($response);
        exit;
    }
    
    $productId = $_POST['product_id'];
    error_log("Processing product ID: $productId");
    
    // Verify product exists
    if (!productExists($_db, $productId)) {
        $response['message'] = "Product not found";
        error_log("Product not found: $productId");
        echo json_encode($response);
        exit;
    }
    
    switch ($_POST['action']) {
        case 'add_favorite':
            try {
                error_log("Adding favorite: $custId, $productId");
                // Updated to use prod_fav table name
                $stmt = $_db->prepare("SELECT favorite_id FROM prod_fav WHERE cust_id = ? AND prod_id = ?");
                $stmt->execute([$custId, $productId]);
                
                if ($stmt->rowCount() === 0) {
                    // Updated to use prod_fav table name
                    $stmt = $_db->prepare("INSERT INTO prod_fav (cust_id, prod_id) VALUES (?, ?)");
                    $stmt->execute([$custId, $productId]);
                    error_log("Favorite added");
                } else {
                    error_log("Favorite already exists");
                }
                
                $response['success'] = true;
            } catch (Exception $e) {
                $response['message'] = "Error adding to favorites";
                error_log("Add favorite error: " . $e->getMessage());
            }
            break;
            
        case 'remove_favorite':
            try {
                error_log("Removing favorite: $custId, $productId");
                // Updated to use prod_fav table name
                $stmt = $_db->prepare("DELETE FROM prod_fav WHERE cust_id = ? AND prod_id = ?");
                $stmt->execute([$custId, $productId]);
                
                $response['success'] = true;
                error_log("Favorite removed");
            } catch (Exception $e) {
                $response['message'] = "Error removing from favorites";
                error_log("Remove favorite error: " . $e->getMessage());
            }
            break;
            
        default:
            $response['message'] = "Invalid action";
            error_log("Invalid action: " . $_POST['action']);
    }
}

echo json_encode($response);
?>