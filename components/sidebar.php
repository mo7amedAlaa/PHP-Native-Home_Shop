<!-- sidebar -->
<div class="col-span-3">
    <div class="px-4 py-3 shadow flex items-center gap-4">
        <div class="flex-shrink-0">
            <img src="../assets/images/uploads/<?= htmlspecialchars($userInfo['personal_image']) ?>" alt="profile"
                 class="rounded-full w-14 h-14 border border-gray-200 p-1 object-cover">
        </div>
        <div class="flex-grow">
            <p class="text-gray-600">Hello,</p>
            <h4 class="text-gray-800 font-medium"><?= htmlspecialchars($userInfo['name']) ?></h4>
        </div>
    </div>

    <div class="mt-6 bg-white shadow rounded p-4 divide-y divide-gray-200 space-y-4 text-gray-600">
        <div class="space-y-1 pl-8">
            <a href="../pages/account.php" class="relative  hover:text-primary block font-medium capitalize transition">
                        <span class="absolute -left-8 top-0 text-base">
                            <i class="fa-regular fa-address-card"></i>
                        </span>
                Manage account
            </a>
            <a href="../pages/profile.php" class="relative hover:text-primary block capitalize transition">
                Profile information
            </a>

            <a href="../pages/authsett.php" class="relative hover:text-primary block capitalize transition">
                Change password/email
            </a>
            <a href="../pages/delete_account.php" class="relative hover:text-primary block capitalize transition">
                Delete  Account
            </a>
        </div>

        <div class="space-y-1 pl-8 pt-4">
            <h1 href="#" class="relative hover:text-primary block font-medium capitalize transition">
                        <span class="absolute -left-8 top-0 text-base">
                            <i class="fa-solid fa-box-archive"></i>
                        </span>
                My Orders 
            </h1>
            <a href="../pages/order-history.php" class="relative hover:text-primary block capitalize transition">
                 My Order History
            </a>
            <a href="../pages/shipped-orders.php" class="relative hover:text-primary block capitalize transition">
                My shipped order Status
            </a>
            <a href="../pages/cancelled-order.php" class="relative hover:text-primary block capitalize transition">
                My Cancellations
            </a>
            
        </div>
           <div class="space-y-1 pl-8 pt-4">
            <a href="#" class="relative hover:text-primary block font-medium capitalize transition">
                        <span class="absolute -left-8 top-0 text-base">
                            <i class="fa-regular fa-credit-card"></i>
                        </span>
                Payment methods
            </a>
            <a href="../pages/payment-order.php" class="relative hover:text-primary block capitalize transition">
                Pay the order
            </a>
        </div>
        <div class="space-y-1 pl-8 pt-4">
            <h1  class="relative hover:text-primary block font-medium capitalize transition">
                        <span class="absolute -left-8 top-0 text-base">
                            <i class="fa-solid fa-box-archive"></i>
                        </span>
                My reviews 
            </h1>
            
            <a href="../pages/my-reviews.php" class="relative hover:text-primary block capitalize transition">
                My reviews
            </a>
        </div>
     

        <div class="space-y-1 pl-8 pt-4">
            <a href="../pages/wishlist.php" class="relative hover:text-primary block font-medium capitalize transition">
                        <span class="absolute -left-8 top-0 text-base">
                            <i class="fa-regular fa-heart"></i>
                        </span>
                My wishlist
            </a>
        </div>

        <div class="space-y-1 pl-8 pt-4">
            <a href="../pages/logout.php" class="relative hover:text-primary block font-medium capitalize transition">
                        <span class="absolute -left-8 top-0 text-base">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </span>
                Logout
            </a>
        </div>

    </div>
</div>
<!-- ./sidebar -->