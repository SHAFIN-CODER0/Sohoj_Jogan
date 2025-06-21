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
    // Fetch user data
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
        echo "<script>alert('Profile updated successfully!'); window.location.href='Customer_Profile.php';</script>";
    } else {
        echo "<script>alert('Failed to update profile. Please try again later.');</script>";
    }

    $updateStmt->close();
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

    <form id="profileForm" method="post" enctype="multipart/form-data">
        <div class="profile-pic-container">
            <h3>প্রোফাইল ছবি</h3>
            <p id="uploadHint">আপনার প্রোফাইল ছবির জন্য একটি ছবি আপলোড করুন</p>
            <div class="profile-pic">
                <!-- Profile Picture Display -->
                <img id="profileImage" src="../uploads/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture">
                <!-- Hidden File Input + Label -->
                <input type="file" id="uploadImage" name="profilePic" accept="image/*" hidden>
                <!-- Camera Button -->
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
        <a href="../Html/Customer_Home.html" id="profileLink">হোম</a>
        <a href="../Html/Customer_settings.php" id="settingsLink">সেটিংস</a>
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

<script src="../java_script/Customer_Edit_profile.js"></script>
</body>
</html>
