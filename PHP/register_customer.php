<?php
include 'db_connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['customerName']);
    $phone = trim($_POST['customerPhone']);
    $gender = $_POST['customerGender'];
    $address = trim($_POST['customerAddress']);
    $email = trim($_POST['customerEmail']);
    $rawPassword = $_POST['customerPassword'];

    // === Validate Phone Number ===
    $allowedPrefixes = ['013', '014', '015', '016', '017', '018', '019'];
    $phonePrefix = substr($phone, 0, 3);
    if (!in_array($phonePrefix, $allowedPrefixes) || strlen($phone) !== 11 || !ctype_digit($phone)) {
        echo "<script>alert('অবৈধ ফোন নম্বর। এটি অবশ্যই ১১ সংখ্যার হতে হবে এবং ০১৩, ০১৪, ০১৫, ০১৬, ০১৭, ০১৮, বা ০১৯ দিয়ে শুরু হতে হবে।'); window.location.href='../Html/index.php';</script>";
        exit();
    }

    // === Validate Password Length ===
    if (strlen($rawPassword) < 8) {
        echo "<script>alert('পাসওয়ার্ড অবশ্যই কমপক্ষে ৮ অক্ষরের হতে হবে।'); window.location.href='../Html/index.php';</script>";
        exit();
    }

    $password = password_hash($rawPassword, PASSWORD_DEFAULT);

    // === Image Upload ===
    $imageName = $_FILES['customerPic']['name'];
    $imageTmpName = $_FILES['customerPic']['tmp_name'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $imageExtension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

    if (!in_array($imageExtension, $allowedExtensions)) {
        echo "<script>alert('অবৈধ ছবি ফাইলের ধরণ। অনুগ্রহ করে JPG, PNG, অথবা GIF ফাইল আপলোড করুন।'); window.location.href='../Html/index.php';</script>";
        exit();
    }

    $uniqueImageName = time() . "_" . basename($imageName);
    $imageFolder = "../uploads/" . $uniqueImageName;

    if (move_uploaded_file($imageTmpName, $imageFolder)) {
        // === Check if phone/email is banned ===
        $banSql = "SELECT id FROM banned_users WHERE phone = ? OR email = ?";
        $banStmt = $conn->prepare($banSql);
        $banStmt->bind_param("ss", $phone, $email);
        $banStmt->execute();
        $banResult = $banStmt->get_result();

        if ($banResult->num_rows > 0) {
            echo "<script>alert('এই ফোন নম্বর বা ইমেইল ব্যান করা হয়েছে। আপনি নতুন একাউন্ট খুলতে পারবেন না!'); window.location.href='../Html/index.php';</script>";
            $banStmt->close();
            // Remove uploaded image since registration is blocked
            if (file_exists($imageFolder)) {
                unlink($imageFolder);
            }
            exit();
        }
        $banStmt->close();

        // Check for duplicate email or phone
        $checkSql = "SELECT * FROM customers WHERE customer_email = ? OR customer_phone = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("ss", $email, $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Error: মেইল অথবা ফোন নম্বর ইতোমধ্যেই নিবন্ধিত!'); window.location.href='../Html/index.php';</script>";
            // Remove uploaded image since registration is blocked
            if (file_exists($imageFolder)) {
                unlink($imageFolder);
            }
        } else {
            // Insert into database
            $sql = "INSERT INTO customers (customer_name, customer_phone, customer_gender, customer_address, customer_email, customer_password, profile_pic) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $name, $phone, $gender, $address, $email, $password, $uniqueImageName);

            if ($stmt->execute()) {
                echo "<script>alert('নিবন্ধন সফল হয়েছে! অনুগ্রহ করে লগইন করুন'); window.location.href='../Html/index.php';</script>";
            } else {
                echo "SQL Error: " . $conn->error;
                // Remove uploaded image if DB error
                if (file_exists($imageFolder)) {
                    unlink($imageFolder);
                }
            }
        }

        $stmt->close();
    } else {
        echo "<script>alert('Error uploading image. Please try again.'); window.location.href='../Html/index.php';</script>";
    }
}
?>