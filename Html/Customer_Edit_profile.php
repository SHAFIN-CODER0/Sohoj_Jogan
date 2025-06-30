<?php
session_start();
include '../PHP/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['customer_email'])) {
    echo "<script>alert('You must log in first!'); window.location.href='../Html/index.php';</script>";
    exit();
}

// Fetch user data based on session email
$email = $_SESSION['customer_email'];
$sql = "SELECT customer_id, customer_name, customer_phone, customer_gender, customer_address, customer_email, profile_pic FROM customers WHERE customer_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $customerId = $row['customer_id'];
    $customerName = $row['customer_name'];
    $customerPhone = $row['customer_phone'];
    $customerGender = $row['customer_gender'];
    $customerAddress = $row['customer_address'];
    $customerEmail = $row['customer_email'];
    $profilePic = $row['profile_pic'];
} else {
    echo "<script>alert('User data not found!'); window.location.href='../Html/index.php';</script>";
    exit();
}

$stmt->close();

// Handle form submission to update data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newName = $_POST['customerName'];
    $newPhone = $_POST['customerPhone'];
    $newAddress = $_POST['customerAddress'];
    $newEmail = $_POST['customerEmail'];
    $newGender = $_POST['customerGender'];

    // Handle profile picture upload if provided
    if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] == 0) {
        $fileName = $_FILES['profilePic']['name'];
        $fileTmpName = $_FILES['profilePic']['tmp_name'];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = $customerId . '.' . $fileExtension;
        $uploadDir = '../uploads/';
        move_uploaded_file($fileTmpName, $uploadDir . $newFileName);
        $profilePic = $newFileName;
    }

    // Update data in database
    $updateSql = "UPDATE customers SET customer_name = ?, customer_phone = ?, customer_address = ?, customer_email = ?, customer_gender = ?, profile_pic = ? WHERE customer_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ssssssi", $newName, $newPhone, $newAddress, $newEmail, $newGender, $profilePic, $customerId);

    if ($updateStmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location.href='../Html/Customer_Home.php';</script>";
        exit();
    } else {
        echo "<script>alert('Failed to update profile. Please try again later.');</script>";
    }

    $updateStmt->close();
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
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
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
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../Css/Customer_Edit_profile.css">
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
</header>
<script>
document.getElementById('messengerBtn').onclick = function() {
    window.location.href = '../Html/Massenger-chat.php';
};
</script>

<!-- PROFILE SECTION -->
<div class="profile-container">
    <h2>আপনার প্রোফাইল</h2>
    <form id="profileForm" method="post" enctype="multipart/form-data">
        <div class="profile-pic-container">
            <h3>প্রোফাইল ছবি</h3>
            <p id="uploadHint">আপনার প্রোফাইল ছবির জন্য একটি ছবি আপলোড করুন</p>
            <div class="profile-pic">
                <img id="profileImage" src="../uploads/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture">
                <input type="file" id="uploadImage" name="profilePic" accept="image/*" hidden>
                <button type="button" id="changePic">
                    <img src="../Images/camera.png" alt="Change Profile Picture">
                </button>
            </div>
        </div>

        <label for="customerName">নাম:</label>
        <input type="text" id="customerName" name="customerName" value="<?php echo htmlspecialchars($customerName); ?>">

        <label for="customerPhone">ফোন নম্বর:</label>
        <input type="text" id="customerPhone" name="customerPhone" value="<?php echo htmlspecialchars($customerPhone); ?>">

        <label for="customerAddress">ঠিকানা:</label>
        <input type="text" id="customerAddress" name="customerAddress" value="<?php echo htmlspecialchars($customerAddress); ?>">

        <label for="customerEmail">ইমেইল:</label>
        <input type="email" id="customerEmail" name="customerEmail" value="<?php echo htmlspecialchars($customerEmail); ?>">

        <label for="customerGender">লিঙ্গ:</label>
        <select id="customerGender" name="customerGender">
            <option value="male" <?php echo $customerGender == 'male' ? 'selected' : ''; ?>>পুরুষ</option>
            <option value="female" <?php echo $customerGender == 'female' ? 'selected' : ''; ?>>মহিলা</option>
            <option value="other" <?php echo $customerGender == 'other' ? 'selected' : ''; ?>>অন্যান্য</option>
        </select>
        <br><br>
        <button type="submit" id="savedata">সেভ করুন</button>
        <button type="button" id="canceldata" onclick="window.location.href='Customer_Profile.php';">বাতিল করুন</button>
    </form>
</div>

<!-- OVERLAY -->
<div id="overlay" class="overlay"></div>

<!-- User Sidebar -->
<div id="userSidebar" class="sidebar">
    <span id="closeUserSidebar" class="close-btn">&times;</span>
    <h3>ব্যবহারকারী মেনু</h3>
    <div class="sidebar-content">
        <a href="../Html/Customer_Home.php" id="profileLink">হোম</a>
        <a href="../Html/Customer_settings.php" id="settingsLink">সেটিংস</a>
        <a href="#" id="logoutLink">লগ আউট</a>
    </div>
</div>

<div id="notificationSidebar" class="sidebar">
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
}
</style>

<script>
    // Profile picture handler
    document.getElementById('changePic').addEventListener('click', function () {
        document.getElementById('uploadImage').click();
    });
    document.getElementById('uploadImage').addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('profileImage').src = e.target.result;
                document.getElementById('uploadHint').style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });
    // No AJAX, let form submit normally!
</script>
<script src="../java_script/Customer_Edit_profile.js"></script>
</body>
</html>