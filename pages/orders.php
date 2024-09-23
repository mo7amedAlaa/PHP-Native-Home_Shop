<?php
include '../config/dbConnection.php';
session_start();

 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

 
$query = "SELECT orders.id AS order_id, orders.created_at AS order_date, orders.total AS total_price, 
                 orders.payment_method, orders.payment_status, orders.status, 
                 orders.first_name, orders.last_name, orders.company, orders.region, orders.address, 
                 orders.city, orders.phone, orders.email, 
                 products.name AS product_name, products.description, products.brand, products.size, products.color, products.base_price as price, 
                 order_items.quantity 
          FROM orders 
          JOIN order_items ON orders.id = order_items.order_id 
          JOIN products ON order_items.product_id = products.id 
          WHERE products.user_id = ? AND orders.status = 'pending' or  orders.status = 'shipped'
          ORDER BY orders.id";
$stmt = $conn->prepare($query);
$stmt->execute([$seller_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../components/header.php';
?>

<div class="container mx-auto p-4">
    <h2 class="text-3xl font-bold mb-6">Incoming Orders</h2>

    <?php if (empty($orders)): ?>
        <p class="text-gray-600 text-lg">No incoming orders at the moment.</p>
    <?php else: ?>
        <div class="space-y-6">
            <?php 
            $currentOrderId = null;
            foreach ($orders as $order): 
                
                if ($currentOrderId !== $order['order_id']): 
                    if ($currentOrderId !== null):  
                        ?>
                        </ul>
                        
                        <div class="flex justify-end space-x-4">
                            <form action="update_order_status.php" method="POST" class="inline-block">
                                <input type="hidden" name="order_id" value="<?= $currentOrderId ?>">
                                <button type="submit" name="status" value="shipped" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                                    Mark as Shipped
                                </button>
                            </form>
                            <form action="update_order_status.php" method="POST" class="inline-block">
                                <input type="hidden" name="order_id" value="<?= $currentOrderId ?>">
                                <button type="submit" name="status" value="completed" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                    Mark as completed
                                </button>
                            </form>
                            <form action="update_order_status.php" method="POST" class="inline-block">
                                <input type="hidden" name="order_id" value="<?= $currentOrderId ?>">
                                <button type="submit" name="status" value="cancelled" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                    Cancel Order
                                </button>
                            </form>
                        </div>
                    </div>  
                <?php
                    endif;
                    $currentOrderId = $order['order_id']; 
            ?>
            <div class="border border-gray-300 shadow-lg rounded-lg p-6 bg-white flex flex-col space-y-4">
              
                <div class="text-lg font-bold text-blue-600">Order #<?= htmlspecialchars($order['order_id']) ?></div>
                <div class="text-gray-800">
                    <strong>Customer:</strong> <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?><br>
                    <strong>Company:</strong> <?= htmlspecialchars($order['company']) ?><br>
                    <strong>Region:</strong> <?= htmlspecialchars($order['region']) ?>, <?= htmlspecialchars($order['city']) ?><br>
                    <strong>Address:</strong> <?= htmlspecialchars($order['address']) ?><br>
                    <strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?><br>
                    <strong>Email:</strong> <?= htmlspecialchars($order['email']) ?><br>
                </div>
                
                 
                <div class="flex flex-wrap items-center justify-between bg-gray-100 p-4 rounded-lg">
                    <div>
                        <span class="text-gray-600">Payment Method:</span> <?= htmlspecialchars($order['payment_method']) ?>
                    </div>
                    <div>
                        <span class="text-gray-600">Payment Status:</span> 
                        <span class="font-semibold <?= $order['payment_status'] == 'completed' ? 'text-green-600' : 'text-red-600' ?>">
                            <?= htmlspecialchars($order['payment_status']) ?>
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-600">Total Price:</span> 
                        <span class="text-xl font-bold text-green-600">$<?= htmlspecialchars($order['total_price']) ?></span>
                    </div>
                    <div>
                        <span class="text-gray-600">Order Date:</span> <?= htmlspecialchars($order['order_date']) ?>
                    </div>
                    <div>
                        <span class="text-gray-600">Order Status:</span> 
                        <span class="font-semibold <?= $order['status'] == 'pending' ? 'text-yellow-600' : 'text-gray-600' ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </div>
                </div>

                <!-- Products Info -->
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold text-gray-700">Products</h3>
                    <ul class="space-y-4">
            <?php endif; ?>
                        <li class="bg-gray-50 p-4 rounded-lg shadow-sm">
                            <div class="flex flex-col md:flex-row justify-between items-center">
                                <div class="flex-1">
                                    <strong><?= htmlspecialchars($order['product_name']) ?></strong> 
                                    (Brand: <?= htmlspecialchars($order['brand']) ?>, Size: <?= htmlspecialchars($order['size']) ?>, Color: <span style="color: <?= htmlspecialchars($order['color']) ?>;">●</span>)
                                    <p class="text-gray-600"><?= htmlspecialchars($order['description']) ?></p>
                                </div>
                                <div class="flex-shrink-0 text-right">
                                    <p class="font-bold text-green-600">$<?= htmlspecialchars($order['price']) ?></p>
                                    <p class="text-gray-600">Quantity: <?= htmlspecialchars($order['quantity']) ?></p>
                                </div>
                            </div>
                        </li>
            <?php 
               
                if (end($orders) === $order || $orders[array_search($order, $orders) + 1]['order_id'] !== $order['order_id']): 
            ?>
                    </ul>
                    <div class="flex justify-end space-x-4">
                        <form action="update_order_status.php" method="POST" class="inline-block">
                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                            <button type="submit" name="status" value="shipped" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                                Mark as Shipped
                            </button>
                        </form>
                         <form action="update_order_status.php" method="POST" class="inline-block">
                                <input type="hidden" name="order_id"  value="<?= $order['order_id'] ?>">
                                <button type="submit" name="status" value="completed" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                    Mark as completed
                                </button>
                            </form>
                        <form action="update_order_status.php" method="POST" class="inline-block">
                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                            <button type="submit" name="status" value="cancelled" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                Cancel Order
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../components/footer.php'; ?>
