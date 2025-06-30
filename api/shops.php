<?php
session_start();
include '../PHP/db_connect.php'; // $conn variable thakbe ekhane

header('Content-Type: application/json; charset=utf-8');

// যদি product search parameter আসে
if (!empty($_GET['product'])) {
    $product = $_GET['product'];
    // পণ্যের নাম দিয়ে shop ও product details show
    $sql = "SELECT s.shop_owner_id, s.shop_name, s.shop_latitude, s.shop_longitude, s.address_area, s.shop_type,
                   p.product_id, p.product_name, p.price, p.stock, p.product_image_path
            FROM shop_owners s
            JOIN products p ON s.shop_owner_id = p.shop_owner_id
            WHERE s.is_active = 1
              AND s.shop_latitude IS NOT NULL
              AND s.shop_longitude IS NOT NULL
              AND p.product_name LIKE ?";
    $stmt = $conn->prepare($sql);
    $like = "%$product%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
    $shops = [];
    while ($row = $result->fetch_assoc()) {
        $row['shop_owner_id'] = (int)$row['shop_owner_id'];
        $row['shop_latitude'] = (float)$row['shop_latitude'];
        $row['shop_longitude'] = (float)$row['shop_longitude'];
        $row['product_id'] = (int)$row['product_id'];
        $row['price'] = (float)$row['price'];
        $row['stock'] = (int)$row['stock'];
        $shops[] = $row;
    }
    echo json_encode($shops, JSON_UNESCAPED_UNICODE);
    $conn->close();
    exit;
}

// না হলে শুধু shop list
$sql = "SELECT shop_owner_id, shop_name, shop_latitude, shop_longitude, address_area, shop_type 
        FROM shop_owners 
        WHERE is_active = 1 
        AND shop_latitude IS NOT NULL 
        AND shop_longitude IS NOT NULL";
$result = $conn->query($sql);
$shops = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['shop_owner_id'] = (int)$row['shop_owner_id'];
        $row['shop_latitude'] = (float)$row['shop_latitude'];
        $row['shop_longitude'] = (float)$row['shop_longitude'];
        $shops[] = $row;
    }
}
echo json_encode($shops, JSON_UNESCAPED_UNICODE);
$conn->close();
exit;
?>