<?php
include '../PHP/db_connect.php';
session_start();

// Universal highlight function
function highlight($text, $search) {
    if ($search === '') return htmlspecialchars($text);
    return preg_replace_callback(
        '/' . preg_quote($search, '/') . '/iu',
        function($matches) {
            return '<span class="highlight">'.$matches[0].'</span>';
        },
        htmlspecialchars($text)
    );
}

// Handle POST Requests (Delete or Warning)
$deleteMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Handle Customer Delete
    if (isset($_POST['delete_customer_id'])) {
        $id = intval($_POST['delete_customer_id']);
        $customer = $conn->query("SELECT customer_phone, customer_email FROM customers WHERE customer_id = $id")->fetch_assoc();
        if ($customer) {
            $conn->query("INSERT INTO banned_users (user_type, phone, email) VALUES ('customer', '{$customer['customer_phone']}', '{$customer['customer_email']}')");
        }
        $conn->query("DELETE FROM shop_reports WHERE customer_id = $id");
        $conn->query("DELETE FROM shop_reviews WHERE customer_id = $id");
        $conn->query("DELETE FROM orders WHERE customer_id = $id");
        $conn->query("DELETE FROM shop_followers WHERE customer_id = $id");
        $conn->query("DELETE FROM product_loves WHERE customer_id = $id");
        $conn->query("DELETE FROM messages WHERE sender_id = $id OR receiver_id = $id");
        $conn->query("DELETE FROM customers WHERE customer_id = $id");
        $deleteMsg = "Customer deleted!";
    }

    // 2. Handle Shop Owner Delete
    if (isset($_POST['delete_shop_owner_id'])) {
        $id = intval($_POST['delete_shop_owner_id']);
        $owner = $conn->query("SELECT shop_owner_phone, shop_owner_email FROM shop_owners WHERE shop_owner_id = $id")->fetch_assoc();
        if ($owner) {
            $conn->query("INSERT INTO banned_users (user_type, phone, email) VALUES ('shop_owner', '{$owner['shop_owner_phone']}', '{$owner['shop_owner_email']}')");
        }
        $conn->query("DELETE FROM shop_reports WHERE shop_owner_id = $id");
        $conn->query("DELETE FROM shop_reviews WHERE shop_owner_id = $id");
        $conn->query("DELETE FROM orders WHERE shop_owner_id = $id");
        $conn->query("DELETE FROM shop_followers WHERE shop_owner_id = $id");
        $conn->query("DELETE FROM products WHERE shop_owner_id = $id");
        $conn->query("DELETE FROM messages WHERE sender_id = $id OR receiver_id = $id");
        $conn->query("DELETE FROM shop_owners WHERE shop_owner_id = $id");
        $deleteMsg = "Shop Owner deleted!";
    }

    // 3. Handle Delivery Man Delete
    if (isset($_POST['delete_delivery_man_id'])) {
        $id = intval($_POST['delete_delivery_man_id']);
        $dm = $conn->query("SELECT delivery_man_phone, delivery_man_email FROM delivery_men WHERE delivery_man_id = $id")->fetch_assoc();
        if ($dm) {
            $conn->query("INSERT INTO banned_users (user_type, phone, email) VALUES ('delivery_man', '{$dm['delivery_man_phone']}', '{$dm['delivery_man_email']}')");
        }
        $conn->query("DELETE FROM delivery_men WHERE delivery_man_id = $id");
        $deleteMsg = "Delivery Man deleted!";
    }

    // 4. Handle Warning Action (for all user types)
    if (isset($_POST['user_type']) && isset($_POST['user_id'])) {
        $user_type = $_POST['user_type'];
        $user_id   = intval($_POST['user_id']);
        $name      = $_POST['name'];
        $phone     = $_POST['phone'];
        $email     = $_POST['email'];

$sms_message = "প্রিয় $name, আপনার কার্যকলাপের জন্য আপনাকে সতর্ক করা হলো। ভবিষ্যতে একই ধরনের কাজ পুনরাবৃত্তি হলে স্থায়ী ব্যান করা হতে পারে।
আপনার যদি কোনো প্রশ্ন বা অভিযোগ থাকে, দয়া করে আমাদের সাথে যোগাযোগ করুন: sohojjogan@gmail.com";

        $stmt = $conn->prepare("INSERT INTO warned_users (user_type, user_id, name, phone, email, reason) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissss", $user_type, $user_id, $name, $phone, $email, $sms_message);
        if ($stmt->execute()) {
            echo "<script>alert('সতর্কতা সংরক্ষিত হয়েছে!');window.history.back();</script>";
            exit;
        } else {
            echo "<script>alert('সতর্কতা সংরক্ষণ ব্যর্থ হয়েছে!');window.history.back();</script>";
            exit;
        }
    }
}
// Warned users fetch (for all user types)
$warned_user_ids = [
    'customer' => [],
    'shop_owner' => [],
    'delivery_man' => []
];
$result = $conn->query("SELECT user_type, user_id FROM warned_users");
while ($row = $result->fetch_assoc()) {
    $warned_user_ids[$row['user_type']][] = $row['user_id'];
}

// === SEARCH ===
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$safe = $conn->real_escape_string($search);

// Customers
$whereCustomer = '';
if ($search !== '') {
    $whereCustomer = "WHERE customer_name LIKE '%$safe%' OR customer_email LIKE '%$safe%' OR customer_phone LIKE '%$safe%'";
}
$customers = $conn->query("SELECT * FROM customers $whereCustomer")->fetch_all(MYSQLI_ASSOC);
$hasCustomer = !empty($customers);

// Shop Owners
$whereOwner = '';
if ($search !== '') {
    $whereOwner = "WHERE shop_owner_name LIKE '%$safe%' OR shop_name LIKE '%$safe%' OR shop_owner_email LIKE '%$safe%'";
}
$shopOwners = $conn->query("SELECT * FROM shop_owners $whereOwner")->fetch_all(MYSQLI_ASSOC);
$hasShopOwner = !empty($shopOwners);

// Delivery Men
$whereDelivery = '';
if ($search !== '') {
    $whereDelivery = "WHERE delivery_man_name LIKE '%$safe%' OR delivery_man_email LIKE '%$safe%' OR delivery_man_phone LIKE '%$safe%'";
}
$deliveryMen = $conn->query("SELECT * FROM delivery_men $whereDelivery")->fetch_all(MYSQLI_ASSOC);
$hasDeliveryMan = !empty($deliveryMen);

// Products (with shop name)
$whereProduct = '';
if ($search !== '') {
    $whereProduct = "WHERE p.product_name LIKE '%$safe%' OR s.shop_owner_name LIKE '%$safe%'";
}
$products = $conn->query("SELECT p.*, s.shop_name, s.shop_owner_name FROM products p LEFT JOIN shop_owners s ON p.shop_owner_id = s.shop_owner_id $whereProduct")->fetch_all(MYSQLI_ASSOC);
$hasProducts = !empty($products);

// Orders (with product name, shop owner name, customer name)
$whereOrder = '';
if ($search !== '') {
    $whereOrder = "WHERE o.order_id LIKE '%$safe%' OR p.product_name LIKE '%$safe%' OR c.customer_name LIKE '%$safe%' OR s.shop_owner_name LIKE '%$safe%'";
}
$orders = $conn->query("
    SELECT o.*, p.product_name, p.product_image_path, s.shop_owner_name, c.customer_name, c.profile_pic as customer_profile_pic
    FROM orders o
    LEFT JOIN products p ON o.product_id = p.product_id
    LEFT JOIN shop_owners s ON o.shop_owner_id = s.shop_owner_id
    LEFT JOIN customers c ON o.customer_id = c.customer_id
    $whereOrder
")->fetch_all(MYSQLI_ASSOC);
$hasOrders = !empty($orders);

// Shop Reports (with shop owner name)
$shopReports = [];
$hasShopReports = false;
if ($conn->query("SHOW TABLES LIKE 'shop_reports'")->num_rows) {
    $whereShopReports = "";
    if ($search !== '') {
        $whereShopReports = "WHERE r.customer_name LIKE '%$safe%' OR s.shop_owner_name LIKE '%$safe%' OR r.shop_name LIKE '%$safe%'";
    }
    $shopReports = $conn->query("SELECT r.*, s.shop_owner_name FROM shop_reports r LEFT JOIN shop_owners s ON r.shop_owner_id = s.shop_owner_id $whereShopReports ORDER BY r.report_id DESC")->fetch_all(MYSQLI_ASSOC);
    $hasShopReports = !empty($shopReports);
}

// Shop Reviews (with customer name, shop owner name)
$reviews = [];
$hasReviews = false;
if ($conn->query("SHOW TABLES LIKE 'shop_reviews'")->num_rows) {
    $whereReviews = "";
    if ($search !== '') {
        $whereReviews = "WHERE r.review_text LIKE '%$safe%' OR c.customer_name LIKE '%$safe%' OR s.shop_owner_name LIKE '%$safe%'";
    }
    $reviews = $conn->query("SELECT r.*, c.customer_name, s.shop_owner_name FROM shop_reviews r LEFT JOIN customers c ON r.customer_id = c.customer_id LEFT JOIN shop_owners s ON r.shop_owner_id = s.shop_owner_id $whereReviews")->fetch_all(MYSQLI_ASSOC);
    $hasReviews = !empty($reviews);
}

// Shop Followers (with customer name, shop owner name)
$shopFollowers = [];
$hasFollowers = false;
if ($conn->query("SHOW TABLES LIKE 'shop_followers'")->num_rows) {
    $whereFollowers = "";
    if ($search !== '') {
        $whereFollowers = "WHERE c.customer_name LIKE '%$safe%' OR s.shop_owner_name LIKE '%$safe%'";
    }
    $shopFollowers = $conn->query("
        SELECT sf.*, c.customer_name, s.shop_owner_name, c.profile_pic
        FROM shop_followers sf
        LEFT JOIN customers c ON sf.customer_id = c.customer_id
        LEFT JOIN shop_owners s ON sf.shop_owner_id = s.shop_owner_id
        $whereFollowers
    ")->fetch_all(MYSQLI_ASSOC);
    $hasFollowers = !empty($shopFollowers);
}

// Product Loves (with customer name, product name)
$productLoves = [];
$hasLoves = false;
if ($conn->query("SHOW TABLES LIKE 'product_loves'")->num_rows) {
    $whereLoves = "";
    if ($search !== '') {
        $whereLoves = "WHERE c.customer_name LIKE '%$safe%' OR p.product_name LIKE '%$safe%'";
    }
    $productLoves = $conn->query("
        SELECT pl.*, c.customer_name, p.product_name, c.profile_pic
        FROM product_loves pl
        LEFT JOIN customers c ON pl.customer_id = c.customer_id
        LEFT JOIN products p ON pl.product_id = p.product_id
        $whereLoves
    ")->fetch_all(MYSQLI_ASSOC);
    $hasLoves = !empty($productLoves);
}

// Payments (with customer name, product name)
$payments = [];
$hasPayments = false;
if ($conn->query("SHOW TABLES LIKE 'payments'")->num_rows) {
    $wherePayments = "";
    if ($search !== '') {
        $wherePayments = "WHERE c.customer_name LIKE '%$safe%' OR p.product_name LIKE '%$safe%'";
    }
    $payments = $conn->query("
        SELECT pay.*, o.customer_id, c.customer_name, o.product_id, p.product_name
        FROM payments pay
        LEFT JOIN orders o ON pay.order_id = o.order_id
        LEFT JOIN customers c ON o.customer_id = c.customer_id
        LEFT JOIN products p ON o.product_id = p.product_id
        $wherePayments
    ")->fetch_all(MYSQLI_ASSOC);
    $hasPayments = !empty($payments);
}

$conn->close();

// Determine which tab to show first if searching
$defaultTab = 'customerTab';
if ($search !== '') {
    if ($hasCustomer) $defaultTab = 'customerTab';
    elseif ($hasShopOwner) $defaultTab = 'shopOwnerTab';
    elseif ($hasDeliveryMan) $defaultTab = 'deliveryManTab';
    elseif ($hasProducts) $defaultTab = 'productsTab';
    elseif ($hasOrders) $defaultTab = 'orderTab';
    elseif ($hasShopReports) $defaultTab = 'reportTab';
    elseif ($hasReviews) $defaultTab = 'reviewTab';
    elseif ($hasMessages) $defaultTab = 'messagesTab';
    elseif ($hasFollowers) $defaultTab = 'followersTab';
    elseif ($hasLoves) $defaultTab = 'lovesTab';
    elseif ($hasPayments) $defaultTab = 'paymentsTab';
}


?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../CSS/AdminDashboard.css?v=1">
    <style>
    .report-img {
        height: 40px;
        width: auto;
        border-radius: 5px;
        border: 1px solid #ddd;
        object-fit: contain;
        margin: 2px 0;
    }
    .main-content {
        overflow-x: auto;
        overflow-y: auto;
        max-width: 100vw;
        max-height: 85vh;
        padding-bottom: 20px;
    }
    .main-content table {
        min-width: 900px;
        width: max-content;
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    .highlight {
    background: yellow;
    color: #000;
    font-weight: bold;
    border-radius: 2px;
    padding: 1px 2px;
}
    </style>
</head>
<body>
    <header>
        <div class="logo" id="logoClickable">
            <img src="../Images/Logo.png" alt="Sohaj Jogan Logo">
            <h2>সহজ যোগান</h2>
        </div>
        <script>
        document.getElementById('logoClickable').addEventListener('click', function() {
            location.reload();
        });
        </script>
        <div class="icons">
            <h1>অ্যাডমিন ড্যাশবোর্ড</h1>
        </div>
        <!-- Search Bar -->
        <div class="search-bar">
            <form method="get" action="" class="search-bar-form">
                <input type="text" name="search" placeholder="নাম লিখুন..." value="<?= htmlspecialchars($search) ?>">
                <button id="submit"><img src="../Images/search.png" alt="Search"></button>
            </form>
            </div>
        <button id="logoutIcon"><img src="../Images/logout.png" alt="User"></button>
    </header>
    <style>
        #logoutIcon {
  background: none;
  border: none;
  cursor: pointer;
  padding: 6px;
  border-radius: 8px;
 margin-right: 50px;
  transition: background-color 0.3s ease;
}

#logoutIcon img {
  width: 34px;
  height: 34px;
  display: block;
}

#logoutIcon:hover {
  background-color: #f2f2f2;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}
</style>
<script>
document.getElementById("logoutIcon").addEventListener("click", function(e) {
    e.preventDefault();
    window.location.href = "../PHP/logout.php";
});
</script>
    <div class="container">
         <nav class="sidebar">
            <h3>টেবিল</h3>
            <ul>
                <li class="<?= $defaultTab == 'customerTab' ? 'active' : '' ?>" data-tab="customerTab" <?= ($search !== '' && !$hasCustomer) ? 'style="display:none;"' : '' ?>><span class="icon">👤</span> <span class="name">কাস্টমার</span></li>
                <li class="<?= $defaultTab == 'shopOwnerTab' ? 'active' : '' ?>" data-tab="shopOwnerTab" <?= ($search !== '' && !$hasShopOwner) ? 'style="display:none;"' : '' ?>><span class="icon">🏪</span> <span class="name">দোকানদার</span></li>
                <li class="<?= $defaultTab == 'deliveryManTab' ? 'active' : '' ?>" data-tab="deliveryManTab" <?= ($search !== '' && !$hasDeliveryMan) ? 'style="display:none;"' : '' ?>><span class="icon">🚚</span> <span class="name">ডেলিভারি পার্সন</span></li>
                <li class="<?= $defaultTab == 'productsTab' ? 'active' : '' ?>" data-tab="productsTab" <?= ($search !== '' && !$hasProducts) ? 'style="display:none;"' : '' ?>><span class="icon">🛒</span> <span class="name">প্রোডাক্ট</span></li>
                <li class="<?= $defaultTab == 'orderTab' ? 'active' : '' ?>" data-tab="orderTab" <?= ($search !== '' && !$hasOrders) ? 'style="display:none;"' : '' ?>><span class="icon">📦</span> <span class="name">অর্ডার</span></li>
                <li class="<?= $defaultTab == 'reportTab' ? 'active' : '' ?>" data-tab="reportTab" <?= ($search !== '' && !$hasShopReports) ? 'style="display:none;"' : '' ?>><span class="icon">📊</span> <span class="name">রিপোর্ট</span></li>
                <li class="<?= $defaultTab == 'reviewTab' ? 'active' : '' ?>" data-tab="reviewTab" <?= ($search !== '' && !$hasReviews) ? 'style="display:none;"' : '' ?>><span class="icon">⭐</span> <span class="name">রিভিউ</span></li>
                <li class="<?= $defaultTab == 'followersTab' ? 'active' : '' ?>" data-tab="followersTab" <?= ($search !== '' && !$hasFollowers) ? 'style="display:none;"' : '' ?>><span class="icon">👥</span> <span class="name">ফলোকারীরা</span></li>
                <li class="<?= $defaultTab == 'lovesTab' ? 'active' : '' ?>" data-tab="lovesTab" <?= ($search !== '' && !$hasLoves) ? 'style="display:none;"' : '' ?>><span class="icon">❤️</span> <span class="name">পণ্য পছন্দ</span></li>
                <li class="<?= $defaultTab == 'paymentsTab' ? 'active' : '' ?>" data-tab="paymentsTab" <?= ($search !== '' && !$hasPayments) ? 'style="display:none;"' : '' ?>><span class="icon">💸</span> <span class="name">পেমেন্ট</span></li>
            </ul>
        </nav>
        <main class="main-content">
    <?php if ($deleteMsg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($deleteMsg) ?></div>
    <?php endif; ?>
   <!-- Customer Table -->
<div id="customerTab" class="tab-pane<?= $defaultTab == 'customerTab' ? ' active' : '' ?>" <?= ($search !== '' && !$hasCustomer) ? 'style="display:none;"' : '' ?>>
    <h2>সব কাস্টমার</h2> <br>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Gender</th><th>Address</th><th>Coin</th><th>Image</th><th>Created</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($customers)): foreach ($customers as $cus): ?>
            <tr>
                <td><?= $cus['customer_id'] ?></td>
                <td><?= highlight($cus['customer_name'], $search) ?></td>
                <td><?= highlight($cus['customer_email'], $search) ?></td>
                <td><?= highlight($cus['customer_phone'], $search) ?></td>
                <td><?= highlight($cus['customer_gender'], $search) ?></td>
                <td><?= highlight($cus['customer_address'], $search) ?></td>
                <td><?= $cus['customer_coins'] ?></td>
                <td>
                    <?php if (!empty($cus['profile_pic'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($cus['profile_pic']) ?>" alt="Customer" class="report-img">
                    <?php else: ?>নেই<?php endif; ?>
                </td>
                <td><?= $cus['created_at'] ?></td>
               <td>
                <!-- Delete Button -->
                <form method="post" style="display:inline;" onsubmit="return confirm('আপনি কি নিশ্চিত কাস্টমার ডিলিট করতে চান?');">
                    <input type="hidden" name="delete_customer_id" value="<?= $cus['customer_id'] ?>">
                    <button type="submit" class="delete-btn">Delete</button>
                </form>
                <!-- Warning Button -->
                <?php if (!in_array($cus['customer_id'], $warned_user_ids['customer'])): ?>
                    <form method="post" style="display:inline;" onsubmit="return confirm('আপনি কি নিশ্চিত সতর্ক করতে চান?');">
                        <input type="hidden" name="user_type" value="customer">
                        <input type="hidden" name="user_id" value="<?= $cus['customer_id'] ?>">
                        <input type="hidden" name="name" value="<?= htmlspecialchars($cus['customer_name']) ?>">
                        <input type="hidden" name="phone" value="<?= htmlspecialchars($cus['customer_phone']) ?>">
                        <input type="hidden" name="email" value="<?= htmlspecialchars($cus['customer_email']) ?>">
                        <button type="submit" class="warn-btn">সতর্কবার্তা

</button>
                    </form>
                <?php else: ?>
                    <span style="color:orange;">ইতিমধ্যেই সতর্ক করা হয়েছে

</span>
                <?php endif; ?>
            </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="10" style="text-align:center;">কাস্টমার নেই</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
  <!-- Shop Owner Table -->
<div id="shopOwnerTab" class="tab-pane<?= $defaultTab == 'shopOwnerTab' ? ' active' : '' ?>" <?= ($search !== '' && !$hasShopOwner) ? 'style="display:none;"' : '' ?>>
    <h2>সব দোকানদার</h2> <br>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Shop Name</th><th>Phone</th><th>Email</th><th>Gender</th><th>Address</th><th>Description</th>
                <th>NID</th><th>Owner Image</th><th>Shop Img</th>
                <th>Street</th><th>Area</th><th>City</th><th>Postcode</th><th>Division</th><th>Created</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($shopOwners)): foreach ($shopOwners as $so): ?>
            <tr>
                <td><?= $so['shop_owner_id'] ?></td>
                <td><?= highlight($so['shop_owner_name'], $search) ?></td>
                <td><?= highlight($so['shop_name'], $search) ?></td>
                <td><?= highlight($so['shop_owner_phone'], $search) ?></td>
                <td><?= highlight($so['shop_owner_email'], $search) ?></td>
                <td><?= highlight($so['shop_owner_gender'], $search) ?></td>
                <td><?= highlight($so['shop_owner_address'], $search) ?></td>
                <td><?= highlight($so['shop_description'], $search) ?></td>
                <td>
                    <?php if (!empty($so['shop_owner_nid_path'])): ?>
                        <a href="<?= htmlspecialchars($so['shop_owner_nid_path']) ?>" target="_blank">NID</a>
                    <?php else: ?>নেই<?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($so['shop_owner_image_path'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($so['shop_owner_image_path']) ?>" alt="Owner" class="report-img">
                    <?php else: ?>নেই<?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($so['shop_image_path'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($so['shop_image_path']) ?>" alt="Shop" class="report-img">
                    <?php else: ?>নেই<?php endif; ?>
                </td>
                <td><?= highlight($so['address_street'], $search) ?></td>
                <td><?= highlight($so['address_area'], $search) ?></td>
                <td><?= highlight($so['address_city'], $search) ?></td>
                <td><?= highlight($so['address_postcode'], $search) ?></td>
                <td><?= highlight($so['address_division'], $search) ?></td>
                <td><?= $so['created_at'] ?></td>
               <td>
                <!-- Delete Button -->
                <form method="post" style="display:inline;" onsubmit="return confirm('আপনি কি নিশ্চিত দোকানদার ডিলিট করতে চান?');">
                    <input type="hidden" name="delete_shop_owner_id" value="<?= $so['shop_owner_id'] ?>">
                    <button type="submit" class="delete-btn">Delete</button>
                </form>
                <!-- Warning Button -->
                <?php if (!in_array($so['shop_owner_id'], $warned_user_ids['shop_owner'])): ?>
                    <form method="post" style="display:inline;" onsubmit="return confirm('আপনি কি নিশ্চিত সতর্ক করতে চান?');">
                        <input type="hidden" name="user_type" value="shop_owner">
                        <input type="hidden" name="user_id" value="<?= $so['shop_owner_id'] ?>">
                        <input type="hidden" name="name" value="<?= htmlspecialchars($so['shop_owner_name']) ?>">
                        <input type="hidden" name="phone" value="<?= htmlspecialchars($so['shop_owner_phone']) ?>">
                        <input type="hidden" name="email" value="<?= htmlspecialchars($so['shop_owner_email']) ?>">
                        <button type="submit" class="warn-btn">সতর্কবার্তা

</button>
                    </form>
                <?php else: ?>
                    <span style="color:orange;">ইতিমধ্যেই সতর্ক করা হয়েছে

</span>
                <?php endif; ?>
            </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="18" style="text-align:center;">দোকানদার নেই</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
    <!-- Delivery Man Table -->
<div id="deliveryManTab" class="tab-pane<?= $defaultTab == 'deliveryManTab' ? ' active' : '' ?>" <?= ($search !== '' && !$hasDeliveryMan) ? 'style="display:none;"' : '' ?>>
    <h2>সব ডেলিভারি পার্সন</h2>
    <br>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>Gender</th><th>Address</th><th>NID</th><th>Image</th><th>Created</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($deliveryMen)): foreach ($deliveryMen as $d): ?>
            <tr>
                <td><?= $d['delivery_man_id'] ?></td>
                <td><?= highlight($d['delivery_man_name'], $search) ?></td>
                <td><?= highlight($d['delivery_man_phone'], $search) ?></td>
                <td><?= highlight($d['delivery_man_email'], $search) ?></td>
                <td><?= highlight($d['delivery_man_gender'], $search) ?></td>
                <td><?= highlight($d['delivery_man_address'], $search) ?></td>
                <td>
                    <?php if (!empty($d['delivery_man_nid_path'])): ?>
                        <a href="<?= htmlspecialchars($d['delivery_man_nid_path']) ?>" target="_blank">NID</a>
                    <?php else: ?>নেই<?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($d['delivery_man_image_path'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($d['delivery_man_image_path']) ?>" alt="Image" class="report-img">
                    <?php else: ?>নেই<?php endif; ?>
                </td>
                <td><?= $d['created_at'] ?></td>
              <td>
                <!-- Delete Button -->
                <form method="post" style="display:inline;" onsubmit="return confirm('আপনি কি নিশ্চিত ডেলিভারি পার্সন ডিলিট করতে চান?');">
                    <input type="hidden" name="delete_delivery_man_id" value="<?= $d['delivery_man_id'] ?>">
                    <button type="submit" class="delete-btn">Delete</button>
                </form>
                <!-- Warning Button -->
                <?php if (!in_array($d['delivery_man_id'], $warned_user_ids['delivery_man'])): ?>
                    <form method="post" style="display:inline;" onsubmit="return confirm('আপনি কি নিশ্চিত সতর্ক করতে চান?');">
                        <input type="hidden" name="user_type" value="delivery_man">
                        <input type="hidden" name="user_id" value="<?= $d['delivery_man_id'] ?>">
                        <input type="hidden" name="name" value="<?= htmlspecialchars($d['delivery_man_name']) ?>">
                        <input type="hidden" name="phone" value="<?= htmlspecialchars($d['delivery_man_phone']) ?>">
                        <input type="hidden" name="email" value="<?= htmlspecialchars($d['delivery_man_email']) ?>">
                        <button type="submit" class="warn-btn">সতর্কবার্তা

</button>
                    </form>
                <?php else: ?>
                    <span style="color:orange;">ইতিমধ্যেই সতর্ক করা হয়েছে

</span>
                <?php endif; ?>
            </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="10" style="text-align:center;">ডেলিভারি পার্সন নেই</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<!-- Products Table -->
<div id="productsTab" class="tab-pane<?= $defaultTab == 'productsTab' ? ' active' : '' ?>" <?= ($search !== '' && !$hasProducts) ? 'style="display:none;"' : '' ?>>
    <h2>সব প্রোডাক্ট</h2>    <br>

    <table>
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Shop Owner</th><th>Shop Name</th><th>Image</th><th>Stock</th><th>Price</th><th>Duration</th><th>Advertise Option</th><th>Advertise Text</th><th>Date Added</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($products)): foreach ($products as $p): ?>
            <tr>
                <td><?= $p['product_id'] ?></td>
                <td><?= highlight($p['product_name'], $search) ?></td>
                <td><?= highlight($p['shop_owner_name'], $search) ?></td>
                <td><?= highlight($p['shop_name'], $search) ?></td>
                <td>
                    <?php if (!empty($p['product_image_path'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($p['product_image_path']) ?>" alt="Product" class="report-img">
                    <?php else: ?>নেই<?php endif; ?>
                </td>
                <td><?= $p['stock'] ?></td>
                <td><?= $p['price'] ?></td>
                <td><?= highlight($p['duration'], $search) ?></td>
                <td><?= highlight($p['advertise_option'], $search) ?></td>
                <td><?= highlight($p['advertise_text'], $search) ?></td>
                <td><?= $p['date_added'] ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="11" style="text-align:center;">প্রোডাক্ট নেই</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
   <!-- Orders Table -->
<div id="orderTab" class="tab-pane<?= $defaultTab == 'orderTab' ? ' active' : '' ?>" <?= ($search !== '' && !$hasOrders) ? 'style="display:none;"' : '' ?>>
    <h2>সব অর্ডার</h2>    <br>

    <table>
        <thead>
            <tr>
                <th>Order ID</th><th>Product Name</th><th>Shop Owner</th><th>Customer</th><th>Quantity</th>
                <th>Delivery Method</th><th>Customer Address</th><th>Customer Phone</th><th>Comment</th>
                <th>Distance</th><th>Delivery Charge</th><th>Order Time</th><th>Status</th><th>Product Img</th><th>Customer Img</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($orders)): foreach ($orders as $o): ?>
            <tr>
                <td><?= highlight($o['order_id'], $search) ?></td>
                <td><?= highlight($o['product_name'], $search) ?></td>
                <td><?= highlight($o['shop_owner_name'], $search) ?></td>
                <td><?= highlight($o['customer_name'], $search) ?></td>
                <td><?= $o['quantity'] ?></td>
                <td><?= highlight($o['delivery_method'], $search) ?></td>
                <td><?= highlight($o['customer_address'], $search) ?></td>
                <td><?= highlight($o['customer_phone'], $search) ?></td>
                <td><?= highlight($o['customer_comment'], $search) ?></td>
                <td><?= $o['distance'] ?></td>
                <td><?= $o['delivery_charge'] ?></td>
                <td><?= $o['order_time'] ?></td>
                <td><?= $o['status'] ?></td>
                <td>
                    <?php if (!empty($o['product_image_path'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($o['product_image_path']) ?>" alt="Product" class="report-img">
                    <?php else: ?>নেই<?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($o['customer_profile_pic'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($o['customer_profile_pic']) ?>" alt="Customer" class="report-img">
                    <?php else: ?>নেই<?php endif; ?>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="15" style="text-align:center;">অর্ডার নেই</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<!-- Shop Reports Table -->
<div id="reportTab" class="tab-pane<?= $defaultTab == 'reportTab' ? ' active' : '' ?>" <?= ($search !== '' && !$hasShopReports) ? 'style="display:none;"' : '' ?>>
    <h2>দোকানের বিরুদ্ধে রিপোর্টসমূহ</h2>    <br>

    <table>
        <thead>
            <tr>
                <th>ID</th><th>Shop Name</th><th>Shop Owner</th><th>Customer Name</th><th>Email</th><th>Phone</th><th>Description</th><th>Image</th><th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($shopReports)): foreach ($shopReports as $r): ?>
            <tr>
                <td><?= highlight($r['report_id'], $search) ?></td>
                <td><?= highlight($r['shop_name'], $search) ?></td>
                <td><?= highlight($r['shop_owner_name'], $search) ?></td>
                <td><?= highlight($r['customer_name'], $search) ?></td>
                <td><?= highlight($r['customer_email'], $search) ?></td>
                <td><?= highlight($r['customer_phone'], $search) ?></td>
                <td><?= highlight($r['description'], $search) ?></td>
                <td>
                    <?php if (!empty($r['image_path'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($r['image_path']) ?>" alt="Report Image" class="report-img">
                    <?php else: ?>নেই<?php endif; ?>
                </td>
                <td><?= $r['created_at'] ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="9" style="text-align:center;">রিপোর্ট নেই</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<!-- Shop Reviews Table -->
<div id="reviewTab" class="tab-pane<?= $defaultTab == 'reviewTab' ? ' active' : '' ?>" <?= ($search !== '' && !$hasReviews) ? 'style="display:none;"' : '' ?>>
    <h2>সব রিভিউ</h2>    <br>

    <table>
        <thead>
            <tr>
                <th>ID</th><th>Shop Owner</th><th>Customer</th><th>Review</th><th>Rating</th><th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($reviews)): foreach ($reviews as $rv): ?>
            <tr>
                <td><?= highlight($rv['review_id'], $search) ?></td>
                <td><?= highlight($rv['shop_owner_name'], $search) ?></td>
                <td><?= highlight($rv['customer_name'], $search) ?></td>
                <td><?= highlight($rv['review_text'], $search) ?></td>
                <td><?= $rv['rating'] ?></td>
                <td><?= $rv['created_at'] ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="6" style="text-align:center;">রিভিউ নেই</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
   
    <!-- Shop Followers Table -->
<div id="followersTab" class="tab-pane<?= $defaultTab == 'followersTab' ? ' active' : '' ?>" <?= ($search !== '' && !$hasFollowers) ? 'style="display:none;"' : '' ?>>
    <h2>দোকানের ফলোয়ার</h2>    <br>

    <table>
        <thead>
            <tr>
                <th>Shop Owner</th><th>Customer</th><th>Customer Image</th><th>Followed At</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($shopFollowers)): foreach ($shopFollowers as $sf): ?>
            <tr>
                <td><?= highlight($sf['shop_owner_name'], $search) ?></td>
                <td><?= highlight($sf['customer_name'], $search) ?></td>
                <td>
                    <?php if (!empty($sf['profile_pic'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($sf['profile_pic']) ?>" alt="Customer" class="report-img">
                    <?php else: ?>নেই<?php endif; ?>
                </td>
                <td><?= $sf['followed_at'] ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="4" style="text-align:center;">কেউ Follow করেনি</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<!-- Product Loves Table -->
<div id="lovesTab" class="tab-pane<?= $defaultTab == 'lovesTab' ? ' active' : '' ?>" <?= ($search !== '' && !$hasLoves) ? 'style="display:none;"' : '' ?>>
    <h2>পণ্য পছন্দ</h2>    <br>

    <table>
        <thead>
            <tr>
                <th>Product</th><th>Customer</th><th>Customer Image</th><th>Loved At</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($productLoves)): foreach ($productLoves as $pl): ?>
            <tr>
                <td><?= highlight($pl['product_name'], $search) ?></td>
                <td><?= highlight($pl['customer_name'], $search) ?></td>
                <td>
                    <?php if (!empty($pl['profile_pic'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($pl['profile_pic']) ?>" alt="Customer" class="report-img">
                    <?php else: ?>নেই<?php endif; ?>
                </td>
                <td><?= $pl['loved_at'] ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="4" style="text-align:center;">কেউ Love দেয়নি</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<!-- Payments Table -->
<div id="paymentsTab" class="tab-pane<?= $defaultTab == 'paymentsTab' ? ' active' : '' ?>" <?= ($search !== '' && !$hasPayments) ? 'style="display:none;"' : '' ?>>
    <h2>পেমেন্ট</h2> <br>
    <table>
        <thead>
            <tr>
                <th>Payment ID</th><th>Order ID</th><th>Customer</th><th>Product</th><th>Method</th><th>Bkash TxID</th><th>Amount</th><th>Status</th><th>Payment Time</th><th>Delivery Confirm Code</th><th>Delivery Confirm Time</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($payments)): foreach ($payments as $pay): ?>
            <tr>
                <td><?= highlight($pay['payment_id'], $search) ?></td>
                <td><?= highlight($pay['order_id'], $search) ?></td>
                <td><?= highlight($pay['customer_name'], $search) ?></td>
                <td><?= highlight($pay['product_name'], $search) ?></td>
                <td><?= highlight($pay['payment_method'], $search) ?></td>
                <td><?= highlight($pay['bkash_txid'], $search) ?></td>
                <td><?= $pay['amount'] ?></td>
                <td><?= highlight($pay['payment_status'], $search) ?></td>
                <td><?= $pay['payment_time'] ?></td>
                <td><?= highlight($pay['delivery_confirm_code'], $search) ?></td>
                <td><?= $pay['delivery_confirm_time'] ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="11" style="text-align:center;">Payments নেই</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</main>
    </div>
   <script>
    // Sidebar Tab Switcher
    document.querySelectorAll('.sidebar li').forEach(li => {
        li.addEventListener('click', function() {
            document.querySelectorAll('.sidebar li').forEach(x => x.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(tab => tab.classList.remove('active'));
            li.classList.add('active');
            document.getElementById(li.dataset.tab).classList.add('active');
        });
    });
    function confirmDelete(msg) { return confirm(msg);}
    </script>
</body>
</html>