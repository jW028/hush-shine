<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Untitled' ?></title>
    <link rel="shortcut icon" href="/images/favicon.png">
    <link rel="stylesheet" href="/css/app.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/js/app.js"></script>
</head>
<body>
    <header>
        
    </header>

    <nav>
        <a href="/">Index</a>
        <div class="navbar">
            <a href="index.php">Home</a>
            <a href="products.php">Products</a>
            <a href="contact.php">Contact</a>
        </div>
    </nav>

    <main>
        <h1><?= $_title ?? 'Untitled' ?></h1>