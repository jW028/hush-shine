<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('LOW_STOCK_THRESHOLD', 10);

function hasLowStockProducts() {
    global $_db;
    try {
        $stmt = $_db->prepare("SELECT COUNT(*) FROM product WHERE quantity <= ? AND quantity > 0");
        $stmt->execute([LOW_STOCK_THRESHOLD]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

$checkLowStock = false;
if (isset($_SESSION['user']) && $_SESSION['user'] == "admin") {
    $checkLowStock = true;
}

$hasLowStock = false;
if ($checkLowStock) {
    $hasLowStock = hasLowStockProducts();
}



if (isset($_SESSION['cust_id'])) {
    $custId = $_SESSION['cust_id'];
} else {
    $custId = null; // Default to null if the customer is not logged in
}
$isAdminSection = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
    $_adminContext = $_adminContext ?? $isAdminSection;



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Untitled' ?></title>
    <link rel="shortcut icon" href="/images/Hush & Shine.svg">
    <link rel="stylesheet" href="/css/app.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/js/app.js"></script>
    <script src="https://kit.fontawesome.com/ff9c54facb.js" crossorigin="anonymous"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
<?php 
    // Display success message if user just logged in (within the last 2 seconds)
    if (isset($_SESSION['login_success']) && isset($_SESSION['login_time']) && time() - $_SESSION['login_time'] < 2):
        // Clear the flags so the message only shows once
        unset($_SESSION['login_success']);
        unset($_SESSION['login_time']);
        
        $user_type = $_SESSION['user'] === 'admin' ? 'Administrator' : 'Customer';
        $user_name = $_SESSION['user'] === 'admin' ? $_SESSION['admin_name'] : $_SESSION['cust_name'];
    ?>
    <div id="login-success-toast" class="success-toast">
        <div class="toast-content">
            <div class="toast-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="toast-message">
                <strong>Welcome back, <?= htmlspecialchars($user_name) ?>!</strong>
                <span>You've successfully logged in as <?= htmlspecialchars($user_type) ?>.</span>
            </div>
        </div>
        <button class="toast-close" onclick="closeToast()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <script>
        // Auto-close the toast after 5 seconds
        setTimeout(function() {
            closeToast();
        }, 5000);
        
        function closeToast() {
            document.getElementById('login-success-toast').classList.add('fade-out');
            setTimeout(function() {
                document.getElementById('login-success-toast').style.display = 'none';
            }, 300);
        }
    </script>
    <?php endif; ?>

    <?php
    if (isset($_SESSION['reset_email_sent']) && isset($_SESSION['reset_time']) && time() - $_SESSION['reset_time'] < 3):
        // Clear the flags so the message only shows once
        $reset_email = $_SESSION['reset_email'] ?? '';
        unset($_SESSION['reset_email_sent']);
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_time']);
    ?>
    <div id="reset-email-toast" class="success-toast reset-email-toast">
        <div class="toast-content">
            <div class="toast-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="toast-message">
                <strong>Password Reset Email Sent</strong>
                <span>Check your email (<?= htmlspecialchars($reset_email) ?>) for a link to reset your password. The link will expire in 10 minutes.</span>
            </div>
        </div>
        <button class="toast-close" onclick="closeResetToast()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <script>
        // Auto-close the reset toast after 10 seconds
        setTimeout(function() {
            closeResetToast();
        }, 10000);
        
        function closeResetToast() {
            var toast = document.getElementById('reset-email-toast');
            if (toast) {
                toast.classList.add('fade-out');
                setTimeout(function() {
                    toast.style.display = 'none';
                }, 300);
            }
        }
    </script>
    <?php endif; ?>

    <?php 
    // Display logout success message
    if (isset($_SESSION['logout_success']) && isset($_SESSION['logout_time']) && time() - $_SESSION['logout_time'] < 2):
        // Clear the flags so the message only shows once
        $logout_name = $_SESSION['logout_name'] ?? 'User';
        unset($_SESSION['logout_success']);
        unset($_SESSION['logout_time']);
        unset($_SESSION['logout_name']);
    ?>
    <div id="logout-success-toast" class="success-toast logout-toast">
        <div class="toast-content">
            <div class="toast-icon">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <div class="toast-message">
                <strong>Successfully Logged Out</strong>
                <span>You've been successfully logged out.</span>
            </div>
        </div>
        <button class="toast-close" onclick="closeLogoutToast()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <script>
        // Auto-close the logout toast after 5 seconds
        setTimeout(function() {
            closeLogoutToast();
        }, 5000);
        
        function closeLogoutToast() {
            var toast = document.getElementById('logout-success-toast');
            if (toast) {
                toast.classList.add('fade-out');
                setTimeout(function() {
                    toast.style.display = 'none';
                }, 300);
            }
        }
    </script>
    <?php endif; ?>

    <?php if (isset($_SESSION['user']) && $_SESSION['user'] == "admin" && $_adminContext): ?>
        <script src="/js/admin.js"></script>
        <header class="admin-header">
            <div class="admin-nav">
                <div class="admin-logo">
                    <h1>Hush & Shine Admin</h1>
                </div>
                <div class="admin-user">
                    <?php if (isset($_SESSION['admin_email'])): ?>
                        <span>Welcome, <?= htmlspecialchars($_SESSION['admin_email']) ?></span>
                        <a href="/page/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <div class="admin-container">
            <nav class="admin-sidebar">
                <ul>
                    <li><a href="../index.php"> <i class="fas fa-home"></i>Home Page</a></li>
                    <li><a href="/admin/admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li>
                        <a href="/admin/admin_products.php">
                            <i class="fas fa-box"></i> Products
                            <?php if ($hasLowStock): ?>
                                <span class="notification-dot" title="Products with low stock">!</span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li><a href="/admin/admin_category.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="/admin/admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <li><a href="/admin/admin_customer.php"><i class="fas fa-users"></i> Customers</a></li>
                    <li><a href="/admin/admin_profile.php"><i class="fas fa-user"></i> Profile</a></li>
            </nav>

    <?php else: ?>
        <?php if (isset($_SESSION['user']) && $_SESSION['user'] == "admin"): ?>
            <div class="admin-toolbar">
                <span>Admin Mode</span>
                <a href="/admin/admin_dashboard.php" title="Dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                </a>
                <a href="/admin/admin_products.php" title="Products <?= $hasLowStock ? '(Low Stock)' : '' ?>">
                    <i class="fas fa-box"></i>
                    <?php if ($hasLowStock): ?>
                        <span class="notification-dot-small" title="Products with low stock">!</span>
                    <?php endif; ?>
                </a>
                <a href="/admin/admin_orders.php" title="Orders">
                    <i class="fas fa-shopping-cart"></i>
                </a>
            </div>
            <?php endif; ?>
        <header class="header">
        <div id="sidebar">
            <button class="close-btn" onclick="toggleSidebar()">&times;</button>
            
                <div>
                    <?php if (isset($_SESSION['user'])): ?>
                        <?php if ($_SESSION['user'] === "admin"): ?>
                            <?php
                            $stm = $_db->prepare("SELECT admin_name, admin_email, admin_photo FROM admin where admin_id = ?");
                            $stm->execute([$_SESSION['admin_id']]);
                            $user = $stm->fetch(PDO::FETCH_ASSOC);
                            $_SESSION['admin_name'] = $user['admin_name'];
                            $_SESSION['admin_email'] = $user['admin_email'];
                            $_SESSION['admin_photo'] = $user['admin_photo'];
                            ?>
                            <div class="admin-side-profile">
                            <img src="/images/admin_img/<?= htmlspecialchars($_SESSION['admin_photo']) ?>" alt="Profile Picture" class="profile-pic">
                                <p>Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?>!</p> 
                            </div>
                            <a href="/index.php" class="sidebar-link">Home</a>
                            <a href="/admin/admin_profile.php" class="sidebar-link">Profile</a>
                            <a href="/page/logout.php" class="sidebar-link">Log out</a>

                        <?php elseif ($_SESSION['user'] === "customer"): ?>
                            <?php
                            $stm = $_db->prepare("SELECT cust_name, cust_photo FROM customer WHERE cust_id = ?");
                            $stm->execute([$_SESSION['cust_id']]);
                            $user = $stm->fetch(PDO::FETCH_ASSOC);
                            $rewardStmt = $_db->prepare("
                                SELECT SUM(points) AS total_points
                                FROM reward_points
                                WHERE cust_id = ?
                            ");
                            $_SESSION['cust_name'] = $user['cust_name'];
                            $_SESSION['cust_photo'] = $user['cust_photo'];
                            if ($custId) {
                                try {
                                    $stmt = $_db->prepare("
                                        SELECT
                                            (
                                                -- Total earned reward points from the reward_points table
                                                (SELECT COALESCE(SUM(rp.points), 0)
                                                FROM reward_points rp
                                                INNER JOIN orders o ON rp.order_id = o.order_id
                                                WHERE rp.cust_id = ? AND o.status NOT IN ('Pending', 'Cancelled'))
                                                +
                                                -- Total earned reward points from the reward_get column in the orders table
                                                (SELECT COALESCE(SUM(o.reward_get), 0)
                                                FROM orders o
                                                WHERE o.cust_id = ? AND o.status NOT IN ('Pending', 'Cancelled'))
                                            ) AS total_earned,
                                            -- Total used reward points from the reward_used column in the orders table
                                            (SELECT COALESCE(SUM(o.reward_used), 0)
                                            FROM orders o
                                            WHERE o.cust_id = ? AND o.status != 'Cancelled') AS total_used
                                    ");
                                    $stmt->execute([$custId, $custId,$custId]);
                                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                    $rewardPoints = 0;
                                    $rewardPoints = ($result['total_earned'] ?? 0) - ($result['total_used'] ?? 0);
                                } catch (Exception $e) {
                                    error_log("Reward Points Calculation Error: " . $e->getMessage());
                                }
                            }
                            ?>
                            <img src="/images/customer_img/<?= htmlspecialchars($user['cust_photo']) ?>" alt="Profile Picture" class="profile-pic">
                            <p>Welcome, <?= htmlspecialchars($_SESSION['cust_name']) ?>!</p>
                            <p>Your Reward Points: <strong><?= number_format($rewardPoints, 2) ?></strong></p>
                            <a href="/index.php" class="sidebar-link">Home</a>
                            <a href="/page/profile.php" class="sidebar-link">Profile</a>
                            <a href="/page/logout.php" class="sidebar-link">Log out</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>Please log in to access your account.</p>
                        <a href="/index.php" class="sidebar-link">Home</a>
                        <a href="/page/login.php" class="sidebar-link">Login</a>
                        <a href="/page/register.php" class="sidebar-link">Register</a>
                    <?php endif; ?>
                </div>
                
        
        </div>
        <div class="overlay" onclick="toggleSidebar()"></div>
        <div class="top-nav">
                <div class = "left-nav">
                    <a href="/index.php"><i class = "fas fa-home"></i></a>
                    <a href="/page/contact.php"><i class = "fas fa-users"></i></a>
                    <a href="/page/fav.php"><i class = "fas fa-heart"></i></a>
                </div>

                <div class="nav-center">
                    <h1>Hush & Shine</h1>
                </div>
                

                <div class = "right-nav">
                    <a href="#" onclick="toggleSidebar()"><i class = "fas fa-user"></i></a>
                    <a href="/page/mypurchase.php"><i class = "fas fa-truck-fast"></i></a>
                    <a href="/page/cart.php" class="cart-link">
                        <i class = "fas fa-cart-shopping"></i>
                        <span class="cart-count" id="cart-count-badge">
                        <?php
                        $custId = $_SESSION['cust_id'] ?? null;
                        $count = 0;
                        if ($custId) {
                            try {
                                // Ensure we have a database connection
                                if (!isset($_db) || !$_db) {
                                    // If $_db is not available, get it from the global scope or create a new connection
                                    global $_db;
                                }
                                
                                if ($_db) {
                                    $stmt = $_db->prepare("
                                        SELECT SUM(ci.quantity) as total_items
                                        FROM cart_item ci
                                        JOIN shopping_cart sc ON ci.cart_id = sc.cart_id
                                        WHERE sc.cust_id = ?
                                    ");
                                    $stmt->execute([$custId]);
                                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                    $count = $result['total_items'] ?? 0;
                                }
                            } catch (Exception $e) {
                                error_log("Cart count error: " . $e->getMessage());
                            }
                        }
                        echo $count > 0 ? "$count" : "";
                        ?>
                    </span>
                    </a>
                </div>
            </div>

            <nav class = "bottom-nav">
                <a href="/page/products.php?category=CT04" data-cat="CT04" class="category-link">Earrings</a>
                <a href="/page/products.php?category=CT01" data-cat="CT01" class="category-link">Necklaces</a>
                <a href="/page/products.php?category=CT02" data-cat="CT02" class="category-link">Bracelets</a>
                <a href="/page/products.php?category=CT03" data-cat="CT03" class="category-link">Rings</a>
                <a href="/page/products.php?category=CT05" data-cat="CT05" class="category-link">Pendants</a>
                <a href="/page/products.php?category=*" data-cat="*" class="category-link">All Products</a>
            </nav>
        </header>

    <?php endif; ?>
    

    <main>
    