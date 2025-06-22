<?php
session_start();
include '../PHP/db_connect.php';

$isOwner = false;
$shopOwnerId = null;
$isActive = 1; // Default value for shop status

// --- SHOP ACTIVE/INACTIVE TOGGLE LOGIC ---
// Only owner can toggle
if (isset($_SESSION['shop_owner_email'], $_POST['toggle_active'])) {
    $email = $_SESSION['shop_owner_email'];
    $newStatus = ($_POST['active_status'] == '1') ? 1 : 0;
    $sql = "UPDATE shop_owners SET is_active=? WHERE shop_owner_email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $newStatus, $email);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

// মালিক নিজে লগইন করলে (session আছে, URL-এ id নাই)
if (isset($_SESSION['shop_owner_email']) && !isset($_GET['id'])) {
    $email = $_SESSION['shop_owner_email'];
    // !!! এখানে is_active যোগ করো !!!
    $sql = "SELECT shop_owner_id, shop_owner_name, shop_name, shop_image_path, shop_owner_image_path, is_active FROM shop_owners WHERE shop_owner_email = ?";
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
        $isActive = $row['is_active']; // এখানে status আনো
    } else {
        echo "<script>
            alert('Shop owner data not found!');
            window.location.href='../Html/index.php';
        </script>";
        exit();
    }
    $stmt->close();
}
// কাস্টমার বা ভিজিটর URL দিয়ে এলে (?id=)
else if (isset($_GET['id'])) {
    $shopOwnerId = intval($_GET['id']);
    // !!! এখানে is_active যোগ করো !!!
    $sql = "SELECT shop_owner_id, shop_owner_name, shop_name, shop_image_path, shop_owner_image_path, is_active FROM shop_owners WHERE shop_owner_id = ?";
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
        $isActive = $row['is_active']; // এখানে status আনো
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


// Notification fetch (shop owner)
$shopOwnerNotifications = [];
if ($isOwner && isset($shopOwnerId)) {
    $notifSql = "
       SELECT n.*, o.customer_name, o.customer_phone, pr.product_name, pr.price, o.quantity,
       dm.delivery_man_name, dm.delivery_man_phone
FROM notifications n
LEFT JOIN orders o ON n.order_id = o.order_id
LEFT JOIN products pr ON o.product_id = pr.product_id
LEFT JOIN delivery_men dm ON n.accepted_by = dm.delivery_man_id
WHERE n.user_id = ? AND n.user_type = 'shop_owner'
ORDER BY n.created_at DESC
LIMIT 30
    ";
    $stmt = $conn->prepare($notifSql);
    $stmt->bind_param("i", $shopOwnerId);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $shopOwnerNotifications[] = $row;
    }
    $stmt->close();
}

// Fetch warning for shop owner (if any)
$warning_message = null;
if (isset($shopOwnerId)) {
    $warnSql = "SELECT reason, warned_at FROM warned_users WHERE user_type='shop_owner' AND user_id=?";
    $warnStmt = $conn->prepare($warnSql);
    $warnStmt->bind_param("i", $shopOwnerId);
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

    <?php if ($isOwner): ?>
<!-- OVERLAY (for background when sidebar is open) -->
<div id="overlay" class="overlay"></div>
<!-- User Sidebar -->
<div id="userSidebar" class="sidebar">
    <span id="closeUserSidebar" class="close-btn">&times;</span>
    <h3>ব্যবহারকারী মেনু</h3>
    <div class="sidebar-content">
        <a href="../Html/ShopOwner_item.php" id="profileLink">নতুন সংগ্রহ</a>
        <a href="../Html/ShopOwner_settings.php" id="settingsLink">সেটিংস</a>
        <a href="../Html/ShopOwner_settings_password.php" id="changePasswordLink">পাসওয়ার্ড পরিবর্তন</a>
        <a href="../Html/Histrory.php?shop_owner_id=<?= urlencode($_SESSION['shop_owner_id']) ?>">লাইব্রেরি</a>

        <!-- Shop Active/Inactive Toggle Button -->
        <form method="post" style="margin: 16px 0; text-align:left;">
            <input type="hidden" name="active_status" value="<?php echo $isActive ? '0' : '1'; ?>">
            <button type="submit" name="toggle_active" class="active-toggle-btn"
                style="display:flex;align-items:center;background:<?php echo $isActive ? '#36e77a' : '#f66'; ?>;color:#fff;border:none;border-radius:8px;padding:8px 14px;font-size:1rem;cursor:pointer;">
                <?php if ($isActive): ?>
                    <img src="../Images/Closing-hour.png" alt="Shop Open" style="height:20px;margin-right:8px;">
                    দোকান চালু <span style="margin-left:8px;font-size:0.95em;opacity:0.7;">(বন্ধ করতে ক্লিক করুন)</span>
                <?php else: ?>
                    <img src="../Images/opening-hours.png" alt="Shop Closed" style="height:20px;margin-right:8px;">
                    দোকান বন্ধ <span style="margin-left:8px;font-size:0.95em;opacity:0.7;">(চালু করতে ক্লিক করুন)</span>
                <?php endif; ?>
            </button>
        </form>

        <a href="#" id="logoutLink">লগ আউট</a>
    </div>
</div>
<?php endif; ?>

  <div id="notificationSidebar" class="sidebar">
    <span id="closeNotification" class="close-btn">&times;</span>
    <h3>নোটিফিকেশন</h3>
    <div class="sidebar-content" style="max-height: 85%; overflow-y: auto;">
        <?php if ($warning_message): ?>
            <div style="background:#fff3cd;color:#856404;padding:12px 16px;border-radius:8px;margin-bottom:11px;border:1px solid #ffeeba;font-size:1.02em;">
                <b>⚠️ সতর্কতা / Warning!</b><br>
                <?= nl2br(htmlspecialchars($warning_message['reason'])) ?><br>
                <span style="font-size:0.93em;color:#b28b00;">তারিখ: <?= htmlspecialchars(date('d M Y, h:i A', strtotime($warning_message['warned_at']))) ?></span>
            </div>
        <?php endif; ?>

        <?php if (empty($shopOwnerNotifications)): ?>
            <p>নতুন কোনো নোটিফিকেশন নেই</p>
        <?php else: ?>
            <ul style="padding-left:0;">
                <?php foreach ($shopOwnerNotifications as $notif): ?>
                    <li style="
                        margin-bottom:14px; 
                        border-bottom:1px solid #eee; 
                        padding-bottom:10px; 
                        list-style:none;
                        <?= $notif['is_read']==0 ? 'font-weight:bold;background:#fffbe6;' : '' ?>
                    ">
                        <div>
                            <b>Order ID:</b> <?= htmlspecialchars($notif['order_id']) ?>
                        </div>
                        <?php if (!empty($notif['customer_name'])): ?>
                            <div>
                                <b>কাস্টমার:</b> <?= htmlspecialchars($notif['customer_name']) ?> 
                                (<?= htmlspecialchars($notif['customer_phone']) ?>)
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($notif['product_name'])): ?>
                            <div>
                                <b>পণ্য:</b> <?= htmlspecialchars($notif['product_name']) ?> × <?= (int)$notif['quantity'] ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($notif['price'])): ?>
                            <div>
                                <b>অর্ডার মূল্য:</b> <?= htmlspecialchars($notif['price'] * $notif['quantity']) ?> টাকা
                            </div>
                        <?php endif; ?>
                        <?php if ($notif['accepted_by']): ?>
                            <div style="color:green;">
                                <b>ডেলিভারি ম্যান:</b>
                                <a href="../Html/DeliveryMan_Home.php?id=<?= urlencode($notif['accepted_by']) ?>" style="color:green;text-decoration:underline;">
                                    <?= htmlspecialchars($notif['delivery_man_name']) ?>
                                </a>
                                (<?= htmlspecialchars($notif['delivery_man_phone']) ?>)
                            </div>
                            <div>
                                <b>Accepted At:</b> <?= htmlspecialchars($notif['accepted_at']) ?>
                            </div>
                        <?php else: ?>
                            <div style="color:#888;">এখনো কোনো ডেলিভারি ম্যান এক্সেপ্ট করেনি</div>
                        <?php endif; ?>
                        <div style="color:#888; font-size:0.9em;">
                            <?= date('d M, h:i A', strtotime($notif['created_at'])) ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
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
 <main>
    
  <section class="shop-banner-section">
    <div class="shop-banner">
      <img src="../uploads/<?php echo htmlspecialchars($shopImagePath); ?>" alt="Shop Background Image" />

      <div class="shop-title">
        <h1><?php echo htmlspecialchars($shopName); ?></h1>
      </div>

  <div class="owner-info">
  <div class="owner-image-wrapper" style="position: relative; display: inline-block;">
    <img src="../uploads/<?php echo htmlspecialchars($shopOwnerPic); ?>" alt="Shop Owner Image" style="width:100px;height:100px;border-radius:50%;object-fit:cover;">
    <?php if ($isActive): ?>
      <!-- Active: Show green dot -->
      <span class="active-indicator" style="
        position: absolute;
        bottom: 8px;
        right: 8px;
        width: 20px;
        height: 20px;
        background: #39e273;
        border: 3px solid #fff;
        border-radius: 50%;
        display: block;
        box-shadow: 0 0 8px #39e273, 0 0 2px #fff;">
        
      </span>
    <?php endif; ?>
  </div>
<style>.owner-image-wrapper {
  position: relative;
  display: inline-block;
}
.owner-image-wrapper img {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
}
.active-indicator {
  position: absolute;
  bottom: 8px;
  right: 8px;
  width: 22px;
  height: 22px;
  background: #39e273;
  border: 3px solid #fff;
  border-radius: 50%;
  display: block;
  box-shadow: 0 0 8px #39e273, 0 0 2px #fff;
}</style>
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
}
.active-toggle-btn {
  font-size: 1rem;
  font-weight: 600;
  transition: background 0.2s;
}
.active-toggle-btn:hover {
  filter: brightness(0.9);
}
</style> 

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
<?php if (!$isActive): ?>
  <?php if ($isOwner): ?>
    <div id="shopOwnerClosedModal" class="shop-closed-popup-overlay">
      <div class="shop-closed-popup-content">
        <span class="shop-closed-popup-close" onclick="document.getElementById('shopOwnerClosedModal').style.display='none'">&times;</span>
        <div style="color:#ce9100;font-weight:bold;font-size:1.15em;margin-bottom:10px;">
          ⚠️ আপনার দোকানটি বর্তমানে বন্ধ
        </div>
        <form method="post" style="margin:0;">
          <input type="hidden" name="active_status" value="1">
          <button type="submit" name="toggle_active" class="active-toggle-btn"
            style="background:#36e77a;color:#fff;border:none;border-radius:8px;padding:9px 23px;cursor:pointer;font-size:1.07em;">
            <img src="../Images/opening-hours.png" alt="Shop Open" style="height:19px;vertical-align:middle;margin-right:7px;">
            এখন চালু করুন
          </button>
        </form>
      </div>
    </div>
  <?php else: ?>
    <div id="shopClosedModal" class="shop-closed-popup-overlay">
      <div class="shop-closed-popup-content">
        <span class="shop-closed-popup-close" onclick="document.getElementById('shopClosedModal').style.display='none'">&times;</span>
        <div style="color:#d00;font-weight:bold;font-size:1.2em;">
          🚫 এই মুহূর্তে দোকানটি বন্ধ
        </div>
        <div style="margin:12px 0 0 0; color:#555;font-size:1em;">
          আমরা খুব শীঘ্রই আবার চালু করব, দয়া করে অপেক্ষা করুন।
        </div>
      </div>
    </div>
  <?php endif; ?>
  <style>
    .shop-closed-popup-overlay {
      position: fixed;
      z-index: 9999;
      left: 0; top: 0; width: 100vw; height: 100vh;
      background: rgba(0,0,0,0.3);
      display: flex; align-items: center; justify-content: center;
    }
    .shop-closed-popup-content {
      background: #fff;
      border-radius: 12px;
      padding: 32px 36px 24px 36px;
      box-shadow: 0 4px 28px #0002;
      text-align: center;
      min-width: 320px;
      position: relative;
      animation: popIn 0.27s cubic-bezier(.7,-0.3,.7,1.6);
    }
    .shop-closed-popup-close {
      position: absolute; top: 10px; right: 18px;
      font-size: 1.7em; color: #888;
      cursor: pointer;
      font-weight: bold;
      z-index: 1;
    }
    .shop-closed-popup-close:hover {
      color: #c00;
    }
    @keyframes popIn {
      0% {transform: scale(0.7); opacity:0;}
      100% {transform: scale(1); opacity:1;}
    }
    .active-toggle-btn:hover {
      filter: brightness(0.92);
    }
  </style>
  <script>
    // Auto-hide for customer modal (not for owner)
    setTimeout(function(){
      var modal = document.getElementById('shopClosedModal');
      if(modal) modal.style.display = 'none';
    }, 3000);
  </script>
<?php endif; ?>



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