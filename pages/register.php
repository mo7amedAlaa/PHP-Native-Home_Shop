<?php
require '../config/dbConnection.php';
function validatePassword($password) {
    $errors = [];
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one digit.";
    }
    if (!preg_match('/[\W_]/', $password)) {
        $errors[] = "Password must contain at least one special character.";
    }
    if (preg_match('/\s/', $password)) {
        $errors[] = "Password must not contain any spaces.";
    }
    return $errors;
}

$errors = [];
$name = $email = $password = $confirm_password = $user_type = $agreement = "";

if(isset($_POST['register'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $agreement = isset($_POST['agreement']) ? $_POST['agreement'] : '';
    $user_type = $_POST['user_type'];

    if(empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($agreement) || empty($user_type)){
        $errors[] = "All fields must be filled.";
    }
    if(strlen($name) < 5 || strlen($name) > 30){
        $errors[] = "Name must be between 5 and 30 characters.";
    }
    if($password !== $confirm_password){
        $errors[] = "Passwords do not match.";
    }
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[] = "Please enter a valid email address.";
    }

    $passwordErrors = validatePassword($password);
    if (!empty($passwordErrors)) {
        $errors = array_merge($errors, $passwordErrors);
    }

    if(empty($errors)){
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $query = "INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([$name, $email, $hashedPassword, $user_type]);

            $user_id = $conn->lastInsertId();
            session_start();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_type'] = $user_type;

            if($user_type == 'customer'){
                $customer_query = "INSERT INTO customers (user_id) VALUES (?)";
                $stmt = $conn->prepare($customer_query);
                $stmt->execute([$user_id]);
            } elseif($user_type == 'seller'){
                $seller_query = "INSERT INTO sellers (user_id) VALUES (?)";
                $stmt = $conn->prepare($seller_query);
                $stmt->execute([$user_id]);
            }

            echo "<p class='p-4 rounded-lg bg-green-200 my-2'>Registration Successful. Redirecting to profile...</p>";
            echo "<script>
                    setTimeout(function(){
                        window.location.href = 'profile.php';
                    }, 3000);  
                  </script>";
        } catch (PDOException $e) {
            if($e->getCode() == 23000) {
                $errors[] = "Email is already in use. Please try another email.";
            } else {
                $errors[] = "Error: " . $e->getMessage();
            }
        }
    }
}
include '../components/header.php';
?>
<div class="contain py-16">
    <div class="max-w-lg mx-auto shadow px-6 py-7 rounded overflow-hidden">
        <h2 class="text-2xl uppercase font-medium mb-1">Create an account</h2>
        <p class="text-gray-600 mb-6 text-sm">
            Register for new customer
        </p>
        <form action="./register.php" method="post" autocomplete="off">
            <div class="space-y-2">
                <div>
                    <label for="user_type" class="text-gray-600 mb-2 block">User Type</label>
                    <select name="user_type" id="user_type"
                            class="block w-full border border-gray-300 px-4 py-3 text-gray-600 text-sm rounded focus:ring-0 focus:border-primary">
                        <option value="customer" <?php echo $user_type == 'customer' ? 'selected' : ''; ?>>Customer</option>
                        <option value="seller" <?php echo $user_type == 'seller' ? 'selected' : ''; ?>>Seller</option>
                    </select>
                </div>
                <div>
                    <label for="name" class="text-gray-600 mb-2 block">Full Name</label>
                    <input type="text" name="name" id="name"
                           class="block w-full border border-gray-300 px-4 py-3 text-gray-600 text-sm rounded focus:ring-0 focus:border-primary placeholder-gray-400"
                           placeholder="Mohamed Alaa" value="<?php echo htmlspecialchars($name); ?>">
                </div>
                <div>
                    <label for="email" class="text-gray-600 mb-2 block">Email address</label>
                    <input type="email" name="email" id="email"
                           class="block w-full border border-gray-300 px-4 py-3 text-gray-600 text-sm rounded focus:ring-0 focus:border-primary placeholder-gray-400"
                           placeholder="youremail@domain.com" value="<?php echo htmlspecialchars($email); ?>">
                </div>
                <div>
                    <label for="password" class="text-gray-600 mb-2 block">Password</label>
                    <input type="password" name="password" id="password"
                           class="block w-full border border-gray-300 px-4 py-3 text-gray-600 text-sm rounded focus:ring-0 focus:border-primary placeholder-gray-400"
                           placeholder="*******">
                </div>
                <div>
                    <label for="confirm_password" class="text-gray-600 mb-2 block">Confirm password</label>
                    <input type="password" name="confirm_password" id="confirm_password"
                           class="block w-full border border-gray-300 px-4 py-3 text-gray-600 text-sm rounded focus:ring-0 focus:border-primary placeholder-gray-400"
                           placeholder="*******">
                </div>
            </div>
            <div class="mt-6">
                <div class="flex items-center">
                    <input type="checkbox" name="agreement" id="agreement"
                           class="text-primary focus:ring-0 rounded-sm cursor-pointer" <?php echo $agreement ? 'checked' : ''; ?>>
                    <label for="agreement" class="text-gray-600 ml-3 cursor-pointer">I have read and agree to the <a
                                href="#" class="text-primary">terms & conditions</a></label>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit"
                        class="block w-full py-2 text-center text-white bg-primary border border-primary rounded hover:bg-transparent hover:text-primary transition uppercase font-roboto font-medium" name="register">
                    create account
                </button>
            </div>

            <?php
            if (!empty($errors)) {
                echo "<ul class='text-red-600 mt-4'>";
                foreach ($errors as $error) {
                    echo "<li>$error</li>";
                }
                echo "</ul>";
            }
            ?>
        </form>

        <div class="mt-6 flex justify-center relative">
            <div class="text-gray-600 uppercase px-3 bg-white z-10 relative">Or signup with</div>
            <div class="absolute left-0 top-3 w-full border-b-2 border-gray-200"></div>
        </div>
        <div class="mt-4 flex gap-4">
            <a href="#"
               class="w-1/2 py-2 text-center text-white bg-blue-800 rounded uppercase font-roboto font-medium text-sm hover:bg-blue-700">facebook</a>
            <a href="#"
               class="w-1/2 py-2 text-center text-white bg-red-600 rounded uppercase font-roboto font-medium text-sm hover:bg-red-500">google</a>
        </div>

        <p class="mt-4 text-center text-gray-600">Already have an account? <a href="login.php"
                                                                              class="text-primary">Login now</a></p>
    </div>
</div>

<?php include '../components/footer.php'; ?>
