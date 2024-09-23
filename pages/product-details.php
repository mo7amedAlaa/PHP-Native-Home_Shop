<?php
include "../config/dbConnection.php";
include "../components/header.php";

if (isset($_POST['view-product'])) {
    $item_id = $_POST['item_id'];

    try {
        $stmt = $conn->prepare("
            SELECT p.*, i.image_url, AVG(r.rating) AS avg_rating, COUNT(DISTINCT r.id) AS review_count
            FROM products p
            LEFT JOIN product_images i ON p.id = i.product_id
            LEFT JOIN reviews r ON p.id = r.product_id
            WHERE p.id = :item_id
            GROUP BY p.id
        ");
        $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        $imagesStmt = $conn->prepare("SELECT image_url FROM product_images WHERE product_id = :item_id");
        $imagesStmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $imagesStmt->execute();
        $images = $imagesStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$product) {
            echo "<p class='text-red-600'>Product not found.</p>";
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo "<p class='text-red-600'>Failed to load product details. Please try again later.</p>";
    }
}
?>

<div class="container mx-auto mt-10">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-5">
            <div>
                <?php if (!empty($images)): ?>
                    <div class="flex justify-center items-center mb-4">
                        <img src="<?php echo htmlspecialchars($images[0]['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-auto object-cover rounded-lg">
                    </div>
                    <div class="flex space-x-4 mt-4">
                        <?php foreach ($images as $image): ?>
                            <img src="<?php echo htmlspecialchars($image['image_url']); ?>" alt="Product Image" class="w-24 h-24 object-cover border rounded-md">
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No images available for this product.</p>
                <?php endif; ?>
            </div>

            <div>
                <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($product['name']); ?></h1>

                <div class="flex items-center my-4">
                    <div class="flex gap-1 text-yellow-400">
                        <?php 
                        $rating = round($product['avg_rating']);
                        for ($i = 0; $i < 5; $i++): 
                            echo $i < $rating ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                        endfor; 
                        ?>
                    </div>
                    <span class="ml-2 text-gray-500">(<?php echo htmlspecialchars($product['review_count']); ?> reviews)</span>
                </div>

                <div class="flex items-baseline space-x-2">
                   
                    <?php if ($product['discount'] > 0): ?>
                        <span class="text-2xl text-primary font-bold">$<?php echo number_format($product['base_price']*($product['discount']/100), 2); ?></span>
                        <span class="text-sm text-gray-400 line-through">$<?php echo number_format($product['base_price'], 2); ?></span>
                        <?php else: ?>
                             <span class="text-2xl text-primary font-bold">$<?php echo number_format($product['base_price'], 2); ?></span>
                    <?php endif; ?>
                </div>

                <p class="mt-4 text-gray-600"><?php echo htmlspecialchars($product['description']); ?></p>

                <div class="mt-4 space-y-2">
                    <p class="text-gray-600">Size: <?php echo htmlspecialchars($product['size']); ?></p>
                    <p class="text-gray-600">In Stock: <?php echo htmlspecialchars($product['stock']); ?></p>
                    
                    <!-- Display color as a circle -->
                    <div class="flex items-center">
                        <span class="text-gray-600 mr-2">Color: </span>
                        <span class="inline-block w-6 h-6 rounded-full" style="background-color: <?php echo htmlspecialchars($product['color']); ?>;"></span>
                    </div>
                    
                    <p class="text-gray-600">Brand: <?php echo htmlspecialchars($product['brand']); ?></p>
                </div>

                <div class="mt-8 space-y-4">
                    <form method="POST" action="add_to_cart.php">
                         <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                         <button type="submit" class="w-full bg-green-500 text-white py-2 px-4 rounded-lg hover:bg-green-600">
                            <i class="fa-solid fa-cart-shopping mr-2"></i> Add to Cart
                        </button>
                    </form>

                    <form method="POST" action="wishlist.php">
                        <input type="hidden" name="item_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" name="add_to_watchlist" class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">
                            <i class="fa-solid fa-heart mr-2"></i> Add to Watchlist
                        </button>
                    </form>

                    <a href="review.php?item_id=<?php echo $product['id']; ?>" class="block w-full bg-yellow-500 text-white py-2 px-4 rounded-lg hover:bg-yellow-600 text-center">
                        <i class="fa-solid fa-pen mr-2"></i> Reviews
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../components/footer.php"; ?>
