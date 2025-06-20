<?php
session_start();
include '../PHP/db_connect.php'; // Database connection

$isDeliveryman = false;
$deliverymanId = null;

// ডেলিভারিম্যান নিজে লগইন করলে (session আছে, URL-এ id নাই)
if (isset($_SESSION['delivery_man_email']) && !isset($_GET['id'])) {
    $email = $_SESSION['delivery_man_email'];
    $sql = "SELECT delivery_man_id, delivery_man_name, delivery_man_image_path FROM delivery_men WHERE delivery_man_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $deliverymanId = $row['delivery_man_id'];
        $deliverymanName = $row['delivery_man_name'];
        $deliverymanPic = $row['delivery_man_image_path'];
        $isDeliveryman = true;
    } else {
        echo "<script>
            alert('Deliveryman data not found!');
            window.location.href='../Html/index.html';
        </script>";
        exit();
    }
    $stmt->close();
}
// কাস্টমার বা ভিজিটর URL দিয়ে এলে (?id=)
else if (isset($_GET['id'])) {
    $deliverymanId = intval($_GET['id']);
    $sql = "SELECT delivery_man_id, delivery_man_name, delivery_man_image_path FROM delivery_men WHERE delivery_man_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $deliverymanId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $deliverymanName = $row['delivery_man_name'];
        $deliverymanPic = $row['delivery_man_image_path'];
    } else {
        echo "<script>
            alert('Deliveryman not found!');
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
$conn->close();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../CSS/Delivaryman_Home.css?v=1">
</head>
<body>

    <!-- HEADER SECTION -->
    <header>
        <div class="logo">
            <img src="../Images/Logo.png" alt="Liberty Logo">
            <h2>সহজ যোগান</h2>
        </div>
        <!-- Top Icons: শুধু ডেলিভারিম্যান নিজে দেখবে -->
        <?php if ($isDeliveryman): ?>
        <div class="icons">
            <button id="userIcon">
                <img src="../Images/Sample_User_Icon.png" alt="User">
            </button>
            <button id="notificationIcon">
                <img src="../Images/notification.png" alt="Notifications">
            </button>
        </div>
        <?php endif; ?>
    </header>

    <!-- OVERLAY & SIDEBAR: শুধু ডেলিভারিম্যান নিজে দেখবে -->
    <?php if ($isDeliveryman): ?>
    <div id="overlay" class="overlay"></div>
    <div id="userSidebar" class="sidebar">
        <span id="closeUserSidebar" class="close-btn">&times;</span>
        <h3>ডেলিভারিম্যান মেনু</h3>
        <div class="sidebar-content">
            <a href="../Html/Delivaryman_setting.php">সেটিংস</a>
            <a href="../Html/Deliveryman_ChangePassword.php">পাসওয়ার্ড পরিবর্তন</a>
            <a href="#" id="logoutLink">লগ আউট</a>
        </div>
    </div>
    <div id="notificationSidebar" class="sidebar">
        <span id="closeNotification" class="close-btn">&times;</span>
        <h3>নোটিফিকেশন</h3>
        <div class="sidebar-content">
            <p>নতুন কোনো নোটিফিকেশন নেই</p>
        </div>
    </div>
    <?php endif; ?>

    <main>
      <section class="deliveryman-banner-section">
        <div class="deliveryman-banner">
          <!-- Background Image -->
          <img src="../Images/deliveryman.jpeg" alt="Deliveryman Background" class="banner-bg-img" />

          <!-- Deliveryman Info Box -->
          <div class="deliveryman-info-box">
            <img 
              src="../uploads/<?php echo htmlspecialchars($deliverymanPic); ?>" 
              alt="Deliveryman Image" 
              class="deliveryman-img" 
            />
            <div class="deliveryman-name">
              <h2><?php echo htmlspecialchars($deliverymanName); ?></h2>
            </div>
          </div>

          <!-- Top Right: কাস্টমার/ভিজিটর দেখলে রিপোর্ট বাটন -->
          <?php if (!$isDeliveryman): ?>
          <button class="report-btn" type="button" onclick="window.location.href='../Html/report.html'">
            রিপোর্ট করুন
          </button>
          <?php endif; ?>

          <!-- Bottom Right: Review Button (সবাই দেখতে পাবে)-->
          <button class="review-toggle-btn" type="button">
            রিভিউ দেখুন
          </button>
        </div>
      </section>
    </main>

    <section class="review-section">
        <h2>রিভিউ</h2>
        <div class="review-list">
            <!-- এখানে ডাটাবেজ থেকে রিভিউ ফেচ করে লুপ চালাতে পারো -->
            <div class="review-item">
                <div class="review-author">জন ডো</div>
                <div class="review-text">অসাধারণ সেবা! পণ্যটি দ্রুত পেয়েছি এবং গুণগত মান খুবই ভালো।</div>
                <div class="review-rating">⭐⭐⭐⭐⭐</div>
            </div>
            <div class="review-item">
                <div class="review-author">মি. শাহিন</div>
                <div class="review-text">ভাল পণ্য, কিন্তু ডেলিভারি একটু দেরি হয়েছিল।</div>
                <div class="review-rating">⭐⭐⭐⭐</div>
            </div>
        </div>
    </section>

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

    <script src="../java_script/DeliveryMan_home.js"></script>
</body>
</html>