<?php
include '../config/dbConnection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

function getUserInfo($conn, $userId)
{
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return null;
    }

    $sql = $user['user_type'] === 'seller' 
        ? "SELECT * FROM sellers WHERE user_id = ?"
        : "SELECT * FROM customers WHERE user_id = ?";
        
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);
    $additionalInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return array_merge($user, $additionalInfo);
}
$userInfo = getUserInfo($conn, $userId);

// Handle add to watchlist
if (isset($_POST['add_to_watchlist'])) {
    $item_id = $_POST['item_id'];

    try {
        $stmt = $conn->prepare("INSERT INTO watchlist (user_id, item_id) VALUES (:user_id, :item_id)");
        $stmt->execute(['user_id' => $userId, 'item_id' => $item_id]);
        header("Location: " . $_SERVER['PHP_SELF']);  // Redirect to avoid form resubmission 
        exit();
    } catch (PDOException $e) {
        // Log error message and show a user-friendly message
        error_log($e->getMessage());
        echo "<p class='text-red-600'>Failed to add item to watchlist. Please try again later.</p>";
    }
}

// Handle remove from watchlist
if (isset($_POST['remove_from_watchlist'])) {
    $item_id = $_POST['item_id'];

    try {
        $stmt = $conn->prepare("DELETE FROM watchlist WHERE user_id = :user_id AND item_id = :item_id");
        $stmt->execute(['user_id' => $userId, 'item_id' => $item_id]);
        header("Location: " . $_SERVER['PHP_SELF']); // Redirect to avoid form resubmission
        exit();
    } catch (PDOException $e) {
        // Log error message and show a user-friendly message
        error_log($e->getMessage());
        echo "<p class='text-red-600'>Failed to remove item from watchlist. Please try again later.</p>";
    }
}

// Fetch watchlist items
try {
    $stmt = $conn->prepare("SELECT item_id FROM watchlist WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    $watchlist_items = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    if (empty($watchlist_items)) {
        $watchlist_items = [];
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $watchlist_items = [];
}

include '../components/header.php';
?>

<!-- breadcrumb -->
<div class="container py-4 flex items-center gap-3">
    <a href="home.php" class="text-primary text-base">
        <i class="fa-solid fa-house"></i>
    </a>
    <span class="text-sm text-gray-400">
            <i class="fa-solid fa-chevron-right"></i>
        </span>
    <p class="text-gray-600 font-medium">Profile</p>
</div>
<!-- ./breadcrumb -->

<!-- wrapper -->
<div class="container grid grid-cols-12 items-start gap-6 pt-4 pb-16">

    <!-- sidebar -->
    <?php include "../components/sidebar.php"; ?>
    <!-- ./sidebar -->

    <!-- wishlist -->
    <div class="col-span-9 space-y-4">
        <?php if (empty($watchlist_items)): ?>
            <p class="text-gray-600">Your watchlist is empty.</p>
        <?php else: ?>
            <?php foreach ($watchlist_items as $item_id): ?>
                <?php
                try {
                    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :item_id");
                    $stmt->execute(['item_id' => $item_id]);
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    error_log($e->getMessage());
                    continue; // Skip this item and continue with the next
                }
                ?>
                <div class="flex items-center justify-between border gap-6 p-4 border-gray-200 rounded">
                    <div class="w-28">
                        <img src="../assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full">
                    </div>
                    <div class="w-1/3">
                        <h2 class="text-gray-800 text-xl font-medium uppercase"><?php echo htmlspecialchars($product['name']); ?></h2>
                        <p class="text-gray-500 text-sm">Availability: <span class="<?php echo $product['stock'] ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $product['stock'] ? 'In Stock' : 'Out of Stock'; ?>
                        </span></p>
                    </div>
                    <div class="text-primary text-lg font-semibold">$<?php echo htmlspecialchars($product['base_price']); ?></div>
                    <form method="POST" action="add_to_cart.php">
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                         <input type="hidden" name="redirect" value="wishlist.php">
                        <button type="submit" class="block w-full py-1 text-center text-white bg-primary border border-primary rounded-b hover:bg-transparent hover:text-primary transition">Add to cart</button>
                    </form>
                    <form method="post" action="">
                        <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item_id); ?>">
                        <button type="submit" name="remove_from_watchlist" class="text-gray-600 cursor-pointer hover:text-primary">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <!-- ./wishlist -->

</div>
<!-- ./wrapper -->

<?php include '../components/footer.php'; ?>
