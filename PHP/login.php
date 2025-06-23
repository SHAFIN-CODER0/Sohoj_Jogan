<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emailOrPhone = trim($_POST['emailOrPhone']);
    $password = trim($_POST['password']);
    $userType = $_POST['userType']; // 'customer', 'shop_owner', 'delivery_man', 'admin'

    if (empty($emailOrPhone) || empty($password) || empty($userType)) {
        echo "<script>alert('ইমেইল বা ফোন নম্বর, পাসওয়ার্ড এবং পরিচয় প্রয়োজন!'); window.location.href='../Html/index.php';</script>";
        exit();
    }

    // Admin login logic
    if ($userType === 'admin') {
        $adminEmail = "mnajmulhossainnur@gmail.com";
        $adminPhone = "01743094595";
        $adminPassword = "12345678";

        if (
            ($emailOrPhone === $adminEmail || $emailOrPhone === $adminPhone) &&
            $password === $adminPassword
        ) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_name'] = "Admin";
            $_SESSION['admin_email'] = $adminEmail;
            echo "<script>window.location.href='../Html/Admin.php';</script>";
            exit();
        } else {
            echo "<script>alert('ভুল অ্যাডমিন তথ্য!'); window.location.href='../Html/index.php';</script>";
            exit();
        }
    }

    // Choose the table and redirect page based on user type
    if ($userType === 'customer') {
        $sql = "SELECT * FROM customers WHERE customer_email = ? OR customer_phone = ?";
    } elseif ($userType === 'shop_owner') {
        $sql = "SELECT * FROM shop_owners WHERE shop_owner_email = ? OR shop_owner_phone = ?";
    } elseif ($userType === 'delivery_man') {
        $sql = "SELECT * FROM delivery_men WHERE delivery_man_email = ? OR delivery_man_phone = ?";
    } else {
        echo "<script>alert('অবৈধ ব্যবহারকারীর ধরন!'); window.location.href='../Html/index.php';</script>";
        exit();
    }

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ss", $emailOrPhone, $emailOrPhone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Get hashed password field by user type
        if ($userType === 'customer') {
            $hashedPassword = $row['customer_password'];
        } elseif ($userType === 'shop_owner') {
            $hashedPassword = $row['shop_owner_password'];
        } else {
            $hashedPassword = $row['delivery_man_password'];
        }

        if (password_verify($password, $hashedPassword)) {
            // Set session and redirect based on type
            if ($userType === 'customer') {
                $_SESSION['customer_id'] = $row['customer_id'];
                $_SESSION['customer_name'] = $row['customer_name'];
                $_SESSION['customer_email'] = $row['customer_email'];
                echo "<script>window.location.href='../Html/Customer_Home.php';</script>";
            } elseif ($userType === 'shop_owner') {
                $_SESSION['shop_owner_id'] = $row['shop_owner_id'];
                $_SESSION['shop_owner_name'] = $row['shop_owner_name'];
                $_SESSION['shop_owner_email'] = $row['shop_owner_email'];
                echo "<script>window.location.href='../Html/ShopOwner_Home.php';</script>";
            } else {
                $_SESSION['delivery_man_id'] = $row['delivery_man_id'];
                $_SESSION['delivery_man_name'] = $row['delivery_man_name'];
                $_SESSION['delivery_man_email'] = $row['delivery_man_email'];
                echo "<script>window.location.href='../Html/DeliveryMan_Home.php';</script>";
            }
            exit();
        } else {
            echo "<script>alert('ভুল পাসওয়ার্ড!'); window.location.href='../Html/index.php';</script>";
        }
    } else {
        echo "<script>alert('ব্যবহারকারী খুঁজে পাওয়া যায়নি!'); window.location.href='../Html/index.php';</script>";
    }

    $stmt->close();
}
?>