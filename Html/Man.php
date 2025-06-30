<?php
include '../PHP/db_connect.php'; // এখানে $conn আগেই তৈরি হয়েছে
session_start();

// এখানে নতুন করে $conn = new mysqli(...) দরকার নেই

// Search/filter logic
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$shop_type = 'men';

// Fetch only active shops of this type
$shop_sql = "SELECT * FROM shop_owners WHERE shop_type='$shop_type' AND is_active=1";
$shops = $conn->query($shop_sql);

$shop_ids = [];
while($row = $shops->fetch_assoc()) $shop_ids[] = $row['shop_owner_id'];
$shop_ids_csv = count($shop_ids) ? implode(',', $shop_ids) : '0';

// Fetch products for all the shops (with search if any)
$product_sql = "SELECT p.*, s.shop_name FROM products p 
    JOIN shop_owners s ON s.shop_owner_id = p.shop_owner_id
    WHERE s.shop_type='$shop_type' AND s.is_active=1";
if($search) $product_sql .= " AND p.product_name LIKE '%$search%'";
$product_sql .= " ORDER BY p.product_id DESC";
$products = $conn->query($product_sql);

if (isset($_POST['add_love']) && isset($_POST['product_id']) && isset($_SESSION['customer_id'])) {
    $product_id = intval($_POST['product_id']);
    $customer_id = intval($_SESSION['customer_id']);

    // ডুপ্লিকেট চেক
    $check = $conn->prepare("SELECT 1 FROM product_loves WHERE product_id=? AND customer_id=?");
    $check->bind_param("ii", $product_id, $customer_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        // ইনসার্ট
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
    <title>সহজ যোগান</title>
    <link rel="stylesheet" href="../CSS/Man.css">
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
        <nav><ul></ul></nav>
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
        <section class="banner">
            <img src="../Images/Man-banner.jpg" alt="Men's Fashion Banner">
            <div class="banner-text">
                <h2>নিজেকে আরও স্মার্ট আর আত্মবিশ্বাসী দেখাতে চাও?</h2>
                <p>এই পাতায় থাকছে তোমার জন্য স্টাইলিশ ও প্রয়োজনীয় সব কিছু। ফ্যাশনেবল, আরামদায়ক, আর প্রতিদিনের জন্য পারফেক্ট। দেখে নাও আজকের কালেকশন!</p>
            </div>
        </section>
        <section class="intro">
            <h1>পুরুষদের জন্য সেরা কালেকশন</h1>
            <p>এখানে আপনি পাবেন স্টাইলিশ জামা-কাপড়, আনুষঙ্গিক পণ্য এবং দৈনন্দিন ব্যবহারের সেরা আইটেম। প্রতিটি পণ্য আপনার প্রতিদিনের লুক ও আত্মবিশ্বাস বাড়াতে সাহায্য করবে। চলো দেখে নিই কী আছে আজকের কালেকশনে!</p>
      <?php if (isset($love_msg)) : ?>
    <div class="wishlist-message"><?php echo htmlspecialchars($love_msg); ?></div>
<?php endif; ?>
<section class="product-grid">
    <?php if($products && $products->num_rows > 0): ?>
        <?php while($product = $products->fetch_assoc()): ?>
        <div class="product-card">
            <img src="<?php echo htmlspecialchars($product['product_image_path'] ?: '../Images/gift.jpg'); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
            <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
            <p>৳ <?php echo htmlspecialchars($product['price']); ?></p>
            <p class="store-name">
                দোকান: 
                <a href="../Html/ShopOwner_Home.php?shop_owner_id=<?php echo $product['shop_owner_id']; ?>">
                    <?php echo htmlspecialchars($product['shop_name']); ?>
                </a>
            </p>
            <!-- ঠিক করা Buy button -->
            <a href="../Html/Buy.php?product_id=<?php echo (int)$product['product_id']; ?>" class="buy-btn">এখন কিনুন</a>
            
            <!-- Wishlist/cart button (form সহ) -->
            <form method="post" action="" style="display:inline;">
                <input type="hidden" name="product_id" value="<?php echo (int)$product['product_id']; ?>">
                <button type="submit" name="add_love" class="cart-btn">কার্টে যোগ করুন</button>
            </form>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center;padding:20px;">কোনো পণ্য পাওয়া যায়নি।</p>
    <?php endif; ?>
</section>
    </main>
    <footer class="footer">
        <div class="footer-links">
            <div class="footer-column">
                <h4>শপিং অনলাই</h4>
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
    .product-card h3 { margin: 13px 0 5px; font-size: 1.1rem; }
    .product-card p { margin: 3px 0; }
    .product-card .buy-btn, .product-card .cart-btn {
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
    .product-card .buy-btn:hover, .product-card .cart-btn:hover {
        background: #0a4025;
    }
    .cart-btn {
    background: #19a463;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 8px 18px;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.2s;
}
.cart-btn:hover { background: #157f4c; }
.wishlist-message {
    margin: 10px 0;
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