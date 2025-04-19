<?php
require_once '../_base.php';
include '../_head.php';

auth('admin');

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

?>

        <main class="admin-main">
            <?php
                try {
                    $stmt = $_db->query("SELECT cat_id, cat_name, material_type
                                        FROM category 
                                        ORDER BY cat_id ASC");
                    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    echo "Database error: " . $e->getMessage();
                    exit;
                }
                ?>
                <div class="admin-title">
                    <h2>Category List</h2>
                    <div class="button-group">
                        <button id="openAddProductModal" class="category-btn">
                            <i class="fas fa-plus"></i> Add Category
                        </button>
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
                
        </main>
    </div>

    <div id="addProductModal" class="modal">
        <div class="admin-modal-content">
            <div class="modal-header">
                <h1>Add New Category</h1>
                <span class="close">&times;</span>
            </div>
            <div class="modal-mody">
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
                        <button id="close-modal" type="button" class="admin-submit-btn secondary">Cancel</button>
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
