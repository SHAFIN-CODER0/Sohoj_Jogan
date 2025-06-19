<?php
session_start();
include '../PHP/db_connect.php';

$isOwner = false;
$shopOwnerId = null;

// মালিক নিজে লগইন করলে (session আছে, URL-এ id নাই)
if (isset($_SESSION['shop_owner_email']) && !isset($_GET['id'])) {
    $email = $_SESSION['shop_owner_email'];
    $sql = "SELECT shop_owner_id, shop_owner_name, shop_name, shop_image_path, shop_owner_image_path FROM shop_owners WHERE shop_owner_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $shopOwnerId = $row['shop_owner_id'];
        $shopOwnerName = $row['shop_owner_name'];
        $shopName = $row['shop_name'];
        $shopImagePath = $row['shop_image_path'];
        $shopOwnerPic = $row['shop_owner_image_path'];
        $isOwner = true;
          $_SESSION['shop_owner_id'] = $shopOwnerId;
    } else {
        echo "<script>
            alert('Shop owner data not found!');
            window.location.href='../Html/index.html';
        </script>";
        exit();
    }
    $stmt->close();
}
// কাস্টমার বা ভিজিটর URL দিয়ে এলে (?id=)
else if (isset($_GET['id'])) {
    $shopOwnerId = intval($_GET['id']);
    $sql = "SELECT shop_owner_id, shop_owner_name, shop_name, shop_image_path, shop_owner_image_path FROM shop_owners WHERE shop_owner_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $shopOwnerId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $shopOwnerName = $row['shop_owner_name'];
        $shopName = $row['shop_name'];
        $shopImagePath = $row['shop_image_path'];
        $shopOwnerPic = $row['shop_owner_image_path'];
    } else {
        echo "<script>
            alert('Shop not found!');
            window.location.href='../Html/index.html';
        </script>";
        exit();
    }
    $stmt->close();
}
// কেউ না থাকলে
else {
    echo "<script>
        alert('You must log in first!');
        window.location.href='../Html/index.html';
    </script>";
    exit();
}

// Product search logic
$product_results = null;
$search_query = '';
if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search_query = trim($_GET['search']);
    // Only search in this shop owner's products!
    $sql = "SELECT * FROM products WHERE shop_owner_id = ? AND product_name LIKE ?";
    $stmt = $conn->prepare($sql);
    $like_search = "%$search_query%";
    $stmt->bind_param('is', $shopOwnerId, $like_search);
    $stmt->execute();
    $product_results = $stmt->get_result();
    $stmt->close();
} else {
    // No search, show all products
    $productSql = "SELECT * FROM products WHERE shop_owner_id = ?";
    $productStmt = $conn->prepare($productSql);
    $productStmt->bind_param("i", $shopOwnerId);
    $productStmt->execute();
    $product_results = $productStmt->get_result();
    $productStmt->close();
}

// বিজ্ঞাপন ফেচ
$adSql = "SELECT advertise_text FROM products WHERE shop_owner_id = ? AND advertise_option = 'yes' AND advertise_text IS NOT NULL AND advertise_text != ''";
$adStmt = $conn->prepare($adSql);
$adStmt->bind_param("i", $shopOwnerId);
$adStmt->execute();
$adResult = $adStmt->get_result();

$advertiseTexts = [];
while ($adRow = $adResult->fetch_assoc()) {
    $advertiseTexts[] = $adRow['advertise_text'];
}
$adStmt->close();

// --- FOLLOWER LOGIC ---
$followerCount = 0;
$isFollowing = false;

// Toggle follow/unfollow if the form is submitted
if (
    !$isOwner &&
    isset($_SESSION['customer_id']) &&
    isset($_POST['toggle_follow']) &&
    isset($shopOwnerId)
) {
    $customer_id = $_SESSION['customer_id'];
    // Check if already following
    $sql = "SELECT 1 FROM shop_followers WHERE shop_owner_id=? AND customer_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $shopOwnerId, $customer_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        // Unfollow
        $stmt->close();
        $sql = "DELETE FROM shop_followers WHERE shop_owner_id=? AND customer_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $shopOwnerId, $customer_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // Follow
        $stmt->close();
        $sql = "INSERT INTO shop_followers (shop_owner_id, customer_id) VALUES (?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $shopOwnerId, $customer_id);
        $stmt->execute();
        $stmt->close();
    }
    // Redirect to avoid form resubmission warning (PRG pattern)
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

if ($shopOwnerId) {
    // Get follower count
    $sql = "SELECT COUNT(*) as cnt FROM shop_followers WHERE shop_owner_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $shopOwnerId);
    $stmt->execute();
    $stmt->bind_result($followerCount);
    $stmt->fetch();
    $stmt->close();

    // Check if current customer is following
    if (!$isOwner && isset($_SESSION['customer_id'])) {
        $customer_id = $_SESSION['customer_id'];
        $sql = "SELECT 1 FROM shop_followers WHERE shop_owner_id = ? AND customer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $shopOwnerId, $customer_id);
        $stmt->execute();
        $stmt->store_result();
        $isFollowing = $stmt->num_rows > 0;
        $stmt->close();
    }
}

// --- PRODUCT LOVE/UNLOVE LOGIC ---
// (MUST be before $conn->close();)
if (isset($_POST['toggle_love'], $_POST['product_id']) && isset($_SESSION['customer_id'])) {
    $product_id = (int)$_POST['product_id'];
    $customer_id = $_SESSION['customer_id'];

    $sql = "SELECT 1 FROM product_loves WHERE product_id=? AND customer_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $product_id, $customer_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        $sql = "DELETE FROM product_loves WHERE product_id=? AND customer_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $product_id, $customer_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt->close();
        $sql = "INSERT INTO product_loves (product_id, customer_id) VALUES (?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $product_id, $customer_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

// Only now, after ALL DB work, close the connection ONCE:
$conn->close();

?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../CSS/ShopOwner_Home.css?v=1">
</head>
<body>

    <!-- HEADER SECTION -->
    <header>
          <div class="logo" id="logoClickable" style="cursor:pointer;">
        <img src="../Images/Logo.png" alt="Liberty Logo">
        <h2>সহজ যোগান</h2>
    </div>

    <!-- ... rest of your page ... -->

    <script>
        // Set JS variable based on PHP variable
        const isOwner = <?php echo $isOwner ? 'true' : 'false'; ?>;

        // Wait for DOM to be ready before adding event
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('logoClickable').addEventListener('click', function() {
                if (isOwner) {
                    window.location.href = '../Html/ShopOwner_Home.php';
                } else {
                    window.location.href = '../Html/Customer_Home.php';
                }
            });
        });
    </script>
        <!-- Top Icons -->
       <?php if ($isOwner): ?>
<div class="icons">
    <button id="userIcon">
        <img src="../Images/Sample_User_Icon.png" alt="User">
    </button>
    <button id="notificationIcon">
        <img src="../Images/notification.png" alt="Notifications">
    </button>
    <button id="messengerBtn">
        <img src="../Images/messenger-icon.png" alt="Messenger">
    </button>
</div>
<?php endif; ?>
    </header>

    <!-- Only show sidebar if owner -->
    <?php if ($isOwner): ?>
    <!-- OVERLAY (for background when sidebar is open) -->
    <div id="overlay" class="overlay"></div>
    <!-- User Sidebar -->
    <div id="userSidebar" class="sidebar">
        <span id="closeUserSidebar" class="close-btn">&times;</span>
        <h3>ব্যবহারকারী মেনু</h3>
        <<div class="sidebar-content">
    <a href="../Html/ShopOwner_item.php" id="profileLink">নতুন সংগ্রহ</a>
    <a href="../Html/ShopOwner_settings.php" id="settingsLink">সেটিংস</a>
    <a href="../Html/ShopOwner_settings_password.php" id="changePasswordLink">পাসওয়ার্ড পরিবর্তন</a>
    <a href="../Html/Histrory.php?shop_owner_id=<?= urlencode($_SESSION['shop_owner_id']) ?>">লাইব্রেরি</a>
    <a href="#" id="logoutLink">লগ আউট</a>
</div>
    </div>
    <?php endif; ?>

    <!-- Notification Sidebar (both can see, you can restrict if you want) -->
    <div id="notificationSidebar" class="sidebar">
        <span id="closeNotification" class="close-btn">&times;</span>
        <h3>নোটিফিকেশন</h3>
        <div class="sidebar-content">
            <p>নতুন কোনো নোটিফিকেশন নেই</p>
        </div>
    </div>
    <!-- Messenger Sidebar (both can see, you can restrict if you want) -->
    <div id="messengerSidebar" class="sidebar">
        <span id="closeMessenger" class="close-btn">&times;</span>
        <h3>মেসেজ</h3>
        <div class="sidebar-content">
            <p>কোনো নতুন মেসেজ নেই</p>
        </div>
    </div>

 <main>
  <section class="shop-banner-section">
    <div class="shop-banner">
      <img src="../uploads/<?php echo htmlspecialchars($shopImagePath); ?>" alt="Shop Background Image" />

      <div class="shop-title">
        <h1><?php echo htmlspecialchars($shopName); ?></h1>
      </div>

  <div class="owner-info">
  <img src="../uploads/<?php echo htmlspecialchars($shopOwnerPic); ?>" alt="Shop Owner Image" />

  <div class="owner-name">
    <h3><?php echo htmlspecialchars($shopOwnerName); ?></h3>
  </div>

  <div class="follower-count" id="followerCount">
    ❤️ <span id="followerNumber"><?php echo $followerCount; ?></span> জন অনুসরণকারী
  </div>

  <?php if (!$isOwner): ?>
    <button class="messenger-btn" title="মেসেজ পাঠান">
      <img src="../Images/chat.png" alt="Messenger">
      মেসেজ
    </button>
  <?php endif; ?>

  <?php if (!$isOwner && isset($_SESSION['customer_id'])): ?>
    <form method="post" style="display:inline;">
      <input type="hidden" name="follow_action" value="1">
      <button class="follow-btn" type="submit" name="toggle_follow">
        <?php echo $isFollowing ? '❤️ অনুসরণ করছেন' : '❤️ অনুসরণ করুন'; ?>
      </button>
    </form>
  <?php endif; ?>

</div>
<style>.messenger-btn {
  padding: 6px 14px;
  background-color: rgb(113, 137, 200);
  border: none;
  border-radius: 15px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.95rem;
  font-weight: 200;
  color: #050505;
  margin-left: 10px;
}
.messenger-btn:hover {
  background-color: rgb(76, 82, 98);
}
.messenger-btn img {
  height: 38px;
  width: 38px;
  object-fit: contain;
}
.follow-btn {
  padding: 6px 14px;
  border: none;
  border-radius: 15px;
  cursor: pointer;
  background: #fff0f6;
  font-size: 0.95rem;
  font-weight: 500;
  color: #e53935;
  margin-left: 10px;
  transition: background 0.2s;
}
.follow-btn:hover {
  background: #ffebee;
}</style>

      <!-- Product Search: Only for visitors/customers -->
      <?php if (!$isOwner): ?>
        <form class="product-search" method="get" action="">
          <input type="text" name="search" placeholder="পণ্য খুঁজুন..." 
            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" required />
          <input type="hidden" name="id" value="<?php echo (int)$shopOwnerId; ?>">
          <button class="search-btn" type="submit">
            <img src="../Images/search.png" alt="Search Icon" class="search-icon" />
          </button>
        </form>
      <?php endif; ?>

<?php if ($isOwner): ?>
    <!-- Shop Owner: নিজের shop-এর review দেখতে -->
    <button class="review-toggle-btn" type="button" onclick="window.location.href='../Html/ShopOwner_review.php'">রিভিউ দেখুন</button>
<?php elseif (!$isOwner): ?>
    <!-- Customer: নির্দিষ্ট shop-এর review দেখতে -->
    <button class="review-toggle-btn" type="button" onclick="window.location.href='../Html/ShopOwner_review.php?shop_owner_id=<?php echo $shopOwnerId; ?>'">রিভিউ দেখুন</button>
<?php endif; ?>   

<?php if (!$isOwner): ?>
<button class="report-btn" type="button"
    onclick="window.location.href='../Html/report.php?shop_owner_id=<?php echo $shopOwnerId; ?>&shop_name=<?php echo urlencode($shopName); ?>'">
    রিপোর্ট করুন
</button>      <?php endif; ?>

      <!-- Add Product Button for Shop Owner -->
      <?php if ($isOwner): ?>
        <a href="../Html/ShopOwner_item.php" class="add-product-btn">+ নতুন পণ্য যোগ করুন</a>
      <?php endif; ?>

    </div> <!-- End of .shop-banner -->
  </section>
</main>

<section class="product-display-section">
  <h2>
      বর্তমান পণ্যসমূহ
      <?php if ($search_query) echo ' (অনুসন্ধান: "' . htmlspecialchars($search_query) . '")'; ?>
  </h2>
  <div id="productDisplayList" class="product-list">
    <?php
    // Re-open DB for product love/fetch (if you want to optimize, you can use PDO persistent or keep $conn open for just the loop, but this structure is safe for most local XAMPP use)
    include '../PHP/db_connect.php';
    if ($product_results && $product_results->num_rows > 0) {
        while($product = $product_results->fetch_assoc()) {
            $product_id = $product['product_id'];

            // Get love count
            $sql = "SELECT COUNT(*) FROM product_loves WHERE product_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $stmt->bind_result($loveCount);
            $stmt->fetch();
            $stmt->close();

            // Check if this user loved it
            $isLoved = false;
            if (isset($_SESSION['customer_id'])) {
                $customer_id = $_SESSION['customer_id'];
                $sql = "SELECT 1 FROM product_loves WHERE product_id=? AND customer_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $product_id, $customer_id);
                $stmt->execute();
                $stmt->store_result();
                $isLoved = $stmt->num_rows > 0;
                $stmt->close();
            }

            echo '<div class="product-card">';
            echo '<img src="' . htmlspecialchars($product['product_image_path']) . '" alt="Product Image">';
            echo '<h4>' . htmlspecialchars($product['product_name']) . '</h4>';
            echo '<p>স্টক: ' . htmlspecialchars($product['stock']) . ' কেজি</p>';
            echo '<p>দাম: ' . htmlspecialchars($product['price']) . ' টাকা</p>';
  if (!$isOwner) {
            // Love button & count
            echo '<div class="love-section">';
            if (isset($_SESSION['customer_id'])) {
                echo '<form method="post" style="display:inline;">';
                echo '<input type="hidden" name="product_id" value="' . $product_id . '">';
                echo '<button type="submit" name="toggle_love" class="love-btn" title="Love this product">';
                echo $isLoved ? '❤️' : '🤍';
                echo '</button>';
                echo '</form> ';
            } else {
                // Not logged in
                echo '<span title="লগইন করে পছন্দ করুন" style="font-size:1.5rem;opacity:0.5;">🤍</span>';
            }
            echo '</div>';
        }
            // Only customer/visitor can see Buy button
            if (!$isOwner) {
                echo '<a href="../Html/Buy.php?product_id=' . (int)$product['product_id'] . '" class="buy-btn">Buy</a>';
            }
            echo '</div>';
        }
    } else {
        echo '<div style="text-align:center; width:100%; padding: 20px;">';
        if ($search_query) {
            echo '<h3 style="color: red;">🚫 কোনো মিল পাওয়া যায়নি</h3>';
        } else {
            echo '<h3 style="color: red;">🚫 কোনো পণ্য পাওয়া যায়নি</h3>';
            if ($isOwner) {
                echo '<p>⚠️ দয়া করে পণ্য যোগ করুন।</p>';
                echo '<a href="../Html/ShopOwner_item.php" class="add-product-btn">Add Product</a>';
            }
        }
        echo '</div>';
    }
    $conn->close();
    ?>
  </div>
</section>
<style>.love-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    transition: transform 0.1s;
}
.love-btn:active {
    transform: scale(1.2);
}
.love-section {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: 75px;
    margin-bottom: 10px;
 
}</style>

    <?php if (count($advertiseTexts) > 0): ?>
    <section class="advertisement-section" >
        <h2>বিজ্ঞাপন</h2>
        <?php foreach ($advertiseTexts as $adText): ?>
            <p><?php echo nl2br(htmlspecialchars($adText)); ?></p>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <!-- FOOTER SECTION -->
    <footer class="footer">
        <div class="footer-links">
            <div class="footer-column">
                <h4>শপিং অনলাই</h4>
                <ul>
                    <li><a href="#">ডেলিভারি</a></li>
                    <li><a href="#">অর্ডার হিস্টোরি</a></li>
                    <li><a href="#">উইস লিস্ট</a></li>
                    <li><a href="#">পেমেন্ট</a></li>
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

    <script src="../java_script/ShopOwner_Home.js"></script>
</body>
</html>