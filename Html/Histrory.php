<?php
include '../PHP/db_connect.php';
session_start();

$orders = [];
$isCustomer = isset($_GET['customer_id']);
$isShopOwner = isset($_GET['shop_owner_id']);

// Shop Owner: Show order table's customer_name (NOT join with customers)
if ($isShopOwner) {
    $shop_owner_id = (int)$_GET['shop_owner_id'];
    $stmt = $conn->prepare(
        "SELECT o.*, p.product_name, p.price, p.product_image_path
         FROM orders o
         JOIN products p ON o.product_id = p.product_id
         WHERE o.shop_owner_id = ?
         ORDER BY o.order_time DESC"
    );
    $stmt->bind_param("i", $shop_owner_id);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} elseif ($isCustomer) {
    $customer_id = (int)$_GET['customer_id'];
    $stmt = $conn->prepare(
        "SELECT o.*, p.product_name, p.price, p.product_image_path, s.shop_name
         FROM orders o
         JOIN products p ON o.product_id = p.product_id
         JOIN shop_owners s ON o.shop_owner_id = s.shop_owner_id
         WHERE o.customer_id = ?
         ORDER BY o.order_time DESC"
    );
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Order History</title>
    <link rel="stylesheet" href="../CSS/History.css">
    <style>
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>
<header>
    <div class="logo" id="logoClickable" style="cursor:pointer;">
    <img src="../Images/Logo.png" alt="Liberty Logo">
    <h2>সহজ যোগান</h2>
</div>
   <script>
    // Pass PHP value into JS variable
    const isShopOwner = <?php echo $isShopOwner ? 'true' : 'false'; ?>;
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('logoClickable').addEventListener('click', function() {
            if (isShopOwner) {
                window.location.href = '../Html/ShopOwner_Home.php';
            } else {
                window.location.href = '../Html/Customer_Home.php';
            }
        });
    });
</script>
</header>
    <main>
        <section class="order-history">
            <h1>
                <?php if ($isShopOwner): ?>
                    আমার বিক্রির ইতিহাস
                <?php elseif ($isCustomer): ?>
                    আমার অর্ডার হিস্টোরি
                <?php else: ?>
                    অর্ডার হিস্টোরি
                <?php endif; ?>
            </h1>
            <table>
                <thead>
                    <tr>
                        <?php if ($isShopOwner): ?>
                            <th>ক্রেতার নাম</th>
                            <th>পণ্য ছবি</th>
                            <th>পণ্য নাম</th>
                            <th>পরিমাণ</th>
                            <th>মূল্য</th>
                            <th>তারিখ</th>
                        <?php elseif ($isCustomer): ?>
                            <th>দোকানের নাম</th>
                            <th>পণ্য ছবি</th>
                            <th>পণ্য নাম</th>
                            <th>পরিমাণ</th>
                            <th>মূল্য</th>
                            <th>তারিখ</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($orders) == 0): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">কোনো ইতিহাস নেই।</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <?php if ($isShopOwner): ?>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td>
                                    <?php if (!empty($order['product_image_path'])): ?>
                                        <img src="../uploads/<?= htmlspecialchars($order['product_image_path']) ?>" alt="Product Image" class="product-img">
                                    <?php else: ?>
                                        <span>ছবি নেই</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($order['product_name']) ?></td>
                                <td><?= htmlspecialchars($order['quantity']) ?></td>
                                <td><?= number_format($order['price'] * $order['quantity']) ?> টাকা</td>
                                <td><?= date('Y-m-d', strtotime($order['order_time'])) ?></td>
                            <?php elseif ($isCustomer): ?>
                                <td><?= htmlspecialchars($order['shop_name']) ?></td>
                                <td>
                                    <?php if (!empty($order['product_image_path'])): ?>
                                        <img src="../uploads/<?= htmlspecialchars($order['product_image_path']) ?>" alt="Product Image" class="product-img">
                                    <?php else: ?>
                                        <span>ছবি নেই</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($order['product_name']) ?></td>
                                <td><?= htmlspecialchars($order['quantity']) ?></td>
                                <td><?= number_format($order['price'] * $order['quantity']) ?> টাকা</td>
                                <td><?= date('Y-m-d', strtotime($order['order_time'])) ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>