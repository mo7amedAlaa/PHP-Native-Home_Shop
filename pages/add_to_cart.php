<?php
session_start();
include '../config/dbConnection.php'; 

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function getProductDetails($conn, $product_id) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += 1;
    } else {
        $product = getProductDetails($conn, $product_id);
        if ($product) {
            $price = ($product['discount'] > 0) ? 
                     $product['base_price'] - ($product['base_price'] * ($product['discount'] / 100)) : 
                     $product['base_price'];

            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => $price,
                'size' => $product['size'],
                'color' => $product['color'],
                'brand' => $product['brand'],
                'quantity' => 1
            ];
        }
    }

    if (isset($_POST['redirect'])) {
        header("Location: " . $_POST['redirect']);
    } else {
        header("Location: cart.php"); 
    }
    exit;
}
?>
