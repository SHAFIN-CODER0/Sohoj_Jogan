<?php
session_start();
include '../PHP/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['customer_email'])) {
    echo "<script>alert('You must log in first!'); window.location.href='../Html/index.php';</script>";
    exit();
}

// Get customer_id from session, or fetch it using email
if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];
} else {
    $email = $_SESSION['customer_email'];
    $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE customer_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($customer_id);
    if ($stmt->fetch()) {
        $_SESSION['customer_id'] = $customer_id;
    } else {
        echo "<script>alert('User data not found!'); window.location.href='../Html/index.php';</script>";
        exit();
    }
    $stmt->close();
}

$success = null;
$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $repeatPassword = $_POST['repeatPassword'];

    // Check if the new passwords match
    if ($newPassword !== $repeatPassword) {
        $error = "নতুন পাসওয়ার্ড এবং পুনরায় পাসওয়ার্ড মিলছে না।";
    } else {
        // Validate current password
        $stmt = $conn->prepare("SELECT customer_password FROM customers WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $stmt->bind_result($dbPassword);
        $stmt->fetch();
        $stmt->close();

        if (!$dbPassword || !password_verify($currentPassword, $dbPassword)) {
            $error = "বর্তমান পাসওয়ার্ড সঠিক নয়।";
        } else {
            // Hash the new password and update
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE customers SET customer_password = ? WHERE customer_id = ?");
            $update_stmt->bind_param("si", $newPasswordHash, $customer_id);
            if ($update_stmt->execute()) {
                $success = "পাসওয়ার্ড সফলভাবে পরিবর্তন হয়েছে।";
                // Optionally, redirect
                echo "<script>alert('পাসওয়ার্ড সফলভাবে পরিবর্তন হয়েছে।'); window.location.href='../Html/Customer_Home.php';</script>";
                exit();
            } else {
                $error = "পাসওয়ার্ড পরিবর্তনে সমস্যা হয়েছে।";
            }
            $update_stmt->close();
        }
    }
}

// Notifications Fetch
$customerNotifications = [];
if (isset($customer_id)) {
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
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $customerNotifications[] = $row;
    }
    $stmt->close();
}

// Fetch warning for this customer (if any)
$warning_message = null;
if (isset($customer_id)) {
    $warnSql = "SELECT reason, warned_at FROM warned_users WHERE user_type='customer' AND user_id=?";
    $warnStmt = $conn->prepare($warnSql);
    $warnStmt->bind_param("i", $customer_id);
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
    <link rel="stylesheet" href="../CSS/Customer_setting.css">
</head>
<body>

<!-- HEADER SECTION -->
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
    <!-- Top Icons -->
    <div class="icons">
        <button id="userIcon" class="icon-btn">
            <img src="../Images/Sample_User_Icon.png" alt="User" class="icon-img">
        </button>
        <button id="notificationIcon" class="icon-btn">
            <img src="../Images/notification.png" alt="Notifications" class="icon-img">
        </button>
        <button id="messengerBtn" class="icon-btn">
            <img src="../Images/messenger-icon.png" alt="Messenger" class="icon-img">
        </button>
    </div>
</header>

<!-- OVERLAY (for background when sidebar is open) -->
<div id="overlay" class="overlay"></div>
<!-- User Sidebar -->
<div id="userSidebar" class="sidebar">
    <span id="closeUserSidebar" class="close-btn">&times;</span>
    <h3>ব্যবহারকারী মেনু</h3>
    <div class="sidebar-content">
        <a href="../Html/Customer_profile.php" id="profileLink">প্রোফাইল</a>
        <a href="../Html/Customer_Home.php" id="settingsLink">হোম</a>
        <a href="#" id="logoutLink">লগ আউট</a>
    </div>
</div>

<div id="notificationSidebar" class="sidebar">
    <span id="closeNotification" class="sidebar-close-icon">&times;</span>
    <h3>নোটিফিকেশন</h3>
    <div class="sidebar-content" style="max-height:85vh;overflow-y:auto;">
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
.sidebar-content::-webkit-scrollbar {
    width: 8px;
    max-height: 100%;
}
.sidebar-content::-webkit-scrollbar-thumb {
    background:rgb(207, 202, 202);
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
}
.error-message {
    color: #d00;
    background: #fff3f3;
    padding: 10px 20px;
    margin: 10px 0;
    border-radius: 7px;
    border: 1px solid #fbb;
    text-align: center;
}
.success-message {
    color: #1a7e2b;
    background: #e6ffe6;
    padding: 10px 20px;
    margin: 10px 0;
    border-radius: 7px;
    border: 1px solid #bdf5d1;
    text-align: center;
}
</style>

<!-- Messenger Sidebar -->
<div id="messengerSidebar" class="sidebar">
    <span id="closeMessenger" class="close-btn">&times;</span>
    <h3>মেসেজ</h3>
    <div class="sidebar-content">
        <p>কোনো নতুন মেসেজ নেই</p>
    </div>
</div>

<script src="../java_script/Cusomer_setting.js"></script>

<!-- PASSWORD CHANGE FORM SECTION -->
<div class="form-container">
    <h3>পাসওয়ার্ড পরিবর্তন করুন</h3>
    <!-- Display error or success messages -->
    <?php if ($error): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>
    <!-- Password Change Form -->
    <form action="" method="POST">
        <label for="currentPassword">বর্তমান পাসওয়ার্ড</label>
        <input type="password" id="currentPassword" name="currentPassword" required>
        <label for="newPassword">নতুন পাসওয়ার্ড</label>
        <input type="password" id="newPassword" name="newPassword" required>
        <label for="repeatPassword">নতুন পাসওয়ার্ড পুনরায় লিখুন</label>
        <input type="password" id="repeatPassword" name="repeatPassword" required>
        <button type="submit" class="save-button">পাসওয়ার্ড পরিবর্তন করুন</button>
    </form>
</div>

</body>
</html>