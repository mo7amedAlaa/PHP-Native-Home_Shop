<?php
session_start();
include '../config/dbConnection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch all unpaid orders for the logged-in user
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT * FROM orders WHERE user_id = ? AND payment_status = ?  AND payment_method = ?');
$stmt->execute([$userId, 'not_paid','online']);
$unpaidOrders = $stmt->fetchAll();

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
    <p class="text-gray-600 font-medium">Unpaid Orders</p>
</div>
<!-- ./breadcrumb -->

<!-- Unpaid Orders Section -->
<div class="container py-8">
    <h2 class="text-2xl font-semibold mb-6">Unpaid Orders</h2>
    <?php if (empty($unpaidOrders)): ?>
        <p class="text-gray-600">You have no unpaid orders at this time.</p>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($unpaidOrders as $order): ?>
                <div class="border border-gray-200 p-6 rounded-lg shadow-lg bg-white">
                    <h3 class="text-xl font-semibold mb-4">Order ID: <?php echo htmlspecialchars($order['id']); ?></h3>
                    <div class="flex justify-between border-b border-gray-200 py-2 text-gray-800 font-medium">
                        <p>Subtotal</p>
                        <p>$<?php echo number_format($order['total'], 2); ?></p>
                    </div>
                    <div class="flex justify-between border-b border-gray-200 py-2 text-gray-800 font-medium">
                        <p>Shipping</p>
                        <p>Free</p>
                    </div>
                    <div class="flex justify-between py-2 text-gray-800 font-medium">
                        <p class="font-semibold">Total</p>
                        <p>$<?php echo number_format($order['total'], 2); ?></p>
                    </div>
                    <div class="mt-4 flex gap-4">
                        <a href="payment_confirmation.php?order_id=<?php echo htmlspecialchars($order['id']); ?>" class="inline-block py-2 px-4 text-white bg-primary border border-primary rounded-md hover:bg-transparent hover:text-primary transition font-semibold">
                            Pay Now
                        </a>
                        <a href="order_details.php?order_id=<?php echo htmlspecialchars($order['id']); ?>" class="inline-block py-2 px-4 text-primary bg-white border border-primary rounded-md hover:bg-primary hover:text-white transition font-semibold">
                            View Details
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<!-- ./Unpaid Orders Section -->

<?php include '../components/footer.php'; ?>
