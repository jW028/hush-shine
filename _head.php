<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
</head>
<body>
    <?php if ($_SESSION['user'] == "admin"): ?>
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
                    <li><a href="/admin/admin_menu.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="/admin/admin_products.php"><i class="fas fa-box"></i> Products</a></li>
                    <li><a href="/admin/admin_category.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="/admin/admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <li><a href="/admin/admin_customer.php"><i class="fas fa-users"></i> Customers</a></li>
            </nav>

    <?php else: ?>
        <header class="header">
        <div id="sidebar">
            <button class="close-btn" onclick="toggleSidebar()">&times;</button>
            
                <div>
                    
                    <?php if (isset($_SESSION['user']) && $_SESSION['user'] === "customer"):
                        $stm = $_db->prepare("SELECT cust_name, cust_photo FROM customer WHERE cust_id = ?");
                        $stm->execute([$_SESSION['cust_id']]);
                        $user = $stm->fetch(PDO::FETCH_ASSOC);
                        $_SESSION['cust_name'] = $user['cust_name'];
                        $_SESSION['cust_photo'] = $user['cust_photo'];
                        ?>
                        <img src="/images/customer_img/<?= htmlspecialchars($user['cust_photo']) ?>" alt="Profile Picture" class="profile-pic">
                        <p>Welcome, <?= htmlspecialchars($_SESSION['cust_name']) ?>!</p>
                        <a href="/index.php" class="sidebar-link">Home</a>
                        <a href="/page/profile.php" class="sidebar-link">Profile</a>
                        <a href="/page/logout.php" class="sidebar-link">Log out</a>
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
                    <a href="/page/contact.php"><i class = "fas fa-circle-exclamation"></i></a>
                    <a href="/page/fav.php"><i class = "fas fa-heart"></i></a>
                </div>

                <div class="nav-center">
                    <h1>Hush & Shine</h1>
                </div>
                

                <div class = "right-nav">
                    <a href="#" onclick="toggleSidebar()"><i class = "fas fa-user"></i></a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="/page/order_history.php"><i class="fas fa-clock-rotate-left"></i></a>
                    <?php endif; ?>
                    <a href="#"><i class = "fas fa-truck-fast"></i></a>
                    <a href="/page/cart.php" class="cart-link">
                        <i class = "fas fa-cart-shopping"></i>
                        <span class="cart-count" id="cart-count-badge">
                            <!-- TODO -->
                            <?php
                            // For testing - hardcode user C0001
                            $testUserId = "C0001";
                            $count = 0;
                            
                            if ($testUserId) {
                                try {
                                    global $_db;
                                    $stmt = $_db->prepare("
                                        SELECT SUM(ci.quantity) as total_items
                                        FROM cart_item ci
                                        JOIN shopping_cart sc ON ci.cart_id = sc.cart_id
                                        WHERE sc.cust_id = ?
                                    ");
                                    $stmt->execute([$testUserId]);
                                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                                    if (!$result || !isset($result['total_items'])) {
                                        error_log("No cart items found for user: $testUserId or query returned null");
                                        $count = 0;
                                    } else {
                                        $count = $result['total_items'];
                                    }
                                    
                                } catch (Exception $e) {
                                    error_log("Cart count error: " . $e->getMessage());
                                    $count = 0;
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
                <a href="/page/products.php?category=CT05" data-cat="CT05" class="category-link">Watches</a>
                <a href="/page/products.php?category=*" data-cat="*" class="category-link">All Products</a>
            </nav>
        </header>

    <?php endif; ?>
    

    <main>
