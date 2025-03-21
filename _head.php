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
</head>
<body>
    <header class="header">
        <nav class="nav">
            <a href="#"><img class="logo" src="/images/Hush & Shine.svg"></a>
            <div class="nav-links">
                <a href="/">Home</a>
                <a href="/page/products.php">Products</a>
                <a href="/page/contact.php">Contact</a>
                <a href="/page/login.php">Login</a>
                <!--<div class="loginicon">
                    <a href="/page/login.php">Login</a>
                    <div class="drop-login">
                        <a href="">Register</a>
                    </div>
                </div>-->
                
            </div>
        </nav>
    </header>

    <main>
        <h1><?= $_title ?? 'Untitled' ?></h1>


        