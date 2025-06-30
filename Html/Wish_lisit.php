<?php
session_start();
include '../PHP/db_connect.php';

if (!isset($_SESSION['customer_id'])) {
    echo "<script>alert('Please login first!'); window.location.href='../Html/Index.php';</script>";
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Product search (by name)
$search_query = '';
if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search_query = trim($_GET['search']);
}

// Get loved products: all products loved by this customer, with optional search
$lovedProducts = [];
$sql = "SELECT p.*, s.shop_name, s.shop_owner_image_path
        FROM products p
        JOIN product_loves l ON p.product_id = l.product_id
        JOIN shop_owners s ON p.shop_owner_id = s.shop_owner_id
        WHERE l.customer_id = ?";
if ($search_query !== '') {
    $sql .= " AND p.product_name LIKE ?";
}
$stmt = $conn->prepare($sql);
if ($search_query !== '') {
    $like_query = "%$search_query%";
    $stmt->bind_param("is", $customer_id, $like_query);
} else {
    $stmt->bind_param("i", $customer_id);
}
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
            <!-- Product Search Bar (Form) -->
            <div class="search-bar">
                <form method="get" action="" class="search-bar-form">
                    <input type="text" name="search" placeholder="পণ্যের নাম লিখুন..." value="<?php echo htmlspecialchars($search_query); ?>" required>
                    <button id="submit"><img src="../Images/search.png" alt="Search"></button>
                </form>
            </div>
            <button id="userIcon"><img src="../Images/Sample_User_Icon.png" alt="User"></button>
        </div>
    </header>
    <main>
        <style>.search-bar {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 20px 0 10px 0;
}

.search-bar-form {
    display: flex;
    align-items: center;
    gap: 7px;
    background: #f6faf7;
    border-radius: 8px;
    padding: 7px 12px;
    box-shadow: 0 1px 6px 0 rgba(28,124,84,0.04);
}

.search-bar-form input[type="text"] {
    padding: 9px 14px;
    border: 1px solid #c2eccb;
    border-radius: 7px;
    font-size: 1rem;
    width: 220px;
    outline: none;
    transition: border-color 0.2s;
    background: #fff;
}

.search-bar-form input[type="text"]:focus {
    border-color: #1c7c54;
}

.search-bar-form button {
    background: #1c7c54;
    border: none;
    border-radius: 7px;
    padding: 7px 13px;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: background 0.2s;
}

.search-bar-form button:hover {
    background: #0a4025;
}

.search-bar-form button img {
    width: 24px;
    height: 24px;
    display: block;
}</style>
        <section class="wishlist">
            <h1>আপনার ইচ্ছের তালিকা</h1>
            <p>আপনার পছন্দের পণ্যসমূহ</p>
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