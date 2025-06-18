<?php
session_start();
include '../PHP/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['customer_email'])) {
    echo "<script>
        alert('You must log in first!');
        window.location.href = '../Html/index.html';
    </script>";
    exit();
}

// Prepare the SQL query to get shop data including shop_owner_id
$sql = "SELECT shop_owner_id, shop_name, shop_description, shop_image_path FROM shop_owners ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}
// Get customer coin balance
$customer_email = $_SESSION['customer_email'];
$coin_sql = "SELECT customer_coins FROM customers WHERE customer_email = ?";
$coin_stmt = $conn->prepare($coin_sql);
$coin_stmt->bind_param('s', $customer_email);
$coin_stmt->execute();
$coin_stmt->bind_result($customer_coins);
$coin_stmt->fetch();
$coin_stmt->close();

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
    $sql = "SELECT p.product_id, p.product_name, p.product_image_path, p.price,
                   s.shop_owner_id, s.shop_name, s.shop_image_path
            FROM products p
            JOIN shop_owners s ON p.shop_owner_id = s.shop_owner_id
            WHERE p.product_name LIKE ?
            ORDER BY s.shop_name ASC, p.product_name ASC";
    $stmt = $conn->prepare($sql);
    $search = "%$search_query%";
    $stmt->bind_param('s', $search);
    $stmt->execute();
    $product_results = $stmt->get_result();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../CSS/Customer_Home.css?=1"> <!-- Correct CSS path -->
</head>
<body>
 <header>
    <div class="logo">
        <img src="../Images/Logo.png" alt="Liberty Logo">
        <h2>সহজ যোগান</h2>
    </div>

    <nav>
        <ul>
                <li><a href="../Html/New_Collection.html">নতুন এসেছে</a></li>
                <li><a href="../Html/Women.html">নারী</a></li>
                <li><a href="../Html/Man.html">পুরুষ</a></li>
                <li><a href="../Html/Gift.html">উপহার</a></li>
                <li><a href="../Html/Histrory.html">লাইব্রেরি</a></li>
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
            <button id="submit"><img src="../Images/search-icon.jpg" alt="Search"></button>
        </form>
    </div>
    <!-- Icons -->
    <button id="userIcon"><img src="../Images/Sample_User_Icon.png" alt="User"></button>
    <button id="notificationIcon"><img src="../Images/notification.png" alt="Notifications"></button>
    <button id="messengerBtn"><img src="../Images/messenger-icon.png" alt="Messenger"></button>
    <!-- Wishlist Button -->
    <button id="wishlistbtn"><img src="../Images/heart.png" alt="Wishlist"></button>
</div>

</header>
<script>// Add this script to enable the wishlist button to navigate to wishlist.html on click
document.addEventListener("DOMContentLoaded", function() {
    var wishlistBtn = document.getElementById("wishlistbtn");
    if (wishlistBtn) {
        wishlistBtn.addEventListener("click", function(e) {
            e.preventDefault(); // Prevent form submission if inside a form
            window.location.href = "../Html/Wish_lisit.php";
        });
    }
});</script>


<!-- OVERLAY (for background when sidebar is open) -->
<div id="overlay" class="overlay"></div>
<!-- User Sidebar -->
<div id="userSidebar" class="sidebar">
    <span id="closeUserSidebar" class="close-btn">&times;</span>
    <h3>ব্যবহারকারী মেনু</h3> <!-- Changed 'User Menu' to 'ব্যবহারকারী মেনু' -->
    <div class="sidebar-content">
        <a href="../Html/Customer_profile.php" id="profileLink">প্রোফাইল</a> <!-- 'New Collection' in Bangla -->
        <a href="../Html/Customer_settings.php" id="settingsLink">সেটিংস</a> <!-- 'Settings' in Bangla -->
        <a href="#" id="logoutLink">লগ আউট</a>
    </div>
</div>

<!-- Notification Sidebar -->
<div id="notificationSidebar" class="sidebar">
    <span id="closeNotification" class="close-btn">&times;</span>
    <h3>নোটিফিকেশন</h3> <!-- Changed 'Notifications' to 'নোটিফিকেশন' -->
    <div class="sidebar-content">
        <p>নতুন কোনো নোটিফিকেশন নেই</p> <!-- 'No new notifications' in Bangla -->
    </div>
</div>

<!-- Messenger Sidebar -->
<div id="messengerSidebar" class="sidebar">
    <span id="closeMessenger" class="close-btn">&times;</span>
    <h3>মেসেজ</h3> <!-- Changed 'Messages' to 'মেসেজ' -->
    <div class="sidebar-content">
        <p>কোনো নতুন মেসেজ নেই</p> <!-- 'No new messages' in Bangla -->
    </div>
</div>



<script src="../java_script/Customer_Home.js"></script> <!-- Link to JS -->

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
        <!-- Title Section -->
        <div class="shop-title">
            <h1>আপনার আশে-পাশের দোকান</h1>
        </div>
    
        <!-- Gallery Section -->
        <div class="gallery-container">
            <div class="gallery-item">
                <img src="../Images/dokan1.jpg" alt="Fabric Design">
                <p class="item-label">লিবারটি ফ্যাব্রিক এসএস২৫: রিটোল্ড</p>
            </div>
            <div class="gallery-item">
                <img src="../Images/Up.jpg" alt="Fragrance">
                <p class="item-label">লিবারটি এলবিটি. ফ্র্যাগ্রান্স</p>
            </div>
            <div class="gallery-item">
                <img src="../Images/Courier.png" alt="Luxury Dress">
                <p class="item-label">ড্রেস</p>
            </div>
            <div class="gallery-item">
                <img src="../Images/comment.jpg" alt="Luxury Bags">
                <p class="item-label">ব্যাগ</p>
            </div>
            <div class="gallery-item">
                <img src="../Images/home_delivery.jpg" alt="Jewellery">
                <p class="item-label">নতুন আসা: জুয়েলারি</p>
            </div>
            <div class="gallery-item">
                <img src="../Images/home_delivery.jpg" alt="Jewellery">
                <p class="item-label">নতুন আসা: জুয়েলারি</p>
            </div>
            <div class="gallery-item">
                <img src="../Images/home_delivery.jpg" alt="Jewellery">
                <p class="item-label">নতুন আসা: জুয়েলারি</p>
            </div>
    
            <!-- Duplicate the items for seamless scrolling -->
            <div class="gallery-item">
                <img src="../Images/dokan1.jpg" alt="Fabric Design">
                <p class="item-label">লিবারটি ফ্যাব্রিক এসএস২৫: রিটোল্ড</p>
            </div>
            <div class="gallery-item">
                <img src="../Images/Up.jpg" alt="Fragrance">
                <p class="item-label">লিবারটি এলবিটি. ফ্র্যাগ্রান্স</p>
            </div>
            <div class="gallery-item">
                <img src="../Images/Courier.png" alt="Luxury Dress">
                <p class="item-label">ড্রেস</p>
            </div>
            <div class="gallery-item">
                <img src="../Images/comment.jpg" alt="Luxury Bags">
                <p class="item-label">ব্যাগ</p>
            </div>
            <div class="gallery-item">
                <img src="../Images/home_delivery.jpg" alt="Jewellery">
                <p class="item-label">নতুন আসা: জুয়েলারি</p>
            </div>
            <div class="gallery-item">
                <img src="../Images/home_delivery.jpg" alt="Jewellery">
                <p class="item-label">নতুন আসা: জুয়েলারি</p>
            </div>
            <div class="gallery-item">
                <img src="../Images/home_delivery.jpg" alt="Jewellery">
                <p class="item-label">নতুন আসা: জুয়েলারি</p>
            </div>
        </div>
    </section>
    
</section>
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
    if(mysqli_num_rows($result) > 0){
        while ($row = mysqli_fetch_assoc($result)) {
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
                <li><a href="#">অর্ডার হিস্টোরি</a></li>
                <li><a href="#">পেমেন্ট </a></li>
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

    <div class="footer-bottom">
        </div>

       
</footer>

</body>
</html>  