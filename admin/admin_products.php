<?php
require_once '../_base.php';

auth('admin');

$_title = 'Products';
include '../_head.php';

$_adminContext = true;

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$itemsPerPage = 10; // Products per page
$offset = ($page - 1) * $itemsPerPage;

// Search and filter parameters
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$stockFilter = isset($_GET['stock_filter']) ? $_GET['stock_filter'] : '';

// Building WHERE clause for filtering
$where = [];
$params = [];

// Search by product name or ID
if (!empty($searchTerm)) {
    $where[] = "(p.prod_name LIKE ? OR p.prod_id LIKE ?)";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
}

// Category filter
if (!empty($categoryFilter)) {
    $where[] = "p.cat_id = ?";
    $params[] = $categoryFilter;
}

if (!empty($stockFilter)) {
    if ($stockFilter == 'low') {
        $where[] = "p.quantity <= ? AND p.quantity > 0";
        $params[] = LOW_STOCK_THRESHOLD;
    } else if ($stockFiler === 'out') {
        $where[] = "p.quantity = 0";
    }
}

// Combine WHERE clauses
$whereClause = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

// Sorting options
$orderBy = match($sortBy) {
    'price_high' => 'p.price DESC',
    'price_low' => 'p.price ASC',
    'stock_high' => 'p.quantity DESC',
    'stock_low' => 'p.quantity ASC',
    'name_asc' => 'p.prod_name ASC',
    'name_desc' => 'p.prod_name DESC',
    'oldest' => 'p.prod_id ASC',
    default => 'p.prod_id DESC'  // newest first (default)
};

// Count total products for pagination
try {
    $countQuery = "SELECT COUNT(*) FROM product p" . $whereClause;
    $stmt = $_db->prepare($countQuery);
    $stmt->execute($params);
    $totalItems = $stmt->fetchColumn();
    $totalPages = ceil($totalItems / $itemsPerPage);
    echo $totalPages;
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}

// Get paginated products
try {
    $query = "SELECT p.prod_id, p.prod_name, p.prod_desc, p.price, p.quantity, p.cat_id as category, p.image
             FROM product p 
             LEFT JOIN category c ON p.cat_id = c.cat_id"
             . $whereClause .
             " ORDER BY " . $orderBy . 
             " LIMIT " . $itemsPerPage . " OFFSET " . $offset;
             
    $stmt = $_db->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}

// Get categories for filter dropdown
try {
    $stmt = $_db->query("SELECT * FROM category ORDER BY cat_name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error loading categories: " . $e->getMessage();
}

?>
            <div class="admin-main">
                <div class="admin-title">
                    <h2>Product List</h2>   
                    <div class="button-group">
                        <button id="openAddProductModal" class="category-btn">
                            <i class="fas fa-plus"></i> Add Product
                        </button>
                    </div>
                </div>

                <div class="filter-section">
                    <form action="admin_products.php" method="GET" class="filter-form">
                        <div class="filter-row">
                            <div class="search-box">
                                <input type="text" name="search" placeholder="Search by ID or name" value="<?= htmlspecialchars($searchTerm) ?>">
                                <button type="submit" class="search-btn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            
                            <div class="filter-group">
                                <select name="category" class="filter-select">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['cat_id'] ?>" <?= $categoryFilter == $category['cat_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['cat_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <select name="sort" class="filter-select">
                                    <option value="newest" <?= $sortBy == 'newest' ? 'selected' : '' ?>>Newest First</option>
                                    <option value="oldest" <?= $sortBy == 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                                    <option value="price_high" <?= $sortBy == 'price_high' ? 'selected' : '' ?>>Price (High to Low)</option>
                                    <option value="price_low" <?= $sortBy == 'price_low' ? 'selected' : '' ?>>Price (Low to High)</option>
                                    <option value="stock_high" <?= $sortBy == 'stock_high' ? 'selected' : '' ?>>Stock (High to Low)</option>
                                    <option value="stock_low" <?= $sortBy == 'stock_low' ? 'selected' : '' ?>>Stock (Low to High)</option>
                                    <option value="name_asc" <?= $sortBy == 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                                    <option value="name_desc" <?= $sortBy == 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                                </select>
                            </div>

                            <div class="filter-group">
                                <label for="stock-filter" class="filter-label">Stock Status:</label>
                                <select name="stock_filter" id="stock-filter" class="filter-select">
                                    <option value="" <?= !isset($_GET['stock_filter']) ? 'selected' : '' ?>>All Products</option>
                                    <option value="low" <?= isset($_GET['stock_filter']) && $_GET['stock_filter'] === 'low' ? 'selected' : '' ?>>
                                        Low Stock (≤ <?= LOW_STOCK_THRESHOLD ?>)
                                    </option>
                                    <option value="out" <?= isset($_GET['stock_filter']) && $_GET['stock_filter'] === 'out' ? 'selected' : '' ?>>
                                        Out of Stock
                                    </option>
                                </select>
                            </div>
                            
                            <div class="filter-buttons">
                                <button type="submit" class="filter-btn">Apply Filters</button>
                                <a href="admin_products.php" class="admin-submit-btn secondary">Clear</a>
                            </div>
                        </div>

                        <!-- Store current page in hidden field -->
                        <input type="hidden" name="page" value="1">
                    </form>
                </div>
                    <?php
                        // Get stock summary counts
                        try {
                            $lowStockQuery = $_db->prepare("SELECT COUNT(*) FROM product WHERE quantity <= ? AND quantity > 0");
                            $lowStockQuery->execute([LOW_STOCK_THRESHOLD]);
                            $lowStockCount = $lowStockQuery->fetchColumn();
                            
                            $outOfStockQuery = $_db->query("SELECT COUNT(*) FROM product WHERE quantity = 0");
                            $outOfStockCount = $outOfStockQuery->fetchColumn();
                        } catch (PDOException $e) {
                            $lowStockCount = 0;
                            $outOfStockCount = 0;
                        }

                        // Only show the summary if there are low stock or out of stock items
                        if ($lowStockCount > 0 || $outOfStockCount > 0):
                        ?>
                        <div class="stock-summary">
                            <h3><i class="fas fa-chart-bar"></i> Inventory Status</h3>
                            <div class="stock-alert">
                                <?php if ($outOfStockCount > 0): ?>
                                    <div class="stock-alert-item critical-alert">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span class="stock-alert-count"><?= $outOfStockCount ?></span> products out of stock
                                        <a href="admin_products.php?stock_filter=out" class="btn btn-sm btn-outline-danger">View All</a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($lowStockCount > 0): ?>
                                    <div class="stock-alert-item warning-alert">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <span class="stock-alert-count"><?= $lowStockCount ?></span> products with low stock
                                        <a href="admin_products.php?stock_filter=low" class="btn btn-sm btn-outline-warning">View All</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                <div class="product-info-bar">
                <div class="product-count">
                    Showing <?= count($products) ?> of <?= $totalItems ?> products
                </div>
            </div>

            <?php if (isset($_SESSION['message'])) : ?>
                <div class="message <?= $_SESSION['message_type'] ?>">
                    <?= $_SESSION['message'] ?>
                </div>
                <?php
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['message'])) : ?>
                    <div class="message <?= $_SESSION['message_type'] ?>">
                        <?= $_SESSION['message'] ?>
                    </div>
                    <?php
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                    ?>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Image</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Category</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($products) > 0): ?>
                                <?php foreach ($products as $product): ?>
                                    <?php
                                    $productImage = '../images/no-image.png';
                                    if (!empty($product['image'])) {
                                        $imageData = json_decode($product['image'], true);
                                        if ($imageData) {
                                            $imageFile = is_array($imageData) ? $imageData[0] : $imageData;
                                            $productImage = '../images/products/' . $imageFile;
                                            if (!file_exists($productImage)) {
                                                $productImage = '../images/no-image.png';
                                            }
                                        }
                                    }
                                    $stm = $_db->prepare("SELECT cat_name FROM category WHERE cat_id = ?");
                                    $stm->execute([$product['category']]);
                                    $category_name = $stm->fetchColumn();
                                    if ($category_name === false) {
                                        $category_name = 'Unknown';
                                    }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($product['prod_id']) ?></td>
                                        <td><?= htmlspecialchars($product['prod_name']) ?></td>
                                        <td class="product-thumbnail">
                                            <img src="<?= htmlspecialchars($productImage) ?>" alt="<?= htmlspecialchars($product['prod_name']) ?>">
                                        </td>
                                        <td><?= number_format($product['price'], 2) ?></td>
                                        <td>
                                            <?php if($product['quantity'] <= LOW_STOCK_THRESHOLD): ?>
                                                <span class="stock-badge low-stock" title="Low stock alert">
                                                    <?= htmlspecialchars($product['quantity']) ?>
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                </span>
                                            <?php elseif($product['quantity'] == 0): ?>
                                                <span class="stock-badge out-of-stock">
                                                    <?= htmlspecialchars($product['quantity']) ?>
                                                    <i class="fas fa-times-circle"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="stock-badge in-stock">
                                                    <?= htmlspecialchars($product['quantity']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($category_name) ?></td>
                                        <td class="actions">
                                            <a href="view_product.php?id=<?= $product['prod_id'] ?>" class="btn">
                                                <i class="fas fa-eye"></i> 
                                            </a>
                                            <a class="btn btn-danger delete-product"
                                                data-id="<?= htmlspecialchars($product['prod_id']) ?>"
                                                data-name="<?= htmlspecialchars($product['prod_name']) ?>">
                                                <i class="fas fa-trash"></i>    
                                            </a> 
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No products found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($searchTerm) ?>&category=<?= urlencode($categoryFilter) ?>&sort=<?= urlencode($sortBy) ?>&stock_filter=<?= urlencode($stockFilter) ?>">&laquo; Previous</a>
                <?php endif; ?>

                <?php 
                $range = 2;
                $startPage = max(1, $page - $range);
                $endPage = min($totalPages, $page + $range);
                
                if ($startPage > 1) {
                    echo "<a href=\"?page=1&search=" . urlencode($searchTerm) . "&category=" . urlencode($categoryFilter) . "&sort=" . urlencode($sortBy) . "&stock_filter=" . urlencode($stockFilter) . "\">1</a>";
                    if ($startPage > 2) {
                        echo "<span class=\"ellipsis\">...</span>";
                    }
                }

                for ($i = $startPage; $i <= $endPage; $i++) {
                    echo '<a href="?page=' . $i . '&search=' . urlencode($searchTerm) . '&category=' . urlencode($categoryFilter) . '&sort=' . urlencode($sortBy) . "&stock_filter=" . urlencode($stockFilter) . '"';
                    echo ($i == $page) ? ' class="active"' : '';
                    echo '>' . $i . '</a>';
                }

                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) {
                        echo "<span class=\"ellipsis\">...</span>";
                    }
                    echo "<a href=\"?page=$totalPages&search=" . urlencode($searchTerm) . "&category=" . urlencode($categoryFilter) . "&sort=" . urlencode($sortBy) . "&stock_filter=" . urlencode($stockFilter) . "\">$totalPages</a>";
                }
                ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($searchTerm) ?>&category=<?= urlencode($categoryFilter) ?>&sort=<?= urlencode($sortBy) ?>&stock_filter=<?= urlencode($stockFilter) ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
                </main>
            </div>

    <div id="addProductModal" class="modal">
        <div class="admin-modal-content">
            <div class="modal-header">
                <h1>Add New Product</h1>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">

                <form action="add_product.php" method="POST" enctype="multipart/form-data" class="product-form">
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="5"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (RM)</label>
                        <input type="number" id="price" name="price" class="form-control" min="0" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" min="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category['cat_id']) ?>"> 
                                <?= htmlspecialchars($category['cat_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="images">Product Image</label>
                    <div class="custom-file" id="dropZone">
                        <input type="file" class="custom-file-input hidden" id="images" name="images[]" accept="image/*" multiple>
                        <label class="custom-file-label" for="images">
                            <div class="drop-zone-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Drag & drop images here or click to browse</p>
                                <small class="form-text">Accepted formats: JPG, JPEG, PNG, WEBP. Max size: 2MB</small>
                            </div>
                        </label>
                    </div>
                    <div class="image-previews" id="imagePreview"></div>
                </div>
                
                <div class="image-previews" id="imagePreview"></div>
                
                <div class="form-group btn">
                    <button type="submit" class="admin-submit-btn primary">Add Product</button>
                    <button id="close-modal" type="button" class="admin-submit-btn secondary close-modal">Cancel</button>
                </div>
            </form>
            </div>
        </div>
    </div>

<div id="deleteProductModal" class="modal">
        <div class="admin-modal-content">
            <div class="modal-header">
                <h2>Delete Product</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
            <p>Are you sure you want to delete the product <strong><?= htmlspecialchars($product['prod_name']) ?></strong>?</p>
            <p>This action cannot be undone.</p>
            <form action="delete_product.php" method="POST" id="deleteProductForm">
                <input type="hidden" name="prod_id" id="prod_id" value="<?= htmlspecialchars($product['prod_id']) ?>">

                <div class="form-group btn">
                    <button type="submit" class="admin-submit-btn danger">Delete Product</button>
                    <button type="button" class="admin-submit-btn secondary close-modal">Cancel</button>
                </div>
        </div>
    </div>
    