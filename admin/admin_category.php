<?php
require_once '../_base.php';
include '../_head.php';

auth('admin');

$_adminContext = true;

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// WHERE clause for searching
$where = [];
$params = [];

if (!empty($searchTerm)) {
    $where[] = "(cat_name LIKE ? OR cat_id LIKE ?)";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
}

$whereClause = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

try {
    $countQuery = "SELECT COUNT(*) FROM category" . $whereClause;
    $stmt = $_db->prepare($countQuery);
    $stmt->execute($params);
    $totalItems = $stmt->fetchColumn();
    $totalPages = ceil($totalItems / $itemsPerPage);
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}

try {
    $query = "SELECT cat_id, cat_name, material_type 
             FROM category" 
             . $whereClause .
             " ORDER BY cat_id ASC" . 
             " LIMIT " . $itemsPerPage . " OFFSET " . $offset;
             
    $stmt = $_db->prepare($query);
    $stmt->execute($params);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

?>

        <main class="admin-main">
                <div class="admin-title">
                    <h2>Category List</h2>
                    <div class="button-group">
                        <button id="openAddProductModal" class="category-btn">
                            <i class="fas fa-plus"></i> Add Category
                        </button>
                    </div>
                </div>

                <!-- Search and pagination info -->
                <div class="filter-section">
                    <form action="admin_category.php" method="GET" class="filter-form">
                        <div class="filter-row">
                            <div class="search-box">
                                <input type="text" name="search" placeholder="Search by ID or name" value="<?= htmlspecialchars($searchTerm) ?>">
                                <button type="submit" class="search-btn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            
                            <div class="filter-buttons">
                                <button type="submit" class="filter-btn">Search</button>
                                <a href="admin_category.php" class="admin-submit-btn secondary">Clear</a>
                            </div>
                        </div>
                        <!-- Store current page in hidden field -->
                        <input type="hidden" name="page" value="1">
                    </form>
                </div>

                <div class="product-info-bar">
                    <div class="product-count">
                        Showing <?= count($categories) ?> of <?= $totalItems ?> categories
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

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Material Types</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($categories) > 0): ?>
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($category['cat_id']) ?></td>
                                        <td><?= htmlspecialchars($category['cat_name']) ?></td>
                                        <td><?= htmlspecialchars($category['material_type'], 2) ?></td>
                                        <td class="actions">
                                            <a href="view_category.php?id=<?= $category['cat_id'] ?>" class="btn">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a class="btn btn-danger delete-product" 
                                            data-id="<?= $category['cat_id'] ?>"
                                            data-name="<?= htmlspecialchars($category['cat_name']) ?>">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No categories found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($searchTerm) ?>">&laquo; Previous</a>
                    <?php endif; ?>
                    
                    <?php
                    // Determine range of page numbers to show
                    $range = 2; // Show 2 pages before and after current page
                    $startPage = max(1, $page - $range);
                    $endPage = min($totalPages, $page + $range);
                    
                    // Always show first page
                    if ($startPage > 1) {
                        echo "<a href=\"?page=1&search=" . urlencode($searchTerm) . "\">1</a>";
                        if ($startPage > 2) {
                            echo "<span class=\"ellipsis\">...</span>";
                        }
                    }
                    
                    // Show page numbers with current page highlighted
                    for ($i = $startPage; $i <= $endPage; $i++) {
                        echo '<a href="?page=' . $i . '&search=' . urlencode($searchTerm) . '"';
                        echo ($i == $page) ? ' class="active"' : '';
                        echo '>' . $i . '</a>';
                    }
                    
                    // Always show last page
                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) {
                            echo "<span class=\"ellipsis\">...</span>";
                        }
                        echo "<a href=\"?page=$totalPages&search=" . urlencode($searchTerm) . "\">$totalPages</a>";
                    }
                    ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($searchTerm) ?>">Next &raquo;</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
        </main>
    </div>

    <div id="addProductModal" class="modal">
        <div class="admin-modal-content">
            <div class="modal-header">
                <h1>Add New Category</h1>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form action="add_category.php" method="POST" enctype="multipart/form-data" class="product-form">
                    <div class="form-group">
                        <label for="category_name">Category Name</label>   
                        <input type="text" id="category_name" name="category_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="material_type">Material Types</label>
                        <input type="text" id="material_type" name="material_type" class="form-control" required>
                    </div>
                    <div class="form-group btn">
                        <button type="submit" class="admin-submit-btn">Add Category</button>
                        <button id="close-modal" type="button" class="admin-submit-btn secondary close-modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div id="deleteProductModal" class="modal">
        <div class="admin-modal-content">
            <div class="modal-header">
                <h2>Delete Category</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the category <strong><?= htmlspecialchars($category['cat_name']) ?></strong>?</p>
                <p>This action cannot be undone.</p>
                <form action="delete_category.php" method="POST" id="deleteProductForm">
                    <input type="hidden" name="cat_id" id="cat_id" value="<?= htmlspecialchars($category['cat_id']) ?>">

                    <div class="form-group btn">
                        <button type="submit" class="admin-submit-btn danger">Delete</button>
                        <button type="button" class="admin-submit-btn secondary close-modal">Cancel</button>   
                    </div>
                </form>
            </div>
        </div>
    </div>
