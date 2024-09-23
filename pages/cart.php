<?php
session_start();
include '../config/dbConnection.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function getProductDetails($conn, $product_id) {
    $stmt = $conn->prepare("SELECT name, base_price, discount FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_POST['update'])) {
    $product_id = $_POST['product_id'];
    $action = $_POST['update'];
    $new_quantity = (int)$_POST['quantity'];
    
    if ($new_quantity <= 0) {
        echo "<p class='text-red-500'>Quantity must be greater than zero.</p>";
    } else {
        $product = getProductDetails($conn, $product_id);
        if ($product) {
            if ($action === 'increment') {
                $_SESSION['cart'][$product_id]['quantity'] += 1;
            } elseif ($action === 'decrement') {
                $_SESSION['cart'][$product_id]['quantity'] -= 1;
                if ($_SESSION['cart'][$product_id]['quantity'] <= 0) {
                    unset($_SESSION['cart'][$product_id]);
                }
            }
        } else {
            echo "<p class='text-red-500'>Product not found.</p>";
        }
    }

    header("Refresh:0");
    exit();
}

if (isset($_POST['delete'])) {
    $product_id = $_POST['product_id'];
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    } else {
        echo "<p class='text-red-500'>Product not found in the cart.</p>";
    }

    header("Refresh:0");
    exit();
}

if (isset($_POST['clear_cart'])) {
    $_SESSION['cart'] = [];
    header("Refresh:0");
    exit();
}

include "../components/header.php";
?>

<div class="container mx-auto p-6">
    <h2 class="text-3xl font-bold mb-6">Your Cart</h2>

    <?php if (!empty($_SESSION['cart'])): ?>
        <form method="POST" action="cart.php" class="bg-white p-6 shadow-lg rounded-lg">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-100 text-gray-600">
                        <th class="border border-gray-300 p-4 text-left">Product</th>
                        <th class="border border-gray-300 p-4 text-left">Price</th>
                        <th class="border border-gray-300 p-4 text-left">Quantity</th>
                        <th class="border border-gray-300 p-4 text-left">Total</th>
                        <th class="border border-gray-300 p-4 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_price = 0;
                    $empty_cart = true;
                    foreach ($_SESSION['cart'] as $product_id => $cart_item):
                        $product = getProductDetails($conn, $product_id);
                        if ($product):
                            $empty_cart = false;
                            $product_name = htmlspecialchars($product['name']);
                            $product_price = number_format($product['base_price'] - $product['base_price'] * ($product['discount'] / 100), 2);
                            $quantity = $cart_item['quantity'];
                            $total = ($product['base_price'] - $product['base_price'] * ($product['discount'] / 100)) * $quantity;
                            $total_price += $total;
                    ?>
                        <tr class="border-b border-gray-200">
                            <td class="border border-gray-300 p-4"><?php echo $product_name; ?></td>
                            <td class="border border-gray-300 p-4">$<?php echo $product_price; ?></td>
                            <td class="border border-gray-300 p-4">
                                <div class="flex items-center space-x-2">
                                    <form method="POST" action="cart.php" style="display: inline;">
                                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">
                                        <button type="submit" name="update" value="decrement" class="bg-gray-300 text-gray-700 px-3 py-1 rounded">-</button>
                                        <input type="number" name="quantity" value="<?php echo htmlspecialchars($quantity); ?>" min="1" class="w-20 text-center border border-gray-300 rounded">
                                        <button type="submit" name="update" value="increment" class="bg-gray-300 text-gray-700 px-3 py-1 rounded">+</button>
                                    </form>
                                </div>
                            </td>
                            <td class="border border-gray-300 p-4">$<?php echo number_format($total, 2); ?></td>
                            <td class="border border-gray-300 p-4">
                                <form method="POST" action="cart.php">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">
                                    <button type="submit" name="delete" class="bg-red-500 text-white px-4 py-2 rounded">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php
                        else:
                            echo "<tr><td colspan='5' class='text-red-500 p-4'>Error fetching product details for product ID $product_id.</td></tr>";
                        endif;
                    endforeach;

                    if ($empty_cart):
                        echo "<tr><td colspan='5' class='p-4 text-center'>Your cart is empty.</td></tr>";
                    endif;
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="border border-gray-300 p-4 text-right font-semibold">Total Price:</td>
                        <td class="border border-gray-300 p-4">$<?php echo number_format($total_price, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
            <div class="mt-6 flex justify-between">
                <button type="submit" name="clear_cart" class="bg-red-500 text-white px-6 py-2 rounded">Clear Cart</button>
                <a href="checkout.php" class="bg-blue-500 text-white px-6 py-2 rounded">Checkout</a>
            </div>
        </form>
    <?php else: ?>
        <p class="text-center text-gray-600">Your cart is empty.</p>
    <?php endif; ?>
</div>

<?php include "../components/footer.php"; ?>
