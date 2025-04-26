<?php
require '../_base.php';

// Get product ID from URL
$productId = $_GET['id'] ?? null;
if (!$productId) {
    header('Location: products.php');
    exit;
}

// Get product details from database
$stmt = $_db->prepare("
    SELECT p.*, c.cat_name 
    FROM product p
    LEFT JOIN category c ON p.cat_id = c.cat_id
    WHERE p.prod_id = ?
");
$stmt->execute([$productId]);
$product = $stmt->fetch();

// Decode the images JSON array
$productImages = json_decode($product->image) ?: [];
$mainImage = !empty($productImages) ? $productImages[0] : 'default-product-image.jpg';

if (!$product) {
    header('Location: products.php');
    exit;
}

// Get related products (same category)
$stmt = $_db->prepare("
    SELECT * FROM product 
    WHERE cat_id = ? AND prod_id != ?
    LIMIT 4
");
$stmt->execute([$product->cat_id, $productId]);
$relatedProducts = $stmt->fetchAll();

// Handle AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $response = ['success' => false, 'message' => '', 'cart_count' => 0];
        
        try {
            switch ($_POST['action']) {
                case 'add_to_cart':
                    // Check if user is logged in
                    if (!isset($_SESSION['cust_id'])) {
                        throw new Exception('Please log in to add items to your cart');
                    }
                    
                    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
                    
                    // Validate quantity
                    if ($quantity < 1) {
                        $quantity = 1;
                    } elseif ($quantity > 99) {
                        $quantity = 99;
                    }
                    
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
                    
                    // Get cart count for response
                    $stmt = $_db->prepare("SELECT SUM(quantity) FROM cart_item WHERE cart_id = ?");
                    $stmt->execute([$cartId]);
                    $response['cart_count'] = $stmt->fetchColumn() ?: 0;
                    
                    $response['success'] = true;
                    $response['message'] = 'Product added to cart!';
                    break;
                    
                case 'toggle_favorite':
                    if (!isset($_SESSION['cust_id'])) {
                        throw new Exception('login_required');
                    }
                    
                    $action = $_POST['favorite_action']; // 'add' or 'remove'
                    $custId = $_SESSION['cust_id'];
                    
                    if ($action === 'add') {
                        $stmt = $_db->prepare("INSERT INTO prod_fav (cust_id, prod_id) VALUES (?, ?)");
                        $stmt->execute([$custId, $productId]);
                    } else {
                        $stmt = $_db->prepare("DELETE FROM prod_fav WHERE cust_id = ? AND prod_id = ?");
                        $stmt->execute([$custId, $productId]);
                    }
                    
                    $response['success'] = true;
                    $response['is_favorite'] = $action === 'add';
                    break;
                    
                default:
                    throw new Exception("Invalid action");
            }
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        
        echo json_encode($response);
        exit;
    }
}

// Check if product is in favorites (if user is logged in)
$isFavorite = false;
if (isset($_SESSION['cust_id'])) {
    $stmt = $_db->prepare("SELECT 1 FROM prod_fav WHERE cust_id = ? AND prod_id = ?");
    $stmt->execute([$_SESSION['cust_id'], $productId]);
    $isFavorite = (bool)$stmt->fetch();
}

// ----------------------------------------------------------------------------
$_title = $product->prod_name;
include '../_head.php';
?>

<div class="product-detail-page">
    <div class="breadcrumb">
        <a href="/">Home</a> &gt;
        <a href="products.php">Products</a> &gt;
        <span><?= htmlspecialchars($product->prod_name) ?></span>
    </div>

    <div class="product-detail-container">
        <div class="product-images">
            <div class="main-image">
                <!-- <img class="product-image" src="/images/products/<?= htmlspecialchars($mainImage) ?>" alt="<?= htmlspecialchars($product->prod_name) ?>"> -->
                <img id="mainProductImage" src="/images/products/<?= htmlspecialchars($mainImage) ?>" alt="<?= htmlspecialchars($product->prod_name) ?>">
            </div>
            <!-- Thumbnail images -->
            <div class="product-thumbnail-container">
                <?php if (!empty($productImages)): ?>
                    <?php foreach ($productImages as $index => $image): ?>
                        <div class="product-thumbnail <?= $index === 0 ? 'active' : '' ?>" 
                             onclick="changeMainImage(this, '/images/products/<?= htmlspecialchars($image) ?>')">
                            <img src="/images/products/<?= htmlspecialchars($image) ?>" 
                                 alt="Thumbnail <?= $index + 1 ?>">
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback if no images -->
                    <div class="product-thumbnail active" 
                         onclick="changeMainImage(this, '/images/products/default-product-image.jpg')">
                        <img src="/images/products/default-product-image.jpg" alt="Default Thumbnail">
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="product-info">
            <h1><?= htmlspecialchars($product->prod_name) ?></h1>
            <div class="product-meta">
                <span class="category">Category: <?= htmlspecialchars($product->cat_name) ?></span>
                <span class="sku">SKU: <?= htmlspecialchars($product->prod_id) ?></span>
            </div>
            
            <div class="price-container">
                <span class="price">RM <?= number_format($product->price, 2) ?></span>
            </div>
            
            <div class="product-description">
                <h3>Description</h3>
                <p><?= nl2br(htmlspecialchars($product->prod_desc)) ?></p>
            </div>
            
            <div class="product-addtocart-actions">
                <div class="quantity-selector">
                    <label for="quantity">Quantity:</label>
                    <div class="quantity-control">
                        <button class="qty-minus">-</button>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= htmlspecialchars($product->quantity)?>">
                        <button class="qty-plus">+</button>
                    </div>

                    <div class="product-stock-status">
                        <?php if ($product->quantity > 10): ?>
                            <span class="product-in-stock"><i class="fas fa-check-circle"></i> In Stock (<?= htmlspecialchars($product->quantity) ?> available)</span>
                        <?php elseif ($product->quantity > 0): ?>
                            <span class="product-low-stock"><i class="fas fa-exclamation-circle"></i> Only <?= htmlspecialchars($product->quantity) ?> left!</span>
                        <?php else: ?>
                            <span class="product-out-of-stock"><i class="fas fa-times-circle"></i> Out of Stock</span>
                        <?php endif; ?>
                    </div>

                </div>
                
                <div class="action-buttons">
                    <button id="add-to-cart" class="btn-primary">Add to Cart</button>
                    <button id="favorite-btn" class="btn-favorite <?= $isFavorite ? 'active' : '' ?>">
                        <i class="<?= $isFavorite ? 'fas' : 'far' ?> fa-heart"></i>
                    </button>
                </div>
            </div>
            
            <div class="product-meta-footer">
                <div class="meta-item">
                    <i class="fas fa-undo"></i>
                    <span>30-day return policy</span>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($relatedProducts)): ?>
    <div class="related-products">
        <h2>You May Also Like</h2>
        <div class="products-grid">
            <?php foreach ($relatedProducts as $related): ?>
                <?php 
                $relatedImages = json_decode($related->image) ?: [];
                $relatedFirstImage = !empty($relatedImages) ? $relatedImages[0] : 'default-product-image.jpg';
                ?>
                <div class="product-card">
                    <a href="product_details.php?id=<?= htmlspecialchars($related->prod_id) ?>">
                        <div class="product-image">
                            <img src="/images/products/<?= htmlspecialchars($relatedFirstImage) ?>" alt="<?= htmlspecialchars($related->prod_name) ?>">
                        </div>
                        <div class="product-info">
                            <h3><?= htmlspecialchars($related->prod_name) ?></h3>
                            <div class="price">RM <?= number_format($related->price, 2) ?></div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Quantity controls
    $('.qty-minus').click(function() {
        let $input = $('#quantity');
        let value = parseInt($input.val());
        if (value > 1) {
            $input.val(value - 1);
        }
    });
    
    $('.qty-plus').click(function() {
        let $input = $('#quantity');
        let value = parseInt($input.val());
        if (value < 99) {
            $input.val(value + 1);
        }
    });
    
    // Ensure valid quantity
    $('#quantity').change(function() {
        let value = parseInt($(this).val());
        if (isNaN(value) || value < 1) {
            $(this).val(1);
        } else if (value > 99) {
            $(this).val(99);
        }
    });
    
function createParticles(sourceElement, count = 20) {
    // Create a container for particles if it doesn't exist
    let container = document.querySelector('.particle-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'particle-container';
        document.body.appendChild(container);
    }
    
    const sourceRect = sourceElement.getBoundingClientRect();
    // Try to find the cart icon in various possible locations
    const cartIcon = document.querySelector('.cart-icon') || 
                    document.querySelector('.fa-shopping-cart') || 
                    document.querySelector('[href*="cart"]');
    
    if (!cartIcon) {
        console.warn('Cart icon not found for particle animation');
        return;
    }
    
    const cartRect = cartIcon.getBoundingClientRect();
    
    for (let i = 0; i < count; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        container.appendChild(particle);
        
        // Set initial position (center of button)
        const startX = sourceRect.left + sourceRect.width / 2;
        const startY = sourceRect.top + sourceRect.height / 2;
        // Target position (center of cart icon)
        const endX = cartRect.left + cartRect.width / 2;
        const endY = cartRect.top + cartRect.height / 2;
        
        // Randomize path slightly for a natural effect
        const controlX = startX + (endX - startX) * 0.5 + (Math.random() - 0.5) * 100;
        const controlY = startY + (endY - startY) * 0.3 - Math.random() * 50;
        
        // Set initial styles
        particle.style.left = `${startX}px`;
        particle.style.top = `${startY}px`;
        particle.style.backgroundColor = `hsl(${Math.random() * 60 + 180}, 100%, 50%)`; // Blue-ish colors
        particle.style.width = `${Math.random() * 6 + 4}px`;
        particle.style.height = particle.style.width;
        
        // Animate along a curved path
        const duration = Math.random() * 1000 + 500; // Random duration between 500-1500ms
        const animation = particle.animate([
            {
                transform: 'translate(0, 0)',
                opacity: 1
            },
            {
                transform: `translate(${controlX - startX}px, ${controlY - startY}px)`,
                opacity: 0.8
            },
            {
                transform: `translate(${endX - startX}px, ${endY - startY}px)`,
                opacity: 0
            }
        ], {
            duration: duration,
            easing: 'cubic-bezier(0.4, 0, 0.2, 1)'
        });
        
        // Remove particle after animation completes
        animation.onfinish = () => particle.remove();
    }
}

    // Add to cart
    $('#add-to-cart').click(function() {
        let $btn = $(this);
        let quantity = parseInt($('#quantity').val()) || 1;
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');
        
        $.ajax({
            url: window.location.href,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'add_to_cart',
                quantity: quantity
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    $btn.html('<i class="fas fa-check"></i> Added!');
                    refreshCartCount(response.cart_count);
                    
                    // Reset button after delay
                    setTimeout(function() {
                        $btn.html('Add to Cart').prop('disabled', false);
                    }, 2000);
                } else {
                    if (response.message === 'Please log in to add items to your cart') {
                        window.location.href = '/page/login.php?redirect=' + encodeURIComponent(window.location.pathname);
                    } else {
                        alert(response.message);
                        $btn.html('Add to Cart').prop('disabled', false);
                    }
                }
            },
            error: function() {
                alert('Error adding to cart. Please try again.');
                $btn.html('Add to Cart').prop('disabled', false);
            }
        });
    });
    
    // Favorite button
    $('#favorite-btn').click(function() {
        let $btn = $(this);
        let isFavorite = $btn.hasClass('active');
        
        $.ajax({
            url: window.location.href,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'toggle_favorite',
                favorite_action: isFavorite ? 'remove' : 'add'
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    $btn.toggleClass('active', !isFavorite);
                    $btn.find('i')
                        .toggleClass('far', isFavorite)
                        .toggleClass('fas', !isFavorite);
                } else if (response.message === 'login_required') {
                    window.location.href = '/page/login.php?redirect=' + encodeURIComponent(window.location.pathname);
                } else {
                    alert(response.message || 'Error updating favorites');
                }
            },
            error: function() {
                alert('Error updating favorites. Please try again.');
            }
        });
    });
    
    // Function to update cart count (you may have this in your _base.js)
    function refreshCartCount(count) {
        $('.cart-count').text(count || 0);
    }

    // Change main product image when thumbnail is clicked
    function changeMainImage(element, imageUrl) {
        document.getElementById('mainProductImage').src = imageUrl;
        
        // Update active thumbnail
        const thumbnails = document.querySelectorAll('.product-thumbnail');
        thumbnails.forEach(thumb => {
            thumb.classList.remove('active');
        });

        element.classList.add('active');
    }
    
    // Change main image on thumbnail click
    document.querySelectorAll('.product-thumbnail').forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            const imageUrl = this.querySelector('img').src;
            changeMainImage(this, imageUrl);
        });
    });

    // Star rating functionality for review modal
    document.querySelectorAll('.star').forEach(star => {
        star.addEventListener('click', function() {
            const value = parseInt(this.getAttribute('data-value'));
            document.getElementById('reviewRating').value = value;
            
            // Update star display
            const stars = document.querySelectorAll('.star');
            stars.forEach((s, index) => {
                if (index < value) {
                    s.classList.remove('far');
                    s.classList.add('fas');
                } else {
                    s.classList.remove('fas');
                    s.classList.add('far');
                }
            });
        });
    });
});
</script>


<style>
/* Product Details Page Styles */
.product-detail-page {
    margin: 120px auto;
    width: 100%;
    padding: 0 20px;
    max-width: 1240px;
    min-width: 984px;
    position: relative;
    background: #fff;
    display: block;
    align-content: center;
    flex: 1;
    font-size: 12px;

    /* max-width: 1200px;
    margin: 200px auto;
    padding: 20px; */
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #333;
}

/* Breadcrumb Navigation */
.breadcrumb {
    margin-bottom: 30px;
    font-size: 14px;
    color: #666;
}

.breadcrumb a {
    color: #0066cc;
    text-decoration: none;
    transition: color 0.2s;
}

.breadcrumb a:hover {
    color: #004499;
    text-decoration: underline;
}

.breadcrumb span {
    color: #333;
    font-weight: 500;
}

/* Main Product Container */
.product-detail-container {
    display: flex;
    flex-wrap: wrap;
    gap: 40px;
    margin-bottom: 50px;
}

/* Product Images Section */
.product-images {
    flex: 1;
    min-width: 300px;
}

.main-image {
    margin-bottom: 20px;
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
    padding: 15px;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 400px;
    width: 400px;
}

.main-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    mix-blend-mode: multiply;
}

.product-thumbnail-container {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.product-thumbnail {
    width: 70px;
    height: 70px;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
    cursor: pointer;
    padding: 5px;
    background: #fff;
    transition: all 0.3s ease-in-out;
    opacity: 0.7;
    transform: scale(0.95);
}

.product-thumbnail.active {
    border-color: #0066cc;
    opacity: 1;
    transform: scale(1.05);
}

.product-thumbnail:hover {
    opacity: 0.9;
    transform: scale(1.05);
} 

.product-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

/* Product Info Section */
.product-info {
    display: block;
    flex: 1;
    flex-wrap: wrap;
    gap: 20px;
    min-width: 300px;
}

.product-info h1 {
    font-size: 28px;
    margin-bottom: 15px;
    color: #222;
    font-weight: 600;
}

.product-meta {
    margin-bottom: 20px;
    font-size: 14px;
    color: #666;
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.product-meta span {
    display: inline-block;
}

.category {
    color: #0066cc;
}

.price-container {
    margin: 25px 0;
}

.price-container .price {
    font-size: 24px;
    font-weight: 700;
    color: #d82c2c;
}

.product-description {
    margin: 30px 0;
    line-height: 1.6;
}

.product-description h3 {
    font-size: 18px;
    margin-bottom: 10px;
    color: #444;
}

/* Product Actions */
.product-addtocart-actions {
    margin: 35px 0;
}

.quantity-selector {
    margin-bottom: 20px;
}

.quantity-selector label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
}

.quantity-control {
    display: flex;
    align-items: center;
    max-width: 150px;
}

.quantity-control button {
    background: #fff;
    border: 1px solid #ddd;
    width: 35px;
    height: 35px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.2s;
}

.quantity-control button:hover {
    background: #e9e9e9;
}

.quantity-control input {
    width: 50px;
    height: 31px;
    text-align: center;
    border: 1px solid #ddd;
    border-left: none;
    border-right: none;
    font-size: 16px;
}

.product-stock-status {
    margin-top: 8px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.product-stock-status i {
    margin-right: 5px;
}

.product-in-stock {
    color: #28a745;
    display: block;
}

.product-low-stock {
    color: #ffc107;
    display: block;
}

.product-out-of-stock {
    color: #dc3545;
    display: block;
}

.action-buttons {
    display: flex;
    gap: 15px;
    margin-top: 25px;
}

.btn-primary {
    background: #0066cc;
    color: white;
    border: none;
    padding: 12px 25px;
    font-size: 16px;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s;
    flex: 1;
}

.btn-primary:hover {
    background: #0055aa;
}

.btn-primary:disabled {
    background: #cccccc;
    cursor: not-allowed;
}

.btn-favorite {
    background: white;
    border: 1px solid #ddd;
    width: 50px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-favorite:hover {
    border-color: #999;
}

.btn-favorite.active {
    color: #d82c2c;
    border-color: #d82c2c;
}

.btn-favorite i {
    font-size: 18px;
}

/* Product Meta Footer */
.product-meta-footer {
    margin-top: 30px;
    border-top: 1px solid #eee;
    padding-top: 20px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
    color: #555;
    font-size: 14px;
}

.meta-item i {
    color: #0066cc;
    font-size: 16px;
}

/* Related Products */
.related-products {
    margin-top: 50px;
    padding-top: 30px;
    border-top: 1px solid #eee;
}

.related-products h2 {
    font-size: 22px;
    margin-bottom: 25px;
    color: #333;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.product-card {
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    background: white;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.product-card a {
    text-decoration: none;
    color: inherit;
}

.product-image {
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 15px;
    background: #f9f9f9;
}

.product-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    mix-blend-mode: multiply;
}

.product-info {
    padding: 15px;
}

.product-info h3 {
    font-size: 16px;
    margin-bottom: 8px;
    color: #333;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.product-info .price {
    font-weight: 700;
    color: #d82c2c;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .product-detail-container {
        flex-direction: column;
        gap: 30px;
    }
    
    .main-image {
        height: 300px;
    }
    
    .product-info h1 {
        font-size: 24px;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
}

@media (max-width: 480px) {
    .product-detail-page {
        padding: 15px;
        min-width: auto;
    }
    
    .main-image {
        height: 250px;
        padding: 10px;
    }
    
    .product-thumbnail {
        width: 50px;
        height: 50px;
    }

    .action-buttons {
        flex-direction: column;
    }
    
    .btn-favorite {
        width: 100%;
        height: 45px;
    }
    
    .products-grid {
        grid-template-columns: 1fr 1fr;
    }
}
</style>
<?php
include '../_foot.php';
?>