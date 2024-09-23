<?php
require '../config/dbConnection.php';
session_start();

// Fetch categories from the database
$query = "SELECT id, name FROM categories";
$stmt = $conn->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$no_categories = empty($categories);

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $base_price = trim($_POST['base_price']);
    $discount = trim($_POST['discount']);
    $size = trim($_POST['size']);
    $stock = trim($_POST['stock']);
    $color = trim($_POST['color']);
    $brand = trim($_POST['brand']);
    $category_id = trim($_POST['category_id']);
    $user_id = $_SESSION['user_id'];
    $images = $_FILES['images'];
    $upload_dir = '../assets/images/products/';
    
    // Ensure upload directory exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Validate the form inputs
    if (empty($name) || empty($description) || empty($base_price) || empty($size) || empty($stock) || empty($brand)) {
        $errors[] = "All fields marked with * are required.";
    }

    if (!is_numeric($base_price) || $base_price <= 0) {
        $errors[] = "Base price must be a positive number.";
    }

    if (!is_numeric($discount)) {
        $errors[] = "Discount must be a numeric value.";
    }

    if (!is_numeric($stock) || $stock < 0) {
        $errors[] = "Stock must be a non-negative number.";
    }

    if ($category_id === '') {
        $errors[] = "Please select a category.";
    }

    // Check if at least one image is uploaded
    if (empty($images['name'][0])) {
        $errors[] = "At least one image is required.";
    }

    if (empty($errors)) {
        // Insert product details into the products table
        $query = "INSERT INTO products (category_id, name, description, base_price, discount, size, stock, color, brand, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        try {
            $stmt->execute([$category_id, $name, $description, $base_price, $discount, $size, $stock, $color, $brand, $user_id]);
            $product_id = $conn->lastInsertId();  // Get the inserted product ID

            // Handle image uploads
            $is_primary = 1; // The first image will be the primary image
            foreach ($images['tmp_name'] as $key => $tmp_name) {
                $file_name = basename($images['name'][$key]);
                $target_file_path = $upload_dir . $file_name;

                // Move uploaded file to the server
                if (move_uploaded_file($tmp_name, $target_file_path)) {
                    // Insert image into product_images table
                    $image_url = $target_file_path;
                    $query = "INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([$product_id, $image_url, $is_primary]);

                    // Only the first image will be marked as primary
                    $is_primary = 0;
                } else {
                    $errors[] = "Failed to upload image: " . htmlspecialchars($file_name);
                }
            }

            $success_message = "Product added successfully";
            header("Location: add_product.php?success=" . urlencode($success_message));
            exit();
        } catch (PDOException $e) {
            $errors[] = "Failed to add product: " . htmlspecialchars($e->getMessage());
        }
    }
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
    <p class="text-gray-600 font-medium">Online Store > Add Product</p>
</div>

<div class="container grid grid-cols-12 items-start gap-6 pt-4 pb-16">
    <?php include '../components/shopSidebar.php'; ?>
    <div class="col-span-9 bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold mb-4">Add New Product</h2>
        <form action="add_product.php" method="post" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="category_id" class="block text-gray-700">Category:</label>
                    <select id="category_id" name="category_id" class="block w-full border border-gray-300 px-4 py-3 text-gray-600 text-sm rounded focus:ring-0 focus:border-primary placeholder-gray-400"  >
                        <option value="">Select a Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category['id']) ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="name" class="block text-gray-700">Product Name:</label>
                    <input type="text" id="name" name="name" class="mt-1 block w-full p-2 border border-gray-300 rounded"  >
                </div>
            </div>

            <div class="mb-4">
                <label for="description" class="block text-gray-700">Description:</label>
                <textarea id="description" name="description" rows="4" class="mt-1 block w-full p-2 border border-gray-300 rounded"  ></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="base_price" class="block text-gray-700">Base Price:</label>
                    <input type="number" id="base_price" name="base_price" step="0.01" class="mt-1 block w-full p-2 border border-gray-300 rounded"  >
                </div>
                <div>
                    <label for="discount" class="block text-gray-700">Discount:</label>
                    <input type="number" id="discount" name="discount" step="0.01" value="0" class="mt-1 block w-full p-2 border border-gray-300 rounded">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="size" class="block text-gray-700">Size:</label>
                    <select id="size" name="size" class="block w-full border border-gray-300 px-4 py-3 text-gray-600 text-sm rounded focus:ring-0 focus:border-primary placeholder-gray-400"  >
                        <option value="">Select a Size</option>
                        <option value="XS">XS</option>
                        <option value="S">S</option>
                        <option value="M">M</option>
                        <option value="L">L</option>
                        <option value="XL">XL</option>
                    </select>
                </div>
                <div>
                    <label for="stock" class="block text-gray-700">Stock:</label>
                    <input type="number" id="stock" name="stock" class="mt-1 block w-full p-2 border border-gray-300 rounded"  >
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="brand" class="block text-gray-700">Brand:</label>
                    <input type="text" id="brand" name="brand" class="mt-1 block w-full p-2 border border-gray-300 rounded"  >
                </div>
                <div>
                    <label for="color" class="block text-gray-700">Color:</label>
                    <input type="color" id="color" name="color" class="mt-1 block w-full p-2 border border-gray-300 rounded">
                </div>
            </div>

            <div class="grid grid-cols-1     mb-4">
                    <label for="images" class="block text-gray-700">Images(multiple):</label>
                    <input type="file" id="images" name="images[]" accept="image/*" class="mt-1 block w-full p-2 border border-gray-300 rounded" multiple>
            </div>

            <button type="submit" class="w-full py-3 px-4 text-center text-white bg-primary border border-primary rounded-md hover:bg-transparent hover:text-primary transition font-medium">Add Product</button>
        </form>
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
    </div>
</div>

<?php include '../components/footer.php'; ?>
