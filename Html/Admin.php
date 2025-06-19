  <?php
include '../PHP/db_connect.php';
session_start();

// Handle Delete Requests
$deleteMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete customer and all related rows
    if (isset($_POST['delete_customer_id'])) {
        $id = intval($_POST['delete_customer_id']);
        $conn->query("DELETE FROM shop_reports WHERE customer_id = $id");
        $conn->query("DELETE FROM shop_reviews WHERE customer_id = $id");
        $conn->query("DELETE FROM orders WHERE customer_id = $id");
        $conn->query("DELETE FROM shop_followers WHERE customer_id = $id");
        $conn->query("DELETE FROM product_loves WHERE customer_id = $id");
        $conn->query("DELETE FROM messages WHERE sender_id = $id OR receiver_id = $id");
        $conn->query("DELETE FROM customers WHERE customer_id = $id");
        $deleteMsg = "Customer deleted!";
    }
    // Delete shop owner and all related rows
    if (isset($_POST['delete_shop_owner_id'])) {
        $id = intval($_POST['delete_shop_owner_id']);
        $conn->query("DELETE FROM shop_reports WHERE shop_owner_id = $id");
        $conn->query("DELETE FROM shop_reviews WHERE shop_owner_id = $id");
        $conn->query("DELETE FROM orders WHERE shop_owner_id = $id");
        $conn->query("DELETE FROM shop_followers WHERE shop_owner_id = $id");
        $conn->query("DELETE FROM products WHERE shop_owner_id = $id");
        $conn->query("DELETE FROM messages WHERE sender_id = $id OR receiver_id = $id");
        $conn->query("DELETE FROM shop_owners WHERE shop_owner_id = $id");
        $deleteMsg = "Shop Owner deleted!";
    }
    // Delete delivery man (add more if there are child tables)
    if (isset($_POST['delete_delivery_man_id'])) {
        $id = intval($_POST['delete_delivery_man_id']);
        // If you have other tables referencing delivery_man_id, delete those first too!
        $conn->query("DELETE FROM delivery_men WHERE delivery_man_id = $id");
        $deleteMsg = "Delivery Man deleted!";
    }
}

// Fetch all columns except password fields
$customers = $conn->query("SELECT customer_id, customer_name, customer_phone, customer_gender, customer_address, customer_email, customer_coins, created_at FROM customers")->fetch_all(MYSQLI_ASSOC);

$shopOwners = $conn->query("SELECT shop_owner_id, shop_owner_name, shop_owner_phone, shop_owner_email, shop_owner_gender, shop_owner_address, shop_description, shop_owner_nid_path, shop_owner_image_path, shop_image_path, shop_name, address_street, address_area, address_city, address_postcode, address_division, created_at FROM shop_owners")->fetch_all(MYSQLI_ASSOC);

$deliveryMen = $conn->query("SELECT delivery_man_id, delivery_man_name, delivery_man_phone, delivery_man_email, delivery_man_gender, delivery_man_address, delivery_man_nid_path, delivery_man_image_path, created_at FROM delivery_men")->fetch_all(MYSQLI_ASSOC);

$products = $conn->query("SELECT * FROM products")->fetch_all(MYSQLI_ASSOC);

$orders = $conn->query("SELECT * FROM orders")->fetch_all(MYSQLI_ASSOC);

$shopReports = [];
if ($conn->query("SHOW TABLES LIKE 'shop_reports'")->num_rows) {
    $shopReports = $conn->query("SELECT * FROM shop_reports ORDER BY report_id DESC")->fetch_all(MYSQLI_ASSOC);
}

$reviews = [];
if ($conn->query("SHOW TABLES LIKE 'shop_reviews'")->num_rows) {
    $reviews = $conn->query("SELECT * FROM shop_reviews")->fetch_all(MYSQLI_ASSOC);
}

$messages = [];
if ($conn->query("SHOW TABLES LIKE 'messages'")->num_rows) {
    $messages = $conn->query("SELECT * FROM messages ORDER BY sent_at DESC")->fetch_all(MYSQLI_ASSOC);
}

$shopFollowers = [];
if ($conn->query("SHOW TABLES LIKE 'shop_followers'")->num_rows) {
    $shopFollowers = $conn->query("SELECT * FROM shop_followers")->fetch_all(MYSQLI_ASSOC);
}

$productLoves = [];
if ($conn->query("SHOW TABLES LIKE 'product_loves'")->num_rows) {
    $productLoves = $conn->query("SELECT * FROM product_loves")->fetch_all(MYSQLI_ASSOC);
}

$payments = [];
if ($conn->query("SHOW TABLES LIKE 'payments'")->num_rows) {
    $payments = $conn->query("SELECT * FROM payments")->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../CSS/AdminDashboard.css?v=1.0">
</head>
<body>
    <header>
        <div class="logo" id="logoClickable">
            <img src="../Images/Logo.png" alt="Sohaj Jogan Logo">
            <h2>সহজ যোগান</h2>
        </div>
        <script>// Put this at the end of your HTML or in a JS file
document.getElementById('logoClickable').addEventListener('click', function() {
    location.reload();
});</script>
        <div class="icons">
            <h1>অ্যাডমিন ড্যাশবোর্ড</h1>
            
        </div>
        <!-- Product Search Bar (Form) -->
        <div class="search-bar">
            <form method="get" action="" class="search-bar-form">
                <input type="text" name="search" placeholder="নাম লিখুন..." value="" required>
                <button id="submit"><img src="../Images/search.png" alt="Search"></button>
            </form>
        </div>
        
    </header>
    <div class="container">
        <nav class="sidebar">
            <h3>টেবিল</h3>
            <ul>
                <li class="active" data-tab="customerTab"><span class="icon">👤</span> <span class="name">কাস্টমার</span></li>
                <li data-tab="shopOwnerTab"><span class="icon">🏪</span> <span class="name">দোকানদার</span></li>
                <li data-tab="deliveryManTab"><span class="icon">🚚</span> <span class="name">ডেলিভারি পার্সন</span></li>
                <li data-tab="productsTab"><span class="icon">🛒</span> <span class="name">প্রোডাক্ট</span></li>
                <li data-tab="orderTab"><span class="icon">📦</span> <span class="name">অর্ডার</span></li>
                <li data-tab="reportTab"><span class="icon">📊</span> <span class="name">রিপোর্ট</span></li>
                <li data-tab="reviewTab"><span class="icon">⭐</span> <span class="name">রিভিউ</span></li>
                <li data-tab="messagesTab"><span class="icon">💬</span> <span class="name">মেসেজ</span></li>
                <li data-tab="followersTab"><span class="icon">👥</span> <span class="name">Followers</span></li>
                <li data-tab="lovesTab"><span class="icon">❤️</span> <span class="name">Product Loves</span></li>
                <li data-tab="paymentsTab"><span class="icon">💸</span> <span class="name">Payments</span></li>
            </ul>
        </nav>
        <main class="main-content">
             <?php if ($deleteMsg): ?>
      <div class="alert alert-success"><?= htmlspecialchars($deleteMsg) ?></div>
  <?php endif; ?>
            <!-- Customer Table -->
            <div id="customerTab" class="tab-pane active">
                <h2>সব কাস্টমার</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th><th>নাম</th><th>ইমেইল</th><th>ফোন</th><th>লিঙ্গ</th><th>ঠিকানা</th><th>কয়েন</th><th>তৈরি</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($customers)): foreach ($customers as $cus): ?>
                        <tr>
                            <td><?= $cus['customer_id'] ?></td>
                            <td><?= htmlspecialchars($cus['customer_name']) ?></td>
                            <td><?= htmlspecialchars($cus['customer_email']) ?></td>
                            <td><?= htmlspecialchars($cus['customer_phone']) ?></td>
                            <td><?= htmlspecialchars($cus['customer_gender']) ?></td>
                            <td><?= htmlspecialchars($cus['customer_address']) ?></td>
                            <td><?= $cus['customer_coins'] ?></td>
                            <td><?= $cus['created_at'] ?></td>
                            <td>
                                <form method="post" style="display:inline;" onsubmit="return confirmDelete('আপনি কি নিশ্চিত কাস্টমার ডিলিট করতে চান?');">
                                    <input type="hidden" name="delete_customer_id" value="<?= $cus['customer_id'] ?>">
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                                <button type="button" class="warn-btn" onclick="alert('⚠️ ডিলিট করলে সংশ্লিষ্ট কাস্টমারের সব তথ্য চিরতরে মুছে যাবে!');">Warning</button>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="9" style="text-align:center;">কাস্টমার নেই</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Shop Owner Table -->
            <div id="shopOwnerTab" class="tab-pane">
                <h2>সব দোকানদার</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th><th>নাম</th><th>Shop Name</th><th>Phone</th><th>Email</th><th>Gender</th><th>Address</th><th>Description</th><th>NID</th><th>Owner Img</th><th>Shop Img</th><th>Street</th><th>Area</th><th>City</th><th>Postcode</th><th>Division</th><th>Created</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($shopOwners)): foreach ($shopOwners as $so): ?>
                        <tr>
                            <td><?= $so['shop_owner_id'] ?></td>
                            <td><?= htmlspecialchars($so['shop_owner_name']) ?></td>
                            <td><?= htmlspecialchars($so['shop_name']) ?></td>
                            <td><?= htmlspecialchars($so['shop_owner_phone']) ?></td>
                            <td><?= htmlspecialchars($so['shop_owner_email']) ?></td>
                            <td><?= htmlspecialchars($so['shop_owner_gender']) ?></td>
                            <td><?= htmlspecialchars($so['shop_owner_address']) ?></td>
                            <td><?= htmlspecialchars($so['shop_description']) ?></td>
                            <td>
                                <?php if (!empty($so['shop_owner_nid_path'])): ?>
                                    <a href="<?= htmlspecialchars($so['shop_owner_nid_path']) ?>" target="_blank">NID</a>
                                <?php else: ?>নেই<?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($so['shop_owner_image_path'])): ?>
                                    <img src="<?= htmlspecialchars($so['shop_owner_image_path']) ?>" alt="Owner" class="report-img">
                                <?php else: ?>নেই<?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($so['shop_image_path'])): ?>
                                    <img src="<?= htmlspecialchars($so['shop_image_path']) ?>" alt="Shop" class="report-img">
                                <?php else: ?>নেই<?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($so['address_street']) ?></td>
                            <td><?= htmlspecialchars($so['address_area']) ?></td>
                            <td><?= htmlspecialchars($so['address_city']) ?></td>
                            <td><?= htmlspecialchars($so['address_postcode']) ?></td>
                            <td><?= htmlspecialchars($so['address_division']) ?></td>
                            <td><?= $so['created_at'] ?></td>
                            <td>
                                <form method="post" style="display:inline;" onsubmit="return confirmDelete('আপনি কি নিশ্চিত দোকানদার ডিলিট করতে চান?');">
                                    <input type="hidden" name="delete_shop_owner_id" value="<?= $so['shop_owner_id'] ?>">
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                                <button type="button" class="warn-btn" onclick="alert('⚠️ ডিলিট করলে সংশ্লিষ্ট দোকানদারের সব তথ্য চিরতরে মুছে যাবে!');">Warning</button>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="18" style="text-align:center;">দোকানদার নেই</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Delivery Man Table -->
            <div id="deliveryManTab" class="tab-pane">
                <h2>সব ডেলিভারি পার্সন</h2>
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
                            <td><?= htmlspecialchars($d['delivery_man_name']) ?></td>
                            <td><?= htmlspecialchars($d['delivery_man_phone']) ?></td>
                            <td><?= htmlspecialchars($d['delivery_man_email']) ?></td>
                            <td><?= htmlspecialchars($d['delivery_man_gender']) ?></td>
                            <td><?= htmlspecialchars($d['delivery_man_address']) ?></td>
                            <td>
                                <?php if (!empty($d['delivery_man_nid_path'])): ?>
                                    <a href="<?= htmlspecialchars($d['delivery_man_nid_path']) ?>" target="_blank">NID</a>
                                <?php else: ?>নেই<?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($d['delivery_man_image_path'])): ?>
                                    <img src="<?= htmlspecialchars($d['delivery_man_image_path']) ?>" alt="Image" class="report-img">
                                <?php else: ?>নেই<?php endif; ?>
                            </td>
                            <td><?= $d['created_at'] ?></td>
                            <td>
                                <form method="post" style="display:inline;" onsubmit="return confirmDelete('আপনি কি নিশ্চিত ডেলিভারি পার্সন ডিলিট করতে চান?');">
                                    <input type="hidden" name="delete_delivery_man_id" value="<?= $d['delivery_man_id'] ?>">
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                                <button type="button" class="warn-btn" onclick="alert('⚠️ ডিলিট করলে সংশ্লিষ্ট ডেলিভারি পার্সনের সব তথ্য চিরতরে মুছে যাবে!');">Warning</button>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="10" style="text-align:center;">ডেলিভারি পার্সন নেই</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Products Table -->
            <div id="productsTab" class="tab-pane">
                <h2>সব প্রোডাক্ট</h2>
                <table>
                    <thead>
                        <tr>
                            <?php if (!empty($products[0])): foreach ($products[0] as $k => $v): ?>
                                <?php if (strpos($k, 'password') === false): ?>
                                    <th><?= htmlspecialchars($k) ?></th>
                                <?php endif; ?>
                            <?php endforeach; endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): foreach ($products as $p): ?>
                        <tr>
                            <?php foreach ($p as $k => $v): ?>
                                <?php if (strpos($k, 'password') === false): ?>
                                    <td><?= htmlspecialchars($v) ?></td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="100" style="text-align:center;">প্রোডাক্ট নেই</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Orders Table -->
            <div id="orderTab" class="tab-pane">
                <h2>সব অর্ডার</h2>
                <table>
                    <thead>
                        <tr>
                            <?php if (!empty($orders[0])): foreach ($orders[0] as $k => $v): ?>
                                <th><?= htmlspecialchars($k) ?></th>
                            <?php endforeach; endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)): foreach ($orders as $o): ?>
                        <tr>
                            <?php foreach ($o as $k => $v): ?>
                                <td><?= htmlspecialchars($v) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="100" style="text-align:center;">অর্ডার নেই</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Shop Reports Table -->
            <div id="reportTab" class="tab-pane">
                <h2>দোকানের বিরুদ্ধে রিপোর্টসমূহ</h2>
                <table>
                    <thead>
                        <tr>
                            <?php if (!empty($shopReports[0])): foreach ($shopReports[0] as $k => $v): ?>
                                <th><?= htmlspecialchars($k) ?></th>
                            <?php endforeach; endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($shopReports)): foreach ($shopReports as $r): ?>
                        <tr>
                            <?php foreach ($r as $k => $v): ?>
                                <td>
                                <?php if ($k === 'image_path' && !empty($v)): ?>
                                    <a href="<?= htmlspecialchars($v) ?>" target="_blank">
                                        <img src="<?= htmlspecialchars($v) ?>" alt="Report Image" class="report-img">
                                    </a>
                                <?php else: ?>
                                    <?= htmlspecialchars($v) ?>
                                <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="100" style="text-align:center;">রিপোর্ট নেই</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Shop Reviews Table -->
            <div id="reviewTab" class="tab-pane">
                <h2>সব রিভিউ</h2>
                <table>
                    <thead>
                        <tr>
                            <?php if (!empty($reviews[0])): foreach ($reviews[0] as $k => $v): ?>
                                <th><?= htmlspecialchars($k) ?></th>
                            <?php endforeach; endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reviews)): foreach ($reviews as $rv): ?>
                        <tr>
                            <?php foreach ($rv as $k => $v): ?>
                                <td><?= htmlspecialchars($v) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="100" style="text-align:center;">রিভিউ নেই</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Messages Table -->
            <div id="messagesTab" class="tab-pane">
                <h2>সব মেসেজ</h2>
                <table>
                    <thead>
                        <tr>
                            <?php if (!empty($messages[0])): foreach ($messages[0] as $k => $v): ?>
                                <th><?= htmlspecialchars($k) ?></th>
                            <?php endforeach; endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($messages)): foreach ($messages as $msg): ?>
                        <tr>
                            <?php foreach ($msg as $k => $v): ?>
                                <td><?= htmlspecialchars($v) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="100" style="text-align:center;">মেসেজ নেই</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Shop Followers Table -->
            <div id="followersTab" class="tab-pane">
                <h2>Shop Followers</h2>
                <table>
                    <thead>
                        <tr>
                            <?php if (!empty($shopFollowers[0])): foreach ($shopFollowers[0] as $k => $v): ?>
                                <th><?= htmlspecialchars($k) ?></th>
                            <?php endforeach; endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($shopFollowers)): foreach ($shopFollowers as $sf): ?>
                        <tr>
                            <?php foreach ($sf as $k => $v): ?>
                                <td><?= htmlspecialchars($v) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="100" style="text-align:center;">কেউ Follow করেনি</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Product Loves Table -->
            <div id="lovesTab" class="tab-pane">
                <h2>Product Loves</h2>
                <table>
                    <thead>
                        <tr>
                            <?php if (!empty($productLoves[0])): foreach ($productLoves[0] as $k => $v): ?>
                                <th><?= htmlspecialchars($k) ?></th>
                            <?php endforeach; endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($productLoves)): foreach ($productLoves as $pl): ?>
                        <tr>
                            <?php foreach ($pl as $k => $v): ?>
                                <td><?= htmlspecialchars($v) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="100" style="text-align:center;">কেউ Love দেয়নি</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Payments Table -->
            <div id="paymentsTab" class="tab-pane">
                <h2>Payments</h2>
                <table>
                    <thead>
                        <tr>
                            <?php if (!empty($payments[0])): foreach ($payments[0] as $k => $v): ?>
                                <th><?= htmlspecialchars($k) ?></th>
                            <?php endforeach; endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($payments)): foreach ($payments as $pay): ?>
                        <tr>
                            <?php foreach ($pay as $k => $v): ?>
                                <td><?= htmlspecialchars($v) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="100" style="text-align:center;">Payments নেই</td></tr>
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