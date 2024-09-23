<?php
 function truncateText($text, $length) {
        return mb_strlen($text) > $length ? mb_substr($text, 0, $length) . '...' : $text;
    }

function displayProductCard($pdo, $product_id) {
    $query = "SELECT * FROM products WHERE id = :product_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    $query = "SELECT image_url FROM product_images WHERE product_id = :product_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_images = count($images);
    $current_index = isset($_GET['index']) ? (int)$_GET['index'] : 0;

    if ($current_index < 0) {
        $current_index = $total_images - 1;
    } elseif ($current_index >= $total_images) {
        $current_index = 0;
    }

    $current_image_url = $total_images > 0 ? $images[$current_index]['image_url'] : '';
    $color = isset($product['color']) ? htmlspecialchars($product['color']) : '#ffffff';

    $maxLength = 45;  

   
    echo '<div class="relative bg-white p-4 rounded-lg shadow-sm overflow-hidden border border-gray-200 transition-transform transform hover:scale-105 duration-300">';

    if (isset($product['discount']) && !empty($product['discount'])) {
        $discount = htmlspecialchars($product['discount']);
        echo '<div class="absolute top-0 left-0 bg-red-600 text-white text-sm font-bold px-2 py-1 rounded-br-lg rounded-tl-lg z-50">
                -' . $discount . '% Off
            </div>';
    }

    echo '<div class="relative group">';
    
    if ($total_images > 0) {
        echo '<div class="relative overflow-hidden rounded-lg h-64">';
        echo "<img src=\"$current_image_url\" alt=\"Product Image\" class=\"w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 ease-in-out\">";
        
        $current_url = $_SERVER['REQUEST_URI'];
        $parsed_url = parse_url($current_url);
        $query_params = [];
        
        if (isset($parsed_url['query'])) {
            parse_str($parsed_url['query'], $query_params);
        }
        
        $prev_index = $current_index - 1;
        $next_index = $current_index + 1;
        
        if ($prev_index < 0) {
            $prev_index = $total_images - 1;
        }
        if ($next_index >= $total_images) {
            $next_index = 0;
        }
        
        $query_params['index'] = $prev_index;
        $prev_link = $parsed_url['path'] . '?' . http_build_query($query_params);

        $query_params['index'] = $next_index;
        $next_link = $parsed_url['path'] . '?' . http_build_query($query_params);

        echo '<div class="absolute inset-x-0 bottom-0 flex justify-between p-2 bg-gradient-to-t from-gray-900 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 ease-in-out">';
        echo '<a href="' . $prev_link . '" class="bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700 transition duration-300 ease-in-out">
                <i class="fas fa-chevron-left"></i>
            </a>';
        echo '<a href="' . $next_link . '" class="bg-red-600 text-white px-3 py-2 rounded-lg hover:bg-red-700 transition duration-300 ease-in-out">
                <i class="fas fa-chevron-right"></i>
            </a>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<p class="text-gray-500 text-center">No images available for this product.</p>';
    }

    echo '</div>';
    echo '<div class="mt-4">';
    
    echo '<h2 class="text-xl font-semibold text-gray-800 mb-2 flex items-center">
            <i class="fas fa-cube mr-2"></i>' . truncateText(htmlspecialchars($product['name']), $maxLength) . '</h2>';
    
    echo '<p class="text-gray-700 mb-2 flex items-center">
            <i class="fas fa-info-circle mr-2"></i>' . (isset($product['description']) && !empty($product['description']) ? truncateText(htmlspecialchars($product['description']), $maxLength) : 'No description available') . '</p>';
    
    echo '<p class="text-gray-800 font-bold text-lg mb-2 flex items-center">
            <i class="fas fa-dollar-sign mr-2"></i>$' . (isset($product['base_price']) && !empty($product['base_price']) ? htmlspecialchars($product['base_price']) : 'Price not available') . '</p>';

    echo '<p class="text-gray-700 mb-2 flex items-center">
            <i class="fas fa-ruler mr-2"></i>Size: ' . (isset($product['size']) && !empty($product['size']) ? htmlspecialchars($product['size']) : 'Size not available') . '</p>';

    echo '<p class="text-green-600 font-semibold mb-4 flex items-center">
            <i class="fas fa-warehouse mr-2"></i>Stock: ' . (isset($product['stock']) && !empty($product['stock']) ? htmlspecialchars($product['stock']) : 'Stock not available') . '</p>';
    
    echo '<p class="text-gray-700 mb-2 flex items-center">
            <i class="fas fa-paint-brush mr-2"></i>Color: ' . (isset($product['color']) && !empty($product['color']) ? '<span style="background-color: ' . $color . ';" class="h-6 w-6 rounded-full inline-block"></span>' : 'Color not available') . '</p>';

    echo '<p class="text-gray-700 mb-2 flex items-center">
            <i class="fas fa-tag mr-2"></i>Brand: ' . (isset($product['brand']) && !empty($product['brand']) ? truncateText(htmlspecialchars($product['brand']),$maxLength) : 'Brand not available') . '</p>';

    echo '<div class="flex gap-2">';
    
    echo '<form action="edit_product.php" method="GET">';
    echo '<input type="hidden" name="product_id" value="' . $product_id . '">';
    echo '<button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300 ease-in-out flex items-center">
            <i class="fas fa-edit mr-2"></i> Edit 
        </button>';
    echo '</form>';

    echo '<form action="delete_product.php" method="POST" onsubmit="return confirm(\'Are you sure you want to delete this product?\')">';
    echo '<input type="hidden" name="product_id" value="' . $product_id . '">';
    echo '<button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-300 ease-in-out flex items-center">
            <i class="fa-regular fa-trash-can mr-2"></i> Delete 
        </button>';
    echo '</form>';

    echo '</div>';
    echo '</div>';
    echo '</div>';
}
?>
