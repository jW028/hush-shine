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
    $showRegularProducts = false;
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
                o.status IN ('Confirmed')
            GROUP BY 
                p.prod_id
            ORDER BY 
                total_sold DESC
            LIMIT 5
        ");
        $topProductsStmt->execute();
        $topProducts = $topProductsStmt->fetchAll(PDO::FETCH_OBJ);

        If(empty($topProducts)) {
            $noBestSellersMessage = "No best-selling products found.";
        }

    } catch (Exception $e) {
        error_log("Error fetching top products: " . $e->getMessage());
        $topProducts = [];
        $noBestSellersMessage = "Error fetching best-selling products.";
    }
} else {
    $showRegularProducts = true;
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

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$itemsPerPage = 8; // Products per page
$offset = ($page - 1) * $itemsPerPage;

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

// Get total count for pagination
try {
    $countQuery = "SELECT COUNT(*) FROM (" . $sql . ") as count_table";
    $countStmt = $_db->prepare($countQuery);
    $countStmt->execute($params);
    $totalItems = $countStmt->fetchColumn();
    $totalPages = ceil($totalItems / $itemsPerPage);
} catch (PDOException $e) {
    error_log("Pagination Error: " . $e->getMessage());
}

$sql .= " LIMIT " . (int)$itemsPerPage . " OFFSET " . (int)$offset;

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
                
                <!-- Preserve other filters when submitting -->
                <?php if ($category): ?>
                    <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                <?php endif; ?>
                <?php if ($sort): ?>
                    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                <?php endif; ?>
            </form>
            <a href="/page/products.php">Clear All Filters</a>
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
    
    <?php if ($isBestSeller): ?>
        <div class="top-selling-container">
            <div class="filter-header">
                <h2>Top Selling Products</h2>
                <a href="?<?= http_build_query(array_filter([
                    'search' => $search,
                    'category' => $category,
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
                    'sort' => $sort,
                    'page' => $page
                ])) ?>" class="remove-filter">Show All Products</a>
            </div>
            
            <?php if (!empty($topProducts)): ?>
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
            <?php else: ?>
                <p class="no-products"><?= $noBestSellersMessage ?? 'No best selling products found' ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>


    <!-- <?php if ($isBestSeller && !empty($topProducts)): ?>
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
    <?php endif; ?> -->

    <?php if ($showRegularProducts): ?>
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

        <div class="pagination">
            <?php if ($totalPages > 1): ?>
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?><?= $isBestSeller ? '&filter=best_seller' : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $category ? '&category=' . urlencode($category) : '' ?><?= $sort ? '&sort=' . urlencode($sort) : '' ?><?= isset($_GET['min_price']) ? '&min_price=' . urlencode($_GET['min_price']) : '' ?><?= isset($_GET['max_price']) ? '&max_price=' . urlencode($_GET['max_price']) : '' ?>">&laquo; Previous</a>
                <?php endif; ?>

                <?php 
                $range = 2;
                $startPage = max(1, $page - $range);
                $endPage = min($totalPages, $page + $range);
                
                if ($startPage > 1) {
                    echo "<a href=\"?page=1" . ($search ? '&search=' . urlencode($search) : '') . ($category ? '&category=' . urlencode($category) : '') . ($sort ? '&sort=' . urlencode($sort) : '') . (isset($_GET['min_price']) ? '&min_price=' . urlencode($_GET['min_price']) : '') . (isset($_GET['max_price']) ? '&max_price=' . urlencode($_GET['max_price']) : '') . "\">1</a>";
                    if ($startPage > 2) {
                        echo "<span class=\"ellipsis\">...</span>";
                    }
                }

                for ($i = $startPage; $i <= $endPage; $i++) {
                    $params = [
                        'page' => $i,
                        'search' => $search,
                        'category' => $category,
                        'sort' => $sort,
                        'min_price' => $_GET['min_price'] ?? null,
                        'max_price' => $_GET['max_price'] ?? null
                    ];
                    $queryString = http_build_query(array_filter($params));
                    
                    echo '<a href="?' . $queryString . '"';
                    echo ($i == $page) ? ' class="active"' : '';
                    echo '>' . $i . '</a>';
                }

                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) {
                        echo "<span class=\"ellipsis\">...</span>";
                    }
                    $params = [
                        'page' => $totalPages,
                        'search' => $search,
                        'category' => $category,
                        'sort' => $sort,
                        'min_price' => $_GET['min_price'] ?? null,
                        'max_price' => $_GET['max_price'] ?? null
                    ];
                    $queryString = http_build_query(array_filter($params));
                    echo "<a href=\"?$queryString\">$totalPages</a>";
                }
                ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $category ? '&category=' . urlencode($category) : '' ?><?= $sort ? '&sort=' . urlencode($sort) : '' ?><?= isset($_GET['min_price']) ? '&min_price=' . urlencode($_GET['min_price']) : '' ?><?= isset($_GET['max_price']) ? '&max_price=' . urlencode($_GET['max_price']) : '' ?>">Next &raquo;</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
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
    grid-template-columns: repeat(3, 1fr); 
    gap: 20px;
    margin: 0 auto;
    max-width: 1200px;
}

.top-product-item {
    width: 250px;
    height: 350px;
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
    height: 260px;
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

.filter-header {
    display: grid;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.remove-filter {
    background: #eee;
    padding: 5px 10px;
    border-radius: 4px;
    width: 150px;
    text-decoration: none;
    font-size: 14px;
}

.remove-filter:hover {
    background-color: #ddd;
}

.no-products {
    text-align: center;
    padding: 20px;
    color: #666;
}

</style>

<?php
include '../_foot.php';
?>