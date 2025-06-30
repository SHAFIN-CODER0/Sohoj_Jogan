<?php
include 'db_connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['fullName']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['deliveryEmail']);
    $gender = $_POST['deliveryGender'];
    $address = trim($_POST['address']);
    $rawPassword = $_POST['password'];

    // === Basic Validation ===
    if (strlen($phone) !== 11 || !ctype_digit($phone)) {
        echo "<script>alert('ফোন নাম্বার অবশ্যই ১১ সংখ্যার হতে হবে।'); window.location.href='../Html/index.php';</script>";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('অবৈধ ইমেইল ঠিকানা।'); window.location.href='../Html/index.php';</script>";
        exit();
    }

    if (strlen($rawPassword) < 8) {
        echo "<script>alert('পাসওয়ার্ড অবশ্যই ৮ অক্ষরের বেশি হতে হবে।'); window.location.href='../Html/index.php';</script>";
        exit();
    }

    $password = password_hash($rawPassword, PASSWORD_DEFAULT);

    // === Handle Image Uploads ===
    $nidImage = $_FILES['nidImage']['name'];
    $nidTmp = $_FILES['nidImage']['tmp_name'];
    $profileImage = $_FILES['profilePic']['name'];
    $profileTmp = $_FILES['profilePic']['tmp_name'];

    $allowed = ['jpg', 'jpeg', 'png'];
    $nidExt = strtolower(pathinfo($nidImage, PATHINFO_EXTENSION));
    $profileExt = strtolower(pathinfo($profileImage, PATHINFO_EXTENSION));

    if (!in_array($nidExt, $allowed) || !in_array($profileExt, $allowed)) {
        echo "<script>alert('শুধুমাত্র JPG, JPEG, PNG ফাইল অনুমোদিত।'); window.location.href='../Html/index.php';</script>";
        exit();
    }

    $nidPath = "../uploads/" . basename($nidImage);
    $profilePath = "../uploads/" . basename($profileImage);

    if (move_uploaded_file($nidTmp, $nidPath) && move_uploaded_file($profileTmp, $profilePath)) {
        // Check if phone or email exists
        $check = $conn->prepare("SELECT * FROM delivery_men WHERE delivery_man_phone = ? OR delivery_man_email = ?");
        $check->bind_param("ss", $phone, $email);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            echo "<script>alert('এই ফোন নাম্বার অথবা ইমেইল ইতোমধ্যেই ব্যবহৃত হয়েছে।'); window.location.href='../Html/index.php';</script>";
            exit();
        }

        // Insert into database
        $sql = "INSERT INTO delivery_men (delivery_man_name, delivery_man_phone, delivery_man_email, delivery_man_gender, delivery_man_address, delivery_man_password, delivery_man_nid_path, delivery_man_image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $name, $phone, $email, $gender, $address, $password, $nidPath, $profilePath);

        if ($stmt->execute()) {
            echo "<script>alert('নিবন্ধন সফল হয়েছে! অনুগ্রহ করে লগইন করুন'); window.location.href='../Html/index.php';</script>";
        } else {
            echo "<script>alert('ত্রুটি: ডাটাবেসে সংরক্ষণ করা যায়নি।'); window.location.href='../Html/index.php';</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('ছবি আপলোড করতে সমস্যা হয়েছে।'); window.location.href='../Html/index.php';</script>";
    }
}
?>