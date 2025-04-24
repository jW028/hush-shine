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
                    <div class="product-container">
                        <div class="product-actions">
                            <button class="favorite-btn active" data-product-id="<?= htmlspecialchars($product['prod_id']) ?>">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                        <a class="product" href="products.php?id=<?= htmlspecialchars($product['prod_id']) ?>&category=<?= htmlspecialchars($product['cat_id']) ?>"
                            data-id="<?= htmlspecialchars($product['prod_id']) ?>"
                            data-name="<?= htmlspecialchars($product['prod_name']) ?>" 
                            data-desc="<?= htmlspecialchars($product['prod_desc']) ?>" 
                            data-price="<?= number_format($product['price'], 2) ?>" 
                            data-image="<?= htmlspecialchars($product['image']) ?>"
                            data-cat-id="<?= htmlspecialchars($product['cat_id']) ?>">
                            
                            <img class="product-image" src="/images/product_img/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['prod_name']) ?>">
                            <div class="prod-description">
                                <p><?= htmlspecialchars($product['prod_name']) ?></p>
                            </div>
                            <div class="prod-price">
                                <span class="price">RM <?= number_format($product['price'], 2) ?></span>
                                <span class="view-details">View Details</span>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Product Detail Pop-up Modal -->
<div id="product-modal" class="products-modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="wrap-product-detail">

            <div class="product-detail-img">
                <div class="main-image-container">
                    <img id="modal-image" src="/images/image1.jpg" alt="Product Image">
                </div>                
                <div class="preview-container">
                    <img class="preview active" src="/images/product_img/blue_ring1.jpg" alt="Preview 1" onclick="changeImage(this, '/images/product_img/blue_ring1.jpg')">
                    <img class="preview" src="/images/product_img/Red_ring2.jpg" alt="Preview 2" onclick="changeImage(this, '/images/product_img/Red_ring2.jpg')">
                    <img class="preview" src="/images/product_img/heart_ear2.webp" alt="Preview 3" onclick="changeImage(this, '/images/product_img/heart_ear2.webp')">
                </div>
            </div>

            <div class="product-detail-button">
                <div class="product-detail">
                    <div class="product-detail-header">
                        <h2 id="modal-name"></h2>
                    </div>
                    <p id="modal-desc"></p>
                    <h3 id="modal-price"></h3>
                    <div class="quantity-selector">
                        <label for="quantity">Quantity: </label>
                        <div class="product-quantity-control">
                            <button type="button" class="qty-btn minus">-</button>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="99">
                            <button type="button" class="qty-btn plus">+</button>
                        </div>
                    </div>
                </div>
                <div class="add-or-cancel" action="add_to_cart.php" >
                    <button type="submit" name="add_to_cart" onclick="addToCart()" class="add-to-cart">Add to Cart</button>
                </div>
                <button type="button" class="modal-favorite-btn" id="modal-favorite-btn">
                    <i class="fas fa-heart"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Image change function for modal
    function changeImage(element, newSrc) {
        // Remove active class from all previews
        document.querySelectorAll('.preview').forEach(preview => {
            preview.classList.remove('active');
        });
        
        // Add active class to clicked preview
        element.classList.add('active');
        
        // Update main image
        document.getElementById('modal-image').src = newSrc;
    }

    // Add to cart functionality
    function addToCart() {
        const productId = $('#product-modal').data('product-id');
        const quantity = parseInt($('#quantity').val());
        
        if (!productId || isNaN(quantity) || quantity < 1) {
            alert('Invalid product or quantity');
            return;
        }
        
        $.ajax({
            url: '/page/favorites.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'add_to_cart',
                product_id: productId,
                quantity: quantity
            },
            success: function(response) {
                if (response.success) {
                    alert('Product added to cart successfully!');
                    $('#product-modal').hide();
                } else {
                    alert(response.message || 'Failed to add product to cart');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    }

    $(document).ready(function() {
        // Handle product click to show modal
        $('.product').click(function(e) {
            e.preventDefault();
            
            const product = $(this);
            const productId = product.data('id');
            const productName = product.data('name');
            const productDesc = product.data('desc');
            const productPrice = product.data('price');
            const productImage = product.data('image');
            const isFavorite = product.closest('.column-container').find('.favorite-btn').hasClass('active');
            
            // Set modal data
            $('#product-modal').data('product-id', productId);
            $('#modal-name').text(productName);
            $('#modal-desc').text(productDesc);
            $('#modal-price').text('RM ' + productPrice);
            $('#modal-image').attr('src', '/images/product_img/' + productImage);
            
            // Set favorite status
            $('#modal-favorite-btn')
                .data('product-id', productId)
                .toggleClass('active', isFavorite)
                .find('i')
                .toggleClass('far', !isFavorite)
                .toggleClass('fas', isFavorite);
            
            // Reset quantity
            $('#quantity').val(1);
            
            // Show modal
            $('#product-modal').show();
        });
        
        // Close modal
        $('.close').click(function() {
            $('#product-modal').hide();
        });
        
        // Close modal when clicking outside
        $(window).click(function(e) {
            if ($(e.target).is('#product-modal')) {
                $('#product-modal').hide();
            }
        });
        
        // Quantity buttons
        $('.qty-btn.minus').click(function() {
            const input = $('#quantity');
            const value = parseInt(input.val());
            if (value > 1) {
                input.val(value - 1);
            }
        });
        
        $('.qty-btn.plus').click(function() {
            const input = $('#quantity');
            const value = parseInt(input.val());
            if (value < 99) {
                input.val(value + 1);
            }
        });
        
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