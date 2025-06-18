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
    echo "<script>alert('আপনাকে প্রথমে লগইন করতে হবে!'); window.location.href='../Html/index.html';</script>";
    exit();
}

$email = $_SESSION['delivery_man_email'];

// --- Fetch Delivery Man Data ---
$sql = "SELECT 
            delivery_man_name, delivery_man_phone, delivery_man_email, 
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
        $deliveryName     = $row['delivery_man_name'];
        $deliveryPhone    = $row['delivery_man_phone'];
        $deliveryEmail    = $row['delivery_man_email'];
        $deliveryGender   = $row['delivery_man_gender'];
        $deliveryAddress  = $row['delivery_man_address'];
        $deliveryPassword = $row['delivery_man_password']; // Login password
        $nidPath          = $row['delivery_man_nid_path'];
        $profilePic       = $row['delivery_man_image_path'];
        $encryptHash      = $row['encrypt_pass_hash'];     // Encryption password
    } else {
        echo "<script>alert('ডেলিভারি ম্যান এর তথ্য পাওয়া যায়নি।'); window.location.href='../Html/index.html';</script>";
        exit();
    }

    $stmt->close();
} else {
    echo "<script>alert('ডাটাবেস ত্রুটি: তথ্য প্রস্তুত করতে ব্যর্থ।'); window.location.href='../Html/index.html';</script>";
    exit();
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
<link rel="stylesheet" href="../CSS/Deliveryman_setting.css?v=1">
        
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

<!-- Notification Sidebar -->
<div id="notificationSidebar" class="sidebar">
    <span id="closeNotification" class="close-btn">&times;</span>
    <h3>নোটিফিকেশন</h3> <!-- Changed 'Notifications' to 'নোটিফিকেশন' -->
    <div class="sidebar-content">
        <p>নতুন কোনো নোটিফিকেশন নেই</p> <!-- 'No new notifications' in Bangla -->
    </div>
</div>
<!-- SETTINGS SECTION -->
<div class="form-container">
    <form id="deliveryForm" action="#" method="POST" enctype="multipart/form-data">
        <h3>সেটিংস</h3>

     <!-- Profile & NID Upload Side by Side -->
<div class="profile-nid-row">
    <!-- Profile Image Upload -->
    <div class="profile-upload">
        <label class="profile-label">প্রোফাইল ছবি:</label>
        <div class="profile-image-box" id="profileImageBox">
            <img src="<?= !empty($profilePic) ? htmlspecialchars($profilePic) : '../Images/Sample_User_Icon.png' ?>" alt="Profile Preview" id="profilePreview" class="profile-image">
        </div>
    </div>

    <!-- NID Upload, Always show, blur if not verified -->
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
            <!-- First time: ask to set password -->
            <p>🔐 প্রথমবার, আপনার NID ছবি দেখতে একটি পাসওয়ার্ড সেট করুন:</p>
            <label for="encryption_pass">নতুন পাসওয়ার্ড দিন:</label><br>
            <input type="password" name="encryption_pass" id="encryption_pass" required><br><br>
            <button type="submit" name="set_password">সেভ করুন ও দেখান</button>
        <?php else: ?>
            <!-- Password already set: ask for password to unlock -->
            <p>🔐 আপনার NID ছবি দেখানোর জন্য পাসওয়ার্ড প্রদান করুন।

</p>
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

</body>
</html>
