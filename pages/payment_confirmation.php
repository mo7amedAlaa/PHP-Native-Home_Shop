<?php
session_start();
include '../config/dbConnection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

if (!$orderId) {
    header('Location: home.php');
    exit();
}

// Fetch the order details
$stmt = $conn->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: home.php');
    exit();
}
if ($order['payment_status']=='paid') {
    header('Location: home.php?message=sorry this order already paid .');
    exit();
}

$subtotal = $order['total'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  
    $paymentSuccess = true;  

    if ($paymentSuccess) {
  
        $updateStmt = $conn->prepare('UPDATE orders SET status = ?, payment_status = ? WHERE id = ?');
        $updateStmt->execute(['paid', 'paid', $orderId]);

    
        header('Location: order_confirmation.php');
        exit();
    } else {
      
        $updateStmt = $conn->prepare('UPDATE orders SET payment_status = ? WHERE id = ?');
        $updateStmt->execute(['failed', $orderId]);
        $error = 'Payment failed. Please try again.';
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
    <p class="text-gray-600 font-medium">Payment & Confirmation</p>
</div>
<!-- ./breadcrumb -->

<!-- wrapper -->
<div class="container grid grid-cols-1 md:grid-cols-2 gap-6 pb-16 pt-4">
    <!-- Payment Form Section -->
    <div class="border border-gray-200 p-6 rounded-lg shadow-lg bg-white col-span-1 md:col-span-2 lg:col-span-1">
        <h3 class="text-2xl font-semibold mb-6">Payment Details</h3>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                <strong class="font-bold">Error!</strong>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>
        <form action="payment_confirmation.php?order_id=<?php echo htmlspecialchars($orderId); ?>" method="POST" class="space-y-6">
            <div>
                <label for="card_number" class="block text-gray-700 text-sm font-medium mb-1">Card Number <span class="text-red-500">*</span></label>
                <input type="text" name="card_number" id="card_number" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="card_name" class="block text-gray-700 text-sm font-medium mb-1">Cardholder Name <span class="text-red-500">*</span></label>
                    <input type="text" name="card_name" id="card_name" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                </div>
                <div>
                    <label for="expiry_date" class="block text-gray-700 text-sm font-medium mb-1">Expiry Date <span class="text-red-500">*</span></label>
                    <input type="text" name="expiry_date" id="expiry_date" placeholder="MM/YY" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                </div>
                <div>
                    <label for="cvv" class="block text-gray-700 text-sm font-medium mb-1">CVV <span class="text-red-500">*</span></label>
                    <input type="text" name="cvv" id="cvv" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                </div>
            </div>
            <button type="submit" class="w-full py-3 px-4 text-white bg-primary border border-primary rounded-md hover:bg-transparent hover:text-primary transition font-semibold">
                Pay Now
            </button>
        </form>
    </div>

    <!-- Order Summary Section -->
    <div class="border border-gray-200 p-6 rounded-lg shadow-lg bg-white col-span-1 md:col-span-1 lg:col-span-1">
        <h4 class="text-lg font-semibold mb-4">Order Summary</h4>
        <div class="space-y-4">
            <div class="flex justify-between border-b border-gray-200 py-2 text-gray-800 font-medium uppercase">
                <p>Order ID</p>
                <p><?php echo htmlspecialchars($orderId); ?></p>
            </div>

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
