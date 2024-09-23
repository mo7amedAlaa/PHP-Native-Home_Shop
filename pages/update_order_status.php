<?php
include '../config/dbConnection.php';
session_start();

 if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];
 if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

     $allowed_statuses = ['shipped', 'cancelled','completed'];
    if (!in_array($new_status, $allowed_statuses)) {
        $error_message = 'Invalid order status.';
        header("Location:orders.php?$error_message");
        exit();
    }

     $query = "UPDATE orders SET status = :status WHERE id = :order_id AND EXISTS (
                SELECT 1 FROM order_items 
                JOIN products ON order_items.product_id = products.id
                WHERE order_items.order_id = :order_id AND products.user_id = :seller_id
              )";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':status', $new_status);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->bindParam(':seller_id', $seller_id);

    if ($stmt->execute()) {
        $success_message = "Order status updated to '$new_status' successfully.";
        header("Location:orders.php?$success_message");
    } else {
        $error_message = 'Failed to update the order status.';
         header("Location:  orders.php?$error_message");
    }

     
    exit();
} else {
     $error_message = 'Invalid request.';
    header("Location:orders.php?$error_message");
    exit();
}
