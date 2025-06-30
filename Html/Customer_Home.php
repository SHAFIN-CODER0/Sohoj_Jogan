<?php
session_start();
include '../PHP/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['customer_email'])) {
    echo "<script>
        alert('You must log in first!');
        window.location.href = '../Html/index.php';
    </script>";
    exit();
}

// Prepare the SQL query to get shop data including shop_owner_id
$shops_sql = "SELECT shop_owner_id, shop_name, shop_description, shop_image_path FROM shop_owners ORDER BY created_at DESC";
$shops_result = mysqli_query($conn, $shops_sql);
if (!$shops_result) {
    die("Database query failed: " . mysqli_error($conn));
}

// Get customer coin balance and ID
$customer_email = $_SESSION['customer_email'];
$coin_sql = "SELECT customer_coins, customer_id FROM customers WHERE customer_email = ?";
$coin_stmt = $conn->prepare($coin_sql);
$coin_stmt->bind_param('s', $customer_email);
$coin_stmt->execute();
$coin_stmt->bind_result($customer_coins, $customer_id);
$coin_stmt->fetch();
$coin_stmt->close();
$_SESSION['customer_id'] = $customer_id;

// Function to convert to Bangla numerals
function bn_number($number) {
    $bn_digits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    return strtr($number, ['0'=>$bn_digits[0],'1'=>$bn_digits[1],'2'=>$bn_digits[2],'3'=>$bn_digits[3],'4'=>$bn_digits[4],'5'=>$bn_digits[5],'6'=>$bn_digits[6],'7'=>$bn_digits[7],'8'=>$bn_digits[8],'9'=>$bn_digits[9]]);
}

// Product search logic
$product_results = null;
$search_query = '';
if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search_query = trim($_GET['search']);
    $product_sql = "SELECT p.product_id, p.product_name, p.product_image_path, p.price,
                   s.shop_owner_id, s.shop_name, s.shop_image_path
            FROM products p
            JOIN shop_owners s ON p.shop_owner_id = s.shop_owner_id
            WHERE p.product_name LIKE ?
            ORDER BY s.shop_name ASC, p.product_name ASC";
    $stmt = $conn->prepare($product_sql);
    if ($stmt) {
        $search = "%$search_query%";
        $stmt->bind_param('s', $search);
        $stmt->execute();
        $product_results = $stmt->get_result();
        $stmt->close();
    } else {
        die("Product search query failed: " . $conn->error);
    }
}
// Customer Notifications Fetch
$customerNotifications = [];
if (isset($_SESSION['customer_id'])) {
    $cid = $_SESSION['customer_id'];
    $notif_sql = "SELECT n.*, 
    p.product_name, o.quantity, p.price, o.delivery_charge,
    so.shop_name, so.shop_owner_phone, 
    dm.delivery_man_name, dm.delivery_man_phone
FROM notifications n
LEFT JOIN orders o ON n.order_id = o.order_id
LEFT JOIN products p ON o.product_id = p.product_id
LEFT JOIN shop_owners so ON o.shop_owner_id = so.shop_owner_id
LEFT JOIN delivery_men dm ON n.accepted_by = dm.delivery_man_id
WHERE n.user_id = ? AND n.user_type = 'customer'
ORDER BY n.created_at DESC";
    $stmt = $conn->prepare($notif_sql);
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $notif_result = $stmt->get_result();
    while ($row = $notif_result->fetch_assoc()) {
        $customerNotifications[] = $row;
    }
    $stmt->close();
}
// Fetch warning for this customer (if any)
$warning_message = null;
if (isset($_SESSION['customer_id'])) {
    $customerId = $_SESSION['customer_id'];
    $warnSql = "SELECT reason, warned_at FROM warned_users WHERE user_type='customer' AND user_id=?";
    $warnStmt = $conn->prepare($warnSql);
    $warnStmt->bind_param("i", $customerId);
    $warnStmt->execute();
    $warnStmt->bind_result($reason, $warned_at);
    if ($warnStmt->fetch()) {
        $warning_message = [
            'reason' => $reason,
            'warned_at' => $warned_at
        ];
    }
    $warnStmt->close();
}
$followed_shops = [];
if (isset($_SESSION['customer_id'])) {
    $cid = $_SESSION['customer_id'];
    $sql = "SELECT so.shop_owner_id, so.shop_name, so.shop_description, so.shop_image_path
            FROM shop_followers sf
            JOIN shop_owners so ON sf.shop_owner_id = so.shop_owner_id
            WHERE sf.customer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $followed_shops[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../CSS/Customer_Home.css?=1">
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
    <nav>
        <ul>
            <li><a href="../Html/Women.php">নারী</a></li>
            <li><a href="../Html/Man.php">পুরুষ</a></li>
            <li><a href="../Html/Gift.php">উপহার</a></li>
            <li><a href="../Html/Histrory.php?customer_id=<?= $_SESSION['customer_id'] ?>">লাইব্রেরি</a></li>
        </ul>
    </nav>
    <div class="icons">
        <!-- Coin Balance -->
        <div class="coin-balance">
            <img src="../Images/coin-icon.png" alt="Coins" class="coin-icon">
            <span id="coinCount">
                <?php echo isset($customer_coins) ? bn_number($customer_coins) : '০'; ?>
            </span>
        </div>
        <!-- Product Search Bar (Form) -->
        <div class="search-bar">
            <form method="get" action="" class="search-bar-form">
                <input type="text" name="search" placeholder="পণ্যের নাম লিখুন..." value="<?php echo htmlspecialchars($search_query); ?>" required>
                <button id="submit"><img src="../Images/search.png" alt="Search"></button>
            </form>
        </div>
        <!-- Icons -->
        <button id="userIcon"><img src="../Images/Sample_User_Icon.png" alt="User"></button>
       <button id="notificationIcon" style="position:relative;">
    <img src="../Images/notification.png" alt="Notifications">
  
</button>


        <button id="messengerBtn"><img src="../Images/messenger-icon.png" alt="Messenger"></button>
        <button id="wishlistbtn"><img src="../Images/heart.png" alt="Wishlist"></button>
    </div>
</header>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Wishlist button
    var wishlistBtn = document.getElementById("wishlistbtn");
    if (wishlistBtn) {
        wishlistBtn.addEventListener("click", function(e) {
            e.preventDefault();
            window.location.href = "../Html/Wish_lisit.php";
        });
    }

    // Messenger button
    var messengerBtn = document.getElementById('messengerBtn');
    if (messengerBtn) {
        messengerBtn.addEventListener('click', function() {
            window.location.href = '../Html/Massenger-chat.php';
        });
    }
});
</script>
<!-- OVERLAY (for background when sidebar is open) -->
<div id="overlay" class="overlay"></div>
<!-- User Sidebar -->
<div id="userSidebar" class="sidebar">
    <br>
<span id="closeUserSidebar" class="sidebar-close-icon">&times;</span>
    <h3>ব্যবহারকারী মেনু</h3>
    <div class="sidebar-content">
        <a href="../Html/Customer_profile.php" id="profileLink">প্রোফাইল</a>
        <a href="../Html/Customer_settings.php" id="settingsLink">সেটিংস</a>
        <a href="#" id="logoutLink">লগ আউট</a>
    </div>
</div>

<style>
.sidebar-close-icon {
  position: absolute;
  top: 10px;
  right: 15px;
  font-size: 28px;
  font-weight: bold;
  color: #333;
  cursor: pointer;
  transition: color 0.3s ease, transform 0.3s ease;
  margin-top: 170px;
}

.sidebar-close-icon:hover {
  color: #d00;
  transform: scale(1.2);
}
</style>
<div id="notificationSidebar" class="sidebar">
    <br>
    <span id="closeNotification" class="sidebar-close-icon">&times;</span>
    <h3>নোটিফিকেশন</h3>
    <div class="sidebar-content" style="max-height:80%;overflow-y:auto;">
       <?php if ($warning_message): ?>
    <div style="background:#fff3cd;color:#856404;padding:12px 16px;border-radius:8px;margin-bottom:11px;border:1px solid #ffeeba;font-size:1.02em;">
        <b>⚠️ সতর্কতা / Warning!</b><br>
        <?= nl2br(htmlspecialchars($warning_message['reason'])) ?><br>
        <span style="font-size:0.93em;color:#b28b00;">তারিখ: <?= htmlspecialchars(date('d M Y, h:i A', strtotime($warning_message['warned_at']))) ?></span>
    </div>
<?php endif; ?>
 <?php if (empty($customerNotifications)): ?>
            <p>নতুন কোনো নোটিফিকেশন নেই</p>
        <?php else: ?>
            <ul class="notifications-list">
                <?php foreach ($customerNotifications as $notif): ?>
                    <li class="notification-item<?= $notif['is_read']==0 ? ' unread' : '' ?>">
                        <div class="notification-order-id">
                            <b>Order ID:</b> <?= htmlspecialchars($notif['order_id']) ?>
                        </div>
                        <?php if (!empty($notif['shop_name'])): ?>
                            <div class="notification-shop">
                                <b>দোকান:</b> <?= htmlspecialchars($notif['shop_name']) ?>
                                <?php if (!empty($notif['shop_owner_phone'])): ?>
                                    (<?= htmlspecialchars($notif['shop_owner_phone']) ?>)
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($notif['product_name'])): ?>
                            <div class="notification-product">
                                <b>পণ্য:</b> <?= htmlspecialchars($notif['product_name']) ?> × <?= (int)$notif['quantity'] ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($notif['price'])): ?>
                            <div class="notification-price">
                                <b>অর্ডার মূল্য:</b> <?= htmlspecialchars($notif['price'] * $notif['quantity']) ?> টাকা
                            </div>
                        <?php endif; ?>
                        <?php if ($notif['accepted_by']): ?>
                            <div class="deliveryman-info">
                                <b>ডেলিভারি ম্যান:</b>
                                <a href="../Html/DeliveryMan_Home.php?id=<?= urlencode($notif['accepted_by']) ?>">
                                    <?= htmlspecialchars($notif['delivery_man_name']) ?>
                                </a>
                                <span class="deliveryman-phone">(<?= htmlspecialchars($notif['delivery_man_phone']) ?>)</span>
                            </div>
                            <div class="notification-accepted-at">
                                <b>Accepted At:</b> <?= htmlspecialchars($notif['accepted_at']) ?>
                            </div>
                        <?php else: ?>
                            <div class="notification-no-delivery">এখনো কোনো ডেলিভারি ম্যান এক্সেপ্ট করেনি</div>
                        <?php endif; ?>
                        <div class="notification-time">
                            <?= date('d M, h:i A', strtotime($notif['created_at'])) ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<style>

.notifications-list {
    padding-left: 0;
    margin: 0;
    
}

.notification-item {
    margin-bottom: 14px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
    list-style: none;
    background: #fff;
    font-weight: normal;
    transition: background 0.2s;

}
* Chrome, Edge, Safari */
.sidebar-content::-webkit-scrollbar {
    width: 8px;
    max-height: 100%;
}

.sidebar-content::-webkit-scrollbar-thumb {
    background: #e53935;
    border-radius: 4px;
}
.sidebar-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}
.notification-item.unread {
    font-weight: bold;
    background: #fffbe6;
}

.notification-order-id,
.notification-shop,
.notification-product,
.notification-price,
.notification-accepted-at,
.notification-deliveryman {
    margin-bottom: 2px;
}

.notification-time {
    color: #888;
    font-size: 0.9em;
}

.notification-no-delivery {
    color: #888;
}

.deliveryman-info {
    color: green;
    font-weight: bold;
    margin-bottom: 2px;
    font-size: 1.05em;
}

.deliveryman-info a {
    color: green;
    text-decoration: underline;
    transition: color 0.2s;
    font-weight: normal;
}

.deliveryman-info a:hover {
    color: darkgreen;
    text-decoration: none;
}

.deliveryman-phone {
    font-weight: normal;
    color: #333;
    margin-left: 4px;
    font-size: 0.95em;
}</style>

<script src="../java_script/Customer_Home.js"></script>
<section class="design-masters">
    <div class="design-masters-content">
        <h1 class="title"></h1>
        <p class="description"></p>
    </div>
    <div class="button-wrapper">
        <a href="../Html/map.html" class="shop-now-btn">
            অবস্থান
            <img src="../Images/location.png" alt="Location Icon" style="width: 50px; height: 50px; vertical-align: middle; margin-left: 10px;">
        </a>
    </div>
</section>
<section class="shop-showcase">
    <div class="shop-title">
        <h1>আপনার ফলো করা দোকান</h1>
    </div>
    <div class="gallery-container">
        <?php if (empty($followed_shops)): ?>
            <p>আপনি এখনও কোনো দোকান ফলো করেননি।</p>
        <?php else: ?>
            <?php foreach ($followed_shops as $shop): ?>
                <div class="gallery-item">
                    <img src="<?= htmlspecialchars($shop['shop_image_path']) ?>" alt="<?= htmlspecialchars($shop['shop_name']) ?>">
                    <p class="item-label">
                        <a href="../Html/ShopOwner_Home.php?id=<?= $shop['shop_owner_id'] ?>">
                            <?= htmlspecialchars($shop['shop_name']) ?>
                        </a>
                    </p>
                    <p style="font-size:0.95em; color:#555;"><?= htmlspecialchars($shop['shop_description']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
<style>.design-masters {
    display: flex;
    align-items: center;
    justify-content: space-between; /* Space between text and button */
    height: 120vh; /* Adjust height to fit screen */
    background-image: url('../Images/front_page.png'); /* Use the correct path */
    background-size: cover;
    background-position: center;
    color: white;
    text-align: left;
    padding: 50px; /* Add padding to space elements */
    margin-top: 50px; /* Add margin to top */
}

.design-masters-content {
    max-width: 50%;
     /* Limit width of text */
}

.title {
    margin-top:240px;
    font-size: 2em;
    font-weight: bold;
    font-family: 'Garamond', 'Times New Roman', serif;
font-weight: bold;

}

.description {
    font-size: 1.2em;
    margin-top: 10px;
    
}

.shop-now-btn {
    display: inline-block;
    padding: 10px 100px;
    font-size: 1.8em;
    font-weight: bold;
    color: white;
    background: #4B014B;
    border-radius: 60px;
    text-decoration: none;
    transition: 0.3s;
    position: absolute;
    right: 10px;
    width: 380px;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    margin-top: -380px; /* Adjust to position the button */
}

.shop-now-btn:hover {
    background: #6b146b;
}
</style>

<section class="game-section">
    <h2>🎮 গেম খেলে কয়েন জিতুন!</h2>
    <p>মজা করেই কয়েন অর্জন করুন — এখনই খেলুন!</p>
    <div class="game-box">
        <img src="../Images/game_preview.png" alt="Game Preview" />
        <a href="../Html/Game.php" class="play-now-btn">গেম খেলুন</a>
    </div>
</section>
<!-- Shops Section -->
<section class="shops-section">
    <h2>দোকানসমূহ</h2>
    <div class="shops-list">
    <?php
    if(mysqli_num_rows($shops_result) > 0){
        while ($row = mysqli_fetch_assoc($shops_result)) {
            $shopOwnerId = $row['shop_owner_id'];
            $shopName = htmlspecialchars($row['shop_name']);
            $shopDescription = htmlspecialchars($row['shop_description']);
            $shopImagePath = htmlspecialchars($row['shop_image_path']);
            echo '
            <div class="shop-card">
                <img src="' . $shopImagePath . '" alt="' . $shopName . '" class="shop-image">
                <h2 class="shop-name">
                    <a href="../Html/ShopOwner_Home.php?id=' . $shopOwnerId . '">' . $shopName . '</a>
                </h2>
                <p class="shop-description">' . $shopDescription . '</p>
            </div>';
        }
    } else {
        echo '<p>কোনও দোকান পাওয়া যায়নি।</p>';
    }
    ?>
    </div>
</section>
<?php if ($product_results !== null): ?>
    <section>
        <h2 class="centered-text">অনুসন্ধান ফলাফল: "<?php echo htmlspecialchars($search_query); ?>"</h2>
        <?php if ($product_results->num_rows > 0): ?>
            <?php while ($row = $product_results->fetch_assoc()): ?>
                <div class="product-card">
                    <img src="<?php echo htmlspecialchars($row['product_image_path']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" class="product-image">
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
                        <p>মূল্য: <?php echo bn_number($row['price']); ?> টাকা</p>
                        <p>
                            <img src="<?php echo htmlspecialchars($row['shop_image_path']); ?>" class="shop-thumb" alt="<?php echo htmlspecialchars($row['shop_name']); ?>">
                            দোকান:
                            <a class="shop-link" href="../Html/ShopOwner_Home.php?id=<?php echo $row['shop_owner_id']; ?>">
                                <?php echo htmlspecialchars($row['shop_name']); ?>
                            </a>
                        </p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="centered-text">কোনও পণ্য বা দোকান পাওয়া যায়নি।</p>
        <?php endif; ?>
    </section>
<?php endif; ?>
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
    <div class="footer-bottom"></div>
</footer>
</body>
</html>