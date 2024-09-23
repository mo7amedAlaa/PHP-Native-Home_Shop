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

    if ($user['user_type'] === 'seller') {
        $sql = "SELECT * FROM sellers WHERE user_id = ?";
    } else {
        $sql = "SELECT * FROM customers WHERE user_id = ?";
    }
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);
    $additionalInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    return array_merge($user, $additionalInfo);
}
$userInfo = getUserInfo($conn, $userId);
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
        <p class="text-gray-600 font-medium">Account</p>
    </div>
    <!-- ./breadcrumb -->

    <!-- account wrapper -->
    <div class="container grid grid-cols-12 items-start gap-6 pt-4 pb-16">

        <?php
include '../components/sidebar.php';
?>

        <!-- info -->
        <div class="col-span-9 grid grid-cols-3 gap-4">

            <div class="shadow rounded bg-white px-4 pt-6 pb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium text-gray-800 text-lg">Personal Profile</h3>
                    <a href="../pages/profile.php" class="text-primary">Edit</a>
                </div>
                <div class="space-y-1">
                    <h4 class="text-gray-700 font-medium"> <?= htmlspecialchars($userInfo['name']) ?> </h4>
                    <p class="text-gray-800"><?= htmlspecialchars($userInfo['email']) ?></p>
                    <p class="text-gray-800"><?= htmlspecialchars($userInfo['phone_number']) ?></p>
                </div>
            </div>



        </div>
        <!-- ./info -->

    </div>
    <!-- ./account wrapper -->

<?php include '../components/footer.php'; ?>