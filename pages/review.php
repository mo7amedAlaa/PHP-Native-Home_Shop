<?php
session_start();
include "../config/dbConnection.php";
include "../components/header.php";

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p class='text-red-600'>Please log in to submit or manage a review.</p>";
    exit();
}

$user_id = $_SESSION['user_id']; // Fetch user ID from session

// Fetch product ID from URL
if (isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];

    try {
        // Fetch product details
        $stmt = $conn->prepare("SELECT name FROM products WHERE id = :item_id");
        $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            echo "<p class='text-red-600'>Product not found.</p>";
            exit();
        }

        // Fetch reviews
        $reviewsStmt = $conn->prepare("SELECT r.*, u.name as user_name FROM reviews r LEFT JOIN users u ON r.user_id = u.id WHERE r.product_id = :item_id ORDER BY r.created_at DESC");
        $reviewsStmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $reviewsStmt->execute();
        $reviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch user's review if exists
        $userReviewStmt = $conn->prepare("SELECT * FROM reviews WHERE product_id = :item_id AND user_id = :user_id");
        $userReviewStmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $userReviewStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $userReviewStmt->execute();
        $userReview = $userReviewStmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo "<p class='text-red-600'>Failed to load product details. Please try again later.</p>";
    }
}

// Initialize success and error messages
$successMessage = "";
$errorMessage = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_review'])) {
        $rating = $_POST['rating'];
        $comment = $_POST['comment'];

        try {
            // Insert review
            $reviewStmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (:item_id, :user_id, :rating, :comment)");
            $reviewStmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
            $reviewStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $reviewStmt->bindParam(':rating', $rating, PDO::PARAM_INT);
            $reviewStmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $reviewStmt->execute();

            $successMessage = "Review submitted successfully.";
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errorMessage = "Failed to submit your review. Please try again later.";
        }
    } elseif (isset($_POST['edit_review'])) {
        $review_id = $_POST['review_id'];
        $rating = $_POST['rating'];
        $comment = $_POST['comment'];

        try {
            // Update review
            $editStmt = $conn->prepare("UPDATE reviews SET rating = :rating, comment = :comment WHERE id = :review_id AND user_id = :user_id");
            $editStmt->bindParam(':review_id', $review_id, PDO::PARAM_INT);
            $editStmt->bindParam(':rating', $rating, PDO::PARAM_INT);
            $editStmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $editStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $editStmt->execute();

            $successMessage = "Review updated successfully.";
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errorMessage = "Failed to update your review. Please try again later.";
        }
    } elseif (isset($_POST['delete_review'])) {
        $review_id = $_POST['review_id'];

        try {
            // Delete review
            $deleteStmt = $conn->prepare("DELETE FROM reviews WHERE id = :review_id AND user_id = :user_id");
            $deleteStmt->bindParam(':review_id', $review_id, PDO::PARAM_INT);
            $deleteStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $deleteStmt->execute();

            $successMessage = "Review deleted successfully.";
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errorMessage = "Failed to delete your review. Please try again later.";
        }
    }

    // Re-fetch the latest reviews and user's review
    try {
        $reviewsStmt = $conn->prepare("SELECT r.*, u.name as user_name FROM reviews r LEFT JOIN users u ON r.user_id = u.id WHERE r.product_id = :item_id ORDER BY r.created_at DESC");
        $reviewsStmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $reviewsStmt->execute();
        $reviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);

        $userReviewStmt = $conn->prepare("SELECT * FROM reviews WHERE product_id = :item_id AND user_id = :user_id");
        $userReviewStmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        $userReviewStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $userReviewStmt->execute();
        $userReview = $userReviewStmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $errorMessage = "Failed to load product details. Please try again later.";
    }
}
?>

<div class="container mx-auto mt-10">
    <h1 class="text-3xl font-bold mb-5">Reviews for <?php echo htmlspecialchars($product['name']); ?></h1>

    <?php if ($successMessage): ?>
        <p class='text-green-600'><?php echo htmlspecialchars($successMessage); ?></p>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <p class='text-red-600'><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>

    <!-- Display User's Review -->
    <?php if ($userReview): ?>
        <div class="bg-white shadow-md rounded-lg p-5 mb-10">
            <h2 class="text-2xl font-semibold mb-4">Your Review</h2>
            <form method="POST" action="">
                <input type="hidden" name="review_id" value="<?php echo htmlspecialchars($userReview['id']); ?>">
                
                <div class="mb-4">
                    <label for="rating" class="block text-gray-700 font-semibold">Rating (out of 5)</label>
                    <select name="rating" id="rating" class="w-full border-gray-300 rounded-md">
                        <option value="5" <?php echo $userReview['rating'] == 5 ? 'selected' : ''; ?>>5 - Excellent</option>
                        <option value="4" <?php echo $userReview['rating'] == 4 ? 'selected' : ''; ?>>4 - Very Good</option>
                        <option value="3" <?php echo $userReview['rating'] == 3 ? 'selected' : ''; ?>>3 - Good</option>
                        <option value="2" <?php echo $userReview['rating'] == 2 ? 'selected' : ''; ?>>2 - Fair</option>
                        <option value="1" <?php echo $userReview['rating'] == 1 ? 'selected' : ''; ?>>1 - Poor</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="comment" class="block text-gray-700 font-semibold">Your Review</label>
                    <textarea name="comment" id="comment" rows="4" class="w-full border-gray-300 rounded-md" required><?php echo htmlspecialchars($userReview['comment']); ?></textarea>
                </div>

                <button type="submit" name="edit_review" class="bg-yellow-500 text-white py-2 px-4 rounded-lg hover:bg-yellow-600">
                    Update Review
                </button>
                <button type="submit" name="delete_review" class="text-red-600 hover:text-red-800 mt-2">
                    Delete Review
                </button>
            </form>
        </div>
    <?php else: ?>
        <!-- Review Form -->
        <div class="bg-white shadow-md rounded-lg p-5 mb-10">
            <h2 class="text-2xl font-semibold mb-4">Submit Your Review</h2>
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="rating" class="block text-gray-700 font-semibold">Rating (out of 5)</label>
                    <select name="rating" id="rating" class="w-full border-gray-300 rounded-md">
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Very Good</option>
                        <option value="3">3 - Good</option>
                        <option value="2">2 - Fair</option>
                        <option value="1">1 - Poor</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="comment" class="block text-gray-700 font-semibold">Your Review</label>
                    <textarea name="comment" id="comment" rows="4" class="w-full border-gray-300 rounded-md" required></textarea>
                </div>

                <button type="submit" name="submit_review" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">
                    Submit Review
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Display All Reviews -->
    <div class="bg-white shadow-md rounded-lg p-5">
        <h2 class="text-2xl font-semibold mb-4">Customer Reviews</h2>
        <?php if ($reviews): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="border-b border-gray-200 py-4">
                    <p class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($review['user_name']); ?></p>
                    <div class="flex items-center my-2">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <?php echo $i < $review['rating'] ? '<i class="fa-solid fa-star text-yellow-400"></i>' : '<i class="fa-regular fa-star text-yellow-400"></i>'; ?>
                        <?php endfor; ?>
                    </div>
                    <p class="text-gray-600"><?php echo htmlspecialchars($review['comment']); ?></p>
                    <span class="text-sm text-gray-500"><?php echo htmlspecialchars($review['created_at']); ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-600">No reviews yet for this product.</p>
        <?php endif; ?>
    </div>
</div>

<?php include "../components/footer.php"; ?>
