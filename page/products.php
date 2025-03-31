<?php
require '../_base.php';
//-----------------------------------------------------------------------------

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
$_title = '';
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
<div id="product-modal" class="modal">
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
                </div>
                <div class="add-or-cancel">
                <button type="submit" name="add_to_cart" onclick="addToCart()" class="add-to-cart">Add to Cart</button>
                    <!-- <button class="cancel">Cancel</button> -->
                </div>
            </div>
        </div>
    </div>
</div>

<form action="add_to_cart.php" method="post">
    <input type="hidden" name="product_id" value="<?= $s->id ?>">
    <input type="hidden" name="product_name" value="<?= htmlspecialchars($s->prod_name) ?>">
    <input type="hidden" name="product_price" value="<?= number_format($s->price, 2) ?>">
    <input type="hidden" name="product_image" value="<?= htmlspecialchars($s->image) ?>">
    <button type="submit" class="add-to-cart">Add to Cart</button>
</form>

<?php
include '../_foot.php';