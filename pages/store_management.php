<?php
include '../config/dbConnection.php';  // Include your DB connection
session_start();

// Check if the seller is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_name = $_POST['store_name'];
    $store_description = $_POST['store_description'];

    $query = "UPDATE sellers SET store_name = ?, store_description = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt->execute([$store_name, $store_description, $seller_id])) {
        $success_message = "Store information updated successfully.";
    } else {
        $error_message = "Failed to update store information.";
    }
}

// Fetch current store info
$query = "SELECT store_name, store_description FROM sellers WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$seller_id]);
$sellers = $stmt->fetch(PDO::FETCH_ASSOC);

include '../components/header.php';  // Include your page header
?>

<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Store Management</h2>

    <?php if (isset($success_message)): ?>
        <div class="bg-green-100 text-green-800 p-4 mb-4 rounded">
            <?= htmlspecialchars($success_message) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="bg-red-100 text-red-800 p-4 mb-4 rounded">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <form action="store_management.php" method="POST">
        <div class="mb-4">
            <label for="store_name" class="block text-gray-700">Store Name</label>
            <input type="text" id="store_name" name="store_name" value="<?= htmlspecialchars($sellers['store_name'] ?? '') ?>" class="border border-gray-300 p-2 rounded w-full" required>
        </div>

        <div class="mb-4">
            <label for="store_description" class="block text-gray-700">Store Description</label>
            <textarea id="store_description" name="store_description" rows="4" class="border border-gray-300 p-2 rounded w-full" required><?= htmlspecialchars($sellers['store_description'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update Store Information</button>
    </form>
</div>

<?php include '../components/footer.php';  // Include your page footer ?>
