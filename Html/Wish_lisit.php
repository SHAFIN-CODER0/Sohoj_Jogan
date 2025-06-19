<?php
session_start();
include '../PHP/db_connect.php';

if (!isset($_SESSION['customer_id'])) {
    echo "<script>alert('Please login first!'); window.location.href='../Html/login.php';</script>";
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Get followed shops: shop_owner_id, shop_name, shop_image_path, shop_owner_image_path
$followedShops = [];
$sql = "SELECT s.shop_owner_id, s.shop_name, s.shop_image_path, s.shop_owner_image_path
        FROM shop_owners s
        JOIN shop_followers f ON s.shop_owner_id = f.shop_owner_id
        WHERE f.customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $followedShops[] = $row;
}
$stmt->close();

// Get loved products: all products loved by this customer
$lovedProducts = [];
$sql = "SELECT p.*, s.shop_name, s.shop_owner_image_path
        FROM products p
        JOIN product_loves l ON p.product_id = l.product_id
        JOIN shop_owners s ON p.shop_owner_id = s.shop_owner_id
        WHERE l.customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $lovedProducts[] = $row;
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan) - Wishlist</title>
    <link rel="stylesheet" href="../CSS/wish_lisit.css">
    <style>
    .shop-card {
        display: flex;
        align-items: center;
        gap: 14px;
        border: 1px solid #d7d7d7;
        border-radius: 10px;
        padding: 10px 18px;
        margin-bottom: 14px;
        background: #f3f8fd;
    }
    .shop-card img {
        width: 54px; height: 54px; border-radius: 50%; object-fit: cover;
    }
    .product-card {
        border: 1px solid #e4e4e4;
        border-radius: 12px;
        padding: 15px;
        margin: 12px;
        width: 250px;
        display: inline-block;
        vertical-align: top;
        background: #fff;
    }
    .product-card img {
        width: 100%; height: 120px; object-fit: contain; border-radius: 5px;
    }
    .buy-btn {
        display: block;
        margin-top: 12px;
        background: #3169d7;
        color: #fff;
        border: none;
        padding: 8px 18px;
        border-radius: 8px;
        text-decoration: none;
        text-align: center;
        font-size: 1rem;
    }
    .buy-btn:hover { background: #14368f; color: #fff; }
    </style>
</head>
<body>
    <header>
         <div class="logo" id="logoClickable" style="cursor:pointer;">
    <img src="../Images/Logo.png" alt="Liberty Logo">
    <h2>সহজ যোগান</h2>
</div>
<script>
    document.getElementById('logoClickable').addEventListener('click', function() {
        window.location.href = '../Html/Customer_Home.php';
    });
</script>
        <div class="icons">
            <button><img src="../Images/search-icon.jpg" alt="Search"></button>
            <button id="userIcon"><img src="../Images/Sample_User_Icon.png" alt="User"></button>
            <button><img src="../Images/heart.png" alt="Wishlist"></button>
        </div>
    </header>
    <main>
        <section class="wishlist">
            <h1>আপনার ইচ্ছের তালিকা</h1>
            <p>আপনার পছন্দের পণ্য এবং ফলো করা দোকান</p>

            <h2>ফলো করা দোকানসমূহ</h2>
            <div class="followed-shops">
                <?php if (count($followedShops) == 0): ?>
                    <p>আপনি কোনো দোকান ফলো করেননি।</p>
                <?php else: ?>
                    <?php foreach ($followedShops as $shop): ?>
                        <div class="shop-card">
                            <img src="../uploads/<?php echo htmlspecialchars($shop['shop_owner_image_path']); ?>" alt="Shop Owner">
                            <div>
                                <strong><?php echo htmlspecialchars($shop['shop_name']); ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <h2 style="margin-top:30px;">আপনার পছন্দের পণ্য</h2>
            <div class="wishlist-items">
                <?php if (count($lovedProducts) == 0): ?>
                    <p>আপনি কোনো পণ্য পছন্দ করেননি।</p>
                <?php else: ?>
                    <?php foreach ($lovedProducts as $product): ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($product['product_image_path']); ?>" alt="Product Image">
                            <h4><?php echo htmlspecialchars($product['product_name']); ?></h4>
                            <p>দোকান: <?php echo htmlspecialchars($product['shop_name']); ?></p>
                            <p>স্টক: <?php echo htmlspecialchars($product['stock']); ?> কেজি</p>
                            <p>দাম: <?php echo htmlspecialchars($product['price']); ?> টাকা</p>
                            <a href="../Html/Buy.php?product_id=<?php echo (int)$product['product_id']; ?>" class="buy-btn">Buy</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>