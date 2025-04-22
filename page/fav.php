<?php
// session_start();
require '../_base.php';
//-----------------------------------------------------------------------------

// For testing - hardcode user C0001
$testUserId = "C0001";
$favorites = [];

try {
    // Get all favorite products for this user
    $stmt = $_db->prepare("
        SELECT p.*, pf.added_at 
        FROM product p
        JOIN product_favorites pf ON p.prod_id = pf.prod_id
        WHERE pf.cust_id = ?
        ORDER BY pf.added_at DESC
    ");
    $stmt->execute([$testUserId]);
    $favorites = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Favorites page error: " . $e->getMessage());
}

// ----------------------------------------------------------------------------
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
                            <button class="favorite-btn active" data-product-id="<?= htmlspecialchars($product->prod_id) ?>">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                        <a class="product" href="products.php?id=<?= htmlspecialchars($product->prod_id) ?>&category=<?= htmlspecialchars($product->cat_id) ?>"
                            data-id="<?= htmlspecialchars($product->prod_id) ?>"
                            data-name="<?= htmlspecialchars($product->prod_name) ?>" 
                            data-desc="<?= htmlspecialchars($product->prod_desc) ?>" 
                            data-price="<?= number_format($product->price, 2) ?>" 
                            data-image="<?= htmlspecialchars($product->image) ?>"
                            data-cat-id="<?= htmlspecialchars($product->cat_id) ?>">
                            
                            <img class="product-image" src="/images/product_img/<?= htmlspecialchars($product->image) ?>" alt="<?= htmlspecialchars($product->prod_name) ?>">
                            <div class="prod-description">
                                <p><?= htmlspecialchars($product->prod_name) ?></p>
                            </div>
                            <div class="prod-price">
                                <span class="price">RM <?= number_format($product->price, 2) ?></span>
                                <span class="view-details">View Details</span>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Product Detail Pop-up Modal (same as in products.php) -->
<div id="product-modal" class="products-modal">
    <!-- Copy your existing modal code here -->
</div>

<?php
include '../_foot.php';
?>