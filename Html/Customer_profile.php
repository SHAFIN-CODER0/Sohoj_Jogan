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
$sql = "SELECT customer_name, customer_phone, customer_gender, customer_address, customer_email, profile_pic FROM customers WHERE customer_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Fetch user data
    $row = $result->fetch_assoc();
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
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../Css/CustomerProfile.css">
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

<!-- PROFILE SECTION -->
<div class="profile-container">
    <h2>আপনার প্রোফাইল</h2>
    <div class="profile-pic">
        <!-- Display current profile picture -->
        <img id="profileImage" src="../uploads/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture">
        <!-- Input field for profile picture upload -->
        <input type="file" id="uploadImage" accept="image/*" hidden>
        
    </div>

    <form id="profileForm" method="post" enctype="multipart/form-data">
       
    <label for="customerName">নাম:</label>
        <input type="text" id="customerName" name="customerName" value="<?php echo htmlspecialchars($customerName); ?>" disabled>

        <label for="customerPhone">ফোন নম্বর:</label>
        <input type="text" id="customerPhone" name="customerPhone" value="<?php echo htmlspecialchars($customerPhone); ?>" disabled>

        <label for="customerAddress">ঠিকানা:</label>
        <input type="text" id="customerAddress" name="customerAddress" value="<?php echo htmlspecialchars($customerAddress); ?>" disabled>

        <label for="customerEmail">ইমেইল:</label>
        <input type="email" id="customerEmail" name="customerEmail" value="<?php echo htmlspecialchars($customerEmail); ?>" disabled>

        <label for="customerGender">লিঙ্গ:</label>
        <select id="customerGender" name="customerGender" disabled>
            <option value="male" <?php echo $customerGender == 'male' ? 'selected' : ''; ?>>পুরুষ</option>
            <option value="female" <?php echo $customerGender == 'female' ? 'selected' : ''; ?>>মহিলা</option>
            <option value="other" <?php echo $customerGender == 'other' ? 'selected' : ''; ?>>অন্যান্য</option>
        </select>

       
    </form>

    <button type="button" id="editdata" onclick="window.location.href='Customer_Edit_profile.php';">এডিট করুন</button>
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
         <a href="../Html/Deliveryman_MyDeliveries.php">আমার ডেলিভারি</a>

        <a href="#" id="logoutLink">লগ আউট</a>
    </div>
</div>

<!-- Notification Sidebar -->
<div id="notificationSidebar" class="sidebar">
    <span id="closeNotification" class="close-btn">&times;</span>
    <h3>নোটিফিকেশন</h3>
    <div class="sidebar-content">
        <p>নতুন কোনো নোটিফিকেশন নেই</p>
    </div>
</div>

<!-- Messenger Sidebar -->
<div id="messengerSidebar" class="sidebar">
    <span id="closeMessenger" class="close-btn">&times;</span>
    <h3>মেসেজ</h3>
    <div class="sidebar-content">
        <p>কোনো নতুন মেসেজ নেই</p>
    </div>
</div>

<script src="../java_script/CustomerProfile.js"></script>
</body>
</html>
