<?php
session_start();
include '../config/dbConnection.php';

 
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
 
$stmt = $conn->prepare('SELECT * FROM orders WHERE user_id = ? AND status = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['user_id'], 'cancelled']);
$cancelledOrders = $stmt->fetchAll();
 
if (isset($_GET['delete_order_id'])) {
    $deleteOrderId = filter_input(INPUT_GET, 'delete_order_id', FILTER_VALIDATE_INT);

    if ($deleteOrderId) {
        $deleteStmt = $conn->prepare('DELETE FROM orders WHERE id = ? AND user_id = ?');
        $deleteStmt->execute([$deleteOrderId, $_SESSION['user_id']]);
        header('Location: cancelled-order.php');
        exit();
    }
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all'])) {
    $deleteAllStmt = $conn->prepare('DELETE FROM orders WHERE user_id = ? AND status = ?');
    $deleteAllStmt->execute([$_SESSION['user_id'], 'cancelled']);
    header('Location: cancelled-order.php');
    exit();
}
?>

<?php include '../components/header.php'; ?>

 
<div class="container py-4 flex items-center gap-3">
    <a href="home.php" class="text-primary text-base">
        <i class="fa-solid fa-house"></i>
    </a>
    <span class="text-sm text-gray-400">
        <i class="fa-solid fa-chevron-right"></i>
    </span>
    <p class="text-gray-600 font-medium">Cancelled Orders</p>
</div>
 
<div class="container py-8">
    <h2 class="text-2xl font-semibold mb-6">Cancelled Orders</h2>

    <?php if (count($cancelledOrders) === 0): ?>
        <p>No cancelled orders found.</p>
    <?php else: ?>
        <form action="cancelled-order.php" method="POST" class="mb-6">
            <button type="submit" name="delete_all" class="py-2 px-4 text-white bg-red-500 border border-red-500 rounded-md hover:bg-red-600 transition font-semibold">
                Delete All Cancelled Orders
            </button>
        </form>
        <div class="space-y-4">
            <?php foreach ($cancelledOrders as $order): ?>
                <div class="border border-gray-200 p-6 rounded-lg shadow-lg bg-white">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="text-lg font-semibold">Order #<?php echo htmlspecialchars($order['id']); ?></h4>
                            <p class="text-gray-600">Order Date: <?php echo htmlspecialchars($order['order_date']); ?></p>
                            <p class="text-gray-600">Status: <?php echo htmlspecialchars($order['status']); ?></p>
                        </div>
                        <div class="flex gap-4">
                            <a href="order_details.php?order_id=<?php echo htmlspecialchars($order['id']); ?>" class="text-primary underline">View Details</a>
                            <a href="cancelled-order.php?delete_order_id=<?php echo htmlspecialchars($order['id']); ?>" 
                               class="text-red-500 underline">
                                Delete
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
 
<?php include '../components/footer.php'; ?>
