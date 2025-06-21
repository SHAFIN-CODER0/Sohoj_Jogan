<?php
session_start();
include '../PHP/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['delivery_man_email'])) {
    echo "<script>alert('প্রথমে লগইন করুন।'); window.location.href='../Html/index.php';</script>";
    exit();
}

// Always fetch delivery_man_id from session or DB
if (!isset($_SESSION['delivery_man_id'])) {
    $sql = "SELECT delivery_man_id FROM delivery_men WHERE delivery_man_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_SESSION['delivery_man_email']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION['delivery_man_id'] = $row['delivery_man_id'];
    }
    $stmt->close();
}
// ... Password change logic unchanged ...

$deliverymanId = $_SESSION['delivery_man_id']; // always set!
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $delivery_man_id = $_SESSION['delivery_man_id'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $repeatPassword = $_POST['repeatPassword'];

    // Check if new passwords match
    if ($newPassword !== $repeatPassword) {
        echo "<script>alert('নতুন পাসওয়ার্ড এবং পুনরায় পাসওয়ার্ড মিলছে না।'); window.history.back();</script>";
        exit();
    } else {
        // Validate current password
        $stmt = $conn->prepare("SELECT delivery_man_password FROM delivery_men WHERE delivery_man_id = ?");
        $stmt->bind_param("i", $delivery_man_id);
        $stmt->execute();
        $stmt->bind_result($dbPassword);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($currentPassword, $dbPassword)) {
            echo "<script>alert('বর্তমান পাসওয়ার্ড সঠিক নয়।'); window.history.back();</script>";
            exit();
        } else {
            // Hash and update the new password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE delivery_men SET delivery_man_password = ? WHERE delivery_man_id = ?");
            $update_stmt->bind_param("si", $newPasswordHash, $delivery_man_id);
            if ($update_stmt->execute()) {
                echo "<script>alert('পাসওয়ার্ড সফলভাবে পরিবর্তন হয়েছে।'); window.location.href='../Html/DeliveryMan_Home.php';</script>";
                exit();
            } else {
                echo "<script>alert('পাসওয়ার্ড পরিবর্তনে সমস্যা হয়েছে।'); window.history.back();</script>";
            }
            $update_stmt->close();
        }
    }
}

// --- Fetch notifications for sidebar ---
$notifications = [];
$unread = 0;
if (!empty($deliverymanId)) {
    $notif_sql = "SELECT n.*, o.customer_name, o.customer_phone, o.customer_address, o.customer_comment,
        so.shop_name, so.shop_owner_address, so.shop_owner_phone, pr.price, o.quantity, o.delivery_charge,
        p.payment_method, p.bkash_txid
    FROM notifications n
    LEFT JOIN orders o ON n.order_id = o.order_id
    LEFT JOIN shop_owners so ON o.shop_owner_id = so.shop_owner_id
    LEFT JOIN payments p ON o.order_id = p.order_id
    LEFT JOIN products pr ON o.product_id = pr.product_id
    WHERE n.user_id = ? AND n.user_type = 'delivery_man'
    ORDER BY n.created_at DESC";
    $notif_stmt = $conn->prepare($notif_sql);
    $notif_stmt->bind_param("i", $deliverymanId);
    $notif_stmt->execute();
    $notifs_result = $notif_stmt->get_result();
    while ($row = $notifs_result->fetch_assoc()) {
        $notifications[] = $row;
        if ($row['is_read'] == 0) $unread++;
    }
    $notif_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../CSS/Deliveryman_ChangePassword.css"> <!-- Ensure CSS Path is Correct -->
"> <!-- Ensure CSS Path is Correct -->
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
        window.location.href = '../Html/Deliveryman_Home.php';
    });
</script>

           <!-- Top Icons -->
        <div class="icons">
            <button id="userIcon">
                <img src="../Images/Sample_User_Icon.png" alt="User">
            </button>
            <button id="notificationIcon" style="position:relative;">
                <img src="../Images/notification.png" alt="Notifications">
                <?php
                $unread = 0;
                foreach($notifications as $n) {
                    if ($n['is_read']==0) $unread++;
                }
                if ($unread > 0) echo "<span class=\"notif-badge\">$unread</span>";
                ?>
            </button>
        </div>
    </header>
<!-- OVERLAY (for background when sidebar is open) -->
<div id="overlay" class="overlay"></div>
<!-- User Sidebar -->
<div id="userSidebar" class="sidebar">
    <span id="closeUserSidebar" class="close-btn">&times;</span>
    <h3>ব্যবহারকারী মেনু</h3> <!-- Changed 'User Menu' to 'ব্যবহারকারী মেনু' -->
    <div class="sidebar-content">
       <a href="../Html/Delivaryman_setting.php">সেটিংস</a>
     <a href="../Html/DeliveryMan_Home.php" id="settingsLink">হোম</a> <!-- 'Settings' in Bangla -->
           <a href="../Html/Deliveryman_MyDeliveries.php">আমার ডেলিভারি</a>

     <a href="#" id="logoutLink">লগ আউট</a>
    </div>
</div>

 <div id="notificationSidebar" class="sidebar">
        <span id="closeNotification" class="close-btn">&times;</span>
        <h3>নোটিফিকেশন</h3>



        <!-- Accepted Orders / History Tab -->
        <div class="tab-content active" id="accepted-orders">
            <?php if (!empty($notifications)): ?>
                
                <?php foreach ($notifications as $row): ?>
                <div class="notification-item<?= $row['is_read']==0 ? ' unread' : '' ?>">
                    <p><?= htmlspecialchars($row['message']) ?></p>
                    <?php if(!empty($row['shop_name'])): ?>
                        <small>দোকান: <b><?= htmlspecialchars($row['shop_name']) ?></b></small><br>
                    <?php endif; ?>
                    <?php if(!empty($row['shop_owner_address'])): ?>
                        <small>দোকান মালিকের ঠিকানা: <?= htmlspecialchars($row['shop_owner_address']) ?></small><br>
                    <?php endif; ?>
                    <?php if(!empty($row['shop_owner_phone'])): ?>
                        <small>দোকান মালিকের ফোন: <?= htmlspecialchars($row['shop_owner_phone']) ?></small><br>
                    <?php endif; ?>
                    <?php if(isset($row['price']) && isset($row['quantity'])): ?>
                        <small>অর্ডার মূল্য: <?= htmlspecialchars($row['price'] * $row['quantity'] + $row['delivery_charge']) ?> টাকা</small><br>
                    <?php endif; ?>
                    <?php if(!empty($row['customer_name'])): ?>
                        <small>কাস্টমার: <?= htmlspecialchars($row['customer_name']) ?> (<?= htmlspecialchars($row['customer_phone']) ?>)</small><br>
                        <small>ঠিকানা: <?= htmlspecialchars($row['customer_address']) ?></small><br>
                    <?php endif; ?>
                    <?php if(!empty($row['customer_comment'])): ?>
                        <small>কমেন্ট: <?= htmlspecialchars($row['customer_comment']) ?></small><br>
                    <?php endif; ?>
                    <?php if(!empty($row['payment_method'])): ?>
                        <small>পেমেন্ট: 
                            <?= $row['payment_method']=='bkash' ? 'bKash (TxID: '.htmlspecialchars($row['bkash_txid']).')' : 'Cash On Delivery' ?>
                        </small><br>
                    <?php endif; ?>
                    <small><?= date('d M, H:i', strtotime($row['created_at'])) ?></small><br>
                    <?php if (!empty($row['accepted_by'])): ?>
                        <div class="accept-info">
                            <b>Accepted By:</b>
                            <?= htmlspecialchars($row['accepted_by_name']) ?? '' ?> (<?= htmlspecialchars($row['accepted_by_phone']) ?? '' ?>)<br>
                            <b>Time:</b> <?= htmlspecialchars($row['accepted_at']) ?? '' ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>নতুন কোনো নোটিফিকেশন নেই</p>
            <?php endif; ?>
        </div>
    </div>

    <style>/* Center the "Mark all as read" button above the tab content */
#notificationSidebar form[method="post"] {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 16px;
}

/* Style for the mark-all-read button */
.accept-btn {
    background: #1c7c54;
    color: #fff;
    padding: 7px 30px;
    font-size: 1em;
    font-weight: 600;
    border: none;
    border-radius: 4px;
    box-shadow: 0 1px 6px rgba(30, 136, 229, 0.06);
    cursor: pointer;
    letter-spacing: 0.03em;
    transition: background 0.18s;
}

.accept-btn:hover,
.accept-btn:focus {
    background: #14593e;
}

/* Notification tab-content scrollable area */
.tab-content {
    max-height: 75vh;
    overflow-y: auto;
    background: #f8fafc;
    padding: 14px 14px 0 14px;
    border-radius: 0 0 8px 8px;
    scrollbar-gutter: stable both-edges;
    display: none;
    m
}
.tab-content.active {
    display: block;
}

/* Custom Scrollbar for Chrome, Edge, Safari */
.tab-content::-webkit-scrollbar {
    width: 8px;
}
.tab-content::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 5px;
}
.tab-content::-webkit-scrollbar-track {
    background: #f8fafc;
}

/* Custom Scrollbar for Firefox */
.tab-content {
    scrollbar-width: thin;
    scrollbar-color: #d1d5db #f8fafc;
}

/* Notification item styling */
.notification-item {
    margin-bottom: 14px;
    border-bottom: 1px solid #e0e6ed;
    padding: 14px 14px 12px 18px;
    background: linear-gradient(100deg, #f5fafd 80%, #f0f4f8 100%);
    border-radius: 8px;
    box-shadow: 0 1px 6px rgba(30, 136, 229, 0.06);
    font-size: 15px;
    transition: background 0.16s, box-shadow 0.16s;
    position: relative;
}
.notification-item:last-child {
    border-bottom: none;
}
.notification-item:hover {
    background: #eef7fa;
    box-shadow: 0 2px 10px rgba(30, 136, 229, 0.13);
}

/* Notification item unread highlight */
.notification-item.unread {
    background: linear-gradient(100deg, #fffbe7 85%, #ffefd9 100%);
    border-left: 5px solid #ff9800;
}

/* Main message style */
.notification-item > p {
    font-weight: 600;
    color: #222b45;
    margin: 0 0 7px 0;
    letter-spacing: 0.01em;
}

.notification-item small {
    color: #495060;
    font-size: 14px;
    line-height: 1.6;
}
.notification-item b {
    color: #008060;
    font-weight: 600;
}

/* Accepted order info */
.accept-info {
    color: #008060;
    font-size: 14px;
    margin-top: 7px;
    padding-left: 10px;
    border-left: 2.5px solid #008060;
    background: #ecfdf5;
    border-radius: 3px;
    display: inline-block;
    margin-bottom: 2px;
}

/* Responsive for mobile */
@media (max-width: 500px) {
    .tab-content {
        padding-left: 4px;
        padding-right: 4px;
    }
    .notification-item {
        padding: 10px 6px 10px 10px;
    }
    #notificationSidebar form[method="post"] {
        margin-bottom: 6px;
    }
}</style>


<div class="form-container">

        <h3>পাসওয়ার্ড পরিবর্তন করুন</h3>

       <!-- Display error or success messages -->
       <?php if (isset($error)) { ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php } ?>
        <!-- Password Change Form -->
        <form action="" method="POST">
            <!-- Current Password -->
            <label for="currentPassword">বর্তমান পাসওয়ার্ড</label>
            <input type="password" id="currentPassword" name="currentPassword" required>

            <!-- New Password -->
            <label for="newPassword">নতুন পাসওয়ার্ড</label>
            <input type="password" id="newPassword" name="newPassword" required>

            <!-- Repeat New Password -->
            <label for="repeatPassword">নতুন পাসওয়ার্ড পুনরায় লিখুন</label>
            <input type="password" id="repeatPassword" name="repeatPassword" required>

            <!-- Submit Button -->
            <button type="submit" class="save-button">পাসওয়ার্ড পরিবর্তন করুন</button>
        </form>
    </div>

<script src="../java_script/ShopOwner_settings_password.js"></script> <!-- Link to JS -->
   
    

<!-- PASSWORD CHANGE FORM SECTION -->

  

</body>
</html>
