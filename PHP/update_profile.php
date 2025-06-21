<?php
session_start();
include '../PHP/db_connect.php';

if (!isset($_SESSION['customer_email']) || !isset($_SESSION['customer_id'])) {
    echo json_encode(["success" => false, "message" => "You must log in first!"]);
    exit();
}

$email = $_SESSION['customer_email'];

// Validate profile form
if (
    isset($_POST['customerName'], $_POST['customerPhone'], $_POST['customerAddress'], $_POST['customerEmail'], $_POST['customerGender']) &&
    !empty($_POST['customerName']) &&
    !empty($_POST['customerPhone']) &&
    !empty($_POST['customerAddress']) &&
    !empty($_POST['customerEmail']) &&
    !empty($_POST['customerGender'])
) {
    $customerName = htmlspecialchars(trim($_POST['customerName']));
    $customerPhone = htmlspecialchars(trim($_POST['customerPhone']));
    $customerAddress = htmlspecialchars(trim($_POST['customerAddress']));
    $customerEmail = htmlspecialchars(trim($_POST['customerEmail']));
    $customerGender = htmlspecialchars(trim($_POST['customerGender']));

    // Validate email format
    if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Invalid email format!"]);
        exit();
    }

    // Optional: Check if email already exists
    $sql = "SELECT COUNT(*) FROM customers WHERE customer_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $customerEmail);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    if ($count > 0) {
        echo json_encode(["success" => false, "message" => "Email is already in use!"]);
        exit();
    }

    // Update customer info
    $sql = "UPDATE customers SET customer_name = ?, customer_phone = ?, customer_address = ?, customer_email = ?, customer_gender = ? WHERE customer_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $customerName, $customerPhone, $customerAddress, $customerEmail, $customerGender, $email);

    if ($stmt->execute()) {
        // Update session if email was changed
        $_SESSION['customer_email'] = $customerEmail;
    } else {
        echo json_encode(["success" => false, "message" => "Error updating profile: " . $stmt->error]);
        exit();
    }

    $stmt->close();
}

// Handle Profile Picture Upload (if present)
if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] == 0) {
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
    $uploadDir = '../uploads/';
    $ext = strtolower(pathinfo($_FILES['profilePic']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExts)) {
        echo json_encode(["success" => false, "message" => "Invalid image format!"]);
        exit();
    }

    if ($_FILES['profilePic']['size'] > 5 * 1024 * 1024) { // 5MB
        echo json_encode(["success" => false, "message" => "File is too large!"]);
        exit();
    }

    $newFileName = uniqid() . "." . $ext;
    $targetFile = $uploadDir . $newFileName;

    if (move_uploaded_file($_FILES['profilePic']['tmp_name'], $targetFile)) {
        $sql = "UPDATE customers SET profile_pic = ? WHERE customer_email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $newFileName, $customerEmail);
        $stmt->execute();
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Failed to upload image!"]);
        exit();
    }
}

// Final success message
echo json_encode(["success" => true, "message" => "Profile updated successfully!"]);
$conn->close();
?>
