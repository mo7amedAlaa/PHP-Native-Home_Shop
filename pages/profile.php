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
    try {
        $sql = "SELECT id, name, email, user_type FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new Exception("User not found.");
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
    } catch (PDOException $e) {
        echo "<p class='text-red-500'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
        return null;
    } catch (Exception $e) {
        echo "<p class='text-red-500'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        return null;
    }
}

$userInfo = getUserInfo($conn, $userId);

if (!$userInfo) {
    header("Location: profile.php?error=" . urlencode("Failed to retrieve user information."));
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $nationalId = trim($_POST['national_id']);
    $birthday = $_POST['birthday'];
    $gender = $_POST['gender'];
    $avatar = $_FILES['avatar']['name'] ? $_FILES['avatar']['name'] : $userInfo['personal_image'];

    if (empty($phone)) $errors[] = 'Phone number is required.';
    if (empty($address)) $errors[] = 'Address is required.';
    if (empty($nationalId)) $errors[] = 'National ID is required.';
    if (empty($birthday)) $errors[] = 'Birthday is required.';
    if (empty($gender)) $errors[] = 'Gender is required.';

    if ($_FILES['avatar']['error'] === 0) {
        $uploadDir = '../assets/images/uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $uploadFile = $uploadDir . basename($avatar);

        if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadFile)) {
            $errors[] = 'Error uploading file.';
        }
    }

    if (empty($errors)) {
        try {
            if ($userInfo['user_type'] === 'seller') {
                $storeName = trim($_POST['store_name']);
                $storeDescription = trim($_POST['store_description']);

                $sql = "UPDATE sellers SET 
                        phone_number = ?, address = ?, national_id = ?, personal_image = ?, birthday = ?, gender = ?, store_name = ?, store_description = ? 
                        WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$phone, $address, $nationalId, $avatar, $birthday, $gender, $storeName, $storeDescription, $userId]);
            } else {
                $sql = "UPDATE customers SET 
                        phone_number = ?, address = ?, national_id = ?, personal_image = ?, birthday = ?, gender = ?
                        WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$phone, $address, $nationalId, $avatar, $birthday, $gender, $userId]);
            }

            header("Location: profile.php");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
            $errors[] = "An error occurred while updating your profile.";
        } catch (Exception $e) {
             $errors[]="Error: " . $e->getMessage();
            $errors[] = "An unexpected error occurred.";
        }
    }
}

include '../components/header.php';
?>

<div class="container py-4 flex items-center gap-3">
    <a href="home.php" class="text-primary text-base">
        <i class="fa-solid fa-house"></i>
    </a>
    <span class="text-sm text-gray-400">
        <i class="fa-solid fa-chevron-right"></i>
    </span>
    <p class="text-gray-600 font-medium">Profile</p>
</div>

<div class="container grid grid-cols-12 items-start gap-6 pt-4 pb-16">
    <?php include '../components/sidebar.php'; ?>

    <div class="col-span-9 shadow rounded px-6 pt-5 pb-7">
        <h4 class="text-lg font-medium capitalize mb-4">Profile Information</h4>
        <form method="post" enctype="multipart/form-data">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="national_id">National ID</label>
                        <input type="text" name="national_id" id="national_id" value="<?= htmlspecialchars($userInfo['national_id']) ?>" class="input-box">
                    </div>
                    <div>
                        <label for="avatar">Avatar</label>
                        <input type="file" name="avatar" id="avatar" class="input-box">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="birthday">Birthday</label>
                        <input type="date" name="birthday" id="birthday" value="<?= htmlspecialchars($userInfo['birthday']) ?>" class="input-box">
                    </div>
                    <div>
                        <label for="gender">Gender</label>
                        <select name="gender" id="gender" class="input-box">
                            <option value="male" <?= $userInfo['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= $userInfo['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="phone">Phone number</label>
                        <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($userInfo['phone_number']) ?>" class="input-box">
                    </div>
                    <div>
                        <label for="address">Address</label>
                        <input type="text" name="address" id="address" value="<?= htmlspecialchars($userInfo['address']) ?>" class="input-box">
                    </div>
                </div>

                <?php if ($userInfo['user_type'] === 'seller'): ?>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="store_name">Store Name</label>
                            <input type="text" name="store_name" id="store_name" value="<?= htmlspecialchars($userInfo['store_name']) ?>" class="input-box">
                        </div>
                        <div>
                            <label for="store_description">Store Description</label>
                            <input type="text" name="store_description" id="store_description" value="<?= htmlspecialchars($userInfo['store_description']) ?>" class="input-box">
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="mt-4">
                <button type="submit" class="py-3 px-4 text-center text-white bg-primary border border-primary rounded-md hover:bg-transparent hover:text-primary transition font-medium">
                    Save changes
                </button>
            </div>
        </form>

        <?php if (!empty($errors)): ?>
            <div class="mt-4">
                <?php foreach ($errors as $error): ?>
                    <p class="text-red-500"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../components/footer.php'; ?>
