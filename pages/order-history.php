<?php
session_start();
include '../config/dbConnection.php';

 
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

 
$stmt = $conn->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at  DESC');
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $orderId = $_POST['order_id'];
    
    $checkStmt = $conn->prepare('SELECT status FROM orders WHERE id = ? AND user_id = ?');
    $checkStmt->execute([$orderId, $_SESSION['user_id']]);
    $orderStatus = $checkStmt->fetchColumn();
    
    if ($orderStatus !== 'shipped' && $orderStatus !== 'cancelled') {
        $cancelStmt = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $cancelStmt->execute(['cancelled', $orderId]);
    }
    header('Location: order-history.php');    
    exit();
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
    <p class="text-gray-600 font-medium">Order History</p>
</div>
<!-- ./breadcrumb -->

<!-- Orders Section -->
<div class="container py-8">
    <h2 class="text-2xl font-semibold mb-6">Order History</h2>

    <?php if (count($orders) === 0): ?>
        <p>No orders found.</p>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($orders as $order): ?>
                <div class="border border-gray-200 p-6 rounded-lg shadow-lg bg-white">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="text-lg font-semibold">Order #<?php echo htmlspecialchars($order['id']); ?></h4>
                            <p class="text-gray-600">Order Date: <?php echo htmlspecialchars($order['created_at']); ?></p>
                            <p class="text-gray-600">Status: <?php echo htmlspecialchars($order['status']); ?></p>
                        </div>
                        <div>
                            <a href="order_details.php?order_id=<?php echo htmlspecialchars($order['id']); ?>" class="text-primary underline">View Details</a>
                        </div>
                    </div>

                     
                    <?php if ($order['status'] !== 'shipped' && $order['status'] !== 'cancelled'): ?>
                        <form method="POST" class="mt-4">
                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
                            <button type="submit" name="cancel_order" class="bg-red-500 text-white px-4 py-2 rounded-md">
                                Cancel Order
                            </button>
                        </form>
                    <?php elseif ($order['status'] === 'cancelled'): ?>
                        <p class="text-red-600 font-semibold mt-4">This order has been canceled.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<!-- ./Orders Section -->

<?php include '../components/footer.php'; ?>
