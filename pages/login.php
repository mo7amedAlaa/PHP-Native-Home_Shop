 <?php
require '../config/dbConnection.php';
session_start();

if (isset($_COOKIE['user_id']) && !isset($_SESSION['user_id'])) {
    $user_id = intval($_COOKIE['user_id']);
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_type'] = $user['user_type'];

        header("Location: profile.php");
        exit();
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $errors[] = "Email and Password are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    } else {

        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];


            if (isset($_POST['remember'])) {
                setcookie("user_id", $user['id'], time() + (86400 * 30), "/"); // 30 days expiration
            }

            header("Location: profile.php");
            exit();
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}

include '../components/header.php';
?>
<div class="contain py-16">
    <div class="max-w-lg mx-auto shadow px-6 py-7 rounded overflow-hidden">
        <h2 class="text-2xl uppercase font-medium mb-1">Login</h2>
        <p class="text-gray-600 mb-6 text-sm">Welcome back!</p>

        <form method="post" autocomplete="off">
            <div class="space-y-2">
                <div>
                    <label for="email" class="text-gray-600 mb-2 block">Email address</label>
                    <input type="email" name="email" id="email"
                           class="block w-full border border-gray-300 px-4 py-3 text-gray-600 text-sm rounded focus:ring-0 focus:border-primary placeholder-gray-400"
                           placeholder="youremail@example.com" value="<?= htmlspecialchars($email ?? '') ?>">
                </div>
                <div>
                    <label for="password" class="text-gray-600 mb-2 block">Password</label>
                    <input type="password" name="password" id="password"
                           class="block w-full border border-gray-300 px-4 py-3 text-gray-600 text-sm rounded focus:ring-0 focus:border-primary placeholder-gray-400"
                           placeholder="*******">
                </div>
            </div>

            <div class="flex items-center justify-between mt-6">
                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember"
                           class="text-primary focus:ring-0 rounded-sm cursor-pointer">
                    <label for="remember" class="text-gray-600 ml-3 cursor-pointer">Remember me</label>
                </div>
                <a href="#" class="text-primary">Forgot password?</a>
            </div>

            <div class="mt-4">
                <button type="submit"
                        class="block w-full py-2 text-center text-white bg-primary border border-primary rounded hover:bg-transparent hover:text-primary transition uppercase font-roboto font-medium" name="login">
                    Login
                </button>
            </div>

            <?php if (!empty($errors)): ?>
                <ul class="text-red-600 mt-4">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </form>

        <div class="mt-6 flex justify-center relative">
            <div class="text-gray-600 uppercase px-3 bg-white z-10 relative">Or login with</div>
            <div class="absolute left-0 top-3 w-full border-b-2 border-gray-200"></div>
        </div>

        <div class="mt-4 flex gap-4">
            <a href="#"
               class="w-1/2 py-2 text-center text-white bg-blue-800 rounded uppercase font-roboto font-medium text-sm hover:bg-blue-700">Facebook</a>
            <a href="#"
               class="w-1/2 py-2 text-center text-white bg-red-600 rounded uppercase font-roboto font-medium text-sm hover:bg-red-500">Google</a>
        </div>

        <p class="mt-4 text-center text-gray-600">Don't have an account? <a href="register.php" class="text-primary">Register now</a></p>
    </div>
</div>

<?php include '../components/footer.php'; ?>
