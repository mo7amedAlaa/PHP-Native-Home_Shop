<?php
session_start();
include "../config/dbConnection.php";

// Fetch categories
try {
    $categoryStmt = $conn->prepare("SELECT * FROM categories ORDER BY name ASC");
    $categoryStmt->execute();
    $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $categories = [];
}

// Fetch wishlist count
$wishlist_count = 0;
$cart_count=isset($_SESSION["cart"]) ? count($_SESSION["cart"]) :0;
try {
    $wishlist_count_Stmt = $conn->prepare("SELECT COUNT(*) FROM watchlist WHERE user_id = ?");
    $wishlist_count_Stmt->execute([$_SESSION['user_id']]);
    $wishlist_count = $wishlist_count_Stmt->fetchColumn();
} catch (PDOException $e) {
    error_log($e->getMessage());
}

 
$search_results = [];
$search_query = '';

if (isset($_GET['search_query']) && !empty($_GET['search_query'])) {
    $search_query = htmlspecialchars($_GET['search_query']);
    try {
        $searchStmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? LIMIT 10");
        $searchStmt->execute(["%$search_query%"]);
        $search_results = $searchStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
}

$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecommerce_App</title>
    <link rel="shortcut icon" href="../assets/images/favicon/Sneaker Shop Neutral Urban Logo(1).png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
<header class="py-4 shadow-sm bg-white">
    <div class="container flex items-center justify-between">
        <a href="../pages/home.php">
            <img src="../assets/images/Logo.png" alt="Logo" class="w-32 h-16">
        </a>
        
        <form action="" method="get" class="w-full max-w-xl relative flex">
            <span class="absolute left-4 top-3 text-lg text-gray-400">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <input type="text" name="search_query" id="search"
                   class="w-full border border-primary border-r-0 pl-12 py-3 pr-3 rounded-l-md focus:outline-none hidden md:flex"
                   placeholder="search">
            <button type="submit" class="bg-primary border border-primary text-white px-8 rounded-r-md hover:bg-transparent hover:text-primary transition hidden md:flex items-center justify-center">
                Search
            </button>
            
            <?php if (!empty($search_query)): ?>
                <div class="absolute w-full bg-white shadow-md mt-1 top-full left-0 search-results">
                    <?php if (!empty($search_results)): ?>
                        <?php foreach ($search_results as $result): ?>
                            <form action="../pages/product-details.php" method="post">
                                <input type="hidden" name="item_id" value="<?= htmlspecialchars($result['id']) ?>">
                                <button type="submit" name="view-product" class="block px-4 py-2 hover:bg-gray-100 w-full text-left">
                                    <strong><?= htmlspecialchars($result['name']) ?></strong><br>
                                    <small>Brand: <?= htmlspecialchars($result['brand']) ?></small>
                                </button>
                            </form>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="px-4 py-2 text-gray-500">No results found</div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </form>
        
        <div class="flex items-center space-x-4">
            <a href="../pages/wishlist.php" class="text-center text-gray-700 hover:text-primary transition relative">
                <div class="text-2xl">
                    <i class="fa-regular fa-heart"></i>
                </div>
                <div class="text-xs leading-3">Wishlist</div>
                <div class="absolute right-0 -top-1 w-5 h-5 rounded-full flex items-center justify-center bg-primary text-white text-xs">
                    <?php echo $wishlist_count; ?>
                </div>
            </a>
            <a href="../pages/cart.php" class="text-center text-gray-700 hover:text-primary transition relative">
                <div class="text-2xl">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
                <div class="text-xs leading-3">Cart</div>
                <div class="absolute -right-3 -top-1 w-5 h-5 rounded-full flex items-center justify-center bg-primary text-white text-xs">
                    <?php echo $cart_count; ?>
                </div>
            </a>
            <a href="../pages/account.php" class="text-center text-gray-700 hover:text-primary transition relative">
                <div class="text-2xl">
                    <i class="fa-regular fa-user"></i>
                </div>
                <div class="text-xs leading-3">Account</div>
            </a>
        </div>
    </div>
</header>

<nav class="bg-gray-800">
    <div class="container flex">
        <div class="px-8 py-4 bg-primary md:flex items-center cursor-pointer relative group hidden">
            <span class="text-white">
                <i class="fa-solid fa-bars"></i>
            </span>
            <span class="capitalize ml-2 text-white">All Categories</span>
            <div class="absolute w-full left-0 top-full bg-white shadow-md py-3 divide-y divide-gray-300 divide-dashed opacity-0 group-hover:opacity-100 transition duration-300 invisible group-hover:visible">
                <?php foreach ($categories as $category): ?>
                    <a href="../pages/shop.php?category[]=<?= htmlspecialchars($category['id']) ?>" class="flex items-center px-6 py-3 hover:bg-gray-100 transition">
                        <img src="<?= htmlspecialchars($category['icon']) ?>" alt="<?= htmlspecialchars($category['name']) ?>" class="w-5 h-5 object-contain">
                        <span class="ml-6 text-gray-600 text-sm"><?= htmlspecialchars($category['name']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="flex items-center justify-between flex-grow md:pl-12 py-5">
            <div class="flex items-center space-x-6 capitalize">
                <a href="../pages/home.php" class="text-gray-200 hover:text-white transition">Home</a>
                <a href="../pages/shop.php" class="text-gray-200 hover:text-white transition">Shop</a>
                <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'seller'): ?>
                    <a href="../pages/store.php" class="text-gray-200 hover:text-white transition">Online Store</a>
                <?php endif; ?>
                <a href="../pages/about-us.php" class="text-gray-200 hover:text-white transition">About us</a>
                <a href="../pages/contact-us.php" class="text-gray-200 hover:text-white transition">Contact us</a>
            </div>
            <?php if (isset($_SESSION['user_name'])): ?>
                <a href="../pages/profile.php" class="text-gray-200 hover:text-white transition">Hi, <?= htmlspecialchars($_SESSION['user_name']) ?></a>
            <?php else: ?>
                <a href="../pages/login.php" class="text-gray-200 hover:text-white transition">Login/Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
