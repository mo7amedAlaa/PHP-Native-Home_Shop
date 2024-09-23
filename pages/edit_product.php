<?php
require '../config/dbConnection.php';
session_start();
 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$errors = [];
$success_message = '';
 
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: home.php");
    exit();
}

$product_id = (int)$_GET['id'];
 
$query = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: home.php");
    exit();
}

 $query = "SELECT id, name FROM categories";
$stmt = $conn->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

 $query = "SELECT * FROM product_images WHERE product_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$product_id]);
$existing_images = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     if (isset($_POST['delete_image']) && isset($_POST['delete_image_id'])) {
        $delete_image_id = (int)$_POST['delete_image_id'];

       
        $query = "SELECT image_url FROM product_images WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$delete_image_id]);
        $image_to_delete = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($image_to_delete) {
            $query = "DELETE FROM product_images WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$delete_image_id]);
            if (file_exists($image_to_delete['image_url'])) {
                unlink($image_to_delete['image_url']);
               
            }
            $success_message = "Image deleted successfully.";
             header("Location: edit_product.php?id=" . urlencode($product_id) . "&success=" . urlencode($success_message));
        }
    }

    
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $base_price = trim($_POST['base_price']);
    $discount = trim($_POST['discount']);
    $size = trim($_POST['size']);
    $stock = trim($_POST['stock']);
    $color = trim($_POST['color']);
    $brand = trim($_POST['brand']);
    $category_id = trim($_POST['category_id']);
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
    // if (empty($images['name'][0]) && empty($product['image_urls'])) {
    //     $errors[] = "At least one image is required.";
    // }

    if (empty($errors)) {
        try {
        
            $query = "UPDATE products SET category_id = ?, name = ?, description = ?, base_price = ?, discount = ?, size = ?, stock = ?, color = ?, brand = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$category_id, $name, $description, $base_price, $discount, $size, $stock, $color, $brand, $product_id]);

         
            if (!empty($images['name'][0])) {
                $is_primary = 1;  
                foreach ($images['tmp_name'] as $key => $tmp_name) {
                    $file_name = basename($images['name'][$key]);
                    $target_file_path = $upload_dir . $file_name;
 
                    if (move_uploaded_file($tmp_name, $target_file_path)) {
                      
                        $image_url = $target_file_path;
                        $query = "INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($query);
                        $stmt->execute([$product_id, $image_url, $is_primary]);
 
                        $is_primary = 0;
                    } else {
                        $errors[] = "Failed to upload image: " . htmlspecialchars($file_name);
                    }
                }
            }

            $success_message = "Product updated successfully";
            header("Location: edit_product.php?id=" . urlencode($product_id) . "&success=" . urlencode($success_message));
            exit();
        } catch (PDOException $e) {
            $errors[] = "Failed to update product: " . htmlspecialchars($e->getMessage());
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
    <p class="text-gray-600 font-medium">Online Store > Edit Product</p>
</div>

<div class="container grid grid-cols-12 items-start gap-6 pt-4 pb-16">
    <?php include '../components/shopSidebar.php'; ?>
    <div class="col-span-9 bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold mb-4">Edit Product</h2>
        <form action="edit_product.php?id=<?= htmlspecialchars($product_id) ?>" method="post" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="category_id" class="block text-gray-700">Category:</label>
                    <select id="category_id" name="category_id" class="block w-full border border-gray-300 px-4 py-3 text-gray-600 text-sm rounded focus:ring-0 focus:border-primary placeholder-gray-400">
                        <option value="">Select a Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category['id']) ?>" <?= $product['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="name" class="block text-gray-700">Product Name:</label>
                    <input type="text" id="name" name="name" class="mt-1 block w-full p-2 border border-gray-300 rounded" value="<?= htmlspecialchars($product['name']) ?>">
                </div>
            </div>

            <div class="mb-4">
                <label for="description" class="block text-gray-700">Description:</label>
                <textarea id="description" name="description" rows="4" class="mt-1 block w-full p-2 border border-gray-300 rounded"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="base_price" class="block text-gray-700">Base Price:</label>
                    <input type="number" id="base_price" name="base_price" step="0.01" class="mt-1 block w-full p-2 border border-gray-300 rounded" value="<?= htmlspecialchars($product['base_price']) ?>">
                </div>
                <div>
                    <label for="discount" class="block text-gray-700">Discount:</label>
                    <input type="number" id="discount" name="discount" step="0.01" value="<?= htmlspecialchars($product['discount']) ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="size" class="block text-gray-700">Size:</label>
                    <input type="text" id="size" name="size" class="mt-1 block w-full p-2 border border-gray-300 rounded" value="<?= htmlspecialchars($product['size']) ?>">
                </div>
                <div>
                    <label for="stock" class="block text-gray-700">Stock:</label>
                    <input type="number" id="stock" name="stock" min="0" class="mt-1 block w-full p-2 border border-gray-300 rounded" value="<?= htmlspecialchars($product['stock']) ?>">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="color" class="block text-gray-700">Color:</label>
                    <input type="color" id="color" name="color" class="mt-1 block w-full p-2 border border-gray-300 rounded" value="<?= htmlspecialchars($product['color']) ?>">
                </div>
                <div>
                    <label for="brand" class="block text-gray-700">Brand:</label>
                    <input type="text" id="brand" name="brand" class="mt-1 block w-full p-2 border border-gray-300 rounded" value="<?= htmlspecialchars($product['brand']) ?>">
                </div>
            </div>

            <div class="mb-4">
                <label for="images" class="block text-gray-700">Upload Images:</label>
                <input type="file" id="images" name="images[]" multiple class="mt-1 block w-full p-2 border border-gray-300 rounded">
            </div>

          

            <?php if (!empty($errors)): ?>
                <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-400 rounded">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="mb-4 p-4 bg-green-100 text-green-700 border border-green-400 rounded">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

            <div class="flex justify-end">
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded hover:bg-primary-dark">Update Product</button>
            </div>
        </form>
          <?php if (!empty($existing_images)): ?>
                <div class="mb-4">
                    <h3 class="text-lg font-semibold">Existing Images:</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php foreach ($existing_images as $image): ?>
                            <div class="relative">
                                <img src="<?= htmlspecialchars($image['image_url']) ?>" alt="Product Image" class="w-full h-32 object-cover mb-2">
                                <form action="edit_product.php?id=<?= htmlspecialchars($product_id) ?>" method="post" class="absolute top-0 right-0 bg-white p-1 rounded">
                                    <input type="hidden" name="delete_image_id" value="<?= htmlspecialchars($image['id']) ?>">
                                    <button type="submit" name="delete_image" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
    </div>
</div>

<?php include '../components/footer.php'; ?>
