<?php
require '../_base.php';

// Check if user is logged in
if (!isset($_SESSION['cust_id']) || empty($_SESSION['cust_id'])) {
    header("Location: ../page/login.php");
    exit();
}

// Handle AJAX requests FIRST
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $response = ['success' => false, 'message' => '', 'cart_count' => 0];
        
        try {
            switch ($_POST['action']) {
                case 'add_to_cart':
                    $productId = $_POST['product_id'];
                    error_log("Product ID: $productId"); // Debugging line
                    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1; // Get quantity from POST
                    
                    // Validate quantity
                    if ($quantity < 1) {
                        $quantity = 1;
                    } elseif ($quantity > 99) {
                        $quantity = 99;
                    }
                    
                    // Get product from database
                    $stmt = $_db->prepare("SELECT * FROM product WHERE prod_id = ?");
                    $stmt->execute([$productId]);
                    $product = $stmt->fetch();
                    
                    if (!$product) throw new Exception("Product not available (ID: $productId)");
                    
                    // Use the customer ID from the session
                    $custId = $_SESSION['cust_id'];

                    // Check if user has an active cart
                    $stmt = $_db->prepare("SELECT cart_id FROM shopping_cart WHERE cust_id = ?");
                    $stmt->execute([$custId]);
                    $cart = $stmt->fetch();

                    // Create new cart if doesn't exist
                    if (!$cart) {
                        $stmt = $_db->prepare("INSERT INTO shopping_cart (cust_id, created_at) VALUES (?, NOW())");
                        $stmt->execute([$custId]);
                        $cartId = $_db->lastInsertId();
                    } else {
                        $cartId = $cart->cart_id;
                    }
                    
                    // Add/update item in cart
                    $stmt = $_db->prepare("SELECT * FROM cart_item WHERE cart_id = ? AND prod_id = ?");
                    $stmt->execute([$cartId, $productId]);
                    $existingItem = $stmt->fetch();
                    
                    if ($existingItem) {
                        $newQty = $existingItem->quantity + $quantity;
                        $stmt = $_db->prepare("UPDATE cart_item SET quantity = ? WHERE cart_id = ? AND prod_id = ?");
                        $stmt->execute([$newQty, $cartId, $productId]);
                    } else {
                        $stmt = $_db->prepare("INSERT INTO cart_item (cart_id, prod_id, quantity) VALUES (?, ?, ?)");
                        $stmt->execute([$cartId, $productId, $quantity]);
                    }
                    
                    $response['success'] = true;
                    break;
                default:
                    throw new Exception("Invalid action");
            }
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
        
        echo json_encode($response);
        exit;
    }
}

$custId = $_SESSION['cust_id'];

try {
    $stmt = $_db->prepare("
        SELECT p.*, pf.favorite_id 
        FROM product p 
        JOIN prod_fav pf ON p.prod_id = pf.prod_id 
        WHERE pf.cust_id = ? 
        ORDER BY pf.favorite_id DESC
    ");
    $stmt->execute([$custId]);
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC); 
} catch (Exception $e) {
    error_log("Favorites page error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$_title = 'My Favorites';
include '../_head.php';
?>

<div class="favorites-page-container">
    <h1 class="page-title">My Favorites</h1>
    
    <?php if (empty($favorites)): ?>
        <div class="favorites-empty">
            <i class="far fa-heart"></i>
            <p>You haven't added any favorites yet.</p>
            <a href="/page/products.php" class="shop-now-btn">Shop Now</a>
        </div>
    <?php else: ?>
        <div class="row-container">
            <?php foreach ($favorites as $index => $product): 
                // Close and start a new row every 4 products
                if ($index > 0 && $index % 4 == 0): ?>
                    </div><div class="row-container">
                <?php endif; ?>
                
                <div class="column-container">
                    <a class="product" href="product_details.php?id=<?= $product['prod_id'] ?>"
                        data-id="<?= htmlspecialchars($product['prod_id']) ?>"
                        data-name="<?= htmlspecialchars($product['prod_name']) ?>" 
                        data-desc="<?= htmlspecialchars($product['prod_desc']) ?>" 
                        data-price="<?= number_format($product['price'], 2) ?>" 
                        data-cat-id="<?= htmlspecialchars($product['cat_id']) ?>">
                        <div class="product-container">
                            <div class="product-actions">
                                <button class="favorite-btn active" data-product-id="<?= htmlspecialchars($product['prod_id']) ?>">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                            <?php
                                // Extract the first image
                                if ($product && !empty($product['image'])) {
                                    $productImages = json_decode($product['image']) ?: [];
                                    $firstImage = !empty($productImages) ? $productImages[0] : 'default-product-image.jpg';                            
                                } else {
                                    $firstImage = 'default_image.webp'; // Fallback image if no images are found
                                }
                            ?>

                                
                            <img class="product-image" src="/images/products/<?= htmlspecialchars($firstImage) ?>" alt="<?= htmlspecialchars($product['prod_name']) ?>">
                            <div class="prod-description">
                                <p><?= htmlspecialchars($product['prod_name']) ?></p>
                            </div>
                            <div class="prod-price">
                                <span class="price">RM <?= number_format($product['price'], 2) ?></span>
                                <span class="view-details">View Details</span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
        // Handle favorites
        $(document).on('click', '.favorite-btn, .modal-favorite-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $btn = $(this);
            const productId = $btn.data('product-id');
            const isFavorite = $btn.hasClass('active');
            const action = isFavorite ? 'remove_favorite' : 'add_favorite';
            
            $.ajax({
                url: '/page/favorites_handler.php',
                method: 'POST',
                data: { action, product_id: productId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Toggle button state
                        const newState = !isFavorite;
                        
                        // Update all buttons for this product
                        $('.favorite-btn[data-product-id="' + productId + '"], .modal-favorite-btn[data-product-id="' + productId + '"]')
                            .toggleClass('active', newState)
                            .find('i')
                            .toggleClass('far fa-heart', !newState)
                            .toggleClass('fas fa-heart', newState);
                        
                        // If removed from favorites, remove from page
                        if (action === 'remove_favorite') {
                            const $productContainer = $('.column-container').filter(function() {
                                return $(this).find('.favorite-btn[data-product-id="' + productId + '"]').length > 0;
                            });
                            
                            $productContainer.fadeOut(300, function() {
                                $(this).remove();
                                
                                // Check if no favorites left
                                if ($('.column-container:visible').length === 0) {
                                    $('.row-container').html('');
                                    $('.favorites-page-container').append(`
                                        <div class="favorites-empty">
                                            <i class="far fa-heart"></i>
                                            <p>You haven't added any favorites yet.</p>
                                            <a href="/page/products.php" class="shop-now-btn">Shop Now</a>
                                        </div>
                                    `);
                                }
                            });
                            
                            // Close modal if open
                            $('#product-modal').hide();
                        }
                    } else {
                        alert(response.message || 'Failed to update favorites');
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr.status, xhr.responseText);
                    alert('Server error. Please try again.');
                }
            });
        });
    });
</script>

<?php
include '../_foot.php';
?>