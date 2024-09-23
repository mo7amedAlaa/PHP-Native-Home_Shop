<?php
session_start();
require "../config/dbConnection.php";
require "../components/product_card_add.php";

$categoryFilter = $_GET['category'] ?? [];
$brandFilter = $_GET['brand'] ?? [];
$colorFilter = $_GET['color'] ?? '';
$sizeFilter = $_GET['size'] ?? '';
$minPrice = $_GET['min'] ?? '';
$maxPrice = $_GET['max'] ?? '';
$sortOption = $_GET['sort'] ?? '';

$query = "SELECT * FROM products WHERE 1";

if (!empty($categoryFilter)) {
    $categoryPlaceholders = implode(',', array_fill(0, count($categoryFilter), '?'));
    $query .= " AND category_id IN ($categoryPlaceholders)";
}

if (!empty($brandFilter)) {
    $brandPlaceholders = implode(',', array_fill(0, count($brandFilter), '?'));
    $query .= " AND brand IN ($brandPlaceholders)";
}

if ($colorFilter) {
    $query .= " AND color = ?";
}
if ($sizeFilter) {
    $query .= " AND size = ?";
}
if ($minPrice && $maxPrice) {
    $query .= " AND base_price BETWEEN ? AND ?";
}

switch ($sortOption) {
    case 'price-low-to-high':
        $query .= " ORDER BY base_price ASC";
        break;
    case 'price-high-to-low':
        $query .= " ORDER BY base_price DESC";
        break;
    case 'latest':
        $query .= " ORDER BY created_at DESC";
        break;
}

try {
    $stmt = $conn->prepare($query);
    $index = 1;

    if (!empty($categoryFilter)) {
        foreach ($categoryFilter as $category) {
            $stmt->bindValue($index++, $category);
        }
    }
    if (!empty($brandFilter)) {
        foreach ($brandFilter as $brand) {
            $stmt->bindValue($index++, $brand);
        }
    }
    if ($colorFilter) {
        $stmt->bindValue($index++, $colorFilter);
    }
    if ($sizeFilter) {
        $stmt->bindValue($index++, $sizeFilter);
    }
    if ($minPrice && $maxPrice) {
        $stmt->bindValue($index++, $minPrice);
        $stmt->bindValue($index++, $maxPrice);
    }

    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

try {
    $categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
    $brands = $conn->query("SELECT DISTINCT brand FROM products")->fetchAll(PDO::FETCH_ASSOC);
    $colors = $conn->query("SELECT DISTINCT color FROM products")->fetchAll(PDO::FETCH_ASSOC);
    $sizes = $conn->query("SELECT DISTINCT size FROM products WHERE size IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching filters: " . $e->getMessage();
}

include '../components/header.php';
?>

<div class="container py-4 flex items-center gap-3">
    <a href="home.php" class="text-primary text-base">
        <i class="fa-solid fa-house"></i>
    </a>
    <span class="text-sm text-gray-400">
        <i class="fa-solid fa-chevron-right"></i>
    </span>
    <p class="text-gray-600 font-medium">Shop</p>
</div>

<div class="container grid md:grid-cols-4 grid-cols-2 gap-6 pt-4 pb-16 items-start">
    <form action="" method="GET" class="col-span-1 bg-white px-4 pb-6 shadow rounded overflow-hidden">
        <div class="divide-y divide-gray-200 space-y-5">
            <div>
                <h3 class="text-xl text-gray-800 mb-3 uppercase font-medium">Categories</h3>
                <div class="space-y-2">
                    <?php foreach ($categories as $category): ?>
                        <label class="flex items-center">
                            <input type="checkbox" name="category[]" value="<?= $category['id'] ?>" <?= in_array($category['id'], $categoryFilter) ? 'checked' : ''; ?> />
                            <span class="ml-3 text-gray-600"><?= $category['name'] ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="pt-4">
                <h3 class="text-xl text-gray-800 mb-3 uppercase font-medium">Brands</h3>
                <div class="space-y-2">
                    <?php foreach ($brands as $brand): ?>
                        <label class="flex items-center">
                            <input type="checkbox" name="brand[]" value="<?= $brand['brand'] ?>" <?= in_array($brand['brand'], $brandFilter) ? 'checked' : ''; ?> />
                            <span class="ml-3 text-gray-600"><?= $brand['brand'] ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="pt-4">
                <h3 class="text-xl text-gray-800 mb-3 uppercase font-medium">Colors</h3>
                <div class="flex space-x-2">
                    <?php foreach ($colors as $color): ?>
                        <label class="flex items-center">
                            <span class="inline-block w-6 h-6 rounded-full border <?= ($colorFilter == $color['color']) ? 'border-primary' : 'border-gray-200' ?>" style="background-color: <?= $color['color'] ?>;"></span>
                            <input type="radio" name="color" value="<?= $color['color'] ?>" <?= ($colorFilter == $color['color']) ? 'checked' : ''; ?> class="hidden" />
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="pt-4">
                <h3 class="text-xl text-gray-800 mb-3 uppercase font-medium">Size</h3>
                <select name="size" class="w-full border-gray-300 rounded px-3 py-1 shadow-sm">
                    <option value="">Select Size</option>
                    <?php foreach ($sizes as $size): ?>
                        <option value="<?= $size['size'] ?>" <?= ($sizeFilter == $size['size']) ? 'selected' : ''; ?>><?= $size['size'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="pt-4">
                <h3 class="text-xl text-gray-800 mb-3 uppercase font-medium">Price</h3>
                <div class="flex items-center">
                    <input type="text" name="min" value="<?= $minPrice ?>" placeholder="Min" class="w-full border-gray-300 rounded px-3 py-1 shadow-sm focus:outline-red-500" />
                    <span class="mx-3 text-gray-500">-</span>
                    <input type="text" name="max" value="<?= $maxPrice ?>" placeholder="Max" class="w-full border-gray-300 rounded px-3 py-1 shadow-sm focus:outline-red-500" />
                </div>
            </div>
            <div class="pt-4">
                <button type="submit" class="w-full bg-primary text-white py-2 rounded">Apply Filters</button>
                <a href="shop.php" class="block w-full text-center text-gray-500 py-2 mt-2">Reset Filters</a>
            </div>
        </div>
    </form>

    <div class="col-span-3">
        <div class="flex items-center mb-4">
            <form id="sortForm" action="" method="GET" class="flex items-center gap-2">
                <select name="sort" id="sort" class="w-44 text-sm text-gray-600 py-3 px-4 border-gray-300 shadow-sm rounded">
                    <option value="">Default sorting</option>
                    <option value="price-low-to-high" <?= ($sortOption == 'price-low-to-high') ? 'selected' : ''; ?>>Price low to high</option>
                    <option value="price-high-to-low" <?= ($sortOption == 'price-high-to-low') ? 'selected' : ''; ?>>Price high to low</option>
                    <option value="latest" <?= ($sortOption == 'latest') ? 'selected' : ''; ?>>Latest product</option>
                </select>
               
            </form>
        </div>

        <div class="grid md:grid-cols-3 grid-cols-2 gap-6">
            <?php 
            if($products):
            foreach ($products as $product): 
                 displayMainProductCard($conn, $product['id'],basename(__FILE__));  
                  endforeach;
                else: echo'No Product Founded';
                endif;
            ?>
        </div>
    </div>
</div>

<script>
document.getElementById('sort').addEventListener('change', function() {
    document.getElementById('sortForm').submit();
});
</script>

<?php include '../components/footer.php'; ?>
