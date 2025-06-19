<?php
session_start();
include '../PHP/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['customer_email'])) {
    echo "<script>alert('You must log in first!'); window.location.href='../Html/index.html';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_id = $_SESSION['customer_id']; // Ensure you have the customer ID from session
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $repeatPassword = $_POST['repeatPassword'];

    // Check if the new passwords match
    if ($newPassword !== $repeatPassword) {
        $error = "নতুন পাসওয়ার্ড এবং পুনরায় পাসওয়ার্ড মিলছে না।"; // New and repeated password don't match
    } else {
        // Validate current password
        $stmt = $conn->prepare("SELECT customer_password FROM customers WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $stmt->bind_result($dbPassword);
        $stmt->fetch();
        $stmt->close();

        echo "Database Password: " . $dbPassword;  // Debugging step

        // Check if current password matches the hashed password
        if (!password_verify($currentPassword, $dbPassword)) {
            $error = "বর্তমান পাসওয়ার্ড সঠিক নয়।"; // Current password is incorrect
        } else {
            // Hash the new password and update
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE customers SET customer_password = ? WHERE customer_id = ?");
            $update_stmt->bind_param("si", $newPasswordHash, $customer_id);
            if ($update_stmt->execute()) {
                // Success message and redirect to Customer_Home.html
                echo "<script>alert('পাসওয়ার্ড সফলভাবে পরিবর্তন হয়েছে।'); window.location.href='../Html/Customer_Home.html';</script>";
                exit(); // Ensure the script stops here
            } else {
                $error = "পাসওয়ার্ড পরিবর্তনে সমস্যা হয়েছে।"; // Error updating the password
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
    <link rel="stylesheet" href="../CSS/Customer_setting.css"> <!-- Ensure CSS Path is Correct -->
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
    <h3>ব্যবহারকারী মেনু</h3> <!-- Changed 'User Menu' to 'ব্যবহারকারী মেনু' -->
    <div class="sidebar-content">
        <a href="../Html/Customer_profile.php" id="profileLink">প্রোফাইল</a> <!-- 'New Collection' in Bangla -->
        <a href="../Html/Customer_Home.php" id="settingsLink">হোম</a> <!-- 'Settings' in Bangla -->
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

<!-- Messenger Sidebar -->
<div id="messengerSidebar" class="sidebar">
    <span id="closeMessenger" class="close-btn">&times;</span>
    <h3>মেসেজ</h3> <!-- Changed 'Messages' to 'মেসেজ' -->
    <div class="sidebar-content">
        <p>কোনো নতুন মেসেজ নেই</p> <!-- 'No new messages' in Bangla -->
    </div>
</div>


<script src="../java_script/Cusomer_setting.js"></script> <!-- Link to JS -->
   
    

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
