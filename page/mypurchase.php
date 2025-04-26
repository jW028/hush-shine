<?php
require '../_base.php';

// Check if user is logged in
if (!isset($_SESSION['cust_id']) || empty($_SESSION['cust_id'])) {
    header("Location: ../page/login.php");
    exit();
}

$custId = $_SESSION['cust_id'];

// Fetch order statistics
try {
    $statsStmt = $_db->prepare("
        SELECT 
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending_orders,
            SUM(CASE WHEN status IN ('Confirmed', 'Processing', 'Shipped', 'Delivered') THEN 1 ELSE 0 END) AS order_in_process,
            SUM(CASE WHEN status = 'Received' THEN 1 ELSE 0 END) AS completed_orders,
            SUM(CASE WHEN status IN ('Approved', 'Rejected', 'Request Pending') AS refunded_orders
        FROM orders
        WHERE cust_id = ?
    ");
    $statsStmt->execute([$custId]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Order Statistics Error: " . $e->getMessage());
    $stats = ['pending_orders' => 0, 'order_in_process' => 0, 'completed_orders' => 0, 'refunded_orders' => 0];
}

// Fetch orders with filters
$status_filter = $_GET['status'] ?? 'all';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';

$baseQuery = "FROM orders WHERE cust_id = ?";
$params = [$custId];

if ($status_filter === 'Refunded') {
    $baseQuery .= " AND status IN ('Request Pending', 'Approved', 'Rejected')";
} elseif ($status_filter === 'OrderInProcess') {
    $baseQuery .= " AND status IN ('Confirmed', 'Processing', 'Shipped', 'Delivered')";
} elseif ($status_filter !== 'all') {
    $baseQuery .= " AND status = ?";
    $params[] = $status_filter;
}

if (!empty($date_from)) {
    $baseQuery .= " AND DATE(order_date) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $baseQuery .= " AND DATE(order_date) <= ?";
    $params[] = $date_to;
}

if (!empty($search)) {
    $baseQuery .= " AND order_id LIKE ?";
    $params[] = "%$search%";
}

// Count total orders for pagination
try {
    $countQuery = "SELECT COUNT(*) " . $baseQuery;
    $stmt = $_db->prepare($countQuery);
    $stmt->execute($params);
    $totalItems = $stmt->fetchColumn();
    $itemsPerPage = 10;
    $totalPages = ceil($totalItems / $itemsPerPage);
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $offset = ($page - 1) * $itemsPerPage;
} catch (Exception $e) {
    error_log("Order Count Error: " . $e->getMessage());
    $totalItems = 0;
    $totalPages = 1;
    $page = 1;
    $offset = 0;
}

// Fetch paginated orders
try {
    $query = "SELECT * " . $baseQuery . " ORDER BY order_date DESC LIMIT $itemsPerPage OFFSET $offset";
    $stmt = $_db->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Order Fetch Error: " . $e->getMessage());
    $orders = [];
}

$_title = 'My Purchases';
include '../_head.php';
?>

<div class="mypurchase-page">
    <div class="purchase-stats-tabs">
        <a href="mypurchase.php?status=Pending" class="purchase-stat-tab <?= $status_filter === 'Pending' ? 'active' : '' ?>">
            <div class="purchase-stat-label">Pending</div>
        </a>
        <a href="mypurchase.php?status=OrderInProcess" class="purchase-stat-tab <?= $status_filter === 'OrderInProcess' ? 'active' : '' ?>">
            <div class="purchase-stat-label">Order in Process</div>
        </a>
        <a href="mypurchase.php?status=Received" class="purchase-stat-tab <?= $status_filter === 'Received' ? 'active' : '' ?>">
            <div class="purchase-stat-label">Completed</div>
        </a>
        <a href="mypurchase.php?status=Refunded" class="purchase-stat-tab <?= $status_filter === 'Refunded' ? 'active' : '' ?>">
            <div class="purchase-stat-label">Return/Refund</div>
        </a>
    </div>

    <div class="purchase-filter-section">
        <form action="mypurchase.php" method="GET" class="purchase-filter-form">
            <div class="purchase-date-range-group">
                <div class="purchase-date-input">
                    <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($date_from) ?>" class="purchase-date-control" placeholder="From">
                </div>
                <span class="purchase-date-separator">to</span>
                <div class="purchase-date-input">
                    <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($date_to) ?>" class="purchase-date-control" placeholder="To">
                </div>
            </div>

            <div class="purchase-search-box">
                <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search orders...">
                <button type="submit" class="purchase-search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <div class="purchase-filter-buttons">
                <button type="submit" class="purchase-filter-btn">Apply</button>
                <a href="mypurchase.php" class="purchase-filter-btn secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="purchase-order-cards">
        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $order): ?>
                <?php
                // Fetch the number of items in the order
                $itemStmt = $_db->prepare("
                   SELECT p.image AS image_url, COUNT(*) AS item_count
                    FROM order_items oi
                    INNER JOIN product p ON oi.prod_id = p.prod_id
                    WHERE oi.order_id = ?
                    GROUP BY p.image  
                ");
                $itemStmt->execute([$order['order_id']]);
                $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
                $item_count = array_sum(array_column($items, 'item_count'));
                ?>
                <div class="purchase-order-card">
                    <div class="purchase-order-card-header">
                        <h3>Order #<?= $order['order_id'] ?></h3>
                        <span class="purchase-status-badge status-<?= strtolower($order['status']) ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </div>
                    <div class="purchase-order-img">
                        <?php foreach ($items as $item): ?>
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="Product Image" class="product-image">
                        <?php endforeach; ?>
                    </div>
                    <div class="purchase-order-card-body">
                        <p><strong>Date:</strong> <?= date('M j, Y g:i A', strtotime($order['order_date'])) ?></p>
                        <p><strong>Items:</strong> <?= $item_count ?> item(s)</p>
                        <p><strong>Total:</strong> RM <?= number_format($order['total_amount'], 2) ?></p>
                    </div>

                    <div class="purchase-order-card-footer">
                        <a href="cust_viewOrder.php?id=<?= $order['order_id'] ?>" class="purchase-btn purchase-btn-primary">View Order</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="purchase-no-results">No orders found matching your criteria.</p>
        <?php endif; ?>
    </div>    
    
    <div class="pagination-info">
        <p>Showing <?= $offset + 1 ?> to <?= min($offset + $itemsPerPage, $totalItems) ?> of <?= number_format($totalItems) ?> orders</p>
        <p class="pagination-info">Page <?= $page ?> of <?= number_format($totalPages) ?></p>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&status=<?= urlencode($status_filter) ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>&search=<?= urlencode($search) ?>">&laquo; Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>&status=<?= urlencode($status_filter) ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>&search=<?= urlencode($search) ?>" <?= $i === $page ? 'class="active"' : '' ?>><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>&status=<?= urlencode($status_filter) ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>&search=<?= urlencode($search) ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php include '../_foot.php'; ?>
