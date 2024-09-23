<?php
session_start();  

function displayMainProductCard($conn, $product_id, $redirect = "cart.php") {
   
    $stmt = $conn->prepare("SELECT  *FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    
    $img_stmt = $conn->prepare("SELECT image_url FROM product_images WHERE product_id = ? AND is_primary = 1");
    $img_stmt->execute([$product_id]);
    $image = $img_stmt->fetchColumn();
 

   
    $rating_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as reviews FROM reviews WHERE product_id = ?");
    $rating_stmt->execute([$product_id]);
    $rating_data = $rating_stmt->fetch(PDO::FETCH_ASSOC);
    
    $rating = round($rating_data['avg_rating']);
    $reviews = $rating_data['reviews'];

    if ($product && $image) {
        echo '<div class="relative bg-white shadow rounded overflow-hidden group">';
        
        
        
            echo '<div class="absolute top-0   left-0 bg-red-500 text-white text-xs font-bold py-1 px-3 rounded-br-lg z-50">-' . number_format($product['discount'], 0) . '%</div>';
        

        echo '
            <div class="relative">
                <img src="' . $image . '" alt="' . htmlspecialchars($product['name']) . '" class="w-full h-64 object-cover z-50">
                <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition z-10">
                   
                    <form method="post" action="../pages/product-details.php">
                        <input type="hidden" name="item_id" value="' . htmlspecialchars($product_id) . '">
                        <button type="submit" name="view-product" class="text-white text-lg w-9 h-8 rounded-full bg-primary flex items-center justify-center hover:bg-gray-800 transition" title="View product">
                            <i class="fa-solid fa-search"></i>
                        </button>
                    </form>
                    <form method="post" action="wishlist.php">
                        <input type="hidden" name="item_id" value="' . htmlspecialchars($product_id) . '">
                        <button type="submit" name="add_to_watchlist" class="text-white text-lg w-9 h-8 rounded-full bg-primary flex items-center justify-center hover:bg-gray-800 transition" title="Add to wishlist">
                            <i class="fa-solid fa-heart"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="pt-4 pb-3 px-4">
                <a href="#">
                    <h4 class="uppercase font-medium text-xl mb-2 text-gray-800 hover:text-primary transition">' . substr(htmlspecialchars($product['name']), 0, 20) . (strlen($product['name']) > 20 ? '...' : '') . '</h4>
                </a>
                
                <div class="flex items-center mb-1 space-x-2">
                    <p class="text-xl text-primary font-semibold">$' . number_format($product['base_price']- ($product['base_price'] * ($product['discount']/100)), 2) . '</p>';
                    
        if ($product['discount'] > 0) {
            echo '<p class="text-sm text-gray-400 line-through">$' . number_format($product['base_price']) . '</p>';
        }

        echo '
                </div>
                <div class="flex items-center">
                    <div class="flex gap-1 text-sm text-yellow-400">';

        for ($i = 0; $i < 5; $i++) {
            echo $i < $rating ? '<span><i class="fa-solid fa-star"></i></span>' : '<span><i class="fa-regular fa-star"></i></span>';
        }

        echo '
                    </div>
                    <div class="text-xs text-gray-500 ml-3">(' . $reviews . ' reviews)</div>
                </div>
                
                
            </div>
            
            <form method="POST" action="add_to_cart.php">
                <input type="hidden" name="product_id" value="' . htmlspecialchars($product_id) . '">
                <input type="hidden" name="redirect" value="' . htmlspecialchars($redirect) . '">
                 
                <button type="submit" class="block w-full py-1 text-center text-white bg-primary border border-primary rounded-b hover:bg-transparent hover:text-primary transition">Add to cart</button>
            </form>
        </div>';
    } else {
        echo '<p>Product not found.</p>';
    }
}
?>
