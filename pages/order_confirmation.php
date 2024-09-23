<?php
session_start();
include '../config/dbConnection.php';

$orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
$paymentMethod = filter_input(INPUT_GET, 'payment_method', FILTER_SANITIZE_STRING);

if (!$orderId) {
    header('Location: home.php');
    exit();
}

$stmt = $conn->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: home.php');
    exit();
}

$stmt = $conn->prepare('SELECT * FROM order_items WHERE order_id = ?');
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll();
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
    <p class="text-gray-600 font-medium">Order Confirmation</p>
</div>
<!-- ./breadcrumb -->

<!-- wrapper -->
<div class="container grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pb-16 pt-4">
    <!-- Order Summary Section -->
    <div class="border border-gray-200 p-6 rounded-lg shadow-lg bg-white col-span-1 md:col-span-2 lg:col-span-2">
        <h3 class="text-2xl font-semibold mb-6">Order Confirmation</h3>
        <div class="mb-6">
            <h4 class="text-gray-800 text-lg mb-4 font-medium uppercase">Order Summary</h4>
            <div class="space-y-4">
                <?php foreach ($orderItems as $item): ?>
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2 mb-2">
                        <div>
                            <h5 class="text-gray-800 font-medium"><?php echo htmlspecialchars($item['product_name']); ?></h5>
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
                    <p>$<?php echo number_format($order['total'], 2); ?></p>
                </div>

                <div class="flex justify-between border-b border-gray-200 py-2 text-gray-800 font-medium uppercase">
                    <p>Shipping</p>
                    <p>Free</p>
                </div>

                <div class="flex justify-between text-gray-800 font-medium py-2 uppercase">
                    <p class="font-semibold">Total</p>
                    <p>$<?php echo number_format($order['total'], 2); ?></p>
                </div>
            </div>
        </div>

        <div>
            <h4 class="text-gray-800 text-lg mb-4 font-medium uppercase">Order Details</h4>
            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($orderId); ?></p>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars(ucfirst($paymentMethod)); ?></p>
        </div>

        <div class="mt-6">
            <h4 class="text-gray-800 text-lg mb-4 font-medium uppercase">Next Steps</h4>
            <?php if ($paymentMethod === 'online'): ?>
                <p class="text-gray-700">Your order is being processed. Please proceed with the payment. <a href="payment_confirmation.php?order_id=<?php echo htmlspecialchars($orderId)?>" class="text-red-500 underline    ">Payment Now</a></p>
            <?php else: ?>
                <p class="text-gray-700">Your order has been placed. Please make the payment upon delivery.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- ./wrapper -->

<?php include '../components/footer.php'; ?>
