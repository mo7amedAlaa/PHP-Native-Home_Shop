<?php
session_start();
include "../config/dbConnection.php";
include "../components/header.php";

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p class='text-red-600'>Please log in to view your reviews.</p>";
    exit();
}

$user_id = $_SESSION['user_id']; // Fetch user ID from session

// Initialize success and error messages
$successMessage = "";
$errorMessage = "";

// Handle form submission for editing or deleting reviews
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_review'])) {
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

    // Re-fetch the latest user's reviews
    try {
        $userReviewsStmt = $conn->prepare("SELECT r.*, p.name as product_name FROM reviews r LEFT JOIN products p ON r.product_id = p.id WHERE r.user_id = :user_id ORDER BY r.created_at DESC");
        $userReviewsStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $userReviewsStmt->execute();
        $userReviews = $userReviewsStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $errorMessage = "Failed to load your reviews. Please try again later.";
    }
} else {
    // Fetch the user's reviews when the page loads
    try {
        $userReviewsStmt = $conn->prepare("SELECT r.*, p.name as product_name FROM reviews r LEFT JOIN products p ON r.product_id = p.id WHERE r.user_id = :user_id ORDER BY r.created_at DESC");
        $userReviewsStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $userReviewsStmt->execute();
        $userReviews = $userReviewsStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $errorMessage = "Failed to load your reviews. Please try again later.";
    }
}
?>

<div class="container mx-auto mt-10">
    <h1 class="text-3xl font-bold mb-5">Your Reviews</h1>

    <?php if ($successMessage): ?>
        <p class='text-green-600'><?php echo htmlspecialchars($successMessage); ?></p>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <p class='text-red-600'><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>

    <?php if ($userReviews): ?>
        <div class="bg-white shadow-md rounded-lg p-5">
            <?php foreach ($userReviews as $review): ?>
                <form method="POST" action="">
                    <input type="hidden" name="review_id" value="<?php echo htmlspecialchars($review['id']); ?>">

                    <div class="mb-4">
                        <h2 class="text-2xl font-semibold mb-2"><?php echo htmlspecialchars($review['product_name']); ?></h2>
                        <label for="rating" class="block text-gray-700 font-semibold">Rating (out of 5)</label>
                        <select name="rating" id="rating" class="w-full border-gray-300 rounded-md">
                            <option value="5" <?php echo $review['rating'] == 5 ? 'selected' : ''; ?>>5 - Excellent</option>
                            <option value="4" <?php echo $review['rating'] == 4 ? 'selected' : ''; ?>>4 - Very Good</option>
                            <option value="3" <?php echo $review['rating'] == 3 ? 'selected' : ''; ?>>3 - Good</option>
                            <option value="2" <?php echo $review['rating'] == 2 ? 'selected' : ''; ?>>2 - Fair</option>
                            <option value="1" <?php echo $review['rating'] == 1 ? 'selected' : ''; ?>>1 - Poor</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="comment" class="block text-gray-700 font-semibold">Your Review</label>
                        <textarea name="comment" id="comment" rows="4" class="w-full border-gray-300 rounded-md" required><?php echo htmlspecialchars($review['comment']); ?></textarea>
                    </div>

                    <button type="submit" name="edit_review" class="bg-yellow-500 text-white py-2 px-4 rounded-lg hover:bg-yellow-600">
                        Update Review
                    </button>
                    <button type="submit" name="delete_review" class="text-red-600 hover:text-red-800 mt-2">
                        Delete Review
                    </button>
                </form>
                <hr class="my-4">
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-600">You have not submitted any reviews yet.</p>
    <?php endif; ?>
</div>

<?php include "../components/footer.php"; ?>
