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
                    <li><a href="/admin/add_product.php"><i class="fas fa-plus-circle"></i> Add Product</a></li>
                    <li><a href="/admin/admin_categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="/admin/admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <li><a href="/admin/admin_customers.php"><i class="fas fa-users"></i> Customers</a></li>
                    <li><a href="/admin/admin_reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                </ul>
            </nav>

    <?php else: ?>
        <header class="header">
        <div class="top-nav">
                <div class = "left-nav">
                <form action="" class="searchform">
                    <input type="search" placeholder="Search here ...">
                    <i class="fa fa-search"></i>
                </form>
                    <a href="/index.php"><i class = "fas fa-home"></i></a>
                    <a href="/page/contact.php"><i class = "fas fa-circle-exclamation"></i></a>
                </div>

                <div class="nav-center">
                    <!-- <a href="#"><img class="logo" src="/images/Hush & Shine.svg"></a> -->
                    <h1>Hush & Shine</h1>
                </div>
                

                <div class = "right-nav">
                    <a href="/page/login.php"><i class = "fas fa-user"></i></a>
                    <a href="#"><i class = "fas fa-truck-fast"></i></a>
                    <a href="#"><i class = "fas fa-cart-shopping"></i></a>
                </div>

            </div>
            <nav class = "bottom-nav">
                <a href="/page/products.php?category=CT04" data-cat="CT04" class="category-link">Earrings</a>
                <a href="/page/products.php?category=CT01" data-cat="CT01" class="category-link">Necklaces</a>
                <a href="/page/products.php?category=CT02" data-cat="CT02" class="category-link">Bracelets</a>
                <a href="/page/products.php?category=CT03" data-cat="CT03" class="category-link">Rings</a>
                <a href="/page/products.php?category=CT05" data-cat="CT05" class="category-link">Watches</a>
                <a href="/page/products.php?category=CT06" data-cat="CT06" class="category-link">Discount</a>
            </nav>
        </header>

    <?php endif; ?>
    

    <!-- <main>
        <h1><?= $_title ?? 'Untitled' ?></h1> -->