<?php
require '../_base.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

try {
    // Get all orders for the user
    $stmt = $_db->prepare("
        SELECT o.*, sp.payment_intent_id, sp.status as payment_status
        FROM orders o
        LEFT JOIN stripe_payments sp ON o.order_id = sp.order_id
        WHERE o.cust_id = ?
        ORDER BY o.order_date DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get order items for each order
    $itemStmt = $_db->prepare("
        SELECT oi.*, p.prod_name, p.image
        FROM order_items oi
        JOIN product p ON oi.prod_id = p.prod_id
        WHERE oi.order_id = ?
    ");

} catch (Exception $e) {
    error_log("Order History Error: " . $e->getMessage());
    $orders = [];
}

$_title = 'Order History';
include '../_head.php';
?>

<div class="order-history-page">
    <h1>Order History</h1>

    <?php if (empty($orders)): ?>
        <div class="no-orders">
            <p>You haven't placed any orders yet.</p>
            <a href="../index.php" class="btn-shop">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <h3>Order #<?= $order['order_id'] ?></h3>
                            <p>Placed on <?= date('F j, Y', strtotime($order['order_date'])) ?></p>
                        </div>
                        <div class="order-status">
                            <span class="status-badge <?= strtolower($order['status']) ?>">
                                <?= htmlspecialchars($order['status']) ?>
                            </span>
                            <?php if ($order['payment_status']): ?>
                                <span class="payment-badge <?= strtolower($order['payment_status']) ?>">
                                    Payment: <?= htmlspecialchars($order['payment_status']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="order-items">
                        <?php
                        $itemStmt->execute([$order['order_id']]);
                        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($items as $item):
                            $images = json_decode($item['image'], true);
                            $firstImage = $images[0] ?? 'default.jpg';
                        ?>
                            <div class="order-item">
                                <img src="/images/prod_img/<?= htmlspecialchars($firstImage) ?>" 
                                     alt="<?= htmlspecialchars($item['prod_name']) ?>">
                                <div class="item-details">
                                    <h4><?= htmlspecialchars($item['prod_name']) ?></h4>
                                    <p>Quantity: <?= $item['quantity'] ?></p>
                                    <p>Price: RM <?= number_format($item['price'], 2) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-footer">
                        <div class="order-total">
                            <span>Total:</span>
                            <span>RM <?= number_format($order['total_amount'], 2) ?></span>
                        </div>
                        <?php if ($order['status'] === 'Processing'): ?>
                            <a href="track_order.php?id=<?= $order['order_id'] ?>" 
                               class="btn-track">Track Order</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.order-history-page {
    max-width: 1200px;
    margin: 150px auto 50px;
    padding: 0 20px;
}

.order-history-page h1 {
    margin-bottom: 30px;
    color: #333;
}

.no-orders {
    text-align: center;
    padding: 50px;
    background: #f9f9f9;
    border-radius: 8px;
}

.btn-shop {
    display: inline-block;
    background: #4CAF50;
    color: white;
    padding: 12px 24px;
    border-radius: 4px;
    text-decoration: none;
    margin-top: 20px;
}

.order-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    margin-bottom: 30px;
    overflow: hidden;
}

.order-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.order-info h3 {
    margin: 0;
    color: #333;
}

.order-info p {
    color: #666;
    margin: 5px 0 0;
}

.status-badge,
.payment-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.9em;
    display: inline-block;
    margin-left: 10px;
}

.status-badge.processing {
    background: #e3f2fd;
    color: #1976d2;
}

.status-badge.confirmed {
    background: #e8f5e9;
    color: #2e7d32;
}

.status-badge.shipped {
    background: #fff3e0;
    color: #f57c00;
}

.payment-badge.succeeded {
    background: #e8f5e9;
    color: #2e7d32;
}

.payment-badge.pending {
    background: #fff3e0;
    color: #f57c00;
}

.order-items {
    padding: 20px;
}

.order-item {
    display: flex;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.order-item:last-child {
    border-bottom: none;
}

.order-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 4px;
}

.item-details h4 {
    margin: 0 0 10px;
    color: #333;
}

.item-details p {
    color: #666;
    margin: 5px 0;
}

.order-footer {
    padding: 20px;
    background: #f9f9f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.order-total {
    font-size: 1.2em;
    font-weight: bold;
    color: #333;
}

.btn-track {
    background: #4CAF50;
    color: white;
    padding: 8px 16px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 0.9em;
}

@media (max-width: 768px) {
    .order-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .order-status {
        margin-top: 10px;
    }

    .status-badge,
    .payment-badge {
        margin: 5px 5px 5px 0;
    }

    .order-footer {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
}
</style>

<?php include '../_foot.php'; ?>