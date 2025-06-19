<?php
include '../PHP/db_connect.php';
session_start();

// Handle Delete Requests
$deleteMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_customer_id'])) {
        $id = intval($_POST['delete_customer_id']);
        $conn->query("DELETE FROM customers WHERE customer_id = $id");
        $deleteMsg = "Customer deleted!";
    }
    if (isset($_POST['delete_shop_owner_id'])) {
        $id = intval($_POST['delete_shop_owner_id']);
        $conn->query("DELETE FROM shop_owners WHERE shop_owner_id = $id");
        $deleteMsg = "Shop Owner deleted!";
    }
    if (isset($_POST['delete_delivery_man_id'])) {
        $id = intval($_POST['delete_delivery_man_id']);
        $conn->query("DELETE FROM delivery_men WHERE delivery_man_id = $id");
        $deleteMsg = "Delivery Man deleted!";
    }
}

// Fetch data
$customers = $conn->query("SELECT * FROM customers")->fetch_all(MYSQLI_ASSOC);
$shopOwners = $conn->query("SELECT * FROM shop_owners")->fetch_all(MYSQLI_ASSOC);
$deliveryMen = $conn->query("SELECT * FROM delivery_men")->fetch_all(MYSQLI_ASSOC);

$orderQuery = "SELECT o.*, c.customer_name, s.shop_name
               FROM orders o
               LEFT JOIN customers c ON o.customer_id = c.customer_id
               LEFT JOIN shop_owners s ON o.shop_owner_id = s.shop_owner_id";
$orders = $conn->query($orderQuery)->fetch_all(MYSQLI_ASSOC);

$shopReports = [];
if ($conn->query("SHOW TABLES LIKE 'shop_reports'")->num_rows) {
    $shopReports = $conn->query(
        "SELECT r.*, c.customer_name AS customer_real_name, s.shop_owner_name
         FROM shop_reports r 
         LEFT JOIN customers c ON r.customer_id = c.customer_id
         LEFT JOIN shop_owners s ON r.shop_owner_id = s.shop_owner_id
         ORDER BY r.report_id DESC"
    )->fetch_all(MYSQLI_ASSOC);
}

$reviews = [];
if ($conn->query("SHOW TABLES LIKE 'reviews'")->num_rows) {
    $reviews = $conn->query("SELECT rv.*, c.customer_name, s.shop_name 
                             FROM reviews rv 
                             LEFT JOIN customers c ON rv.customer_id = c.customer_id
                             LEFT JOIN shop_owners s ON rv.shop_owner_id = s.shop_owner_id")->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../CSS/AdminDashboard.css">
    <script>
    function confirmDelete(msg) {
        return confirm(msg);
    }
    </script>
</head>

<body>
    <h1>অ্যাডমিন ড্যাশবোর্ড</h1>
    <?php if ($deleteMsg): ?>
        <div style="color:green;text-align:center;margin:12px;font-weight:bold;"><?= htmlspecialchars($deleteMsg) ?></div>
    <?php endif; ?>

    <section>
        <h2>সব কাস্টমার</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>নাম</th><th>ইমেইল</th><th>ফোন</th><th>লিঙ্গ</th><th>ঠিকানা</th><th>কয়েন</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $cus): ?>
                <tr>
                    <td><?= $cus['customer_id'] ?></td>
                    <td><?= htmlspecialchars($cus['customer_name']) ?></td>
                    <td><?= htmlspecialchars($cus['customer_email']) ?></td>
                    <td><?= htmlspecialchars($cus['customer_phone']) ?></td>
                    <td><?= htmlspecialchars($cus['customer_gender']) ?></td>
                    <td><?= htmlspecialchars($cus['customer_address']) ?></td>
                    <td><?= $cus['customer_coins'] ?></td>
                    <td>
                        <form method="post" style="display:inline;" onsubmit="return confirmDelete('আপনি কি নিশ্চিত কাস্টমার ডিলিট করতে চান?');">
                            <input type="hidden" name="delete_customer_id" value="<?= $cus['customer_id'] ?>">
                            <button type="submit" class="delete-btn">Delete</button>
                        </form>
                        <button type="button" class="warn-btn" onclick="alert('⚠️ ডিলিট করলে সংশ্লিষ্ট কাস্টমারের সব তথ্য চিরতরে মুছে যাবে!');">Warning</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section>
        <h2>সব দোকানদার</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>নাম</th><th>দোকানের নাম</th><th>ইমেইল</th>
                    <th>ফোন</th><th>ঠিকানা</th><th>লিঙ্গ</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shopOwners as $so): ?>
                <tr>
                    <td><?= $so['shop_owner_id'] ?></td>
                    <td><?= htmlspecialchars($so['shop_owner_name']) ?></td>
                    <td><?= htmlspecialchars($so['shop_name']) ?></td>
                    <td><?= htmlspecialchars($so['shop_owner_email']) ?></td>
                    <td><?= htmlspecialchars($so['shop_owner_phone']) ?></td>
                    <td><?= htmlspecialchars($so['shop_owner_address']) ?></td>
                    <td><?= htmlspecialchars($so['shop_owner_gender']) ?></td>
                    <td>
                        <form method="post" style="display:inline;" onsubmit="return confirmDelete('আপনি কি নিশ্চিত দোকানদার ডিলিট করতে চান?');">
                            <input type="hidden" name="delete_shop_owner_id" value="<?= $so['shop_owner_id'] ?>">
                            <button type="submit" class="delete-btn">Delete</button>
                        </form>
                        <button type="button" class="warn-btn" onclick="alert('⚠️ ডিলিট করলে সংশ্লিষ্ট দোকানদারের সব তথ্য চিরতরে মুছে যাবে!');">Warning</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
<h1> teri maka naka </h1>
    <section>
        <h2>সব ডেলিভারি পার্সন</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>নাম</th>
                    <th>ইমেইল</th>
                    <th>ফোন</th>
                    <th>লিঙ্গ</th>
                    <th>ঠিকানা</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deliveryMen as $d): ?>
                <tr>
                    <td><?= $d['delivery_man_id'] ?></td>
                    <td><?= htmlspecialchars($d['delivery_man_name']) ?></td>
                    <td><?= htmlspecialchars($d['delivery_man_email']) ?></td>
                    <td><?= htmlspecialchars($d['delivery_man_phone']) ?></td>
                    <td><?= htmlspecialchars($d['delivery_man_gender']) ?></td>
                    <td><?= htmlspecialchars($d['delivery_man_address']) ?></td>
                    <td>
                        <form method="post" style="display:inline;" onsubmit="return confirmDelete('আপনি কি নিশ্চিত ডেলিভারি পার্সন ডিলিট করতে চান?');">
                            <input type="hidden" name="delete_delivery_man_id" value="<?= $d['delivery_man_id'] ?>">
                            <button type="submit" class="delete-btn">Delete</button>
                        </form>
                        <button type="button" class="warn-btn" onclick="alert('⚠️ ডিলিট করলে সংশ্লিষ্ট ডেলিভারি পার্সনের সব তথ্য চিরতরে মুছে যাবে!');">Warning</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section>
        <h2>সব অর্ডার</h2>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Shop</th>
                    <th>Product ID</th>
                    <th>পরিমাণ</th>
                    <th>Order Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?= $o['order_id'] ?></td>
                    <td><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td><?= htmlspecialchars($o['shop_name']) ?></td>
                    <td><?= $o['product_id'] ?></td>
                    <td><?= $o['quantity'] ?></td>
                    <td><?= $o['order_time'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <?php if (!empty($shopReports)): ?>
    <section>
        <h2>দোকানের বিরুদ্ধে রিপোর্টসমূহ</h2>
        <table>
            <thead>
                <tr>
                    <th>Report ID</th>
                    <th>Shop Name</th>
                    <th>Shop Owner</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shopReports as $r): ?>
                <tr>
                    <td><?= $r['report_id'] ?></td>
                    <td><?= htmlspecialchars($r['shop_name']) ?></td>
                    <td><?= htmlspecialchars($r['shop_owner_name']) ?></td>
                    <td><?= htmlspecialchars($r['customer_real_name'] ?: $r['customer_name']) ?></td>
                    <td><?= htmlspecialchars($r['customer_email']) ?></td>
                    <td><?= htmlspecialchars($r['customer_phone']) ?></td>
                    <td><?= nl2br(htmlspecialchars($r['description'])) ?></td>
                    <td>
                        <?php if (!empty($r['image_path'])): ?>
                            <a href="<?= htmlspecialchars($r['image_path']) ?>" target="_blank">
                                <img src="<?= htmlspecialchars($r['image_path']) ?>" alt="Report Image" class="report-img">
                            </a>
                        <?php else: ?>
                            <span>ছবি নেই</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($r['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
    <?php endif; ?>

    <?php if (!empty($reviews)): ?>
    <section>
        <h2>সব রিভিউ</h2>
        <table>
            <thead>
                <tr>
                    <th>Review ID</th><th>Customer</th>
                    <th>Shop</th><th>Rating</th><th>Review</th><th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $rv): ?>
                <tr>
                    <td><?= $rv['review_id'] ?></td>
                    <td><?= htmlspecialchars($rv['customer_name']) ?></td>
                    <td><?= htmlspecialchars($rv['shop_name']) ?></td>
                    <td><?= $rv['rating'] ?? '' ?></td>
                    <td><?= htmlspecialchars($rv['review_text'] ?? '') ?></td>
                    <td><?= $rv['review_time'] ?? '' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
    <?php endif; ?>
</body>
</html>