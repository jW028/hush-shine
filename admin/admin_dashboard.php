<?php
require_once '../_base.php';

// Check if user is logged in and has admin privileges
auth('admin');

$_title = 'Admin Dashboard';
include '../_head.php';

$_adminContext = true;

// Get current month and year for default filters
$currentMonth = date('m');
$currentYear = date('Y');
$selectedPeriod = $_GET['period'] ?? 'month';
$selectedMonth = $_GET['month'] ?? $currentMonth;
$selectedYear = $_GET['year'] ?? $currentYear;

// Function to get sales data
function getSalesData($period, $month = null, $year = null) {
    global $_db, $currentYear, $currentMonth;
    
    $received_status = getRevenueStatusCondition();
    
    switch ($period) {
        case 'day':
            // Last 30 days using order_items
            $query = "SELECT DATE(o.order_date) as label, 
                      SUM(oi.price * oi.quantity) as value 
                      FROM orders o 
                      JOIN order_items oi ON o.order_id = oi.order_id
                      WHERE o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                      AND o.status = ?
                      GROUP BY DATE(o.order_date) 
                      ORDER BY label";
            $params = [$received_status];
            break;
            
        case 'month':
            // Monthly data for selected year
            $query = "SELECT MONTH(o.order_date) as month_num, 
                      MONTHNAME(o.order_date) as label, 
                      SUM(oi.price * oi.quantity) as value 
                      FROM orders o 
                      JOIN order_items oi ON o.order_id = oi.order_id
                      WHERE YEAR(o.order_date) = ?
                      AND o.status = ?
                      GROUP BY MONTH(o.order_date), MONTHNAME(o.order_date) 
                      ORDER BY MONTH(o.order_date)";
            $params = [$year, $received_status];
            break;
            
        case 'year':
            // Yearly data
            $query = "SELECT YEAR(o.order_date) as label, 
                      SUM(oi.price * oi.quantity) as value 
                      FROM orders o
                      JOIN order_items oi ON o.order_id = oi.order_id
                      WHERE o.status = ?
                      GROUP BY YEAR(o.order_date) 
                      ORDER BY YEAR(o.order_date)";
            $params = [$received_status];
            break;
            
        default:
            // Default to monthly
            $query = "SELECT MONTH(o.order_date) as month_num, 
                      MONTHNAME(o.order_date) as label, 
                      SUM(oi.price * oi.quantity) as value 
                      FROM orders o 
                      JOIN order_items oi ON o.order_id = oi.order_id
                      WHERE YEAR(o.order_date) = ?
                      AND o.status = ?
                      GROUP BY MONTH(o.order_date), MONTHNAME(o.order_date) 
                      ORDER BY MONTH(o.order_date)";
            $params = [$currentYear, $received_status];
    }
    
    try {
        $stmt = $_db->prepare($query);
        if (isset($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug total
        $total = 0;
        foreach ($result as $row) {
            $total += floatval($row['value']);
        }
        error_log("Total from sales data: $total");
        
        return $result;
    } catch (PDOException $e) {
        error_log("Error fetching sales data: " . $e->getMessage());
        return [];
    }
}

function getRevenueStatusCondition() {
    global $_db;
    
    // Check all statuses first to debug
    $status_check = $_db->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
    $statuses = $status_check->fetchAll(PDO::FETCH_ASSOC);
    error_log("Order statuses in database: " . json_encode($statuses));
    
    // Use the correct status from what's actually in the database
    $received_status = 'Received'; // Default value
    foreach ($statuses as $status_row) {
        // Case insensitive comparison to find the closest match to "received"
        if (strtolower($status_row['status']) == 'received' || 
            strtolower($status_row['status']) == 'completed') {
            $received_status = $status_row['status']; // Use the exact case from database
            break;
        }
    }
    
    return $received_status;
}

// Get category popularity data
// Add this right after the getCategoryPopularity function
function getCategoryPopularity() {
    global $_db;
    
    try {
        $received_status = getRevenueStatusCondition();
        
        error_log("Using status: '$received_status' for category query");
        
        // Use parameters in query to avoid SQL injection and handle case sensitivity
        $query = "SELECT 
                c.cat_name as label,
                SUM(oi.quantity) as quantity,
                SUM(oi.price * oi.quantity) as value
            FROM 
                category c
                JOIN product p ON c.cat_id = p.cat_id
                JOIN order_items oi ON p.prod_id = oi.prod_id
                JOIN orders o ON oi.order_id = o.order_id
            WHERE 
                o.status = ?
            GROUP BY 
                c.cat_id, c.cat_name
            ORDER BY 
                value DESC";
        
        error_log("Category popularity query with status: $received_status");
                 
        $stmt = $_db->prepare($query);
        $stmt->execute([$received_status]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug the results
        error_log("getCategoryPopularity returned " . count($result) . " rows");
        
        // Calculate total for debugging
        $total = 0;
        foreach ($result as $row) {
            $total += floatval($row['value']);
        }
        error_log("Total from category data: $total");
        
        // If no data found with the received status, try with all statuses
        if (empty($result)) {
            // Fallback logic...
        }
        
        return $result;
    } catch (PDOException $e) {
        error_log("Error fetching category data: " . $e->getMessage());
        return [];
    }
}

// Get recent orders
function getRecentOrders() {
    global $_db;
    
    try {
        $query = "SELECT o.order_id, o.cust_id, c.cust_name, o.order_date, 
                 o.total_amount, o.status, o.payment_status
                 FROM orders o 
                 LEFT JOIN customer c ON o.cust_id = c.cust_id
                 ORDER BY o.order_date DESC
                 LIMIT 10";
                 
        $stmt = $_db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching recent orders: " . $e->getMessage());
        return [];
    }
}

// Get summary statistics
function getDashboardStats() {
    global $_db, $currentMonth, $currentYear;
    
    try {
        // Get total sales, orders, customers and products
        $stats = [
            'total_sales' => 0,
            'orders_count' => 0,
            'customers_count' => 0,
            'products_count' => 0,
            'avg_order_value' => 0,
            'pending_orders' => 0
        ];

        $received_status = getRevenueStatusCondition();

        // Total sales (only from Received orders)
        // Total sales using order_items calculation (consistent with category data)
        $stmt = $_db->prepare("SELECT SUM(oi.price * oi.quantity) as total 
                    FROM order_items oi 
                    JOIN orders o ON oi.order_id = o.order_id 
                    WHERE o.status = ?");
        $stmt->execute([$received_status]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_sales'] = $result['total'] ?? 0;

        // Log the total for debugging
        error_log("Total sales calculated: " . $stats['total_sales']);

        // Orders count (for avg calculation - only counting received orders)
        $stmt = $_db->prepare("SELECT COUNT(*) as count FROM orders WHERE status = ?");
        $stmt->execute([$received_status]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['received_orders_count'] = $result['count'] ?? 0;

        // Calculate average order value based on received orders
        $stats['avg_order_value'] = $stats['received_orders_count'] > 0 ? 
            $stats['total_sales'] / $stats['received_orders_count'] : 0;

        // This month's sales using same calculation method
        $stmt = $_db->prepare("SELECT SUM(oi.price * oi.quantity) as total 
                              FROM order_items oi 
                              JOIN orders o ON oi.order_id = o.order_id 
                              WHERE o.status = ? 
                              AND MONTH(o.order_date) = ? 
                              AND YEAR(o.order_date) = ?");
        $stmt->execute([$received_status, $currentMonth, $currentYear]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['monthly_sales'] = $result['total'] ?? 0;
        
        // Orders count
        $stmt = $_db->query("SELECT COUNT(*) as count FROM orders");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['orders_count'] = $result['count'] ?? 0;
        
        // Customers count
        $stmt = $_db->query("SELECT COUNT(*) as count FROM customer");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['customers_count'] = $result['count'] ?? 0;
        
        // Products count
        $stmt = $_db->query("SELECT COUNT(*) as count FROM product");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['products_count'] = $result['count'] ?? 0;
        
        // Pending orders
        $stmt = $_db->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Pending'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['pending_orders'] = $result['count'] ?? 0;
        
        // This month's orders count
        $stmt = $_db->prepare("SELECT COUNT(*) as count FROM orders WHERE MONTH(order_date) = ? AND YEAR(order_date) = ?");
        $stmt->execute([$currentMonth, $currentYear]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['monthly_orders'] = $result['count'] ?? 0;
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Error fetching dashboard stats: " . $e->getMessage());
        return [];
    }
}

function debugOutput($title, $data) {
    echo '<div style="margin: 10px; padding: 15px; background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 5px;">';
    echo '<h4 style="margin-top: 0; color: #007bff;">' . htmlspecialchars($title) . '</h4>';
    
    if (is_array($data) || is_object($data)) {
        echo '<pre style="background-color: #eee; padding: 10px; overflow: auto; max-height: 300px;">';
        print_r($data);
        echo '</pre>';
    } else {
        echo '<p style="margin-bottom: 0;">' . htmlspecialchars($data) . '</p>';
    }
    
    echo '</div>';
}

// Get top-selling products
function getTopProducts($limit = 5) {
    global $_db;
    try {
        // First, check all order statuses in the database
        $status_query = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
        $status_stmt = $_db->query($status_query);
        $statuses = $status_stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Order statuses in database: " . json_encode($statuses));
        
        
        // Now use the exact status string from the database
        $query = "SELECT p.prod_id, p.prod_name, p.image, 
                 SUM(oi.quantity) as quantity_sold,
                 SUM(oi.price * oi.quantity) as revenue
                 FROM order_items oi
                 JOIN product p ON oi.prod_id = p.prod_id
                 JOIN orders o ON oi.order_id = o.order_id
                 WHERE o.status = 'Received'
                 GROUP BY p.prod_id, p.prod_name, p.image
                 ORDER BY quantity_sold DESC
                 LIMIT 5";
                 
        $stmt = $_db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("getTopProducts query returned " . count($result) . " rows");
        
        // If no results with 'Received' status, try 'Confirmed' status
        if (empty($result)) {
            error_log("No results with 'Received' status, trying 'Confirmed' status");
            $fallback_query = "SELECT p.prod_id, p.prod_name, p.image, 
                             SUM(oi.quantity) as quantity_sold,
                             SUM(oi.price * oi.quantity) as revenue
                             FROM order_items oi
                             JOIN product p ON oi.prod_id = p.prod_id
                             JOIN orders o ON oi.order_id = o.order_id
                             WHERE o.status = 'Confirmed'
                             GROUP BY p.prod_id, p.prod_name, p.image
                             ORDER BY quantity_sold DESC
                             LIMIT ?";
                             
            $fallback_stmt = $_db->prepare($fallback_query);
            $fallback_stmt->execute([$limit]);
            $result = $fallback_stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Fallback query returned " . count($result) . " rows");
        }
        
        // Return the results (or empty array if nothing found)
        return $result;
    } catch (PDOException $e) {
        error_log("Error fetching top products: " . $e->getMessage());
        return [];
    }
}

// Get low stock products
function getLowStockProducts() {
    global $_db;
    
    try {
        $query = "SELECT prod_id, prod_name, quantity, price, cat_id
                 FROM product
                 WHERE quantity <= 10
                 ORDER BY quantity ASC";
                 
        $stmt = $_db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching low stock products: " . $e->getMessage());
        return [];
    }
}

// Fetch all data for the dashboard
$salesData = getSalesData($selectedPeriod, $selectedMonth, $selectedYear);
$categoryData = getCategoryPopularity();
$recentOrders = getRecentOrders(5);
$stats = getDashboardStats();
$topProducts = getTopProducts(5);
$lowStockProducts = getLowStockProducts(5);

// Prepare data for charts
$chartLabels = [];
$chartValues = [];
$categoryLabels = [];
$categoryValues = [];
$categoryQuantities = [];

foreach ($salesData as $data) {
    $chartLabels[] = $data['label'];
    $chartValues[] = floatval($data['value'] ?? 0);
}

foreach ($categoryData as $data) {
    $categoryLabels[] = $data['label'];
    $categoryValues[] = floatval($data['value'] ?? 0);
    $categoryQuantities[] = intval($data['quantity'] ?? 0);
}

// Convert to JSON for JavaScript
$chartLabelsJSON = json_encode($chartLabels);
$chartValuesJSON = json_encode($chartValues);
$categoryLabelsJSON = json_encode($categoryLabels);
$categoryValuesJSON = json_encode($categoryValues);
$categoryQuantitiesJSON = json_encode($categoryQuantities);
?>

<div class="admin-main">
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Dashboard</h1>
            <div class="dashboard-actions">
                <form action="admin_dashboard.php" method="GET" class="period-selector">
                    <div class="form-group">
                        <label for="period">View Sales By:</label>
                        <select name="period" id="period" class="form-control" onchange="this.form.submit()">
                            <option value="day" <?= $selectedPeriod == 'day' ? 'selected' : '' ?>>Daily (Last 30 days)</option>
                            <option value="month" <?= $selectedPeriod == 'month' ? 'selected' : '' ?>>Monthly</option>
                            <option value="year" <?= $selectedPeriod == 'year' ? 'selected' : '' ?>>Yearly</option>
                        </select>
                    </div>
                    
                    <?php if ($selectedPeriod == 'month'): ?>
                    <div class="form-group">
                        <label for="year">Year:</label>
                        <select name="year" id="year" class="form-control" onchange="this.form.submit()">
                            <?php for($y = 2020; $y <= date('Y'); $y++): ?>
                                <option value="<?= $y ?>" <?= $selectedYear == $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value">RM <?= number_format($stats['total_sales'], 2) ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= number_format($stats['orders_count']) ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= number_format($stats['customers_count']) ?></div>
                    <div class="stat-label">Customers</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= number_format($stats['products_count']) ?></div>
                    <div class="stat-label">Products</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value">RM <?= number_format($stats['avg_order_value'], 2) ?></div>
                    <div class="stat-label">Avg. Order Value</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?= number_format($stats['pending_orders']) ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
            </div>
        </div>
        
        <!-- Chart Sections -->
        <div class="dashboard-charts">
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Sales Overview</h3>
                </div>
                <div class="chart-body">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Category Performance</h3>
                </div>
                <div class="chart-body">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Data Sections -->
        <div class="dashboard-data-sections">
            <!-- Recent Orders -->
            <div class="data-card">
                <div class="data-header">
                    <h3>Recent Orders</h3>
                    <a href="admin_orders.php" class="view-all">View All</a>
                </div>
                <div class="data-body">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><a href="admin_orders.php?id=<?= $order['order_id'] ?>">#<?= $order['order_id'] ?></a></td>
                                <td><?= htmlspecialchars($order['cust_name'] ?? 'Customer '.$order['cust_id']) ?></td>
                                <td><?= date('M j, Y', strtotime($order['order_date'])) ?></td>
                                <td>RM <?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                        <?= $order['status'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentOrders)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No recent orders found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Top Products -->
            <div class="data-card">
                <div class="data-header">
                    <h3>Top Selling Products</h3>
                    <a href="admin_products.php" class="view-all">View All Products</a>
                </div>
                <div class="data-body">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Sold</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topProducts as $product): ?>
                                <tr>
                                    <td class="product-cell">
                                        <div class="product-info">
                                            <span><?= htmlspecialchars($product['prod_name']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= number_format($product['quantity_sold']) ?> units</td>
                                    <td>RM <?= number_format($product['revenue'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($topProducts)): ?>
                                <tr>
                                    <td colspan="3" class="text-center">No product sales data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Low Stock Products -->
            <div class="data-card">
                <div class="data-header">
                    <h3>Low Stock Products</h3>
                </div>
                <div class="data-body">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Stock</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockProducts as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['prod_name']) ?></td>
                                    <td>
                                        <span class="stock-indicator <?= $product['quantity'] <= 0 ? 'out-of-stock' : 'low-stock' ?>">
                                            <?= $product['quantity'] ?>
                                        </span>
                                    </td>
                                    <td>RM <?= number_format($product['price'], 2) ?></td>
                                    <td>
                                        <a href="view_product.php?id=<?= $product['prod_id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus"></i> Restock
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($lowStockProducts)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No low stock products</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Chart configuration
document.addEventListener('DOMContentLoaded', function() {
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?= $chartLabelsJSON ?>,
            datasets: [{
                label: 'Revenue (RM)',
                data: <?= $chartValuesJSON ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                tension: 0.3,
                pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                pointBorderColor: '#fff',
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'RM ' + parseFloat(value).toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: RM ' + parseFloat(context.parsed.y).toLocaleString();
                        }
                    }
                },
                legend: {
                    position: 'top',
                }
            }
        }
    });
    
    // Category Chart - use a doughnut chart for category distribution
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: <?= $categoryLabelsJSON ?>,
            datasets: [{
                label: 'Revenue by Category',
                data: <?= $categoryValuesJSON ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(199, 199, 199, 0.7)',
                    'rgba(83, 102, 255, 0.7)',
                    'rgba(40, 159, 64, 0.7)',
                    'rgba(210, 199, 199, 0.7)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(199, 199, 199, 1)',
                    'rgba(83, 102, 255, 1)',
                    'rgba(40, 159, 64, 1)',
                    'rgba(210, 199, 199, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = parseFloat(context.parsed) || 0;
                            const total = context.dataset.data.reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
                            const percentage = Math.round((value * 100) / total) + '%';
                            return label + ': RM ' + value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' (' + percentage + ')';
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });
});
</script>