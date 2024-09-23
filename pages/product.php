<?php
require '../config/dbConnection.php';
session_start();
$user_id=$_SESSION['user_id'];
// Check if the user is logged in and has the right permissions
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$errors = [];
$success_message = '';

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    
    try {
        $query = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$product_id]);

        $query = "DELETE FROM product_images WHERE product_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$product_id]);

        $success_message = "Product deleted successfully";
        header("Location: product.php?success=" . urlencode($success_message));
        exit();
    } catch (PDOException $e) {
        $errors[] = "Failed to delete product: " . htmlspecialchars($e->getMessage());
    }
}

// Fetch all products from the database
$query = "SELECT p.id, p.name, p.description, p.base_price, p.discount, p.size, p.stock, p.color, p.brand, c.name, p.user_id AS category_name
          FROM products p
          JOIN categories c ON p.category_id = c.id
          where p.user_id =?; 
          ";
$stmt = $conn->prepare($query);
$stmt->execute([$user_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../components/header.php';
?>

<div class="container py-4 flex items-center gap-3">
    <a href="home.php" class="text-primary text-base">
        <i class="fa-solid fa-house"></i>
    </a>
    <span class="text-sm text-gray-400">
        <i class="fa-solid fa-chevron-right"></i>
    </span>
    <p class="text-gray-600 font-medium">Online Store > Products</p>
</div>

<div class="container bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold mb-4">Products</h2>
    
    <?php if (!empty($success_message)): ?>
        <div class="mb-4 text-green-600">
            <?= htmlspecialchars($success_message) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="mb-4">
            <ul class="list-disc pl-5 text-red-600">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <table class="w-full border-collapse border border-gray-300">
        <thead>
            <tr>
                <th class="border border-gray-300 px-4 py-2">ID</th>
                <th class="border border-gray-300 px-4 py-2">Name</th>
                <th class="border border-gray-300 px-4 py-2">Category</th>
                <th class="border border-gray-300 px-4 py-2">Price</th>
                <th class="border border-gray-300 px-4 py-2">Stock</th>
                <th class="border border-gray-300 px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($product['id']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($product['name']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($product['category_name']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($product['base_price']) ?></td>
                    <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($product['stock']) ?></td>
                    <td class="border border-gray-300 px-4 py-2 flex justify-center  items-center gap-10 ">
                        <a href="edit_product.php?id=<?= htmlspecialchars($product['id']) ?>" class="text-white text-lg w-9 h-8 rounded-full bg-blue-500    flex items-center justify-center hover:bg-gray-800 transition" title="Edit Product" ><i class="fa-solid fa-edit"></i></a>
                        <form method="post" action="product-details.php">
                        <input type="hidden" name="item_id" value="<?=htmlspecialchars($product['id'])?>">
                        <button type="submit" name="view-product" class="text-white text-lg w-9 h-8 rounded-full bg-green-500  flex items-center justify-around hover:bg-gray-800 transition" title="View Product">
                            <i class="fa-solid fa-search"></i> 
                        </button>
                        </form>
                        <a href="product.php?delete=<?= htmlspecialchars($product['id']) ?>"  class="text-white text-lg w-9 h-8 rounded-full bg-primary flex items-center justify-center hover:bg-gray-800 transition" title="Delete Product" onclick="return confirm('Are you sure you want to delete this product?');"><i class="fa-solid fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../components/footer.php'; ?>
