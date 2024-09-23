<?php
session_start();
include '../config/dbConnection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$cart = $_SESSION['cart'] ?? [];
$subtotal = 0;
$errors = [];

foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  
    $firstName = filter_input(INPUT_POST, 'first-name', FILTER_SANITIZE_STRING);
    $lastName = filter_input(INPUT_POST, 'last-name', FILTER_SANITIZE_STRING);
    $company = filter_input(INPUT_POST, 'company', FILTER_SANITIZE_STRING);
    $region = filter_input(INPUT_POST, 'region', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $paymentMethod = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);
    $total = $subtotal;

    if (!$email) {
        $errors[] = 'Invalid email address';
    }

    foreach ($cart as $id => $item) {
        $stmt = $conn->prepare('SELECT stock FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if ($product && $product['stock'] < $item['quantity']) {
            $errors[] = "Insufficient stock for " . htmlspecialchars($item['name']);
        }
    }

    if (empty($errors)) {
        try {
            
            $stmt = $conn->prepare('INSERT INTO orders (user_id, first_name, last_name, company, region, address, city, phone, email, total, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$userId, $firstName, $lastName, $company, $region, $address, $city, $phone, $email, $total, $paymentMethod]);
            $orderId = $conn->lastInsertId();

          
            $stmt = $conn->prepare('INSERT INTO order_items (order_id, product_id, product_name, size, quantity, price, brand, color) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            foreach ($cart as $id => $item) {
                $stmt->execute([$orderId, $id, $item['name'], $item['size'], $item['quantity'], $item['price'], $item['brand'], $item['color']]);
                
                $stmtUpdate = $conn->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
                $stmtUpdate->execute([$item['quantity'], $id]);
            }

            // Clear cart
            unset($_SESSION['cart']);

            // Redirect to confirmation page
            header('Location: order_confirmation.php?order_id=' . $orderId . '&payment_method=' . urlencode($paymentMethod));
            exit();
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<?php include '../components/header.php'; ?>

<!-- breadcrumb -->
<div class="container py-4 flex items-center gap-3">
    <a href="home.php" class="text-primary text-base">
        <i class="fa-solid fa-house"></i>
    </a>
    <span class="text-sm text-gray-400">
        <i class="fa-solid fa-chevron-right"></i>
    </span>
    <p class="text-gray-600 font-medium">Checkout</p>
</div>
<!-- ./breadcrumb -->

<!-- wrapper -->
<div class="container grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pb-16 pt-4">
    <!-- Form Section -->
    <div class="border border-gray-200 p-6 rounded-lg shadow-lg bg-white col-span-1 md:col-span-2 lg:col-span-2">
        <h3 class="text-2xl font-semibold mb-6">Checkout</h3>
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                <strong class="font-bold">Error!</strong>
                <ul class="list-disc pl-5">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form action="checkout.php" method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="first-name" class="block text-gray-700 text-sm font-medium mb-1">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first-name" id="first-name" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                </div>
                <div>
                    <label for="last-name" class="block text-gray-700 text-sm font-medium mb-1">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last-name" id="last-name" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                </div>
            </div>
            <div>
                <label for="company" class="block text-gray-700 text-sm font-medium mb-1">Company</label>
                <input type="text" name="company" id="company" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label for="region" class="block text-gray-700 text-sm font-medium mb-1">Country/Region</label>
                <input type="text" name="region" id="region" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label for="address" class="block text-gray-700 text-sm font-medium mb-1">Street Address</label>
                <input type="text" name="address" id="address" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label for="city" class="block text-gray-700 text-sm font-medium mb-1">City</label>
                <input type="text" name="city" id="city" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label for="phone" class="block text-gray-700 text-sm font-medium mb-1">Phone Number</label>
                <input type="text" name="phone" id="phone" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label for="email" class="block text-gray-700 text-sm font-medium mb-1">Email Address <span class="text-red-500">*</span></label>
                <input type="email" name="email" id="email" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
            </div>
            <div>
                <label for="payment_method" class="block text-gray-700 text-sm font-medium mb-1">Payment Method</label>
                <select name="payment_method" id="payment_method" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                    <option value="cash">Cash on Delivery</option>
                    <option value="online">Online Payment</option>
                </select>
            </div>
            <button type="submit" class="w-full py-3 px-4 text-white bg-primary border border-primary rounded-md hover:bg-transparent hover:text-primary transition font-semibold">
                Place Order
            </button>
        </form>
    </div>

    <!-- Order Summary Section -->
    <div class="border border-gray-200 p-6 rounded-lg shadow-lg bg-white col-span-1 md:col-span-1 lg:col-span-1">
        <h4 class="text-lg font-semibold mb-4">Order Summary</h4>
        <div class="space-y-4">
            <?php foreach ($cart as $item): ?>
                <div class="flex justify-between items-center border-b border-gray-200 pb-2 mb-2">
                    <div>
                        <h5 class="text-gray-800 font-medium"><?php echo htmlspecialchars($item['name']); ?></h5>
                        <p class="text-sm text-gray-600">Size: <?php echo htmlspecialchars($item['size']); ?></p>
                        <p class="text-sm text-gray-600">Brand: <?php echo htmlspecialchars($item['brand']); ?></p>
                        <p class="text-sm text-gray-600">Color: <?php echo htmlspecialchars($item['color']); ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-gray-600">x<?php echo htmlspecialchars($item['quantity']); ?></p>
                        <p class="text-gray-800 font-medium">$<?php echo htmlspecialchars($item['price'] * $item['quantity']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="flex justify-between border-b border-gray-200 py-2 text-gray-800 font-medium uppercase">
                <p>Subtotal</p>
                <p>$<?php echo number_format($subtotal, 2); ?></p>
            </div>

            <div class="flex justify-between border-b border-gray-200 py-2 text-gray-800 font-medium uppercase">
                <p>Shipping</p>
                <p>Free</p>
            </div>

            <div class="flex justify-between text-gray-800 font-medium py-2 uppercase">
                <p class="font-semibold">Total</p>
                <p>$<?php echo number_format($subtotal, 2); ?></p>
            </div>
        </div>
    </div>
</div>
<!-- ./wrapper -->

<?php include '../components/footer.php'; ?>
