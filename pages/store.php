<?php
require '../config/dbConnection.php';
session_start();
require '../components/product_card.php';  

$user_id = $_SESSION['user_id'] ?? 0; 

$search_name = $_GET['name'] ?? '';
$search_min_price = $_GET['min_price'] ?? '';
$search_max_price = $_GET['max_price'] ?? '';
$search_category = $_GET['category'] ?? '';
$search_stock = $_GET['stock'] ?? '';
$search_color = $_GET['color'] ?? '';
$limit = 2; 
$page = isset($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;  
$offset = ($page - 1) * $limit;  
$query = "SELECT * FROM products WHERE user_id = :user_id";
$params = [':user_id' => $user_id];

if (!empty($search_name)) {
    $query .= " AND name LIKE :search_name";
    $params[':search_name'] = "%$search_name%";
}
if (!empty($search_min_price)) {
    $query .= " AND base_price >= :search_min_price";
    $params[':search_min_price'] = $search_min_price;
}
if (!empty($search_max_price)) {
    $query .= " AND base_price <= :search_max_price";
    $params[':search_max_price'] = $search_max_price;
}
if (!empty($search_category)) {
    $query .= " AND category_id = :search_category";
    $params[':search_category'] = $search_category;
}
if (!empty($search_stock)) {
    $query .= " AND stock >= :search_stock";
    $params[':search_stock'] = $search_stock;
}
if (!empty($search_color)) {
    $query .= " AND color = :search_color";
    $params[':search_color'] = $search_color;
}

$query .= " LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($query);

 
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

 
$stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

 
try {
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    
    echo "Error executing query: " . $e->getMessage();
}

// Get total number of products for pagination
$total_query = "SELECT COUNT(*) FROM products WHERE user_id = :user_id";
$total_params = [':user_id' => $user_id];

if (!empty($search_name)) {
    $total_query .= " AND name LIKE :search_name";
    $total_params[':search_name'] = "%$search_name%";
}
if (!empty($search_min_price)) {
    $total_query .= " AND base_price >= :search_min_price";
    $total_params[':search_min_price'] = $search_min_price;
}
if (!empty($search_max_price)) {
    $total_query .= " AND base_price <= :search_max_price";
    $total_params[':search_max_price'] = $search_max_price;
}
if (!empty($search_category)) {
    $total_query .= " AND category_id = :search_category";
    $total_params[':search_category'] = $search_category;
}
if (!empty($search_stock)) {
    $total_query .= " AND stock >= :search_stock";
    $total_params[':search_stock'] = $search_stock;
}
if (!empty($search_color)) {
    $total_query .= " AND color = :search_color";
    $total_params[':search_color'] = $search_color;
}

$total_stmt = $conn->prepare($total_query);
$total_stmt->execute($total_params);
$total_products = $total_stmt->fetchColumn();
$total_pages = ceil($total_products / $limit);

// Fetch categories for the dropdown
$category_query = "SELECT id, name FROM categories";
$category_stmt = $conn->prepare($category_query);
$category_stmt->execute();
$categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch unique colors for the dropdown
$color_query = "SELECT DISTINCT color FROM products WHERE color IS NOT NULL AND color != ''";
$color_stmt = $conn->prepare($color_query);
$color_stmt->execute();
$colors = $color_stmt->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
$success_message = '';

include '../components/header.php';
?>

<div class="container py-4 flex items-center gap-3">
    <a href="home.php" class="text-primary text-base">
        <i class="fa-solid fa-house"></i>
    </a>
    <span class="text-sm text-gray-400">
        <i class="fa-solid fa-chevron-right"></i>
    </span>
    <p class="text-gray-600 font-medium">Online Store > All Products</p>
</div>

<div class="container grid grid-cols-12 items-start gap-6 pt-4 pb-16">
    <?php include '../components/shopSidebar.php'; ?>
    <div class="col-span-9 bg-white p-6 rounded-lg shadow-md">
        <?php if ($errors): ?>
            <div class="mt-4">
                <?php foreach ($errors as $error): ?>
                    <div class="text-red-500"><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="mt-4 text-green-500"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <!-- Search Form -->
        <form method="GET" action="" class="mb-6">
            <div class="flex flex-wrap gap-4">
                <input type="text" name="name" placeholder="Search by name" value="<?= htmlspecialchars($search_name) ?>" class="border p-2 rounded-lg w-full md:w-1/4">
                <input type="number" name="min_price" placeholder="Min price" value="<?= htmlspecialchars($search_min_price) ?>" class="border p-2 rounded-lg w-full md:w-1/4">
                <input type="number" name="max_price" placeholder="Max price" value="<?= htmlspecialchars($search_max_price) ?>" class="border p-2 rounded-lg w-full md:w-1/4">
                <select name="category" class="border p-2 rounded-lg w-full md:w-1/4">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['id']) ?>" <?= $search_category === (string)$cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="color" class="border p-2 rounded-lg w-full md:w-1/4">
                    <option value="">Select Color</option>
                    <?php foreach ($colors as $color): ?>
                        <?php $color_code = htmlspecialchars($color['color']); ?>
                        <option value="<?= $color_code ?>"
                            <?= $search_color === $color_code ? 'selected' : '' ?>
                            style="background-color: <?= $color_code ?>; color: <?= $color_code === '#ffffff' ? '#000000' : '#ffffff' ?>;">
                            <?= $color_code ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="stock" placeholder="Search by stock" value="<?= htmlspecialchars($search_stock) ?>" class="border p-2 rounded-lg w-full md:w-1/4">
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300 ease-in-out">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="store.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition duration-300 ease-in-out">
                    <i class="fas fa-undo"></i> Reset
                </a>
            </div>
        </form>

        <!-- Display products -->
        <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
            <?php foreach ($products as $product): ?>
                <?php displayProductCard($conn, $product['id']); ?>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-center">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&name=<?= urlencode($search_name) ?>&min_price=<?= $search_min_price ?>&max_price=<?= $search_max_price ?>&category=<?= $search_category ?>&stock=<?= $search_stock ?>&color=<?= $search_color ?>" class="px-3 py-2 mx-1 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    Previous
                </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&name=<?= urlencode($search_name) ?>&min_price=<?= $search_min_price ?>&max_price=<?= $search_max_price ?>&category=<?= $search_category ?>&stock=<?= $search_stock ?>&color=<?= $search_color ?>" class="px-3 py-2 mx-1 <?= $i === $page ? 'bg-blue-500 text-white' : 'bg-gray-300' ?> rounded-lg hover:bg-blue-600">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>&name=<?= urlencode($search_name) ?>&min_price=<?= $search_min_price ?>&max_price=<?= $search_max_price ?>&category=<?= $search_category ?>&stock=<?= $search_stock ?>&color=<?= $search_color ?>" class="px-3 py-2 mx-1 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    Next
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../components/footer.php'; ?>
