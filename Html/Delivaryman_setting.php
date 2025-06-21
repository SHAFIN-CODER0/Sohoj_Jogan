<?php
session_start();
include '../PHP/db_connect.php';

// --- Reset Password Session ---
if (isset($_GET['reset']) && $_GET['reset'] === 'true') {
    unset($_SESSION['encryption_verified']);
    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?')); // clean reload
    exit();
}

// --- Login Check ---
if (!isset($_SESSION['delivery_man_email'])) {
    echo "<script>alert('আপনাকে প্রথমে লগইন করতে হবে!'); window.location.href='../Html/index.php';</script>";
    exit();
}

$email = $_SESSION['delivery_man_email'];

// --- Fetch Delivery Man Data ---
$sql = "SELECT 
            delivery_man_id, delivery_man_name, delivery_man_phone, delivery_man_email, 
            delivery_man_gender, delivery_man_address, 
            delivery_man_password, delivery_man_nid_path, 
            delivery_man_image_path, encrypt_pass_hash
        FROM delivery_men 
        WHERE delivery_man_email = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Assign values
        $deliverymanId   = $row['delivery_man_id'];
        $deliveryName    = $row['delivery_man_name'];
        $deliveryPhone   = $row['delivery_man_phone'];
        $deliveryEmail   = $row['delivery_man_email'];
        $deliveryGender  = $row['delivery_man_gender'];
        $deliveryAddress = $row['delivery_man_address'];
        $deliveryPassword= $row['delivery_man_password']; // Login password
        $nidPath         = $row['delivery_man_nid_path'];
        $profilePic      = $row['delivery_man_image_path'];
        $encryptHash     = $row['encrypt_pass_hash'];     // Encryption password
    } else {
        echo "<script>alert('ডেলিভারি ম্যান এর তথ্য পাওয়া যায়নি।'); window.location.href='../Html/index.php';</script>";
        exit();
    }

    $stmt->close();
} else {
    echo "<script>alert('ডাটাবেস ত্রুটি: তথ্য প্রস্তুত করতে ব্যর্থ।'); window.location.href='../Html/index.php';</script>";
    exit();
}


// --- Fetch notifications for sidebar ---
$notifications = [];
if (!empty($deliverymanId)) {
    $notif_sql = "
        SELECT n.*, o.customer_name, o.customer_phone, o.customer_address, o.customer_comment,
               so.shop_name, so.shop_owner_address, so.shop_owner_phone, pr.price, o.quantity, o.delivery_charge,
               p.payment_method, p.bkash_txid
        FROM notifications n
        LEFT JOIN orders o ON n.order_id = o.order_id
        LEFT JOIN shop_owners so ON o.shop_owner_id = so.shop_owner_id
        LEFT JOIN payments p ON o.order_id = p.order_id
        LEFT JOIN products pr ON o.product_id = pr.product_id
        WHERE n.user_id = ? AND n.user_type = 'delivery_man'
        ORDER BY n.created_at DESC
       
    ";
    $notif_stmt = $conn->prepare($notif_sql);
    $notif_stmt->bind_param("i", $deliverymanId);
    $notif_stmt->execute();
    $notifs_result = $notif_stmt->get_result();
    while ($row = $notifs_result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $notif_stmt->close();
}
// --- Handle Password Encryption Input ---
$errorMsg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['encryption_pass'])) {
    $inputPass = $_POST['encryption_pass'];

    if (empty($encryptHash)) {
        // First-time: Store new hashed password
        $newHash = password_hash($inputPass, PASSWORD_DEFAULT);

        $updateSql = "UPDATE delivery_men SET encrypt_pass_hash = ? WHERE delivery_man_email = ?";
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

    } else if (password_verify($inputPass, $encryptHash)) {
        // Correct password
        $_SESSION['encryption_verified'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $errorMsg = "পাসওয়ার্ড ভুল হয়েছে!";
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../CSS/Deliveryman_setting.css?v=1">
    <style>
    .notif-badge {
        background: #ff5722;
        color: #fff;
        border-radius: 50%;
        padding: 2px 7px;
        font-size: 12px;
        position: absolute;
        top: -4px; right: -4px;
    }
   
    </style>
</head>
<body>
    <!-- HEADER SECTION -->
    <header>
        <div class="logo">
            <img src="../Images/Logo.png" alt="Liberty Logo">
            <h2>সহজ যোগান</h2>
        </div>

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
        <h3>ব্যবহারকারী মেনু</h3>
        <div class="sidebar-content">
            <a href="../Html/DeliveryMan_Home.php" id="settingsLink">হোম</a>
            <a href="../Html/Deliveryman_MyDeliveries.php">আমার ডেলিভারি</a>
            <a href="../Html/Deliveryman_ChangePassword.php">পাসওয়ার্ড পরিবর্তন</a>
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

    <!-- SETTINGS SECTION -->
    <div class="form-container">
        <form id="deliveryForm" action="#" method="POST" enctype="multipart/form-data">
            <h3>সেটিংস</h3>
            <!-- Profile & NID Upload Side by Side -->
            <div class="profile-nid-row">
                <div class="profile-upload">
                    <label class="profile-label">প্রোফাইল ছবি:</label>
                    <div class="profile-image-box" id="profileImageBox">
                        <img src="<?= !empty($profilePic) ? htmlspecialchars($profilePic) : '../Images/Sample_User_Icon.png' ?>" alt="Profile Preview" id="profilePreview" class="profile-image">
                    </div>
                </div>
                <div class="nid-upload">
                    <label class="nid-label">জাতীয় পরিচয়পত্র (NID)/ জন্ম সনদ:</label>
                    <div class="nid-image-box" id="nidImageBox">
                        <img
                            src="<?= !empty($nidPath) ? htmlspecialchars($nidPath) : '../Images/Sample_User_Icon.png' ?>"
                            alt="NID Preview"
                            id="nidPreview"
                            class="nid-image <?= (!isset($_SESSION['encryption_verified']) || $_SESSION['encryption_verified'] !== true) ? 'blur' : '' ?>"
                        >
                    </div>
                </div>
            </div>
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
                <a href="?reset=true" class="nid-reset-link">🔁 ব্লার করুন </a>
            <?php endif; ?>
            <!-- Full Name -->
            <label for="deliveryName">পুরো নাম (NID/ জন্ম সনদ অনুসারে):</label>
            <input type="text" id="deliveryName" name="deliveryName" value="<?= htmlspecialchars($deliveryName) ?>" disabled required>
            <!-- Phone Number -->
            <label for="deliveryPhone">ফোন নম্বর:</label>
            <input type="text" id="deliveryPhone" name="deliveryPhone" value="<?= htmlspecialchars($deliveryPhone) ?>" disabled required>
            <!-- Email -->
            <label for="deliveryEmail">ইমেইল:</label>
            <input type="email" id="deliveryEmail" name="deliveryEmail" value="<?= htmlspecialchars($deliveryEmail) ?>" disabled>
            <!-- Address -->
            <label for="deliveryAddress">ঠিকানা:</label>
            <input type="text" id="deliveryAddress" name="deliveryAddress" value="<?= htmlspecialchars($deliveryAddress) ?>" disabled required>
            <!-- Gender -->
            <label for="deliveryGender">লিঙ্গ:</label>
            <select id="deliveryGender" name="deliveryGender" disabled required>
                <option value="male" <?= $deliveryGender === 'male' ? 'selected' : '' ?>>পুরুষ</option>
                <option value="female" <?= $deliveryGender === 'female' ? 'selected' : '' ?>>মহিলা</option>
                <option value="other" <?= $deliveryGender === 'other' ? 'selected' : '' ?>>অন্যান্য</option>
            </select>
            <!-- Edit Button -->
            <button type="button" class="edit-button" onclick="window.location.href='../Html/Delivaryman_setting_edit.php'">
                এডিট করুন
            </button>
        </form>
    </div>

    <script src="../java_script/Delivery_Setting.js"></script>
    <script>
    // Tab switching for notification sidebar (only one tab now)
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('#notificationSidebar .tab-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('#notificationSidebar .tab-btn').forEach(function(b) {
                    b.classList.remove('active');
                });
                document.querySelectorAll('#notificationSidebar .tab-content').forEach(function(tab) {
                    tab.classList.remove('active');
                });
                btn.classList.add('active');
                var tabId = btn.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
    });
    </script>
</body>
</html>