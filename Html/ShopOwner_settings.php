 <?php
session_start();
include '../PHP/db_connect.php';

if (isset($_GET['reset']) && $_GET['reset'] === 'true') {
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
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../CSS/ShopOwner_setting.css"> <!-- Ensure CSS Path is Correct -->
    <style>
        /* Add a hidden class to manage elements visibility */
        .hidden {
            display: none;
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
            <button id="notificationIcon">
                <img src="../Images/notification.png" alt="Notifications">
            </button>
           <button id="messengerBtn">
    <img src="../Images/messenger-icon.png" alt="Messenger">
</button>
        </div>
    </header>
   
<!-- OVERLAY (for background when sidebar is open) -->
<div id="overlay" class="overlay"></div>
<!-- User Sidebar -->
<!-- User Sidebar -->
<div id="userSidebar" class="sidebar">
    <span id="closeUserSidebar" class="close-btn">&times;</span>
    <h3>ব্যবহারকারী মেনু</h3> <!-- Changed 'User Menu' to 'ব্যবহারকারী মেনু' -->
    <div class="sidebar-content">
        <a href="../Html/ShopOwner_item.html" id="profileLink">নতুন সংগ্রহ</a> <!-- 'New Collection' in Bangla -->
        <a href="../Html/ShopOwner_Home.php" id="settingsLink">হোম</a> <!-- 'Settings' in Bangla -->
        <a href="../Html/ShopOwner_settings_password.php" id="changePasswordLink">পাসওয়ার্ড পরিবর্তন</a> <!-- 'Password' in Bangla -->

        <a href="" id="logoutLink">লগ আউট</a>
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
            <!-- First time: ask to set password -->
            <p>🔐 প্রথমবার, আপনার NID ছবি দেখতে একটি পাসওয়ার্ড সেট করুন:</p>
            <label for="encryption_pass">নতুন পাসওয়ার্ড দিন:</label><br>
            <input type="password" name="encryption_pass" id="encryption_pass" required><br><br>
            <button type="submit" name="set_password">সেভ করুন ও দেখান</button>
        <?php else: ?>
            <!-- Password already set: ask for password to unlock -->
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
    <!-- Password verified success -->
    <p class="nid-success">✅ আপনার পাসওয়ার্ড সফলভাবে যাচাই করা হয়েছে। এখন আপনি NID ছবি দেখতে পারবেন।</p>
<div class="nid-link-box">
    <a href="?reset=1" class="nid-reset-link">🔁 ব্লার করুন</a>
</div>
<?php endif; ?>

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
 <?php
session_start();
include '../PHP/db_connect.php';

if (isset($_GET['reset']) && $_GET['reset'] === 'true') {
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
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../CSS/ShopOwner_setting.css"> <!-- Ensure CSS Path is Correct -->
    <style>
        /* Add a hidden class to manage elements visibility */
        .hidden {
            display: none;
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
            <button id="notificationIcon">
                <img src="../Images/notification.png" alt="Notifications">
            </button>
           <button id="messengerBtn">
    <img src="../Images/messenger-icon.png" alt="Messenger">
</button>
        </div>
    </header>
   
<!-- OVERLAY (for background when sidebar is open) -->
<div id="overlay" class="overlay"></div>
<!-- User Sidebar -->
<!-- User Sidebar -->
<div id="userSidebar" class="sidebar">
    <span id="closeUserSidebar" class="close-btn">&times;</span>
    <h3>ব্যবহারকারী মেনু</h3> <!-- Changed 'User Menu' to 'ব্যবহারকারী মেনু' -->
    <div class="sidebar-content">
        <a href="../Html/ShopOwner_item.html" id="profileLink">নতুন সংগ্রহ</a> <!-- 'New Collection' in Bangla -->
        <a href="../Html/ShopOwner_Home.php" id="settingsLink">হোম</a> <!-- 'Settings' in Bangla -->
        <a href="../Html/ShopOwner_settings_password.php" id="changePasswordLink">পাসওয়ার্ড পরিবর্তন</a> <!-- 'Password' in Bangla -->

        <a href="" id="logoutLink">লগ আউট</a>
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
            <!-- First time: ask to set password -->
            <p>🔐 প্রথমবার, আপনার NID ছবি দেখতে একটি পাসওয়ার্ড সেট করুন:</p>
            <label for="encryption_pass">নতুন পাসওয়ার্ড দিন:</label><br>
            <input type="password" name="encryption_pass" id="encryption_pass" required><br><br>
            <button type="submit" name="set_password">সেভ করুন ও দেখান</button>
        <?php else: ?>
            <!-- Password already set: ask for password to unlock -->
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
    <!-- Password verified success -->
    <p class="nid-success">✅ আপনার পাসওয়ার্ড সফলভাবে যাচাই করা হয়েছে। এখন আপনি NID ছবি দেখতে পারবেন।</p>
<div class="nid-link-box">
    <a href="?reset=1" class="nid-reset-link">🔁 ব্লার করুন</a>
</div>
<?php endif; ?>

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
