<?php
// session_start();
require '../_base.php';
//-----------------------------------------------------------------------------

// Handle AJAX requests FIRST
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $response = ['success' => false, 'message' => '', 'cart_count' => 0];
        
        try {
            switch ($_POST['action']) {
                case 'add_to_cart':
                    $productId = $_POST['product_id'];
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
                    
                    // FOR TESTING - Always use database storage
                    $testUserId = "C0001"; // Hardcoded test user ID

                    // Check if test user has an active cart
                    $stmt = $_db->prepare("SELECT cart_id FROM shopping_cart WHERE cust_id = ?");
                    $stmt->execute([$testUserId]);
                    $cart = $stmt->fetch();

                    // Create new cart if doesn't exist
                    if (!$cart) {
                        $stmt = $_db->prepare("INSERT INTO shopping_cart (cust_id, created_at) VALUES (?, NOW())");
                        $stmt->execute([$testUserId]);
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

$arr = $_db->query('SELECT * FROM product')->fetchAll();

// Define known categories
$categories = [
    'CT04' => 'Earrings',
    'CT01' => 'Necklaces',
    'CT02' => 'Bracelets',
    'CT03' => 'Rings'
];

// Sort products by category (Unknown categories go last)
usort($arr, function ($a, $b) use ($categories) {
    $catA = isset($categories[$a->cat_id]) ? array_search($a->cat_id, array_keys($categories)) : count($categories);
    $catB = isset($categories[$b->cat_id]) ? array_search($b->cat_id, array_keys($categories)) : count($categories);
    return $catA <=> $catB;
});

// ----------------------------------------------------------------------------
$_title = 'Products';
include '../_head.php';
?>

<!-- Manual define row & column since database no image yet -->
<div class="product-page-container">
    <div class="row-container">
        <div class="column-container">
            <a class="product" href="products.php">
                <div class="product-container">
                    <img class="product-image" src="/images/product_img/Red_ring1.jpg" alt="Jewelry">
                    <br>
                    <div class="prod-description">
                        <p>Red Ring Red Ring Red Ring Red Ring</p>
                    </div>
                    <div class="prod-price">
                        <span class="price">RM8888.00</span>
                        <span class="view-details">View Details</span>
                    </div>
                </div>  
            </a>
        </div>  
        <div class="column-container">
            <a class="product" href="products.php">
                <div class="product-container">
                    <img class="product-image" src="/images/product_img/Red_ring2.jpg" alt="Jewelry">
                    <br>
                    <div class="prod-description">
                        <p>Red Ring</p>
                    </div>
                    <div class="prod-price">
                        <span class="price">RM8888.00</span>
                        <span class="view-details">View Details</span>
                    </div>
                </div>  
            </a>
        </div>  
        <div class="column-container">
            <a class="product" href="products.php">
                <div class="product-container">
                    <img class="product-image" src="/images/product_img/blue_ring1.jpg" alt="Jewelry">
                    <br>
                    <div class="prod-description">
                        <p>Red Ring</p>
                    </div>
                    <div class="prod-price">
                        <span class="price">RM8888.00</span>
                        <span class="view-details">View Details</span>
                    </div>
                </div>  
            </a>
        </div>  
        <div class="column-container">
            <a class="product" href="products.php">
                <div class="product-container">
                    <img class="product-image" src="/images/product_img/blue_ring2.jpg" alt="Jewelry">
                    <br>
                    <div class="prod-description">
                        <p>Red Ring</p>
                    </div>
                    <div class="prod-price">
                        <span class="price">RM8888.00</span>
                        <span class="view-details">View Details</span>
                    </div>
                </div>  
            </a>
        </div>  
    </div>

    <div class="row-container">
        <div class="column-container">
            <a class="product" href="products.php">
                <div class="product-container">
                    <img class="product-image" src="/images/product_img/heart_ear2.webp" alt="Jewelry">
                    <br>
                    <div class="prod-description">
                        <p>Red Ring</p>
                    </div>
                    <div class="prod-price">
                        <span class="price">RM8888.00</span>
                        <span class="view-details">View Details</span>
                    </div>
                </div>  
            </a>
        </div>  
        <div class="column-container">
            <a class="product" href="products.php">
                <div class="product-container">
                    <img class="product-image" src="/images/product_img/pad_pendant2.webp" alt="Jewelry">
                    <br>
                    <div class="prod-description">
                        <p>Red Ring</p>
                    </div>
                    <div class="prod-price">
                        <span class="price">RM8888.00</span>
                        <span class="view-details">View Details</span>
                    </div>
                </div>  
            </a>
        </div>  
        <div class="column-container">
            <a class="product" href="products.php">
                <div class="product-container">
                    <img class="product-image" src="/images/product_img/perettidiamond_ring2.webp" alt="Jewelry">
                    <br>
                    <div class="prod-description">
                        <p>Red Ring</p>
                    </div>
                    <div class="prod-price">
                        <span class="price">RM8888.00</span>
                        <span class="view-details">View Details</span>
                    </div>
                </div>  
            </a>
        </div>  
        <div class="column-container">
            <a class="product" href="products.php">
                <div class="product-container">
                    <img class="product-image" src="/images/product_img/picassoo_ear3.webp" alt="Jewelry">
                    <br>
                    <div class="prod-description">
                        <p>Red Ring</p>
                    </div>
                    <div class="prod-price">
                        <span class="price">RM8888.00</span>
                        <span class="view-details">View Details</span>
                    </div>
                </div>  
            </a>
        </div>  
    </div>

    <div class="row-container">
        <div class="column-container">
            <a class="product" href="products.php">
                <div class="product-container">
                    <img class="product-image" src="/images/product_img/heart_ear2.webp" alt="Jewelry">
                    <br>
                    <div class="prod-description">
                        <p>Red Ring</p>
                    </div>
                    <div class="prod-price">
                        <span class="price">RM8888.00</span>
                        <span class="view-details">View Details</span>
                    </div>
                </div>  
            </a>
        </div>  
        <div class="column-container">
            <a class="product" href="products.php">
                <div class="product-container">
                    <img class="product-image" src="/images/product_img/pad_pendant2.webp" alt="Jewelry">
                    <br>
                    <div class="prod-description">
                        <p>Red Ring Red Ring Red Ring Red Ring Red Ring</p>
                    </div>
                    <div class="prod-price">
                        <span class="price">RM8888.00</span>
                        <span class="view-details">View Details</span>
                    </div>
                </div>  
            </a>
        </div>  
        <div class="column-container">
            <a class="product" href="products.php">
                <div class="product-container">
                    <img class="product-image" src="/images/product_img/perettidiamond_ring2.webp" alt="Jewelry">
                    <br>
                    <div class="prod-description">
                        <p>Red Ring</p>
                    </div>
                    <div class="prod-price">
                        <span class="price">RM8888.00</span>
                        <span class="view-details">View Details</span>
                    </div>
                </div>  
            </a>
        </div>  
    </div>
<!-- </div> -->


<!-- For data retrieval from database to display product by row & column-->
<!-- <div class="product-page-container"> -->
    <div class="row-container">
        <?php 
        $columnCount = 0;
        foreach ($arr as $s): 
            if ($columnCount % 4 == 0 && $columnCount > 0): ?>
                </div><div class="row-container">
            <?php endif; ?>

            <div class="column-container">
                <a class="product" href="products.php?id=<?= $s->id ?>"
                    data-id="<?= $productId ?>"
                    data-name="<?= htmlspecialchars($s->prod_name) ?>" 
                    data-desc="<?= htmlspecialchars($s->prod_desc) ?>" 
                    data-price="<?= number_format($s->price, 2) ?>" 
                    data-image="<?= htmlspecialchars($s->image) ?>"
                    data-cat-id="<?= htmlspecialchars($s->cat_id) ?>">
                    
                    <div class="product-container">
                        <img class="product-image" src="/images/product_img/<?= htmlspecialchars($s->image) ?>" alt="<?= htmlspecialchars($s->prod_name) ?>">
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
<!-- </div> -->


<!-- <div class="product-page-container"> -->
    <div class="row-container">
        <?php 
        $columnCount = 0;
        $firstProduct = []; // Track the first product of each category

        foreach ($arr as $s): 
            // Check if this is the first product of its category
            $productId = htmlspecialchars($s->prod_id);
            $categoryId = htmlspecialchars($s->cat_id);

            if (!isset($firstProduct[$categoryId])) {
                $firstProduct[$categoryId] = true;
                    echo '<div class="category-spacer" id="cat-' . $categoryId . '"></div>'; // Spacer added
                $idAttribute = 'id="cat-' . $categoryId . '"'; // Assign ID only to the first product
            } else {
                $idAttribute = ''; // No ID for other products
            }

            if ($columnCount % 4 == 0 && $columnCount > 0): ?>
                </div><div class="row-container">
            <?php endif; ?>

            <div class="column-container">
                <a class="product" href="products.php?id=<?= $productId ?>&category=<?= $categoryId ?>"
                    data-id="<?= $productId ?>"
                    <?= $idAttribute ?>
                    data-name="<?= htmlspecialchars($s->prod_name) ?>" 
                    data-desc="<?= htmlspecialchars($s->prod_desc) ?>" 
                    data-price="<?= number_format($s->price, 2) ?>" 
                    data-image="<?= htmlspecialchars($s->image) ?>"
                    data-cat-id="<?= $categoryId ?>">
                    
                    <div class="product-container">
                        <img class="product-image" src="/images/product_img/<?= htmlspecialchars($s->image) ?>" alt="<?= htmlspecialchars($s->prod_name) ?>">
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
                    <img class="preview active" src="/images/product_img/blue_ring1.jpg" alt="Preview 1" onclick="changeImage(this, '/images/product_img/blue_ring1.jpg')">
                    <img class="preview" src="/images/product_img/Red_ring2.jpg" alt="Preview 2" onclick="changeImage(this, '/images/product_img/Red_ring2.jpg')">
                    <img class="preview" src="/images/product_img/heart_ear2.webp" alt="Preview 3" onclick="changeImage(this, '/images/product_img/heart_ear2.webp')">
                </div>
            </div>

            <div class="product-detail-button">
                <div class="product-detail">
                    <h2 id="modal-name"></h2>
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
            </div>
        </div>
    </div>
</div>

<?php
include '../_foot.php';