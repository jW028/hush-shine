<?php
require_once '../_base.php';
auth('admin');

$_title = 'Customers';
include '../_head.php';
$_adminContext = true;

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$itemsPerPage = 3; // Customers per page
$offset = ($page - 1) * $itemsPerPage;

// Search and filter parameters
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$genderFilter = isset($_GET['gender']) ? $_GET['gender'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Auto-unblock customers whose block period has expired
try {
    $stmt = $_db->prepare("UPDATE customer SET status = 'active', blocked_until = NULL 
                          WHERE status = 'blocked' AND blocked_until < NOW()");
    $stmt->execute();
} catch (PDOException $e) {
    // Silently handle error
}

// Building WHERE clause for filtering
$where = [];
$params = [];

// Search by name, ID, email or contact
if (!empty($searchTerm)) {
    $where[] = "(cust_name LIKE ? OR cust_id LIKE ? OR cust_email LIKE ? OR cust_contact LIKE ?)";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
}

// Gender filter
if (!empty($genderFilter)) {
    $where[] = "cust_gender = ?";
    $params[] = $genderFilter;
}

// Status filter
if (!empty($statusFilter)) {
    if ($statusFilter === 'blocked') {
        $where[] = "status = 'blocked'";
    } else if ($statusFilter === 'active') {
        $where[] = "status = 'active' OR status IS NULL";
    }
}

// Combine WHERE clauses
$whereClause = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

// Sorting options
$orderBy = match($sortBy) {
    'name_asc' => 'cust_name ASC',
    'name_desc' => 'cust_name DESC',
    'oldest' => 'cust_id ASC',
    default => 'cust_id DESC'  // newest first (default)
};

// Count total customers for pagination
try {
    $countQuery = "SELECT COUNT(*) FROM customer" . $whereClause;
    $stmt = $_db->prepare($countQuery);
    $stmt->execute($params);
    $totalItems = $stmt->fetchColumn();
    $totalPages = ceil($totalItems / $itemsPerPage);
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}

// Get paginated customers
try {
    $query = "SELECT cust_id, cust_name, cust_contact, cust_email, cust_gender, status, blocked_until
             FROM customer" 
             . $whereClause .
             " ORDER BY " . $orderBy . 
             " LIMIT " . $itemsPerPage . " OFFSET " . $offset;
             
    $stmt = $_db->prepare($query);
    $stmt->execute($params);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}

// Get customer statistics
try {
    $statsQuery = $_db->query("SELECT 
        COUNT(*) as total_customers,
        SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) as blocked_customers
        FROM customer");
    $stats = $statsQuery->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats = ['total_customers' => 0, 'blocked_customers' => 0];
}
?>

<main class="admin-main">
    <div class="admin-title">
        <h2>Customers List</h2>
    </div>

    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($stats['total_customers']) ?></div>
                <div class="stat-label">Total Customers</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-user-lock"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($stats['blocked_customers']) ?></div>
                <div class="stat-label">Blocked Customers</div>
            </div>
        </div>
    </div>

    <div class="filter-section">
        <form action="admin_customer.php" method="GET" class="filter-form">
            <div class="filter-row">
                <div class="search-box">
                    <input type="text" name="search" placeholder="Search by ID, name, email or contact" value="<?= htmlspecialchars($searchTerm) ?>">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                
                <div class="filter-group">
                    <select name="gender" class="filter-select">
                        <option value="">All Genders</option>
                        <option value="M" <?= $genderFilter == 'M' ? 'selected' : '' ?>>Male</option>
                        <option value="F" <?= $genderFilter == 'F' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select name="status" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="active" <?= $statusFilter == 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="blocked" <?= $statusFilter == 'blocked' ? 'selected' : '' ?>>Blocked</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select name="sort" class="filter-select">
                        <option value="newest" <?= $sortBy == 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="oldest" <?= $sortBy == 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                        <option value="name_asc" <?= $sortBy == 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                        <option value="name_desc" <?= $sortBy == 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                    </select>
                </div>
                
                <div class="filter-buttons">
                    <button type="submit" class="filter-btn">Apply Filters</button>
                    <a href="admin_customer.php" class="admin-submit-btn secondary">Clear</a>
                </div>
            </div>

            <!-- Store current page in hidden field -->
            <input type="hidden" name="page" value="1">
        </form>
    </div>

    <div class="product-info-bar">
        <div class="product-count">
            Showing <?= count($customers) ?> of <?= $totalItems ?> customers
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
                    <th>Customer ID</th>
                    <th>Customer Name</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Gender</th>
                    <th>Status</th>
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
                            <td>
                                <?php if ($customer['status'] === 'blocked'): ?>
                                    <span class="status-badge blocked" title="Blocked until: <?= date('F j, Y g:i A', strtotime($customer['blocked_until'])) ?>">
                                        Blocked
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge active">Active</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="view_customer.php?id=<?= $customer['cust_id'] ?>" class="btn" title="View Customer">
                                    <i class="fas fa-eye"></i> 
                                </a>
                                <a href="block_customer.php?id=<?= $customer['cust_id'] ?>" class="btn <?= $customer['status'] === 'blocked' ? 'btn-warning' : 'btn-danger' ?>" title="<?= $customer['status'] === 'blocked' ? 'Manage Block Status' : 'Block Customer' ?>">
                                    <i class="fas <?= $customer['status'] === 'blocked' ? 'fa-unlock' : 'fa-ban' ?>"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No customers found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($searchTerm) ?>&gender=<?= urlencode($genderFilter) ?>&status=<?= urlencode($statusFilter) ?>&sort=<?= urlencode($sortBy) ?>">&laquo; Previous</a>
        <?php endif; ?>

        <?php 
        $range = 2;
        $startPage = max(1, $page - $range);
        $endPage = min($totalPages, $page + $range);
        
        if ($startPage > 1) {
            echo "<a href=\"?page=1&search=" . urlencode($searchTerm) . "&gender=" . urlencode($genderFilter) . "&status=" . urlencode($statusFilter) . "&sort=" . urlencode($sortBy) . "\">1</a>";
            if ($startPage > 2) {
                echo "<span class=\"ellipsis\">...</span>";
            }
        }

        for ($i = $startPage; $i <= $endPage; $i++) {
            echo '<a href="?page=' . $i . '&search=' . urlencode($searchTerm) . '&gender=' . urlencode($genderFilter) . '&status=' . urlencode($statusFilter) . '&sort=' . urlencode($sortBy) . '"';
            echo ($i == $page) ? ' class="active"' : '';
            echo '>' . $i . '</a>';
        }

        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                echo "<span class=\"ellipsis\">...</span>";
            }
            echo "<a href=\"?page=$totalPages&search=" . urlencode($searchTerm) . "&gender=" . urlencode($genderFilter) . "&status=" . urlencode($statusFilter) . "&sort=" . urlencode($sortBy) . "\">$totalPages</a>";
        }
        ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($searchTerm) ?>&gender=<?= urlencode($genderFilter) ?>&status=<?= urlencode($statusFilter) ?>&sort=<?= urlencode($sortBy) ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</main>