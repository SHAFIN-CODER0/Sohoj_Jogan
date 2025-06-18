<?php 
session_start();  
include '../PHP/db_connect.php';

// Validate session using shop_owner_email
if (!isset($_SESSION['shop_owner_email']) || !filter_var($_SESSION['shop_owner_email'], FILTER_VALIDATE_EMAIL)) {
    echo "Invalid session. Please log in again.";
    exit();
}
$shop_owner_email = $_SESSION['shop_owner_email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate inputs
    $shop_owner_name = htmlspecialchars($_POST['shopOwnerName']);
    $shop_owner_phone = htmlspecialchars($_POST['shopPhone']);
    $shop_name = htmlspecialchars($_POST['shopName']);
    $shop_owner_address = htmlspecialchars($_POST['shopAddress']);
    $address_street = htmlspecialchars($_POST['addressStreet']);
    $address_area = htmlspecialchars($_POST['addressArea']);
    $address_city = htmlspecialchars($_POST['addressCity']);
    $address_postcode = htmlspecialchars($_POST['addressPostcode']);
    $address_division = htmlspecialchars($_POST['addressDivision']);
    $shop_owner_gender = htmlspecialchars($_POST['shopOwnerGender']);
    $shop_description = htmlspecialchars($_POST['shopDescription']);

    // Validate input fields
    if (!preg_match('/^[0-9]{10,20}$/', $shop_owner_phone)) {
        header("Location: ../Html/ShopOwner_settings.php?error=Invalid+phone+number");
        exit();
    }
    if (!filter_var($shop_owner_email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../Html/ShopOwner_settings.php?error=Invalid+email+address");
        exit();
    }
    if (!preg_match('/^[0-9]{4,10}$/', $address_postcode)) {
        header("Location: ../Html/ShopOwner_settings.php?error=Invalid+postcode");
        exit();
    }

    // File uploads
    $target_dir = "../uploads/"; // Ensure the uploads folder exists
    $nid_image_path = "";
    $shop_owner_image_path = "";
    $shop_image_path = "";

    // NID Image
    if (!empty($_FILES['nid']['name'])) {
        $nid_image_path = $target_dir . uniqid() . '_' . basename($_FILES['nid']['name']);
        if (!move_uploaded_file($_FILES['nid']['tmp_name'], $nid_image_path)) {
            header("Location: ../Html/ShopOwner_settings.php?error=NID+file+upload+failed");
            exit();
        }
    }

    // Shop Owner Picture
    if (!empty($_FILES['shopOwnerPic']['name'])) {
        $shop_owner_image_path = $target_dir . uniqid() . '_' . basename($_FILES['shopOwnerPic']['name']);
        if (!move_uploaded_file($_FILES['shopOwnerPic']['tmp_name'], $shop_owner_image_path)) {
            header("Location: ../Html/ShopOwner_settings.php?error=Shop+owner+picture+upload+failed");
            exit();
        }
    }

    // Shop Picture
    if (!empty($_FILES['shopPic']['name'])) {
        $shop_image_path = $target_dir . uniqid() . '_' . basename($_FILES['shopPic']['name']);
        if (!move_uploaded_file($_FILES['shopPic']['tmp_name'], $shop_image_path)) {
            header("Location: ../Html/ShopOwner_settings.php?error=Shop+picture+upload+failed");
            exit();
        }
    }

    // Update query
    $sql = "UPDATE shop_owners SET 
                shop_owner_name = ?, 
                shop_owner_phone = ?, 
                shop_owner_address = ?, 
                address_street = ?, 
                address_area = ?, 
                address_city = ?, 
                address_postcode = ?, 
                address_division = ?, 
                shop_owner_gender = ?, 
                shop_description = ?, 
                shop_owner_nid_path = ?, 
                shop_owner_image_path = ?, 
                shop_image_path = ?, 
                shop_name = ? 
            WHERE shop_owner_email = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param(
            "sssssssssssssss", 
            $shop_owner_name, 
            $shop_owner_phone, 
            $shop_owner_address, 
            $address_street, 
            $address_area, 
            $address_city, 
            $address_postcode, 
            $address_division, 
            $shop_owner_gender, 
            $shop_description, 
            $nid_image_path, 
            $shop_owner_image_path, 
            $shop_image_path, 
            $shop_name, 
            $shop_owner_email
        );
        if ($stmt->execute()) {
            header("Location: ../Html/ShopOwner_Home.php?success=1");
            exit();
        } else {
            echo "Error executing query: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../CSS/ShopOwner_setting_edit.css"> <!-- Ensure CSS Path is Correct -->
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
    <span id="closeMessenger" class="close-btn">&times;</span> <!-- Ensure this ID matches the JavaScript -->
    <h3>মেসেজ</h3> <!-- 'Messages' to 'মেসেজ' -->
    <div class="sidebar-content">
        <p>কোনো নতুন মেসেজ নেই</p> <!-- 'No new messages' in Bangla -->
    </div>
</div>



<!-- SETTINGS SECTION -->
    <div class="form-container">
        <form id="shopForm" action="ShopOwner_settings_Edit.php" method="POST" enctype="multipart/form-data">
            <h3>সেটিংস</h3>
            <!-- NID Upload -->
            <div class="nid-upload">
                <label for="nid" class="nid-label">জাতীয় পরিচয়পত্র (NID)/ জন্ম সনদ:</label>
                <div class="nid-image-box">
                    <img id="nidImage" src="" alt="NID/Birth Certificate Image" class="nid-image">
                </div>
                <input type="file" id="nid" name="nid" accept="image/*" required>
            </div>

            <!-- Shop Owner's Name -->
            <label for="shopOwnerName">দোকান মালিকের নাম:</label>
            <input type="text" id="shopOwnerName" name="shopOwnerName" placeholder="NID এর নাম অনুসারে" required>

            <!-- Phone Number -->
            <label for="shopPhone">দোকানের ফোন নম্বর:</label>
            <input type="text" id="shopPhone" name="shopPhone" required>

            <!-- Email -->
            <label for="shopEmail">দোকানের ইমেইল (অপশনাল):</label>
            <input type="email" id="shopEmail" name="shopEmail">

            <!-- Shop Owner Picture -->
            <label for="shopOwnerPic">দোকান মালিকের ছবি:</label>
            <input type="file" id="shopOwnerPic" name="shopOwnerPic" accept="image/*" required>

            <!-- Shop Name -->
            <label for="shopName">দোকানের নাম:</label>
            <input type="text" id="shopName" name="shopName" required placeholder="আপনার দোকানের নাম লিখুন">

            <!-- Shop Picture -->
            <label for="shopPic">দোকানের ছবি:</label>
            <input type="file" id="shopPic" name="shopPic" accept="image/*" required>

            <!-- Address Section -->
            <label for="shopAddress">দোকানের ঠিকানা:</label>
            <input type="text" id="shopAddress" name="shopAddress" placeholder="দোকানের ঠিকানা লিখুন" required>
            <button type="button" id="mapButton" class="map-button" onclick="openMap()">ঠিকানা নির্বাচন করুন</button>

            <label for="addressStreet">রাস্তা/বিল্ডিং:</label>
            <input type="text" id="addressStreet" name="addressStreet" required>

            <label for="addressArea">এরিয়া / থানা:</label>
            <input type="text" id="addressArea" name="addressArea" required>

            <label for="addressCity">শহর / উপজেলা:</label>
            <input type="text" id="addressCity" name="addressCity" required>

            <label for="addressPostcode">পোস্টকোড:</label>
            <input type="text" id="addressPostcode" name="addressPostcode" required>

            <label for="addressDivision">বিভাগ:</label>
            <input type="text" id="addressDivision" name="addressDivision" required>

            <!-- Gender -->
            <label for="shopOwnerGender">লিঙ্গ:</label>
            <select id="shopOwnerGender" name="shopOwnerGender" required>
                <option value="male">পুরুষ</option>
                <option value="female">মহিলা</option>
                <option value="other">অন্যান্য</option>
            </select>

            <!-- Shop Description -->
            <label for="shopDescription">দোকানের বর্ণনা দিন:</label>
            <textarea id="shopDescription" name="shopDescription" rows="4" cols="50" required></textarea>

            <!-- Submit Buttons -->
            <div class="button-container">
                <button type="submit" id="saveButton" class="save-button">সেভ করুন</button>
                <button type="button" id="cancelButton" class="cancel-button" onclick="window.location.href='../Html/ShopOwner_settings.php'">বাতিল করুন</button>
            </div>
        </form>
    </div>
    <div id="mapModal" class="map-modal hidden">
        <h3>ঠিকানা নির্বাচন করুন</h3>
        <!-- Map container -->
        <div id="map" style="width: 100%; height: 400px;"></div>
        <span id="closeMap" class="close-icon" onclick="closeMap()">&times;</span>
        <!-- Action Buttons -->
        <button type="button" id="locateButton" onclick="locateCurrentPosition()">বর্তমান অবস্থান</button>
        <button type="button" id="saveLocation" onclick="saveLocation()">ঠিকানা সংরক্ষণ করুন</button>
    </div>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="../java_script/Shopowner_Setting_edit.js"></script>

</body>
</html>
