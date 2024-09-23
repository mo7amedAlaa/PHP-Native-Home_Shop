<?php
include '../config/dbConnection.php';
require('../components/product_card_add.php');
session_start();
$query = "SELECT * FROM categories";
$stmt = $conn->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
$no_categories = empty($categories);
$query = "SELECT * FROM `products` ORDER BY id DESC LIMIT 4 ";
$stmt = $conn->prepare($query);
$stmt->execute();
$new_arrival_product = $stmt->fetchAll(PDO::FETCH_ASSOC);
$no_new_arrival_product = empty($categories);
require '../components/header.php';
?>
<!-- ./header -->

<!-- banner -->
<div class="bg-cover bg-no-repeat bg-center py-36" style="background-image: url('../assets/images/banner-bg.jpg');">
    <div class="container">
        <h1 class="text-6xl text-gray-800 font-medium mb-4 capitalize">
            best collection for <br> home decoration
        </h1>
        <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Aperiam <br>
            accusantium perspiciatis, sapiente
            magni eos dolorum ex quos dolores odio</p>
        <div class="mt-12">
            <a href="./shop.php" class="bg-primary border border-primary text-white px-8 py-3 font-medium
                    rounded-md hover:bg-transparent hover:text-primary">Shop Now</a>
        </div>
    </div>
</div>
<!-- ./banner -->

<!-- features -->
<div class="container py-16">
    <div class="w-10/12 grid grid-cols-1 md:grid-cols-3 gap-6 mx-auto justify-center">
        <div class="border border-primary rounded-sm px-3 py-6 flex justify-center items-center gap-5">
            <img src="../assets/images/icons/delivery-van.svg" alt="Delivery" class="w-12 h-12 object-contain">
            <div>
                <h4 class="font-medium capitalize text-lg">Free Shipping</h4>
                <p class="text-gray-500 text-sm">Order over $200</p>
            </div>
        </div>
        <div class="border border-primary rounded-sm px-3 py-6 flex justify-center items-center gap-5">
            <img src="../assets/images/icons/money-back.svg" alt="Delivery" class="w-12 h-12 object-contain">
            <div>
                <h4 class="font-medium capitalize text-lg">Money Rturns</h4>
                <p class="text-gray-500 text-sm">30 days money returs</p>
            </div>
        </div>
        <div class="border border-primary rounded-sm px-3 py-6 flex justify-center items-center gap-5">
            <img src="../assets/images/icons/service-hours.svg" alt="Delivery" class="w-12 h-12 object-contain">
            <div>
                <h4 class="font-medium capitalize text-lg">24/7 Support</h4>
                <p class="text-gray-500 text-sm">Customer support</p>
            </div>
        </div>
    </div>
</div>
<!-- ./features -->

<!-- categories -->
<div class="container py-16">
    <h2 class="text-2xl font-medium text-gray-800 uppercase mb-6">shop by category</h2>
    <div class="grid grid-cols-3 gap-3">
        <?php if($no_categories):?>
            <div>some error accure </div>
            <?php else:?>
                <?php foreach($categories as $category ): ?>
<div class="relative rounded-sm overflow-hidden group">
            <img src=<?= htmlspecialchars($category['icon']) ?> alt="category 1" class="w-full">
            <a href="../pages/shop.php?category[]=<?= htmlspecialchars($category['id']) ?>"
               class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center text-xl text-white font-roboto font-medium group-hover:bg-opacity-60 transition"><?= htmlspecialchars($category['name']) ?></a>
        </div>
                <?php endforeach; ?>
 
        <?php endif;?>
       
    </div>
</div>
<!-- ./categories -->

<!-- new arrival -->
<div class="container pb-16">
    <h2 class="text-2xl font-medium text-gray-800 uppercase mb-6">top new arrival</h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
         <?php if($no_new_arrival_product):?>
            <div>some error accure </div>
            <?php else:?>
                <?php foreach($new_arrival_product as $product  ): ?>
                <?php displayMainProductCard($conn,$product['id'],  basename(__FILE__)); ?>
                <?php endforeach; ?>
        <?php endif;?>
    
    </div>
</div>
<!-- ./new arrival -->

<!-- ads -->
<div class="container pb-16">
    <a href="#">
        <img src="../assets/images/offer.jpg" alt="ads" class="w-full">
    </a>
</div>
<!-- ./ads -->

<!-- product -->
<div class="container pb-16">
    <h2 class="text-2xl font-medium text-gray-800 uppercase mb-6">recomended for you</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="bg-white shadow rounded overflow-hidden group">
            <div class="relative">
                <img src="../assets/images/products/category-4.jpg" alt="product 1" class="w-full">
                <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center
                    justify-center gap-2 opacity-0 group-hover:opacity-100 transition">
                    <a href="#"
                       class="text-white text-lg w-9 h-8 rounded-full bg-primary flex items-center justify-center hover:bg-gray-800 transition"
                       title="view product">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </a>
                    <a href="#"
                       class="text-white text-lg w-9 h-8 rounded-full bg-primary flex items-center justify-center hover:bg-gray-800 transition"
                       title="add to wishlist">
                        <i class="fa-solid fa-heart"></i>
                    </a>
                </div>
            </div>
            <div class="pt-4 pb-3 px-4">
                <a href="#">
                    <h4 class="uppercase font-medium text-xl mb-2 text-gray-800 hover:text-primary transition">Guyer
                        Chair</h4>
                </a>
                <div class="flex items-baseline mb-1 space-x-2">
                    <p class="text-xl text-primary font-semibold">$45.00</p>
                    <p class="text-sm text-gray-400 line-through">$55.90</p>
                </div>
                <div class="flex items-center">
                    <div class="flex gap-1 text-sm text-yellow-400">
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                    </div>
                    <div class="text-xs text-gray-500 ml-3">(150)</div>
                </div>
            </div>
            <a href="#"
               class="block w-full py-1 text-center text-white bg-primary border border-primary rounded-b hover:bg-transparent hover:text-primary transition">Add
                to cart</a>
        </div>
        <div class="bg-white shadow rounded overflow-hidden group">
            <div class="relative">
                <img src="../assets/images/products/category-3.jpg" alt="product 1" class="w-full">
                <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center
                    justify-center gap-2 opacity-0 group-hover:opacity-100 transition">
                    <a href="#"
                       class="text-white text-lg w-9 h-8 rounded-full bg-primary flex items-center justify-center hover:bg-gray-800 transition"
                       title="view product">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </a>
                    <a href="#"
                       class="text-white text-lg w-9 h-8 rounded-full bg-primary flex items-center justify-center hover:bg-gray-800 transition"
                       title="add to wishlist">
                        <i class="fa-solid fa-heart"></i>
                    </a>
                </div>
            </div>
            <div class="pt-4 pb-3 px-4">
                <a href="#">
                    <h4 class="uppercase font-medium text-xl mb-2 text-gray-800 hover:text-primary transition">Bed
                        King Size</h4>
                </a>
                <div class="flex items-baseline mb-1 space-x-2">
                    <p class="text-xl text-primary font-semibold">$45.00</p>
                    <p class="text-sm text-gray-400 line-through">$55.90</p>
                </div>
                <div class="flex items-center">
                    <div class="flex gap-1 text-sm text-yellow-400">
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                    </div>
                    <div class="text-xs text-gray-500 ml-3">(150)</div>
                </div>
            </div>
            <a href="#"
               class="block w-full py-1 text-center text-white bg-primary border border-primary rounded-b hover:bg-transparent hover:text-primary transition">Add
                to cart</a>
        </div>
        <div class="bg-white shadow rounded overflow-hidden group">
            <div class="relative">
                <img src="../assets/images/products/category-2.jpg" alt="product 1" class="w-full">
                <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center
                    justify-center gap-2 opacity-0 group-hover:opacity-100 transition">
                    <a href="#"
                       class="text-white text-lg w-9 h-8 rounded-full bg-primary flex items-center justify-center hover:bg-gray-800 transition"
                       title="view product">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </a>
                    <a href="#"
                       class="text-white text-lg w-9 h-8 rounded-full bg-primary flex items-center justify-center hover:bg-gray-800 transition"
                       title="add to wishlist">
                        <i class="fa-solid fa-heart"></i>
                    </a>
                </div>
            </div>
            <div class="pt-4 pb-3 px-4">
                <a href="#">
                    <h4 class="uppercase font-medium text-xl mb-2 text-gray-800 hover:text-primary transition">
                        Couple Sofa</h4>
                </a>
                <div class="flex items-baseline mb-1 space-x-2">
                    <p class="text-xl text-primary font-semibold">$45.00</p>
                    <p class="text-sm text-gray-400 line-through">$55.90</p>
                </div>
                <div class="flex items-center">
                    <div class="flex gap-1 text-sm text-yellow-400">
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                    </div>
                    <div class="text-xs text-gray-500 ml-3">(150)</div>
                </div>
            </div>
            <a href="#"
               class="block w-full py-1 text-center text-white bg-primary border border-primary rounded-b hover:bg-transparent hover:text-primary transition">Add
                to cart</a>
        </div>
        <div class="bg-white shadow rounded overflow-hidden group">
            <div class="relative">
                <img src="../assets/images/products/category-1.jpg" alt="product 1" class="w-full">
                <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center
                    justify-center gap-2 opacity-0 group-hover:opacity-100 transition">
                    <a href="#"
                       class="text-white text-lg w-9 h-8 rounded-full bg-primary flex items-center justify-center hover:bg-gray-800 transition"
                       title="view product">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </a>
                    <a href="#"
                       class="text-white text-lg w-9 h-8 rounded-full bg-primary flex items-center justify-center hover:bg-gray-800 transition"
                       title="add to wishlist">
                        <i class="fa-solid fa-heart"></i>
                    </a>
                </div>
            </div>
            <div class="pt-4 pb-3 px-4">
                <a href="#">
                    <h4 class="uppercase font-medium text-xl mb-2 text-gray-800 hover:text-primary transition">
                        Mattrass X</h4>
                </a>
                <div class="flex items-baseline mb-1 space-x-2">
                    <p class="text-xl text-primary font-semibold">$45.00</p>
                    <p class="text-sm text-gray-400 line-through">$55.90</p>
                </div>
                <div class="flex items-center">
                    <div class="flex gap-1 text-sm text-yellow-400">
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                    </div>
                    <div class="text-xs text-gray-500 ml-3">(150)</div>
                </div>
            </div>
            <a href="#"
               class="block w-full py-1 text-center text-white bg-primary border border-primary rounded-b hover:bg-transparent hover:text-primary transition">Add
                to cart</a>
        </div>
        <div class="bg-white shadow rounded overflow-hidden group">
            <div class="relative">
                <img src="../assets/images/products/category-6.jpg" alt="product 1" class="w-full">
                <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center
                    justify-center gap-2 opacity-0 group-hover:opacity-100 transition">
                    <a href="#"
                       class="text-white text-lg w-9 h-8 rounded-full bg-primary flex items-center justify-center hover:bg-gray-800 transition"
                       title="view product">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </a>
                    <a href="#"
                       class="text-white text-lg w-9 h-8 rounded-full bg-primary flex items-center justify-center hover:bg-gray-800 transition"
                       title="add to wishlist">
                        <i class="fa-solid fa-heart"></i>
                    </a>
                </div>
            </div>
            <div class="pt-4 pb-3 px-4">
                <a href="#">
                    <h4 class="uppercase font-medium text-xl mb-2 text-gray-800 hover:text-primary transition">Guyer
                        Chair</h4>
                </a>
                <div class="flex items-baseline mb-1 space-x-2">
                    <p class="text-xl text-primary font-semibold">$45.00</p>
                    <p class="text-sm text-gray-400 line-through">$55.90</p>
                </div>
                <div class="flex items-center">
                    <div class="flex gap-1 text-sm text-yellow-400">
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                    </div>
                    <div class="text-xs text-gray-500 ml-3">(150)</div>
                </div>
            </div>
            <a href="#"
               class="block w-full py-1 text-center text-white bg-primary border border-primary rounded-b hover:bg-transparent hover:text-primary transition">Add
                to cart</a>
        </div>
        <div class="bg-white shadow rounded overflow-hidden group">
            <div class="relative">
                <img src="../assets/images/products/category-1.jpg" alt="product 1" class="w-full">
                <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center
                    justify-center gap-2 opacity-0 group-hover:opacity-100 transition">
                    <a href="#"
                       class="text-white text-lg w-9 h-8 rounded-full bg-primary flex items-center justify-center hover:bg-gray-800 transition"
                       title="view product">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </a>
                    <a href="#"
                       class="text-white text-lg w-9 h-8 rounded-full bg-primary flex items-center justify-center hover:bg-gray-800 transition"
                       title="add to wishlist">
                        <i class="fa-solid fa-heart"></i>
                    </a>
                </div>
            </div>
            <div class="pt-4 pb-3 px-4">
                <a href="#">
                    <h4 class="uppercase font-medium text-xl mb-2 text-gray-800 hover:text-primary transition">Bed
                        King Size</h4>
                </a>
                <div class="flex items-baseline mb-1 space-x-2">
                    <p class="text-xl text-primary font-semibold">$45.00</p>
                    <p class="text-sm text-gray-400 line-through">$55.90</p>
                </div>
                <div class="flex items-center">
                    <div class="flex gap-1 text-sm text-yellow-400">
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                    </div>
                    <div class="text-xs text-gray-500 ml-3">(150)</div>
                </div>
            </div>
            <a href="#"
               class="block w-full py-1 text-center text-white bg-primary border border-primary rounded-b hover:bg-transparent hover:text-primary transition">Add
                to cart</a>
        </div>
        <div class="bg-white shadow rounded overflow-hidden group">
            <div class="relative">
                <img src="../assets/images/products/category-3.jpg" alt="product 1" class="w-full">
                <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center
                    justify-center gap-2 opacity-0 group-hover:opacity-100 transition">
                    <a href="#"
                       class="text-white text-lg w-9 h-8 rounded-full bg-primary flex items-center justify-center hover:bg-gray-800 transition"
                       title="view product">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </a>
                    <a href="#"
                       class="text-white text-lg w-9 h-8 rounded-full bg-primary flex items-center justify-center hover:bg-gray-800 transition"
                       title="add to wishlist">
                        <i class="fa-solid fa-heart"></i>
                    </a>
                </div>
            </div>
            <div class="pt-4 pb-3 px-4">
                <a href="#">
                    <h4 class="uppercase font-medium text-xl mb-2 text-gray-800 hover:text-primary transition">
                        Couple Sofa</h4>
                </a>
                <div class="flex items-baseline mb-1 space-x-2">
                    <p class="text-xl text-primary font-semibold">$45.00</p>
                    <p class="text-sm text-gray-400 line-through">$55.90</p>
                </div>
                <div class="flex items-center">
                    <div class="flex gap-1 text-sm text-yellow-400">
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                    </div>
                    <div class="text-xs text-gray-500 ml-3">(150)</div>
                </div>
            </div>
            <a href="#"
               class="block w-full py-1 text-center text-white bg-primary border border-primary rounded-b hover:bg-transparent hover:text-primary transition">Add
                to cart</a>
        </div>
        <div class="bg-white shadow rounded overflow-hidden group">
            <div class="relative">
                <img src="../assets/images/products/category-1.jpg" alt="product 1" class="w-full">
                <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center
                    justify-center gap-2 opacity-0 group-hover:opacity-100 transition">
                    <a href="#"
                       class="text-white text-lg w-9 h-8 rounded-full bg-primary flex items-center justify-center hover:bg-gray-800 transition"
                       title="view product">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </a>
                    <a href="#"
                       class="text-white text-lg w-9 h-8 rounded-full bg-primary flex items-center justify-center hover:bg-gray-800 transition"
                       title="add to wishlist">
                        <i class="fa-solid fa-heart"></i>
                    </a>
                </div>
            </div>
            <div class="pt-4 pb-3 px-4">
                <a href="#">
                    <h4 class="uppercase font-medium text-xl mb-2 text-gray-800 hover:text-primary transition">
                        Mattrass X</h4>
                </a>
                <div class="flex items-baseline mb-1 space-x-2">
                    <p class="text-xl text-primary font-semibold">$45.00</p>
                    <p class="text-sm text-gray-400 line-through">$55.90</p>
                </div>
                <div class="flex items-center">
                    <div class="flex gap-1 text-sm text-yellow-400">
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                        <span><i class="fa-solid fa-star"></i></span>
                    </div>
                    <div class="text-xs text-gray-500 ml-3">(150)</div>
                </div>
            </div>
            <a href="#"
               class="block w-full py-1 text-center text-white bg-primary border border-primary rounded-b hover:bg-transparent hover:text-primary transition">Add
                to cart</a>
        </div>
    </div>
</div>
 

<?php include '../components/footer.php'; ?>