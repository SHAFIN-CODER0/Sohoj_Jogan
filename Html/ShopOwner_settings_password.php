<?php
session_start();
include '../PHP/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['shop_owner_email'])) {
    echo "<script>alert('প্রথমে লগইন করুন।'); window.location.href='../Html/index.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $shop_owner_id = $_SESSION['shop_owner_id'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $repeatPassword = $_POST['repeatPassword'];

    // Check if new passwords match
    if ($newPassword !== $repeatPassword) {
        $error = "নতুন পাসওয়ার্ড এবং পুনরায় পাসওয়ার্ড মিলছে না।";
    } else {
        // Validate current password
        $stmt = $conn->prepare("SELECT shop_owner_password FROM shop_owners WHERE shop_owner_id = ?");
        $stmt->bind_param("i", $shop_owner_id);
        $stmt->execute();
        $stmt->bind_result($dbPassword);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($currentPassword, $dbPassword)) {
            $error = "বর্তমান পাসওয়ার্ড সঠিক নয়।";
        } else {
            // Hash and update the new password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE shop_owners SET shop_owner_password = ? WHERE shop_owner_id = ?");
            $update_stmt->bind_param("si", $newPasswordHash, $shop_owner_id);
            if ($update_stmt->execute()) {
                echo "<script>alert('পাসওয়ার্ড সফলভাবে পরিবর্তন হয়েছে।'); window.location.href='../Html/ShopOwner_Home.php';</script>";
exit();

            } else {
                $error = "পাসওয়ার্ড পরিবর্তনে সমস্যা হয়েছে।";
            }
            $update_stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../CSS/ShopOwner_settings_password.css"> <!-- Ensure CSS Path is Correct -->
</head>
<body>

    <!-- HEADER SECTION -->
    <header>
        <div class="logo">
            <img src="../Images/Logo.png" alt="Liberty Logo" class="logo-img">
            <h2>সহজ যোগান</h2>
        </div>

        <!-- Top Icons -->
        <div class="icons">
            <button id="userIcon" class="icon-btn">
                <img src="../Images/Sample_User_Icon.png" alt="User" class="icon-img">
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
<a href="../Html/ShopOwner_Home.php" id="settingsLink">হোম</a> <!-- 'Home' in Bangla -->
        <a href="../Html/ShopOwner_settings.php" id="settingsLink">সেটিংস</a> <!-- 'Settings' in Bangla -->

        <a href="#" id="logoutLink">লগ আউট</a>
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


<script src="../java_script/ShopOwner_settings_password.js"></script> <!-- Link to JS -->
   
    

<!-- PASSWORD CHANGE FORM SECTION -->

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

</body>
</html>
