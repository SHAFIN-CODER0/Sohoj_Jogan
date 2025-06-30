<?php
session_start();
include '../PHP/db_connect.php';

// Delivery man info from URL
$deliveryManId = isset($_GET['delivery_man_id']) ? intval($_GET['delivery_man_id']) : '';
$deliveryManName = isset($_GET['delivery_man_name']) ? htmlspecialchars($_GET['delivery_man_name']) : '';

// Customer info (if logged in)
$customerName = '';
$customerEmail = '';
$customerPhone = '';
$isCustomer = false;
$successMsg = '';
$errorMsg = '';

if (isset($_SESSION['customer_email'])) {
    $email = $_SESSION['customer_email'];
    $sql = "SELECT customer_id, customer_name, customer_email, customer_phone FROM customers WHERE customer_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $customerId = $row['customer_id'];
        $customerName = $row['customer_name'];
        $customerEmail = $row['customer_email'];
        $customerPhone = $row['customer_phone'];
        $isCustomer = true;
    }
    $stmt->close();
}

// Handle form submission
$redirect = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && $isCustomer && $deliveryManName) {
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $deliveryManId = isset($_POST['delivery_man_id']) ? intval($_POST['delivery_man_id']) : 0;
    $deliveryManName = isset($_POST['delivery_man_name']) ? trim($_POST['delivery_man_name']) : '';
    $imagePath = null;

    // Handle image upload
    if (isset($_FILES['delivery-image']) && $_FILES['delivery-image']['error'] == UPLOAD_ERR_OK) {
        $targetDir = "../uploads/delivery_reports/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = uniqid('report_') . '_' . basename($_FILES['delivery-image']['name']);
        $targetFile = $targetDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $check = getimagesize($_FILES['delivery-image']['tmp_name']);
        if ($check !== false && ($_FILES['delivery-image']['size'] <= 2*1024*1024) && ($imageFileType == "jpg" || $imageFileType == "jpeg" || $imageFileType == "png")) {
            if (move_uploaded_file($_FILES['delivery-image']['tmp_name'], $targetFile)) {
                $imagePath = $targetFile;
            }
        }
    }

    // Insert into database (deliveryman_reports table)
    $insertSql = "INSERT INTO deliveryman_reports (delivery_man_id, customer_id, delivery_man_name, customer_name, customer_email, customer_phone, description, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("iissssss", $deliveryManId, $customerId, $deliveryManName, $customerName, $customerEmail, $customerPhone, $description, $imagePath);
    if ($insertStmt->execute()) {
        $successMsg = "আপনার রিপোর্ট সফলভাবে জমা হয়েছে! কিছুক্ষণের মধ্যে কাস্টমার হোম পেজে চলে যাবে...";
        $redirect = true;
    } else {
        $errorMsg = "রিপোর্ট জমা হয়নি, আবার চেষ্টা করুন।";
    }
    $insertStmt->close();
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../CSS/report.css">
     <?php if ($redirect): ?>
    <script>
        // 2.5 seconds later, redirect to customer_Home.php
        setTimeout(function() {
            window.location.href = "../Html/customer_Home.php";
        }, 2500);
    </script>
    <?php endif; ?>
</head>
<body>
<header class="site-header">
    <div class="logo">
        <img src="../Images/Logo.png" alt="Sohaj Jogan Logo">
        <h2>সহজ যোগান</h2>
    </div>
</header>
<main>
    <section class="report-section">
        <div class="report-text">
            <h2>"ডেলিভারিম্যানের বিরুদ্ধে অভিযোগ জমা দিন"</h2>
            <div class="report-info">
                <p>আপনার যদি কোনো ডেলিভারিম্যানের বিরুদ্ধে অভিযোগ থাকে, তাহলে দয়া করে নিচের ফর্মটি পূরণ করুন।</p>
                <p>আমরা আপনার রিপোর্টের ভিত্তিতে প্রয়োজনীয় ব্যবস্থা গ্রহণ করব।</p>
                <p>আপনার রিপোর্টের গোপনীয়তা আমাদের কাছে গুরুত্বপূর্ণ।</p>
            </div>
        </div>
        <div class="report-image">
            <img src="../Images/report_data.jpg" alt="রিপোর্ট ব্যানার ছবি">
        </div>
    </section>

    <section class="report-form-section">
    <?php if ($successMsg): ?>
        <div style="color:green; margin-bottom:20px; font-size:18px;">
            <?= htmlspecialchars($successMsg); ?>
        </div>
    <?php elseif ($errorMsg): ?>
        <div style="color:red; margin-bottom:20px;">
            <?= htmlspecialchars($errorMsg); ?>
        </div>
    <?php endif; ?>

    <?php if (!$isCustomer): ?>
        <div style="color:red; margin-bottom:20px;">
            রিপোর্ট করতে হলে <a href="../Html/login.html">লগইন করুন</a>।
        </div>
    <?php elseif (!$deliveryManName): ?>
        <div style="color:red; margin-bottom:20px;">
            ডেলিভারিম্যানের নাম পাওয়া যায়নি। দয়া করে আবার চেষ্টা করুন।
        </div>
    <?php endif; ?>

    <?php if (!$successMsg): ?>
    <form method="POST" enctype="multipart/form-data" class="report-form" 
        <?= (!$isCustomer || !$deliveryManName) ? 'onsubmit="return false;"' : '' ?>>
        <fieldset <?= (!$isCustomer || !$deliveryManName) ? 'disabled' : '' ?>>
            <legend>রিপোর্ট ফর্ম</legend>

            <!-- Hidden delivery_man_id -->
            <input type="hidden" name="delivery_man_id" value="<?= (int)$deliveryManId; ?>">

            <!-- Delivery Man Name -->
            <div class="form-group">
                <label for="delivery_man_name">ডেলিভারিম্যানের নাম</label>
                <input type="text" id="delivery_man_name" name="delivery_man_name" 
                    value="<?= htmlspecialchars($deliveryManName); ?>" readonly required>
            </div>

            <!-- Customer Name -->
            <div class="form-group">
                <label for="customer_name">আপনার নাম</label>
                <input type="text" id="customer_name" name="customer_name" 
                    value="<?= htmlspecialchars($customerName); ?>" readonly required>
            </div>

            <!-- Customer Email -->
            <div class="form-group">
                <label for="customer_email">আপনার ইমেইল</label>
                <input type="email" id="customer_email" name="customer_email" 
                    value="<?= htmlspecialchars($customerEmail); ?>" readonly required>
            </div>

            <!-- Customer Phone -->
            <div class="form-group">
                <label for="customer_phone">আপনার ফোন নম্বর</label>
                <input type="text" id="customer_phone" name="customer_phone" 
                    value="<?= htmlspecialchars($customerPhone); ?>" readonly required>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="description">সমস্যার বিবরণ</label>
                <textarea id="description" name="description" rows="4" required placeholder="সমস্যার বিস্তারিত লিখুন..."></textarea>
            </div>

            <!-- Upload Image -->
            <div class="form-group">
                <label for="delivery_image">প্রমাণ হিসেবে ছবি আপলোড করুন</label>
                <input type="file" id="delivery_image" name="delivery_image" accept="image/png, image/jpeg">
                <small>(সর্বোচ্চ ২MB, JPG/PNG ফরম্যাট)</small>
            </div>

            <div class="form-note">
                <p>আমাদের টিম দ্রুত আপনার রিপোর্ট পর্যালোচনা করে প্রয়োজনীয় ব্যবস্থা নেবে।</p>
                <h3>আপনার সহায়তা আমাদের জন্য অমূল্য!</h3>
            </div>

            <div class="form-action">
                <button type="submit" class="cta-btn report-btn" aria-label="রিপোর্ট সাবমিট করুন">রিপোর্ট করুন</button>
            </div>
        </fieldset>
    </form>
    <?php endif; ?>
</section>
</main>
</body>
</html>