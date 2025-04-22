<?php
require_once '../_base.php';
include '../_head.php';

auth('admin');

$_adminContext = true;

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

?>

    <main class="admin-main">
        <?php
            try {
                $stmt = $_db->query("SELECT cust_id, cust_name, cust_contact, cust_email, cust_gender
                                    FROM customer 
                                    ORDER BY cust_id ASC");
                $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo "Database error: " . $e->getMessage();
                exit;
            }
            ?>
            <h2>Customers List</h2>

            <?php if (isset($_SESSION['message'])) : ?>
                <div class="message <?= $_SESSION['message_type'] ?>">
                    <?= $_SESSION['message'] ?>
                </div>
                <?php
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
            <?php endif; ?>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Customer ID</th>
                        <th>Customer Name</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Gender</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($customers) > 0): ?>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?= htmlspecialchars($customer['cust_id']) ?></td>
                                <td><?= htmlspecialchars($customer['cust_name']) ?></td>
                                <td><?= htmlspecialchars($customer['cust_contact']) ?></td>
                                <td><?= htmlspecialchars($customer['cust_email']) ?></td>
                                <td><?= htmlspecialchars($customer['cust_gender']) ?></td>

                                <td class="actions">
                                    <a href="view_customer.php?id=<?= $customer['cust_id'] ?>" class="btn">
                                        <i class="fas fa-eye"></i> 
                                    </a>
                                    <a href="delete_product.php?id=<?= $product['prod_id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">
                                        <i class="fas fa-trash"></i>    
                                    </a>    
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No customers found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
    </main>
