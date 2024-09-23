<?php
session_start();
include '../config/dbConnection.php';
if(!$_SESSION['user_id']){
    header('location: login.php');
}
 
$message = $_GET['message'] ?? '';
$error = $_GET['error'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = trim($_POST['reason']);
    $customReason = trim($_POST['custom_reason']);
    $confirm = isset($_POST['confirm']);

    if ($confirm) {
        $reasonText = $reason === 'Other' ? $customReason : $reason;
        try {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            session_unset();
            session_destroy();
            header("Location: delete_account.php?message=Account Deleted,Good Bye");
            exit();
        } catch (Exception $e) {
            header("Location: delete_account.php?error=Account deletion failed. Please try again.");
            exit();
        }
    } else {
        header("Location: delete_account.php?error=Please confirm that you want to delete your account.");
        exit();
    }
}

include '../components/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Delete Account</h2>


            <?php if ($message): ?>
                <p class="text-green-600  mb-4"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <?php if ($error): ?>
                <p class="text-red-500 mb-4"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form id="deleteForm" action="delete_account.php" method="post">
                <div class="mb-4">
                    <label for="reason" class="block text-gray-700 font-medium mb-2">Please tell us why you want to delete your account:</label>
                    <select name="reason" id="reason" class="block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50" required>
                        <option value="" disabled selected>Select a reason</option>
                        <option value="Privacy concerns">Privacy concerns</option>
                        <option value="Too many notifications">Too many notifications</option>
                        <option value="Not using the service">Not using the service</option>
                        <option value="Other">Other (please specify)</option>
                    </select>
                </div>
                <div class="mb-4">
                    <input type="text" name="custom_reason" placeholder="If other, please specify" class="block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                </div>
                <div class="mb-4 flex items-center">
                    <input type="checkbox" name="confirm" id="confirm" class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" required>
                    <label for="confirm" class="text-gray-700">I understand that this action is irreversible.</label>
                </div>
                <button type="button" id="deleteButton" class="w-full py-2 px-4 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50">Delete My Account</button>
            </form>

            <a href="profile.php" class="block text-blue-500 mt-4 text-center hover:underline">Cancel</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('deleteButton').addEventListener('click', function() {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action is irreversible. Your account will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteForm').submit();
            }
        });
    });
</script>

<?php include '../components/footer.php'; ?>
