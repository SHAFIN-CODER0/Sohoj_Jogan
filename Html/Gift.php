<?php
include '../PHP/db_connect.php';
session_start();

// Search logic
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Fetch products from shop_owners where shop_type = 'gift'
$product_sql = "SELECT p.*, s.shop_name 
    FROM products p
    JOIN shop_owners s ON s.shop_owner_id = p.shop_owner_id
    WHERE s.shop_type='gift' AND s.is_active=1";
if($search) $product_sql .= " AND p.product_name LIKE '%$search%'";
$product_sql .= " ORDER BY p.product_id DESC";
$products = $conn->query($product_sql);

if (isset($_POST['add_love']) && isset($_POST['product_id']) && isset($_SESSION['customer_id'])) {
    $product_id = intval($_POST['product_id']);
    $customer_id = intval($_SESSION['customer_id']);
    $check = $conn->prepare("SELECT 1 FROM product_loves WHERE product_id=? AND customer_id=?");
    $check->bind_param("ii", $product_id, $customer_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO product_loves (product_id, customer_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $product_id, $customer_id);
        if ($stmt->execute()) {
            $love_msg = "প্রোডাক্টটি Wishlist-এ যোগ হয়েছে!";
        } else {
            $love_msg = "যোগ করা যায়নি! আবার চেষ্টা করুন।";
        }
        $stmt->close();
    } else {
        $love_msg = "এই প্রোডাক্টটি আগেই Wishlist-এ আছে!";
    }
    $check->close();
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../CSS/Gift.css">
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
        <nav></nav>
        <div class="icons">
            <div class="search-bar">
                <form method="get" action="" class="search-bar-form">
                    <input type="text" name="search" placeholder="পণ্যের নাম লিখুন..." value="<?php echo htmlspecialchars($search); ?>">
                    <button id="submit"><img src="../Images/search.png" alt="Search"></button>
                </form>
            </div> 
           
        </div>
    </header>

    <main>
        <section class="gift-banner">
            <img src="../Images/gift.jpg" alt="উপহারের জন্য বিশেষ অফার" class="banner-img">
            <div class="banner-text">
                <h1>উপহারের জন্য শ্রেষ্ঠ পছন্দ</h1>
                <p>আপনার প্রিয়জনকে খুশি করুন একটি নিখুঁত উপহার দিয়ে!</p>
            </div>
        </section>

       <section class="gift-products">
    <h2>সকল উপহার</h2>
    <div class="product-grid">
        <?php if($products && $products->num_rows > 0): ?>
            <?php while($product = $products->fetch_assoc()): ?>
            <div class="product-card">
                <img src="<?php echo htmlspecialchars($product['product_image_path'] ?: '../Images/gift.jpg'); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                <h4><?php echo htmlspecialchars($product['product_name']); ?></h4>
                <p>৳ <?php echo htmlspecialchars($product['price']); ?></p>
                <p class="shop-name">দোকান: <?php echo htmlspecialchars($product['shop_name']); ?></p>
                <form method="post" action="" style="display:inline;">
                    <input type="hidden" name="product_id" value="<?php echo (int)$product['product_id']; ?>">
                    <button type="submit" name="add_love" class="cart-btn">কার্টে যোগ করুন</button>
                </form>
                <!-- "ক্রয় করুন" বাটন Buy.php তে প্রোডাক্ট আইডি সহ পাঠাবে -->
                <a href="../Html/Buy.php?product_id=<?php echo (int)$product['product_id']; ?>" class="buy-btn">ক্রয় করুন</a>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;padding:20px;">কোনো উপহার পাওয়া যায়নি।</p>
        <?php endif; ?>
    </div>
    <?php if (isset($love_msg)) : ?>
        <div class="wishlist-message" style="text-align:center;"><?php echo htmlspecialchars($love_msg); ?></div>
    <?php endif; ?>
</section>
    </main>

    <footer class="footer">
        <div class="footer-links">
            <div class="footer-column">
                <h4>শপিং অনলাই </h4>
                <ul>
                    <li><a href="#">ডেলিভারি</a></li>
                  
                </ul>
            </div>
            <div class="footer-column">
                <h4>আমাদের সম্পর্কে</h4>
                <ul>
                    <li>
                        <a href="../Html/About_us.html">
                            <img src="../Images/light-bulb.png" alt="info icon" class="link-icon">
                            আমাদের সম্পর্কে বিস্তারিত জানুন
                        </a>
                    </li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>যোগাযোগের তথ্য</h4>
                <ul>
                    <li><a href="#">📞 ফোন</a></li>
                    <li><a href="#">✉ ইমেইল</a></li>
                </ul>
            </div>
        </div>
    </footer>
    <style>
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
    }
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 18px;
        margin: 30px 0;
    }
    .product-card {
        border: 1px solid #e2e2e2;
        border-radius: 10px;
        padding: 12px;
        background: #fff;
        box-shadow: 0 1px 3px 0 rgba(28,124,84,0.03);
        text-align: center;
    }
    .product-card img {
        width: 140px; height: 140px; object-fit: cover; border-radius: 7px;
    }
    .product-card h4 { margin: 13px 0 5px; font-size: 1.1rem; }
    .product-card p { margin: 3px 0; }
    .product-card button, .product-card .buy-btn {
        margin: 7px 3px 0 3px;
        padding: 7px 14px;
        border: none;
        border-radius: 5px;
        background: #1c7c54;
        color: #fff;
        cursor: pointer;
        font-size: .97rem;
        transition: background 0.2s;
    }
    .product-card button:hover, .product-card .buy-btn:hover {
        background: #0a4025;
    }
    .buy-btn {
        background: #19a463;
        color: #fff;
    }
    .wishlist-message {
        margin: 10px auto;
        background: #eaffea;
        color: #2a7c46;
        padding: 8px 16px;
        border-radius: 5px;
        border: 1px solid #c2e8c2;
        width: fit-content;
    }
    </style>
</body>
</html>