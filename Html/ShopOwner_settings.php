<?php
session_start();
include '../PHP/db_connect.php';

if (isset($_GET['reset'])) {
    unset($_SESSION['encryption_verified']);
    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?')); // clean reload
    exit();
}
// --- Login check ---
if (!isset($_SESSION['shop_owner_email'])) {
    echo "<script>alert('আপনাকে প্রথমে লগইন করতে হবে!'); window.location.href='../Html/index.php';</script>";
    exit();
}

$email = $_SESSION['shop_owner_email'];
$isOwner = true; // Always true for this page

// --- Get shop_owner_id for notifications ---
$shopOwnerId = null;
$stmt = $conn->prepare("SELECT shop_owner_id FROM shop_owners WHERE shop_owner_email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($shopOwnerId);
$stmt->fetch();
$stmt->close();
if (!$shopOwnerId) {
    echo "<script>alert('ব্যবহারকারীর তথ্য পাওয়া যায়নি।'); window.location.href='../Html/index.php';</script>";
    exit();
}

// --- Fetch Shop Owner Data ---
$sql = "SELECT 
            shop_owner_name, shop_owner_phone, shop_owner_email, shop_owner_gender, 
            shop_owner_address, shop_description, shop_owner_password, 
            shop_owner_nid_path, shop_owner_image_path, shop_image_path, 
            address_street, address_area, address_city, address_postcode, 
            address_division, encrypt_pass_hash 
        FROM shop_owners 
        WHERE shop_owner_email = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Assign values
        $shopOwnerName   = $row['shop_owner_name'];
        $shopPhone       = $row['shop_owner_phone'];
        $shopEmail       = $row['shop_owner_email'];
        $shopGender      = $row['shop_owner_gender'];
        $shopAddress     = $row['shop_owner_address'];
        $shopDescription = $row['shop_description'];
        $shopPassword    = $row['shop_owner_password'];
        $nidPath         = $row['shop_owner_nid_path'];
        $shopOwnerPic    = $row['shop_owner_image_path'];
        $shopPic         = $row['shop_image_path'];
        $addressStreet   = $row['address_street'];
        $addressArea     = $row['address_area'];
        $addressCity     = $row['address_city'];
        $addressPostcode = $row['address_postcode'];
        $addressDivision = $row['address_division'];
        $encryptHash     = $row['encrypt_pass_hash'];
    } else {
        echo "<script>alert('ব্যবহারকারীর তথ্য পাওয়া যায়নি।'); window.location.href='../Html/index.php';</script>";
        exit();
    }

    $stmt->close();
} else {
    echo "<script>alert('ডাটাবেস ত্রুটি: তথ্য প্রস্তুত করতে ব্যর্থ।'); window.location.href='../Html/index.php';</script>";
    exit();
}

// --- Notification fetch (shop owner) ---
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

if (isset($_GET['reset']) && $_GET['reset'] === 'true') {
    unset($_SESSION['encryption_verified']);
    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?')); // clean reload
    exit();
}

$errorMsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['encryption_pass'])) {
    $inputPass = $_POST['encryption_pass'];

    if (empty($encryptHash)) {
        $newHash = password_hash($inputPass, PASSWORD_DEFAULT);

        $updateSql = "UPDATE shop_owners SET encrypt_pass_hash = ? WHERE shop_owner_email = ?";
        if ($stmt = $conn->prepare($updateSql)) {
            $stmt->bind_param("ss", $newHash, $email);
            if ($stmt->execute()) {
                $_SESSION['encryption_verified'] = true;
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $errorMsg = "পাসওয়ার্ড সংরক্ষণে সমস্যা হয়েছে!";
            }
            $stmt->close();
        } else {
            $errorMsg = "ডাটাবেস সমস্যার কারণে পাসওয়ার্ড সংরক্ষণ করা যায়নি!";
        }
    }
    else if (password_verify($inputPass, $encryptHash)) {
        $_SESSION['encryption_verified'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $errorMsg = "পাসওয়ার্ড ভুল হয়েছে!";
    }
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
    <link rel="stylesheet" href="../CSS/ShopOwner_setting.css">
    <style>
        .hidden { display: none; }
        .sidebar h3 { text-align: center; }
    </style>
</head>
<body>

    <!-- HEADER SECTION -->
    <header>
        <div class="logo">
            <img src="../Images/Logo.png" alt="Liberty Logo">
            <h2>সহজ যোগান</h2>
        </div>
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
   
<div id="overlay" class="overlay"></div>
<div id="userSidebar" class="sidebar">
    <span id="closeUserSidebar" class="close-btn">&times;</span>
    <h3>ব্যবহারকারী মেনু</h3>
    <div class="sidebar-content">
        <a href="../Html/ShopOwner_item.php" id="profileLink">নতুন সংগ্রহ</a>
        <a href="../Html/ShopOwner_Home.php" id="settingsLink">হোম</a>
        <a href="../Html/ShopOwner_settings_password.php" id="changePasswordLink">পাসওয়ার্ড পরিবর্তন</a>
        <a href="" id="logoutLink">লগ আউট</a>
    </div>
</div>
<script>
    document.getElementById('messengerBtn').addEventListener('click', function() {
        window.location.href = '../Html/Massenger-chat-shop.php';
    });
</script>
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
                                      <span style="color:green;text-decoration:underline;">
    <?= htmlspecialchars($notif['delivery_man_name']) ?>
</span>
                      
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


<div class="form-container">
    <form id="shopForm" action="#" method="POST" enctype="multipart/form-data" autocomplete="off">
        <h3>সেটিংস</h3>

        <?php if (!empty($nidPath)): ?>
            <div class="nid-image-box">
                <img id="nidImage" 
                    src="../uploads/<?php echo htmlspecialchars($nidPath); ?>" 
                    alt="NID/Birth Certificate Image" 
                    class="nid-image"
                    style="<?php echo isset($_SESSION['encryption_verified']) && $_SESSION['encryption_verified'] === true ? '' : 'filter: blur(10px); transition: 0.3s;'; ?>">
            </div>
        <?php else: ?>
            <p style="color: gray;">ছবি আপলোড করা হয়নি।</p>
        <?php endif; ?>

        <!-- Only show password form if not verified -->
        <?php if (!isset($_SESSION['encryption_verified']) || $_SESSION['encryption_verified'] !== true): ?>
            <div class="nid-password-form">
                <?php if (empty($encryptHash)): ?>
                    <p>🔐 প্রথমবার, আপনার NID ছবি দেখতে একটি পাসওয়ার্ড সেট করুন:</p>
                    <label for="encryption_pass">নতুন পাসওয়ার্ড দিন:</label><br>
                    <input type="password" name="encryption_pass" id="encryption_pass" required><br><br>
                    <button type="submit" name="set_password">সেভ করুন ও দেখান</button>
                <?php else: ?>
                    <p>🔐 আপনার NID ছবি দেখানোর জন্য পাসওয়ার্ড প্রদান করুন।</p>
                    <label for="encryption_pass">পাসওয়ার্ড দিন:</label><br>
                    <input type="password" name="encryption_pass" id="encryption_pass" required><br><br>
                    <button type="submit" name="verify_password">দেখান</button>
                <?php endif; ?>
                <?php if (!empty($errorMsg)): ?>
                    <p style="color:red;"><?php echo htmlspecialchars($errorMsg); ?></p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p class="nid-success">✅ আপনার পাসওয়ার্ড সফলভাবে যাচাই করা হয়েছে। এখন আপনি NID ছবি দেখতে পারবেন।</p>
            <div class="nid-link-box">
                <a href="?reset=1" class="nid-reset-link">🔁 ব্লার করুন</a>
            </div>
        <?php endif; ?>
<style>.nid-password-form {
    max-width: 350px;
    margin: 25px auto 20px auto;
    padding: 24px 28px 18px 28px;
    background: #f9f9fc;
    border-radius: 12px;
    box-shadow: 0 2px 12px #6b6b6b11;
    border: 1px solid #ececf6;
    text-align: center;
}

.nid-password-form p {
    margin-bottom: 14px;
    color: #253054;
    font-size: 1.07em;
    font-weight: 500;
}

.nid-password-form label {
    font-size: 0.97em;
    color: #26325d;
    margin-bottom: 5px;
    display: block;
}

.nid-password-form input[type="password"] {
    width: 80%;
    padding: 7px 10px;
    border: 1px solid #ccd2e7;
    border-radius: 7px;
    font-size: 1em;
    margin: 7px 0 16px 0;
    background: #f5f7fa;
    transition: border-color 0.18s;
}

.nid-password-form input[type="password"]:focus {
    border-color: #8ca4f5;
    outline: none;
    background: #f2f5ff;
}

.nid-password-form button[type="submit"] {
    background: #2e7dfa;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 8px 22px;
    font-size: 1.08em;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.16s;
}

.nid-password-form button[type="submit"]:hover {
    background: #245fb6;
}

.nid-success {
    color: #279e5d;
    background: #ebfff5;
    border: 1px solid #c3f0d5;
    padding: 10px 12px;
    border-radius: 7px;
    text-align: center;
    font-size: 1.07em;
    margin: 22px auto 8px auto;
    max-width: 320px;
}

.nid-link-box {
    text-align: center;
    margin: 10px 0 20px 0;
}

.nid-reset-link {
    color: #2e7dfa;
    background: #f5f8ff;
    padding: 6px 20px;
    border-radius: 7px;
    font-size: 1.01em;
    text-decoration: none;
    border: 1px solid #dbe2fb;
    transition: background 0.18s, color 0.18s;
}

.nid-reset-link:hover {
    color: #fff;
    background: #2e7dfa;
    border-color: #2e7dfa;
}</style>
        <!-- Shop Owner's Name -->
        <label for="shopOwnerName">দোকান মালিকের নাম:</label>
        <input type="text" id="shopOwnerName" name="shopOwnerName" value="<?= htmlspecialchars($shopOwnerName) ?>" disabled required />

        <!-- Phone Number -->
        <label for="shopPhone">দোকানের ফোন নম্বর:</label>
        <input type="text" id="shopPhone" name="shopPhone" value="<?= htmlspecialchars($shopPhone) ?>" disabled required />

        <!-- Optional Email -->
        <label for="shopEmail">দোকানের ইমেইল (অপশনাল):</label>
        <input type="email" id="shopEmail" name="shopEmail" value="<?= htmlspecialchars($shopEmail) ?>" disabled />

        <!-- Shop Address -->
        <label for="shopAddress">দোকানের ঠিকানা:</label>
        <input type="text" id="shopAddress" name="shopAddress" value="<?= htmlspecialchars($shopAddress) ?>" disabled required />

        <!-- Detailed Address Fields -->
        <label for="addressStreet">রাস্তা/বিল্ডিং:</label>
        <input type="text" id="addressStreet" name="addressStreet" value="<?= htmlspecialchars($addressStreet) ?>" disabled required />

        <label for="addressArea">এরিয়া / থানা:</label>
        <input type="text" id="addressArea" name="addressArea" value="<?= htmlspecialchars($addressArea) ?>" disabled required />

        <label for="addressCity">শহর / উপজেলা:</label>
        <input type="text" id="addressCity" name="addressCity" value="<?= htmlspecialchars($addressCity) ?>" disabled required />

        <label for="addressPostcode">পোস্টকোড:</label>
        <input type="text" id="addressPostcode" name="addressPostcode" value="<?= htmlspecialchars($addressPostcode) ?>" disabled required />

        <label for="addressDivision">বিভাগ:</label>
        <input type="text" id="addressDivision" name="addressDivision" value="<?= htmlspecialchars($addressDivision) ?>" disabled required />

        <!-- Gender -->
        <label for="shopOwnerGender">লিঙ্গ:</label>
        <select id="shopOwnerGender" name="shopOwnerGender" disabled required>
            <option value="male" <?= ($shopGender === 'male') ? 'selected' : '' ?>>পুরুষ</option>
            <option value="female" <?= ($shopGender === 'female') ? 'selected' : '' ?>>মহিলা</option>
            <option value="other" <?= ($shopGender === 'other') ? 'selected' : '' ?>>অন্যান্য</option>
        </select>

        <!-- Edit Button -->
        <button type="button" id="editButton" class="edit-button" onclick="window.location.href='../Html/ShopOwner_settings_Edit.php'">
            এডিট করুন
        </button>
    </form>
</div>

<script src="../java_script/Shopowner_Setting.js"></script>

</body>
</html>