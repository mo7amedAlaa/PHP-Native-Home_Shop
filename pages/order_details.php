<?php
session_start();
include '../config/dbConnection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get the order ID from the URL
$orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

if (!$orderId) {
    header('Location: orders.php');
    exit();
}

// Fetch the order details
$stmt = $conn->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Fetch the items in the order
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
    <a href="order-history.php" class="text-primary text-base">Orders</a>
    <span class="text-sm text-gray-400">
        <i class="fa-solid fa-chevron-right"></i>
    </span>
    <p class="text-gray-600 font-medium">Order Details</p>
</div>
<!-- ./breadcrumb -->

<!-- Order Details Section -->
<div class="container py-8">
    <h2 class="text-2xl font-semibold mb-6">Order #<?php echo htmlspecialchars($order['id']); ?></h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Order Info -->
        <div class="border border-gray-200 p-6 rounded-lg shadow-lg bg-white">
            <h4 class="text-lg font-semibold mb-4">Order Information</h4>
            <div class="space-y-2">
                <div class="flex justify-between text-gray-800 font-medium">
                    <p>Order Date:</p>
                    <p><?php echo htmlspecialchars($order['created_at']); ?></p>
                </div>
                <div class="flex justify-between text-gray-800 font-medium">
                    <p>Order Status:</p>
                    <p><?php echo htmlspecialchars($order['status']); ?></p>
                </div>
                <div class="flex justify-between text-gray-800 font-medium">
                    <p>Payment Status:</p>
                    <p><?php echo htmlspecialchars($order['payment_status']); ?></p>
                </div>
                <div class="flex justify-between text-gray-800 font-medium">
                    <p>Subtotal:</p>
                    <p>$<?php echo number_format($order['total'], 2); ?></p>
                </div>
                <div class="flex justify-between text-gray-800 font-medium">
                    <p>Shipping:</p>
                    <p>Free</p>
                </div>
                <div class="flex justify-between text-gray-800 font-semibold">
                    <p>Total:</p>
                    <p>$<?php echo number_format($order['total'], 2); ?></p>
                </div>
            </div>
        </div>

        <!-- Ordered Items -->
        <div class="border border-gray-200 p-6 rounded-lg shadow-lg bg-white">
            <h4 class="text-lg font-semibold mb-4">Items Ordered</h4>
            <div class="space-y-4">
                <?php if (count($orderItems) === 0): ?>
                    <p>No items in this order.</p>
                <?php else: ?>
                    <?php foreach ($orderItems as $item): ?>
                        <div class="flex justify-between border-b border-gray-200 py-2 text-gray-800 font-medium">
                            <p><?php echo htmlspecialchars($item['product_name']); ?></p>
                            <p>$<?php echo number_format($item['price'], 2); ?> (x<?php echo htmlspecialchars($item['quantity']); ?>)</p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Back to Orders Button -->
    <a href="order-history.php" class="text-primary underline">Back to Orders</a>
</div>
<!-- ./Order Details Section -->

<?php include '../components/footer.php'; ?>
