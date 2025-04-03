<?php
require_once '../_base.php';
include '../_head.php';

auth('admin');

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

?>

    <div class="admin-container">

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
                <h2>Category List</h2>

                <?php if (isset($_SESSION['message'])) : ?>
                    <div class="message <?= $_SESSION['message_type'] ?>">
                        <?= $_SESSION['message'] ?>
                    </div>
                    <?php
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                    ?>
                <?php endif; ?>

                <table class="table">
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
                                        <button data-get="view_category.php?id=<?= $category['cat_id'] ?>" class="btn btn-sm">View</button>
                                        <button data-get="edit_category.php?id=<?= $category['cat_id'] ?>" class="btn btn-sm btn-secondary">Edit</button>
                                        <button data-get="delete_category.php?id=<?= $category['cat_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this category?')">Delete</button>
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
        </main>
    </div>
