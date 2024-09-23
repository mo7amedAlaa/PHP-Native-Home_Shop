<?php
include '../config/dbConnection.php'; // Include your DB connection
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

// Fetch store information
$query = "SELECT store_name, personal_image FROM sellers WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$seller_id]);
$store_info = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!-- Sidebar -->
<div class="col-span-3">
    <!-- Store Manager Info -->
    <div class="px-4 py-3 shadow flex items-center gap-4">
        <div class="flex-shrink-0">
            <img src="../assets/images/uploads/<?= htmlspecialchars($store_info['personal_image'] ?? '../assets/images/default-avatar.jpg') ?>" alt="store-logo" class="w-14 h-14 object-cover">
        </div>
        <div class="flex-grow flex items-center gap-4  ">
            <i class="fa-solid fa-store "> </i>
            <h4 class="text-gray-800 font-medium"><?= htmlspecialchars($store_info['store_name'] ?? 'Store Manager') ?></h4>
        </div>
    </div>

    <!-- Main Seller Actions -->
    <div class="mt-6 bg-white shadow rounded p-4 divide-y divide-gray-200 space-y-4 text-gray-600">
        <!-- Product Management -->
        <div class="space-y-1 pl-8">
            <h3 class="text-gray-700 font-semibold uppercase">Product Management</h3>
            <a href="../pages/product.php" class="relative hover:text-primary block font-medium capitalize transition">
                <span class="absolute -left-8 top-0 text-base">
                    <i class="fa-solid fa-box"></i>
                </span>
                View & Manage Products
            </a>
            <a href="../pages/add_product.php" class="relative hover:text-primary block capitalize transition">
                <span class="absolute -left-8 top-0 text-base">
                    <i class="fa-solid fa-plus"></i>
                </span>
                Add New Product
            </a>
        </div>

        <!-- Order Management -->
        <div class="space-y-1 pl-8 pt-4">
            <h3 class="text-gray-700 font-semibold uppercase">Order Management</h3>
            <a href="../pages/orders.php" class="relative hover:text-primary block font-medium capitalize transition">
                <span class="absolute -left-8 top-0 text-base">
                    <i class="fa-solid fa-receipt"></i>
                </span>
                View Incoming Orders
            </a>
        </div>

        <!-- Account Management -->
        <div class="space-y-1 pl-8 pt-4">
            <h3 class="text-gray-700 font-semibold uppercase">Account Management</h3>
            <a href="../pages/profile.php" class="relative hover:text-primary block font-medium capitalize transition">
                <span class="absolute -left-8 top-0 text-base">
                    <i class="fa-solid fa-user"></i>
                </span>
                Edit Account Details
            </a>
            <a href="../pages/store_management.php" class="relative hover:text-primary block capitalize transition">
                <span class="absolute -left-8 top-0 text-base">
                    <i class="fa-solid fa-store"></i>
                </span>
                Manage Store
            </a>
            <a href="../pages/payment_settings.php" class="relative hover:text-primary block capitalize transition">
                <span class="absolute -left-8 top-0 text-base">
                    <i class="fa-solid fa-credit-card"></i>
                </span>
                Manage Payment Settings
            </a>
        </div>

        <!-- Sales Reports -->
        <div class="space-y-1 pl-8 pt-4">
            <h3 class="text-gray-700 font-semibold uppercase">Sales Reports</h3>
            <a href="../pages/sales_reports.php" class="relative hover:text-primary block font-medium capitalize transition">
                <span class="absolute -left-8 top-0 text-base">
                    <i class="fa-solid fa-chart-line"></i>
                </span>
                View Sales Reports
            </a>
            <a href="../pages/export_reports.php" class="relative hover:text-primary block capitalize transition">
                <span class="absolute -left-8 top-0 text-base">
                    <i class="fa-solid fa-file-export"></i>
                </span>
                Export Reports
            </a>
        </div>

        <!-- Discounts and Promotions -->
        <div class="space-y-1 pl-8 pt-4">
            <h3 class="text-gray-700 font-semibold uppercase">Discounts and Promotions</h3>
            <a href="../pages/add_discount.php" class="relative hover:text-primary block font-medium capitalize transition">
                <span class="absolute -left-8 top-0 text-base">
                    <i class="fa-solid fa-tags"></i>
                </span>
                Add Discount or Promotion
            </a>
            <a href="../pages/edit_discount.php" class="relative hover:text-primary block capitalize transition">
                <span class="absolute -left-8 top-0 text-base">
                    <i class="fa-solid fa-pencil-alt"></i>
                </span>
                Edit or Remove Discounts
            </a>
        </div>

        <!-- Customer Communication -->
        <div class="space-y-1 pl-8 pt-4">
            <h3 class="text-gray-700 font-semibold uppercase">Customer Communication</h3>
            <a href="../pages/customer_inquiries.php" class="relative hover:text-primary block font-medium capitalize transition">
                <span class="absolute -left-8 top-0 text-base">
                    <i class="fa-solid fa-comments"></i>
                </span>
                Reply to Customer Inquiries
            </a>
            <a href="../pages/send_notifications.php" class="relative hover:text-primary block capitalize transition">
                <span class="absolute -left-8 top-0 text-base">
                    <i class="fa-solid fa-bell"></i>
                </span>
                Send Notifications to Customers
            </a>
        </div>

        <!-- Inventory Management -->
        <div class="space-y-1 pl-8 pt-4">
            <h3 class="text-gray-700 font-semibold uppercase">Inventory Management</h3>
            <a href="../pages/inventory_update.php" class="relative hover:text-primary block font-medium capitalize transition">
                <span class="absolute -left-8 top-0 text-base">
                    <i class="fa-solid fa-boxes"></i>
                </span>
                Update Quantities
            </a>
            <a href="../pages/stock_notifications.php" class="relative hover:text-primary block capitalize transition">
                <span class="absolute -left-8 top-0 text-base">
                    <i class="fa-solid fa-bell-exclamation"></i>
                </span>
                Stock Notifications
            </a>
        </div>

        <!-- Reviews and Ratings -->
        <div class="space-y-1 pl-8 pt-4">
            <h3 class="text-gray-700 font-semibold uppercase">Reviews and Ratings</h3>
            <a href="../pages/reviews.php" class="relative hover:text-primary block font-medium capitalize transition">
                <span class="absolute -left-8 top-0 text-base">
                    <i class="fa-solid fa-star"></i>
                </span>
                View Reviews and Ratings
            </a>
            <a href="../pages/reply_reviews.php" class="relative hover:text-primary block capitalize transition">
                <span class="absolute -left-8 top-0 text-base">
                    <i class="fa-solid fa-reply"></i>
                </span>
                Reply to Reviews
            </a>
        </div>

        <!-- Logout -->
        <div class="space-y-1 pl-8 pt-4">
            <a href="../pages/logout.php" class="relative hover:text-primary block font-medium capitalize transition">
                <span class="absolute -left-8 top-0 text-base">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </span>
                Logout
            </a>
        </div>
    </div>
</div>
<!-- ./Sidebar -->
