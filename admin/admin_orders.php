<?php
// filepath: /Users/jw/Documents/hushandshine/admin/admin_orders.php
require_once '../_base.php';

// Ensure user is authorized as admin
auth('admin');

$_title = 'Order Management';
include '../_head.php';

$_adminContext = true;

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$itemsPerPage = 15; // Orders per page
$offset = ($page - 1) * $itemsPerPage;

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';
$payment_filter = $_GET['payment'] ?? 'all';

// Base query
$baseQuery = "FROM orders o 
             LEFT JOIN customer c ON o.cust_id = c.cust_id 
             WHERE 1=1";

// Add filters
$params = [];

if ($status_filter !== 'all') {
    $baseQuery .= " AND o.status = ?";
    $params[] = $status_filter;
}

if ($payment_filter !== 'all') {
    $baseQuery .= " AND o.payment_status = ?";
    $params[] = $payment_filter;
}

if (!empty($date_from)) {
    $baseQuery .= " AND DATE(o.order_date) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $baseQuery .= " AND DATE(o.order_date) <= ?";
    $params[] = $date_to;
}

if (!empty($search)) {
    $baseQuery .= " AND (o.order_id LIKE ? OR c.cust_name LIKE ? OR c.cust_email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

// Count total orders for pagination
try {
    $countQuery = "SELECT COUNT(*) " . $baseQuery;
    $stmt = $_db->prepare($countQuery);
    $stmt->execute($params);
    $totalItems = $stmt->fetchColumn();
    $totalPages = ceil($totalItems / $itemsPerPage);
} catch (PDOException $e) {
    echo "Database error counting orders: " . $e->getMessage();
    exit;
}

// Get paginated orders
try {
    $query = "SELECT o.order_id, o.cust_id, c.cust_name, o.order_date, o.total_amount, o.status, o.payment_status " 
           . $baseQuery 
           . " ORDER BY o.order_date DESC LIMIT " . $itemsPerPage . " OFFSET " . $offset;
    
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
        SUM(CASE WHEN status = 'Received' THEN 1 ELSE 0 END) as received_orders,
        SUM(CASE WHEN status = 'Refunded' THEN 1 ELSE 0 END) as refunded_orders,
        SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
        SUM(CASE WHEN status = 'Received' THEN total_amount ELSE 0 END) as total_revenue
        FROM orders");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    echo "Database error retrieving orders: " . $e->getMessage();
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
        <div class="stat-card delivered">
            <div class="stat-value"><?= number_format($stats['received_orders'] ?? 0) ?></div>
            <div class="stat-label">Received</div>
        </div>
        <div class="stat-card delivered">
            <div class="stat-value"><?= number_format($stats['refunded_orders'] ?? 0) ?></div>
            <div class="stat-label">Refunded</div>
        </div>
        <div class="stat-card revenue">
            <div class="stat-value">RM <?= number_format($stats['total_revenue'] ?? 0, 2) ?></div>
            <div class="stat-label">Revenue</div>
        </div>
    </div>
    
    <!-- Redesigned Filter Section (Horizontal Layout) -->
    <div class="filter-section">
        <form action="admin_orders.php" method="GET" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <select id="status" name="status" class="filter-select">
                        <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                        <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Confirmed" <?= $status_filter === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="Shipped" <?= $status_filter === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                        <option value="Delivered" <?= $status_filter === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                        <option value="Request Pending" <?= $status_filter === 'Request Pending' ? 'selected' : '' ?>>Request Pending</option>
                        <option value="Received" <?= $status_filter === 'Received' ? 'selected' : '' ?>>Received</option>
                        <option value="Refunded" <?= $status_filter === 'Refunded' ? 'selected' : '' ?>>Refunded</option>
                        <option value="Cancelled" <?= $status_filter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select id="payment" name="payment" class="filter-select">
                        <option value="all" <?= $payment_filter === 'all' ? 'selected' : '' ?>>All Payments</option>
                        <option value="Paid" <?= $payment_filter === 'Paid' ? 'selected' : '' ?>>Paid</option>
                        <option value="Pending" <?= $payment_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Failed" <?= $payment_filter === 'Failed' ? 'selected' : '' ?>>Failed</option>
                    </select>
                </div>
                
                <div class="date-range-group">
                    <div class="date-input">
                        <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($date_from) ?>" class="date-control" placeholder="From">
                    </div>
                    <span class="date-separator">to</span>
                    <div class="date-input">
                        <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($date_to) ?>" class="date-control" placeholder="To">
                    </div>
                </div>
                
                <div class="search-box">
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search orders...">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                
                <div class="filter-buttons">
                    <button type="submit" class="filter-btn">Apply</button>
                    <a href="admin_orders.php" class="admin-submit-btn secondary">Reset</a>
                </div>
            </div>
            <!-- Store current page in hidden field -->
            <input type="hidden" name="page" value="1">
        </form>
    </div>
    
    <!-- Order count info -->
    <div class="product-info-bar">
        <div class="product-count">
            Showing <?= count($orders) ?> of <?= $totalItems ?> orders
        </div>
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
                            <td><a href="view_order.php?id=<?= $order['order_id'] ?>" class="order-link">#<?= $order['order_id'] ?></a></td>
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
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&status=<?= urlencode($status_filter) ?>&payment=<?= urlencode($payment_filter) ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>&search=<?= urlencode($search) ?>">&laquo; Previous</a>
        <?php endif; ?>
        
        <?php
        // Determine range of page numbers to show
        $range = 2; // Show 2 pages before and after current page
        $startPage = max(1, $page - $range);
        $endPage = min($totalPages, $page + $range);
        
        // Always show first page
        if ($startPage > 1) {
            echo '<a href="?page=1&status=' . urlencode($status_filter) . '&payment=' . urlencode($payment_filter) . '&date_from=' . urlencode($date_from) . '&date_to=' . urlencode($date_to) . '&search=' . urlencode($search) . '">1</a>';
            if ($startPage > 2) {
                echo '<span class="ellipsis">...</span>';
            }
        }
        
        // Show page numbers with current page highlighted
        for ($i = $startPage; $i <= $endPage; $i++) {
            echo '<a href="?page=' . $i . '&status=' . urlencode($status_filter) . '&payment=' . urlencode($payment_filter) . '&date_from=' . urlencode($date_from) . '&date_to=' . urlencode($date_to) . '&search=' . urlencode($search) . '"';
            echo ($i == $page) ? ' class="active"' : '';
            echo '>' . $i . '</a>';
        }
        
        // Always show last page
        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                echo '<span class="ellipsis">...</span>';
            }
            echo '<a href="?page=' . $totalPages . '&status=' . urlencode($status_filter) . '&payment=' . urlencode($payment_filter) . '&date_from=' . urlencode($date_from) . '&date_to=' . urlencode($date_to) . '&search=' . urlencode($search) . '">' . $totalPages . '</a>';
        }
        ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&status=<?= urlencode($status_filter) ?>&payment=<?= urlencode($payment_filter) ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>&search=<?= urlencode($search) ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>