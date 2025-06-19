<?php
include '../PHP/db_connect.php'; // ডাটাবেজ কানেকশন

// POST ডেটা সংগ্রহ
$product_id = (int)$_POST['product_id'];
$quantity = (int)$_POST['quantity'];
$delivery = $_POST['delivery'];
$customer_name = trim($_POST['customer_name']);
$customer_address = trim($_POST['customer_address']);
$customer_phone = trim($_POST['customer_phone']);
$customer_comment = trim($_POST['customer_comment']);
$distance = isset($_POST['distance']) ? (float)$_POST['distance'] : 0;
$delivery_charge = isset($_POST['delivery_charge']) ? (int)$_POST['delivery_charge'] : 0;

// (১) অর্ডার টেবিলে সেভ করুন
$sql = "INSERT INTO orders (product_id, quantity, delivery_method, customer_name, customer_address, customer_phone, customer_comment, distance, delivery_charge, order_time)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iisssssdi', $product_id, $quantity, $delivery, $customer_name, $customer_address, $customer_phone, $customer_comment, $distance, $delivery_charge);
$success = $stmt->execute();

// (২) product টেবিল থেকে stock কমান
if ($success) {
    $update_sql = "UPDATE products SET stock = stock - ? WHERE product_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('ii', $quantity, $product_id);
    $update_stmt->execute();
    $update_stmt->close();
}

// (৩) সফল হলে কনফার্মেশন
if ($success) {
    echo "<script>alert('অর্ডার সফলভাবে গ্রহণ করা হয়েছে!');window.location.href='../Html/Customer_Home.php';</script>";
} else {
    echo "<script>alert('কিছু ভুল হয়েছে! আবার চেষ্টা করুন।');history.back();</script>";
}

$stmt->close();
$conn->close();
?>