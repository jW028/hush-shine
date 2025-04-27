<?php
// session_start();
require '../_base.php';
//-----------------------------------------------------------------------------

// Check if best seller filter is active
$isBestSeller = isset($_GET['filter']) && $_GET['filter'] === 'best_seller';

// Initialize topProducts variable
$topProducts = [];

// Get top 5 selling products if best seller filter is active
if ($isBestSeller) {
    try {
        $topProductsStmt = $_db->prepare("
            SELECT 
                p.prod_id,
                p.prod_name,
                p.price,
                p.image,
                p.cat_id,
                SUM(oi.quantity) as total_sold
            FROM 
                order_items oi
            JOIN 
                orders o ON oi.order_id = o.order_id
            JOIN 
                product p ON oi.prod_id = p.prod_id
            WHERE 
                o.status IN ('received')
            GROUP BY 
                p.prod_id
            ORDER BY 
                total_sold DESC
            LIMIT 5
        ");
        $topProductsStmt->execute();
        $topProducts = $topProductsStmt->fetchAll(PDO::FETCH_OBJ);
    } catch (Exception $e) {
        error_log("Error fetching top products: " . $e->getMessage());
        $topProducts = [];
    }
} 

// Handle AJAX requests FIRST
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
                    
                    $productId = $_POST['product_id'];
                    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
                    
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
                    
                    $custId = $_SESSION['cust_id'];

                    // Check if user has an active cart
                    $stmt = $_db->prepare("SELECT cart_id FROM shopping_cart WHERE cust_id = ?");
                    $stmt->execute([$custId]);
                    $cart = $stmt->fetch();

                    // Create new cart if doesn't exist
                    if (!$cart) {
                        $stmt = $_db->prepare("INSERT INTO shopping_cart (cust_id, created_at) VALUES (?, NOW())");
                        $stmt->execute([$custId]);  // FIXED: Using custId instead of cartId
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


$search = $_GET['search'] ?? null;
$category = $_GET['category'] ?? null;
$minPrice = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? floatval($_GET['min_price']) : null;
$maxPrice = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? floatval($_GET['max_price']) : null;
$sort = $_GET['sort'] ?? null;


$params = [];
$where = [];

if ($search) {
    $where[] = "(prod_name LIKE ? OR prod_desc LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category && $category !== '*') {
    $where[] = "cat_id = ?";
    $params[] = $category;
}

// Add price range filter
if ($minPrice !== null) {
    $where[] = "price >= ?";
    $params[] = $minPrice;
}

if ($maxPrice !== null) {
    $where[] = "price <= ?";
    $params[] = $maxPrice;
}

$sql = "SELECT * FROM product";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// Add sorting
if ($sort) {
    switch ($sort) {
        case 'name_asc':
            $sql .= " ORDER BY prod_name ASC";
            break;
        case 'name_desc':
            $sql .= " ORDER BY prod_name DESC";
            break;
        case 'price_asc':
            $sql .= " ORDER BY price ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY price DESC";
            break;
        default:
            // Default sorting - by category
            break;
    }
}

$stmt = $_db->prepare($sql);
$stmt->execute($params);
$arr = $stmt->fetchAll();

// Define known categories
$categories = [
    'CT04' => 'Earrings',
    'CT01' => 'Necklaces',
    'CT02' => 'Bracelets',
    'CT03' => 'Rings'
];

if (!$sort && !$search) {
    // Sort products by category (Unknown categories go last)
    usort($arr, function ($a, $b) use ($categories) {
        $catA = isset($categories[$a->cat_id]) ? array_search($a->cat_id, array_keys($categories)) : count($categories);
        $catB = isset($categories[$b->cat_id]) ? array_search($b->cat_id, array_keys($categories)) : count($categories);
        return $catA <=> $catB;
    });
}

// Get min and max prices for the filter
$minAvailablePrice = $_db->query("SELECT MIN(price) as min_price FROM product")->fetch()->min_price;
$maxAvailablePrice = $_db->query("SELECT MAX(price) as max_price FROM product")->fetch()->max_price;

// Round to nearest 10
$minAvailablePrice = floor($minAvailablePrice / 10) * 10;
$maxAvailablePrice = ceil($maxAvailablePrice / 10) * 10;

// ----------------------------------------------------------------------------
$_title = 'Products';
include '../_head.php';
?>

<div class="product-page-container">
    <div class="product-filters-container">
        <div class="product-search-container">
            <form action="/page/products.php" method="get" class="product-search-form">
                <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search ?? '') ?>">
                <button type="submit"><i class="fa fa-search"></i></button>
                <?php if ($search || $category || $minPrice || $maxPrice || $sort): ?>
                    <a href="/page/products.php" class="clear-search">Clear All Filters</a>
                <?php endif; ?>
                
                <!-- Preserve other filters when submitting -->
                <?php if ($category): ?>
                    <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                <?php endif; ?>
                <?php if ($sort): ?>
                    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                <?php endif; ?>
            </form>
        </div>
        
        <div class="filter-sort-container">
            <!-- Price filter -->
            <div class="price-filter">
                <h3>Price Range</h3>
                <form action="/page/products.php" method="get" class="price-filter-form">
                    <div class="price-inputs">
                        <div class="price-input">
                            <label for="min_price">Min (RM)</label>
                            <input type="number" id="min_price" name="min_price" min="<?= $minAvailablePrice ?>" max="<?= $maxAvailablePrice ?>" value="<?= htmlspecialchars($minPrice ?? $minAvailablePrice) ?>">
                        </div>
                        <div class="price-input">
                            <label for="max_price">Max (RM)</label>
                            <input type="number" id="max_price" name="max_price" min="<?= $minAvailablePrice ?>" max="<?= $maxAvailablePrice ?>" value="<?= htmlspecialchars($maxPrice ?? $maxAvailablePrice) ?>">
                        </div>
                    </div>
                    
                    <!-- Preserve other filters when submitting -->
                    <?php if ($search): ?>
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    <?php endif; ?>
                    <?php if ($category): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                    <?php endif; ?>
                    <?php if ($sort): ?>
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                    <?php endif; ?>
                    
                    <button type="submit" class="apply-price">Apply</button>
                </form>
            </div>
            
            <!-- Sorting -->
            <div class="product-sort">
                <h3>Sort By</h3>
                <form action="/page/products.php" method="get" class="sort-form">
                    <select name="sort" id="sort" onchange="this.form.submit()">
                        <option value="">Default</option>
                        <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                        <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price (Low to High)</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price (High to Low)</option>
                    </select>
                    
                    <!-- Preserve other filters when submitting -->
                    <?php if ($search): ?>
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    <?php endif; ?>
                    <?php if ($category): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                    <?php endif; ?>
                    <?php if ($minPrice): ?>
                        <input type="hidden" name="min_price" value="<?= htmlspecialchars($minPrice) ?>">
                    <?php endif; ?>
                    <?php if ($maxPrice): ?>
                        <input type="hidden" name="max_price" value="<?= htmlspecialchars($maxPrice) ?>">
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <?php if ($isBestSeller && !empty($topProducts)): ?>
    <div class="top-selling-container">
        <h2>Top Selling Products</h2>
        
        <div class="top-products-grid">
            <?php foreach ($topProducts as $product): ?>
                <div class="top-product-item">
                    <a href="product_details.php?id=<?= $product->prod_id ?>">
                        <?php
                            $images = json_decode($product->image) ?: [];
                            $firstImage = !empty($images) ? $images[0] : 'default-product-image.jpg';
                        ?>
                        <img src="/images/products/<?= htmlspecialchars($firstImage) ?>" 
                            alt="<?= htmlspecialchars($product->prod_name) ?>">
                        <h3><?= htmlspecialchars($product->prod_name) ?></h3>
                        <div class="product-meta">
                            <span class="price">RM <?= number_format($product->price, 2) ?></span>
                            <span class="sold-count"><?= $product->total_sold ?> sold</span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="row-container">
        <?php if (empty($arr)): ?>
            <div class="no-products-found">
                <p>No products found<?= $search ? " for \"" . htmlspecialchars($search) . "\"" : "" ?>.</p>
            </div>
        <?php else: ?>
        <?php endif; ?>

        <?php 
        $columnCount = 0;
        $firstProduct = []; // Track the first product of each category

        foreach ($arr as $s): 
            // Check if this is the first product of its category
            $productId = htmlspecialchars($s->prod_id);
            $categoryId = htmlspecialchars($s->cat_id);

            if ($columnCount % 4 == 0 && $columnCount > 0): ?>
                </div><div class="row-container">
            <?php endif; ?>

            <div class="column-container">
                <a class="product" href="product_details.php?id=<?= $productId ?>"
                    data-id="<?= $productId ?>"
                    data-name="<?= htmlspecialchars($s->prod_name) ?>" 
                    data-desc="<?= htmlspecialchars($s->prod_desc) ?>" 
                    data-price="<?= number_format($s->price, 2) ?>" 
                    data-image="<?= htmlspecialchars($s->image) ?>"
                    data-cat-id="<?= $categoryId ?>">
                    
                    <div class="product-container">
                        <div class="product-actions">
                            <button type="button" class="favorite-btn" data-product-id="<?= htmlspecialchars($s->prod_id) ?>">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                        <?php
                        // Extract the first image
                        if ($s && !empty($s->image)) {
                            $productImages = json_decode($s->image) ?: [];
                            $firstImage = !empty($productImages) ? $productImages[0] : 'default-product-image.jpg';                            
                        } else {
                            $firstImage = 'default_image.webp'; // Fallback image if no images are found
                        }
                        ?>
                        <img class="product-image" src="/images/products/<?= htmlspecialchars($firstImage) ?>" alt="<?= htmlspecialchars($s->prod_name) ?>">
                        <div class="prod-description">
                            <p><?= htmlspecialchars($s->prod_name) ?></p>
                        </div>
                        <div class="prod-price">
                            <span class="price">RM <?= number_format($s->price, 2) ?></span>
                            <span class="view-details">View Details</span>
                        </div>
                    </div>
                </a>
            </div>

            <?php 
            $columnCount++;
        endforeach; ?>
    </div>
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
                    <!-- <img class="preview active" src="/images/image1.jpg" alt="Preview 1" onclick="changeImage(this, '/images/image1.jpg')">
                    <img class="preview" src="/images/image2.jpg" alt="Preview 2" onclick="changeImage(this, '/images/image2.jpg')">
                    <img class="preview" src="/images/image3.jpg" alt="Preview 3" onclick="changeImage(this, '/images/image3.jpg')"> -->
                    <img class="preview active" src="/images/products/blue_ring1.jpg" alt="Preview 1" onclick="changeImage(this, '/images/products/blue_ring1.jpg')">
                    <img class="preview" src="/images/products/Red_ring2.jpg" alt="Preview 2" onclick="changeImage(this, '/images/products/Red_ring2.jpg')">
                    <img class="preview" src="/images/products/heart_ear2.webp" alt="Preview 3" onclick="changeImage(this, '/images/products/heart_ear2.webp')">
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
                    <!-- <button class="cancel">Cancel</button> -->
                </div>
                <button type="button" class="modal-favorite-btn" id="modal-favorite-btn">
                            <i class="far fa-heart"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
    // Load favorites status when the page loads
    loadFavoriteStatus();
    
    // Handle clicks on favorite buttons
    $(document).on('click', '.favorite-btn, .modal-favorite-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        let $button = $(this);
        let productId = $button.data('product-id');
        let isActive = $button.hasClass('active');
        
        console.log("Toggling favorite for product:", productId, "Current status:", isActive);
        toggleFavorite(productId, !isActive, $button);
    });
    
    // Update modal favorite button when product is clicked
    $(document).on('click', '.product', function() {
        let productId = $(this).data('id');
        let isFavorite = $(this).find('.favorite-btn').hasClass('active');
        
        $('#modal-favorite-btn')
            .data('product-id', productId)
            .toggleClass('active', isFavorite)
            .find('i')
            .toggleClass('far fa-heart', !isFavorite)
            .toggleClass('fas fa-heart', isFavorite);
    });
    
    // Load favorites from server
    function loadFavoriteStatus() {
        console.log("Loading favorites status...");
        $.ajax({
            url: '/page/favorites_handler.php',
            type: 'GET',
            dataType: 'json',
            data: { action: 'get_favorites' },
            success: function(response) {
                console.log("Favorites loaded:", response);
                if (response.success && response.favorites) {
                    response.favorites.forEach(function(prodId) {
                        updateFavoriteUI(prodId, true);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to load favorites:', error);
                console.error('Response:', xhr.responseText);
            }
        });
    }
    
    // Toggle favorite status on server
    function toggleFavorite(productId, addToFavorites, $button) {
        console.log("Sending request to toggle favorite:", productId, addToFavorites);
        $.ajax({
            url: '/page/favorites_handler.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: addToFavorites ? 'add_favorite' : 'remove_favorite',
                product_id: productId
            },
            success: function(response) {
                console.log("Toggle favorite response:", response);
                if (response.success) {
                    updateFavoriteUI(productId, addToFavorites);
                } else {
                    if (response.message === 'login_required') {
                        window.location.href = '/page/login.php?redirect=' + encodeURIComponent(window.location.pathname);
                    } else {
                        alert(response.message || 'Error updating favorites');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to update favorites:', error);
                console.error('Response:', xhr.responseText);
                alert('Failed to update favorites. Please try again.');
            }
        });
    }
    
    // Update UI to reflect favorite status
    function updateFavoriteUI(productId, isFavorite) {
        console.log("Updating UI for product:", productId, "Favorite:", isFavorite);
        $('.favorite-btn[data-product-id="' + productId + '"], .modal-favorite-btn[data-product-id="' + productId + '"]')
            .toggleClass('active', isFavorite)
            .find('i')
            .toggleClass('far fa-heart', !isFavorite)
            .toggleClass('fas fa-heart', isFavorite);
    }
});
</script>

<style> 
/* Top Selling Products Styles */
.top-selling-container {
    margin: 30px 0;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
}

.top-selling-container h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #333;
    font-size: 24px;
}

.top-products-grid {
    display: grid;
    /* grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); */
    grid-template-columns: repeat(4, 1fr); 
    gap: 20px;
    margin: 0 auto;
    max-width: 1200px;
}

.top-product-item {
    width: 250px;
    height: 300px;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    text-align: center;
}

.top-product-item a{
    text-decoration: none;
}

.top-product-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.top-product-item img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-bottom: 1px solid #eee;
}

.top-product-item h3 {
    padding: 10px 15px;
    font-size: 16px;
    margin: 0;
    color: #333;
}

.top-product-item .product-meta {
    padding: 0 15px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.top-product-item .price {
    font-size: 15px;
}

.top-product-item .sold-count {
    font-size: 13px;
    color: #666;
    background: #f0f0f0;
    padding: 3px 8px;
    border-radius: 10px;
}
</style>

<?php
include '../_foot.php';
?>