<?php
include 'db_connect.php'; // Include the database connection file

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $shopName = trim($_POST['shopName']);
    $shopOwnerName = trim($_POST['shopOwnerName']);
    $shopPhone = trim($_POST['shopPhone']);
    $shopEmail = trim($_POST['shopEmail']);
    $shopOwnerGender = $_POST['shopOwnerGender'];
    $shopAddress = trim($_POST['shopAddress']);
    $shopDescription = trim($_POST['shopDescription']);
    $rawPassword = $_POST['shopPassword'];
    $addressStreet = trim($_POST['addressStreet']);
    $addressArea = trim($_POST['addressArea']);
    $addressCity = trim($_POST['addressCity']);
    $addressPostcode = trim($_POST['addressPostcode']);
    $addressDivision = trim($_POST['addressDivision']);

    // === NEW: Get shop type ===
    $shopType = $_POST['shopType']; // <-- ADD THIS LINE

    // === NEW: Get Latitude & Longitude ===
    $shopLatitude = isset($_POST['shopLatitude']) ? trim($_POST['shopLatitude']) : null;
    $shopLongitude = isset($_POST['shopLongitude']) ? trim($_POST['shopLongitude']) : null;

    // === Validate Phone Number ===
    if (strlen($shopPhone) !== 11 || !ctype_digit($shopPhone)) {
        echo "<script>alert('অবৈধ ফোন নম্বর। এটি অবশ্যই ১১ সংখ্যার হতে হবে।'); window.location.href='../Html/index.php';</script>";
        exit();
    }

    // === Validate Password Length ===
    if (strlen($rawPassword) < 8) {
        echo "<script>alert('পাসওয়ার্ড অবশ্যই কমপক্ষে ৮ অক্ষরের হতে হবে।'); window.location.href='../Html/index.php';</script>";
        exit();
    }

    // === Validate Email (only if not empty) ===
    if (!empty($shopEmail) && !filter_var($shopEmail, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('অবৈধ ইমেল ঠিকানা। অনুগ্রহ করে সঠিক ইমেল দিন।'); window.location.href='../Html/index.php';</script>";
        exit();
    }

    // === Validate Latitude & Longitude ===
    if (empty($shopLatitude) || empty($shopLongitude)) {
        echo "<script>alert('দয়া করে map-এ দোকানের লোকেশন নির্বাচন করুন!'); window.location.href='../Html/index.php';</script>";
        exit();
    }

    $password = password_hash($rawPassword, PASSWORD_DEFAULT); // Hash the password for security

    // === Image Upload ===
    $nidImage = $_FILES['nid']['name'];
    $nidTmpName = $_FILES['nid']['tmp_name'];
    $shopOwnerPic = $_FILES['shopOwnerPic']['name'];
    $shopOwnerPicTmpName = $_FILES['shopOwnerPic']['tmp_name'];
    $shopPic = $_FILES['shopPic']['name'];
    $shopPicTmpName = $_FILES['shopPic']['tmp_name'];

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $nidExtension = strtolower(pathinfo($nidImage, PATHINFO_EXTENSION));
    $ownerPicExtension = strtolower(pathinfo($shopOwnerPic, PATHINFO_EXTENSION));
    $shopPicExtension = strtolower(pathinfo($shopPic, PATHINFO_EXTENSION));

    // Check image file types
    if (
        !in_array($nidExtension, $allowedExtensions) ||
        !in_array($ownerPicExtension, $allowedExtensions) ||
        !in_array($shopPicExtension, $allowedExtensions)
    ) {
        echo "<script>alert('অবৈধ ছবি ফাইলের ধরণ। অনুগ্রহ করে JPG, PNG, অথবা GIF ফাইল আপলোড করুন।'); window.location.href='../Html/index.php';</script>";
        exit();
    }

    // Generate unique names for images
    $uniqueNidImageName = time() . "_" . basename($nidImage);
    $uniqueOwnerPicName = time() . "_" . basename($shopOwnerPic);
    $uniqueShopPicName = time() . "_" . basename($shopPic);

    // Set the paths for each file type
    $nidFolder = "../uploads/" . $uniqueNidImageName;
    $ownerPicFolder = "../uploads/" . $uniqueOwnerPicName;
    $shopPicFolder = "../uploads/" . $uniqueShopPicName;

    // Move the files to the respective folders
    if (
        move_uploaded_file($nidTmpName, $nidFolder) &&
        move_uploaded_file($shopOwnerPicTmpName, $ownerPicFolder) &&
        move_uploaded_file($shopPicTmpName, $shopPicFolder)
    ) {

        // Check for duplicate email or phone
        $checkSql = "SELECT * FROM shop_owners WHERE shop_owner_phone = ? OR shop_owner_email = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("ss", $shopPhone, $shopEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Error: মেইল অথবা ফোন নম্বর ইতোমধ্যেই নিবন্ধিত!'); window.location.href='../Html/index.php';</script>";
        } else {
            // Insert into database (with shop_type, latitude, longitude)
            $sql = "INSERT INTO shop_owners (
                shop_owner_name, shop_owner_phone, shop_owner_email,
                shop_owner_gender, shop_owner_address, shop_description,
                shop_owner_password, shop_owner_nid_path, shop_owner_image_path,
                shop_image_path, shop_type, shop_name, address_street, address_area,
                address_city, address_postcode, address_division,
                shop_latitude, shop_longitude
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssssssssssssssssss",
                $shopOwnerName, $shopPhone, $shopEmail,
                $shopOwnerGender, $shopAddress, $shopDescription,
                $password, $nidFolder, $ownerPicFolder,
                $shopPicFolder, $shopType, $shopName, $addressStreet, $addressArea,
                $addressCity, $addressPostcode, $addressDivision,
                $shopLatitude, $shopLongitude
            );

            if ($stmt->execute()) {
                echo "<script>alert('নিবন্ধন সফল হয়েছে! অনুগ্রহ করে লগইন করুন'); window.location.href='../Html/index.php';</script>";
            } else {
                echo "SQL Error: " . $conn->error;
            }
        }

        $stmt->close();
    } else {
        echo "<script>alert('ছবিগুলি আপলোড করতে সমস্যা হয়েছে। দয়া করে আবার চেষ্টা করুন।'); window.location.href='../Html/index.html';</script>";
    }
}
?>