<?php
include '../config/dbConnection.php';  // Include your DB connection
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

// Handle form submission for filtering
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';

// Build query with date range filter
$query = "
    SELECT order_id, product_name, quantity, total_price, order_date 
    FROM sales 
    WHERE seller_id = ?
";
$params = [$seller_id];

if ($start_date && $end_date) {
    $query .= " AND order_date BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
}

$query .= " ORDER BY order_date DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports</title>
    <link href="../assets/css/tailwind.css" rel="stylesheet">
</head>
<body>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Sales Reports</h1>

        <!-- Filter Form -->
        <form action="" method="post" class="mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-gray-700">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="end_date" class="block text-gray-700">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
            </div>
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md mt-4">Filter</button>
        </form>

        <!-- Sales Table -->
        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow">
            <thead>
                <tr class="w-full bg-gray-200 border-b">
                    <th class="p-4 text-left text-gray-600">Order ID</th>
                    <th class="p-4 text-left text-gray-600">Product Name</th>
                    <th class="p-4 text-left text-gray-600">Quantity</th>
                    <th class="p-4 text-left text-gray-600">Total Price</th>
                    <th class="p-4 text-left text-gray-600">Order Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sales_data)): ?>
                    <tr>
                        <td colspan="5" class="p-4 text-center text-gray-600">No sales data available.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sales_data as $sale): ?>
                        <tr>
                            <td class="p-4"><?= htmlspecialchars($sale['order_id']) ?></td>
                            <td class="p-4"><?= htmlspecialchars($sale['product_name']) ?></td>
                            <td class="p-4"><?= htmlspecialchars($sale['quantity']) ?></td>
                            <td class="p-4"><?= htmlspecialchars(number_format($sale['total_price'], 2)) ?></td>
                            <td class="p-4"><?= htmlspecialchars(date('Y-m-d', strtotime($sale['order_date']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
