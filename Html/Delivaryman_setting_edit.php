<?php
session_start();
include '../PHP/db_connect.php';

// Validate session using delivery_man_email
if (!isset($_SESSION['delivery_man_email']) || !filter_var($_SESSION['delivery_man_email'], FILTER_VALIDATE_EMAIL)) {
    echo "Invalid session. Please log in again.";
    exit();
}
$delivery_man_email = $_SESSION['delivery_man_email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate inputs
    $delivery_man_name = htmlspecialchars(trim($_POST['shopOwnerName']));
    $delivery_man_phone = htmlspecialchars(trim($_POST['shopPhone']));
    $delivery_man_email_input = htmlspecialchars(trim($_POST['shopEmail']));
    $delivery_man_address = htmlspecialchars(trim($_POST['shopAddress']));
    $delivery_man_gender = htmlspecialchars(trim($_POST['shopOwnerGender']));

    // Validate phone number
    if (!preg_match('/^[0-9]{10,20}$/', $delivery_man_phone)) {
        header("Location: ../Html/Delivaryman_setting_edit.php?error=Invalid+phone+number");
        exit();
    }
    // Validate email
    if (!filter_var($delivery_man_email_input, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../Html/Delivaryman_setting_edit.php?error=Invalid+email+address");
        exit();
    }

    // File uploads
    $target_dir = "../uploads/";
    $delivery_man_image_path = "";
    $delivery_man_nid_path = "";

    // Profile Image
    if (!empty($_FILES['profilePic']['name'])) {
        $delivery_man_image_path = $target_dir . uniqid() . '_' . basename($_FILES['profilePic']['name']);
        if (!move_uploaded_file($_FILES['profilePic']['tmp_name'], $delivery_man_image_path)) {
            header("Location: ../Html/Delivaryman_setting_edit.php?error=Profile+image+upload+failed");
            exit();
        }
    }

    // NID Image
    if (!empty($_FILES['nid']['name'])) {
        $delivery_man_nid_path = $target_dir . uniqid() . '_' . basename($_FILES['nid']['name']);
        if (!move_uploaded_file($_FILES['nid']['tmp_name'], $delivery_man_nid_path)) {
            header("Location: ../Html/Delivaryman_setting_edit.php?error=NID+image+upload+failed");
            exit();
        }
    }

    // Build the SQL query
    $sql = "UPDATE delivery_men SET 
                delivery_man_name = ?, 
                delivery_man_phone = ?, 
                delivery_man_email = ?, 
                delivery_man_gender = ?, 
                delivery_man_address = ?";

    $types = "sssss";
    $params = [$delivery_man_name, $delivery_man_phone, $delivery_man_email_input, $delivery_man_gender, $delivery_man_address];

    if ($delivery_man_nid_path !== "") {
        $sql .= ", delivery_man_nid_path = ?";
        $types .= "s";
        $params[] = $delivery_man_nid_path;
    }
    if ($delivery_man_image_path !== "") {
        $sql .= ", delivery_man_image_path = ?";
        $types .= "s";
        $params[] = $delivery_man_image_path;
    }

    $sql .= " WHERE delivery_man_email = ?";
    $types .= "s";
    $params[] = $delivery_man_email;

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            // If email was changed, update session to use the new email
            if ($delivery_man_email_input !== $delivery_man_email) {
                $_SESSION['delivery_man_email'] = $delivery_man_email_input;
            }
            header("Location: ../Html/DeliveryMan_Home.php");
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
    <link rel="stylesheet" href="../CSS/Delivaryman_setting_edit.css"> <!-- Ensure CSS Path is Correct -->
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
        
            
        </div>
    </header>
   
<!-- OVERLAY (for background when sidebar is open) -->
<div id="overlay" class="overlay"></div>
<!-- User Sidebar -->
<div id="userSidebar" class="sidebar">
    <span id="closeUserSidebar" class="close-btn">&times;</span>
    <h3>ব্যবহারকারী মেনু</h3> <!-- Changed 'User Menu' to 'ব্যবহারকারী মেনু' -->
    <div class="sidebar-content">
     <a href="../Html/DeliveryMan_Home.php" id="settingsLink">হোম</a> <!-- 'Settings' in Bangla -->

      <a href="../Html/Deliveryman_ChangePassword.php">পাসওয়ার্ড পরিবর্তন</a>
       <a href="#" id="logoutLink">লগ আউট</a>
    </div>
</div>



<!-- SETTINGS SECTION -->
<div class="form-container">
  <form id="shopForm" action="#" method="POST" enctype="multipart/form-data">
    <h3>সেটিংস</h3>

  <!-- Profile & NID Upload Side by Side -->
  <div class="profile-nid-row">
    <!-- Profile Image Upload -->
    <div class="profile-upload">
      <label for="profilePic" class="profile-label">প্রোফাইল ছবি:</label>
      <div class="profile-image-box" id="profileImageBox">
        <input 
          type="file" 
          id="profilePic" 
          name="profilePic" 
          accept="image/*" 
          required
        >
      
      </div>
    </div>

    <!-- NID/Birth Certificate Upload -->
    <div class="nid-upload">
      <label for="nid" class="nid-label">জাতীয় পরিচয়পত্র (NID)/ জন্ম সনদ:</label>
      <div class="nid-image-box" id="nidImageBox">
        <input 
          type="file" 
          id="nid" 
          name="nid" 
          accept="image/*" 
          required
        >
      </div>
    </div>
  </div>
    <!-- Shop Owner's Name -->
    <label for="shopOwnerName">পুরো নাম (NID/ জন্ম সনদ অনুসারে):</label>
    <input 
      type="text" 
      id="shopOwnerName" 
      name="shopOwnerName" 
      placeholder="NID এর নাম অনুসারে"  
      required
    >

    <!-- Phone Number -->
    <label for="shopPhone">ফোন নম্বর:</label>
    <input 
      type="tel" 
      id="shopPhone" 
      name="shopPhone" 
      pattern="[0-9]{10,15}" 
      placeholder="আপনার ফোন নম্বর লিখুন" 
      required
    >

    <!-- Email -->
    <label for="shopEmail">ইমেইল:</label>
    <input 
      type="email" 
      id="shopEmail" 
      name="shopEmail" 
      placeholder="আপনার ইমেইল লিখুন" 
      required
    >

    <!-- Address Section -->
    <label for="shopAddress">ঠিকানা:</label>
    <input 
      type="text" 
      id="shopAddress" 
      name="shopAddress" 
      placeholder="আপনার ঠিকানা লিখুন" 
      required
    >
    <button type="button" id="mapButton" class="map-button" onclick="openMap()">ঠিকানা নির্বাচন করুন</button>

    <!-- Gender -->
    <label for="shopOwnerGender">লিঙ্গ:</label>
    <select id="shopOwnerGender" name="shopOwnerGender" required>
      <option value="">--লিঙ্গ নির্বাচন করুন--</option>
      <option value="male">পুরুষ</option>
      <option value="female">মহিলা</option>
      <option value="other">অন্যান্য</option>
    </select>

   <!-- Save and Cancel Buttons (side by side) -->
        <div class="button-container">
            <button type="submit" id="saveButton" class="save-button" >সেভ করুন</button>
            <button type="button" id="cancelButton" class="cancel-button" onclick="window.location.href='../Html/Delivaryman_setting.php'">বাতিল করুন</button></div>
  </form>
</div>


    <div id="mapModal" class="map-modal hidden">
        <h3>ঠিকানা নির্বাচন করুন</h3>
    
        <!-- Map container -->
        <div id="map" style="width: 100%; height: 400px;"></div>
        <span id="closeMap" class="close-icon" onclick="closeMap()">&times;</span> <!-- Close Button -->

        <!-- Action Buttons -->
        <button type="button" id="locateButton" onclick="locateCurrentPosition()">বর্তমান অবস্থান</button>
        <button type="button" id="saveLocation" onclick="saveLocation()">ঠিকানা সংরক্ষণ করুন</button>
    </div>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="../java_script/Delivaryman_setting_edit.js"></script>

</body>
</html>
