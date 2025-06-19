<?php
session_start();
include '../PHP/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['delivery_man_email'])) {
    echo "<script>alert('প্রথমে লগইন করুন।'); window.location.href='../Html/index.html';</script>";
    exit();
}

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
    <a href="#" id="logoutLink">লগ আউট</a>
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
