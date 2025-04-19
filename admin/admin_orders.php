<?php
// filepath: /Users/jw/Documents/hushandshine/admin/admin_orders.php
require_once '../_base.php';

// Ensure user is authorized as admin
auth('admin');

$_title = 'Order Management';
include '../_head.php';

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';

// Base query
$query = "SELECT o.order_id, o.cust_id, c.cust_name, o.order_date, o.total_amount, o.status, o.payment_status 
          FROM orders o 
          LEFT JOIN customer c ON o.cust_id = c.cust_id 
          WHERE 1=1";

// Add filters
$params = [];

if ($status_filter !== 'all') {
    $query .= " AND o.status = ?";
    $params[] = $status_filter;
}

if (!empty($date_from)) {
    $query .= " AND DATE(o.order_date) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $query .= " AND DATE(o.order_date) <= ?";
    $params[] = $date_to;
}

if (!empty($search)) {
    $query .= " AND (o.order_id LIKE ? OR c.cust_name LIKE ? OR c.cust_email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$query .= " ORDER BY o.order_date DESC";

// Execute query
try {
    $stmt = $_db->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get order statistics
    $stmt = $_db->query("SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'Confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
        SUM(CASE WHEN status = 'Shipped' THEN 1 ELSE 0 END) as shipped_orders,
        SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) as delivered_orders,
        SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
        SUM(total_amount) as total_revenue
        FROM orders");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'] ?? '';
    $new_status = $_POST['status'] ?? '';
    
    if (!empty($order_id) && !empty($new_status)) {
        try {
            $stmt = $_db->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
            $stmt->execute([$new_status, $order_id]);
            
            $_SESSION['success'] = "Order #$order_id status updated to $new_status";
            header("Location: admin_orders.php");
            exit;
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating order: " . $e->getMessage();
        }
    }
}

?>

<div class="admin-main">
    <div class="admin-title">
        <h1>Order Management</h1>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-value"><?= number_format($stats['total_orders'] ?? 0) ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card pending">
            <div class="stat-value"><?= number_format($stats['pending_orders'] ?? 0) ?></div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card confirmed">
            <div class="stat-value"><?= number_format($stats['confirmed_orders'] ?? 0) ?></div>
            <div class="stat-label">Confirmed</div>
        </div>
        <div class="stat-card shipped">
            <div class="stat-value"><?= number_format($stats['shipped_orders'] ?? 0) ?></div>
            <div class="stat-label">Shipped</div>
        </div>
        <div class="stat-card delivered">
            <div class="stat-value"><?= number_format($stats['delivered_orders'] ?? 0) ?></div>
            <div class="stat-label">Delivered</div>
        </div>
        <div class="stat-card revenue">
            <div class="stat-value">RM <?= number_format($stats['total_revenue'] ?? 0, 2) ?></div>
            <div class="stat-label">Revenue</div>
        </div>
    </div>
    
    <!-- Filter Form -->
    <div class="filter-section">
        <form action="admin_orders.php" method="GET" class="filter-form">
            <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" name="status" class="form-control">
                    <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Confirmed" <?= $status_filter === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="Shipped" <?= $status_filter === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="Delivered" <?= $status_filter === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="Cancelled" <?= $status_filter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="date_from">Date From:</label>
                <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($date_from) ?>" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="date_to">Date To:</label>
                <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($date_to) ?>" class="form-control">
            </div>
            
            <div class="form-group search-group">
                <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search order #, customer..." class="form-control">
                <button type="submit" class="admin-submit-btn">Filter</button>
                <a href="admin_orders.php" class="admin-submit-btn secondary">Reset</a>
            </div>
        </form>
    </div>
    
    <!-- Orders Table -->
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                        <?php 
                        // Get order items count
                        $stmt = $_db->prepare("SELECT COUNT(*) as item_count FROM order_items WHERE order_id = ?");
                        $stmt->execute([$order['order_id']]);
                        $item_count = $stmt->fetch(PDO::FETCH_ASSOC)['item_count'];
                        ?>
                        <tr>
                            <td><a href="#" class="order-link" data-order="<?= $order['order_id'] ?>">#<?= $order['order_id'] ?></a></td>
                            <td><?= htmlspecialchars($order['cust_name'] ?? 'Unknown') ?> (<?= htmlspecialchars($order['cust_id']) ?>)</td>
                            <td><?= date('M j, Y g:i A', strtotime($order['order_date'])) ?></td>
                            <td><?= $item_count ?> item(s)</td>
                            <td>RM <?= number_format($order['total_amount'], 2) ?></td>
                            <td>
                                <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                    <?= $order['status'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="payment-status <?= strtolower($order['payment_status']) ?>">
                                    <?= $order['payment_status'] ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="view_order.php?id=<?= $order['order_id'] ?>" class="btn btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="print_order.php?id=<?= $order['order_id'] ?>" class="btn btn-sm btn-info" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="no-results">No orders found matching your criteria</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>