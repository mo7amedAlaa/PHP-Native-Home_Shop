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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $name = trim($_POST['name']);
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['password'];

    if (!password_verify($oldPassword, $userInfo['password'])) {
        $error = "Incorrect old password";
    } else {
        $updates = [];
        $params = [];

        if (!empty($name)) {
            $updates[] = "name = ?";
            $params[] = $name;
        }


        if (!empty($email)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                $error = "Email is already in use by another account";
            } else {
                $updates[] = "email = ?";
                $params[] = $email;
            }
        }


        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updates[] = "password = ?";
            $params[] = $hashedPassword;
        }


        if (empty($updates)) {
            $error = "No changes detected";
        } else {
            $params[] = $userId;
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute($params)) {
                $success = "Changes saved successfully";
            } else {
                $error = "An error occurred while updating your information";
            }
        }
    }
    if (empty($error)  ) {
        header("Location: authsett.php");
        exit();
    }
    }


require '../components/header.php';
?>
<div class="container py-4 flex items-center gap-3">
    <a href="home.php" class="text-primary text-base">
        <i class="fa-solid fa-house"></i>
    </a>
    <span class="text-sm text-gray-400">
        <i class="fa-solid fa-chevron-right"></i>
    </span>
    <p class="text-gray-600 font-medium">Security Setting</p>
</div>

<div class="container grid grid-cols-12 items-start gap-6 pt-4 pb-16">
    <?php include '../components/sidebar.php'; ?>
    <div class="col-span-9 shadow rounded px-6 pt-5 pb-7">
        <h4 class="text-lg font-medium capitalize mb-4">Security Setting</h4>
        <form method="post" id="securityForm">
            <div class="space-y-4">
                <div>
                    <label for="Name">Name</label>
                    <input type="text" name="name" id="Name" value="<?= htmlspecialchars($userInfo['name']) ?>" class="input-box" required>
                </div>
                <div>
                    <label for="Email">Email</label>
                    <input type="email" name="email" id="Email" value="<?= htmlspecialchars($userInfo['email']) ?>" class="input-box" required>
                </div>
                <div>
                    <label for="OldPassword">Old Password</label>
                    <input type="password" name="old_password" id="OldPassword" class="input-box" required>
                </div>
                <div>
                    <label for="Password">New Password</label>
                    <input type="password" name="password" id="Password" class="input-box"  >
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" id="saveChangesBtn" class="py-3 px-4 text-center text-white bg-primary border border-primary rounded-md hover:bg-transparent hover:text-primary transition font-medium">
                    Save changes
                </button>
            </div>
        </form>

        <?php if (isset($error)): ?>
            <p class='text-red-500 mt-4'><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </div>
</div>

<?php require '../components/footer.php'; ?>

<script>
    document.getElementById('securityForm').addEventListener('submit', function() {
        document.getElementById('saveChangesBtn').innerText = 'Saving...';
        document.getElementById('saveChangesBtn').disabled = true;
    });
</script>
