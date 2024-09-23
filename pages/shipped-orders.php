<?php
session_start();
include '../config/dbConnection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch the shipped orders for the logged-in user
$stmt = $conn->prepare('SELECT * FROM orders WHERE user_id = ? AND status = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['user_id'], 'shipped']);
$shippedOrders = $stmt->fetchAll();
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
    <p class="text-gray-600 font-medium">Shipped Orders</p>
</div>
<!-- ./breadcrumb -->

<!-- Shipped Orders Section -->
<div class="container py-8">
    <h2 class="text-2xl font-semibold mb-6">Shipped Orders</h2>

    <?php if (count($shippedOrders) === 0): ?>
        <p>No shipped orders found.</p>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($shippedOrders as $order): ?>
                <div class="border border-gray-200 p-6 rounded-lg shadow-lg bg-white">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="text-lg font-semibold">Order #<?php echo htmlspecialchars($order['id']); ?></h4>
                            <p class="text-gray-600"> Delivery Date: <?php echo htmlspecialchars(date("Y-m-d", strtotime($order['created_at']."+3 days")) ); ?></p>
                            <p class="text-gray-600">Status: <?php echo htmlspecialchars($order['status']); ?></p>
                        </div>
                        <div>
                            <a href="order_details.php?order_id=<?php echo htmlspecialchars($order['id']); ?>" class="text-primary underline">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<!-- ./Shipped Orders Section -->

<?php include '../components/footer.php'; ?>
