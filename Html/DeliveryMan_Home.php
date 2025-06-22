<?php
session_start();
include '../PHP/db_connect.php'; // Database connection

$isDeliveryman = false;
$deliverymanId = null;
$deliverymanName = '';
$deliverymanPic = '';
$deliverymanPhone = '';

// =============================
// Accept/Cancel Handler (Pure PHP)
// =============================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'accept' && isset($_SESSION['delivery_man_email'])) {
        $oid = intval($_POST['oid'] ?? 0);

        // ডেলিভারিম্যান ইনফো
        $email = $_SESSION['delivery_man_email'];
        $sql = "SELECT delivery_man_id, delivery_man_name, delivery_man_phone FROM delivery_men WHERE delivery_man_email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $deliverymanId = $row['delivery_man_id'];
        $deliverymanName = $row['delivery_man_name'];
        $deliverymanPhone = $row['delivery_man_phone'];
        $stmt->close();

        // Check if already accepted by someone else
        $check_sql = "SELECT status FROM orders WHERE order_id=? AND status='accepted'";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $oid);
        $check_stmt->execute();
        $check_stmt->store_result();
        if ($check_stmt->num_rows > 0) {
            echo "<script>alert('অর্ডার ইতিমধ্যে এক্সেপ্ট হয়েছে!');window.location='".$_SERVER['PHP_SELF']."';</script>";
            exit();
        }
        $check_stmt->close();

        // Order status change (accepted)
        $sql = "UPDATE orders SET status='accepted' WHERE order_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $oid);
        $stmt->execute();
        $stmt->close();

        // Delivery man notification INSERT
        $sql = "INSERT INTO notifications (
            user_id, user_type, order_id, message, is_read, created_at,
            accepted_by, accepted_by_name, accepted_by_phone, accepted_at
        ) VALUES (?, 'delivery_man', ?, 'অর্ডার এক্সেপ্ট হয়েছে', 0, NOW(), ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisss", $deliverymanId, $oid, $deliverymanId, $deliverymanName, $deliverymanPhone);
        $stmt->execute();
        $stmt->close();

        // --- UPDATE shop_owner notification ---
        $sql = "UPDATE notifications SET 
            accepted_by = ?, 
            accepted_by_name = ?, 
            accepted_by_phone = ?, 
            accepted_at = NOW()
        WHERE order_id = ? AND user_type = 'shop_owner'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issi", $deliverymanId, $deliverymanName, $deliverymanPhone, $oid);
        $stmt->execute();
        $stmt->close();

        // --- UPDATE customer notification ---
        $sql = "UPDATE notifications SET 
            accepted_by = ?, 
            accepted_by_name = ?, 
            accepted_by_phone = ?, 
            accepted_at = NOW()
        WHERE order_id = ? AND user_type = 'customer'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issi", $deliverymanId, $deliverymanName, $deliverymanPhone, $oid);
        $stmt->execute();
        $stmt->close();

        // If this deliveryman had cancelled this order before, remove from cancelled list so he can see it again in future if needed.
        $sql = "DELETE FROM deliveryman_cancelled_orders WHERE delivery_man_id = ? AND order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $deliverymanId, $oid);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('অর্ডার এক্সেপ্ট হয়েছে!');window.location='".$_SERVER['PHP_SELF']."';</script>";
        exit();
    }
    // Cancel handler for pending order (just reload)
    elseif ($_POST['action'] == 'cancel_pending' && isset($_POST['oid'])) {
        $oid = intval($_POST['oid'] ?? 0);
        if (isset($_SESSION['delivery_man_email'])) {
            $email = $_SESSION['delivery_man_email'];
            $sql = "SELECT delivery_man_id FROM delivery_men WHERE delivery_man_email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $deliverymanId = $row['delivery_man_id'];
                // Insert cancel info, ignore if already exists
                $sql2 = "INSERT IGNORE INTO deliveryman_cancelled_orders (delivery_man_id, order_id) VALUES (?, ?)";
                $stmt2 = $conn->prepare($sql2);
                $stmt2->bind_param("ii", $deliverymanId, $oid);
                $stmt2->execute();
                $stmt2->close();
            }
            $stmt->close();
        }
        echo "<script>alert('অর্ডার বাতিল করা হয়েছে এবং আর দেখাবে না!');window.location='".$_SERVER['PHP_SELF']."';</script>";
        exit();
    }
    // Cancel Handler (for notification history)
    elseif ($_POST['action'] == 'cancel' && isset($_POST['nid'])) {
        $nid = intval($_POST['nid']);

        // Find the order_id before deleting notification
        $sql = "SELECT order_id, user_id FROM notifications WHERE notification_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $nid);
        $stmt->execute();
        $result = $stmt->get_result();
        $order_id = 0;
        $deliverymanId = 0;
        if ($row = $result->fetch_assoc()) {
            $order_id = $row['order_id'];
            $deliverymanId = $row['user_id'];
        }
        $stmt->close();

        // 1. Delete the notification
        $sql = "DELETE FROM notifications WHERE notification_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $nid);
        $stmt->execute();
        $stmt->close();

        // 2. Change order status to pending (if order_id found)
        if ($order_id) {
            $sql = "UPDATE orders SET status='pending' WHERE order_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $stmt->close();
        }

        // 3. Insert into cancelled orders so this deliveryman doesn't see this order in pending again
        if ($order_id && $deliverymanId) {
            $sql = "INSERT IGNORE INTO deliveryman_cancelled_orders (delivery_man_id, order_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $deliverymanId, $order_id);
            $stmt->execute();
            $stmt->close();
        }

        echo "<script>alert('নোটিফিকেশন বাতিল হয়েছে! অর্ডার আবার পেন্ডিং এ গেছে, এবং এই ডেলিভারিম্যান এর pending এ দেখাবে না!');window.location='".$_SERVER['PHP_SELF']."';</script>";
        exit();
    }
}

// =============================
// Authentication & Info Fetch
// =============================
if (isset($_SESSION['delivery_man_email']) && !isset($_GET['id'])) {
    $email = $_SESSION['delivery_man_email'];
    $sql = "SELECT delivery_man_id, delivery_man_name, delivery_man_image_path, delivery_man_phone FROM delivery_men WHERE delivery_man_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $deliverymanId = $row['delivery_man_id'];
        $deliverymanName = $row['delivery_man_name'];
        $deliverymanPic = $row['delivery_man_image_path'];
        $deliverymanPhone = $row['delivery_man_phone'];
        $isDeliveryman = true;
    } else {
        echo "<script>
            alert('Deliveryman data not found!');
            window.location.href='../Html/index.html';
        </script>";
        exit();
    }
    $stmt->close();
}
// কাস্টমার বা ভিজিটর URL দিয়ে এলে (?id=)
else if (isset($_GET['id'])) {
    $deliverymanId = intval($_GET['id']);
    $sql = "SELECT delivery_man_id, delivery_man_name, delivery_man_image_path FROM delivery_men WHERE delivery_man_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $deliverymanId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $deliverymanName = $row['delivery_man_name'];
        $deliverymanPic = $row['delivery_man_image_path'];
    } else {
        echo "<script>
            alert('Deliveryman not found!');
            window.location.href='../Html/index.html';
        </script>";
        exit();
    }
    $stmt->close();
}
// কেউ না থাকলে
else {
    echo "<script>
        alert('You must log in first!');
        window.location.href='../Html/index.html';
    </script>";
    exit();
}

// =============================
// Pending Order Fetch (shop_owner address/phone & payment)
// =============================
$orders = null;
if ($isDeliveryman) {
    $order_sql = "
        SELECT o.*, so.shop_name, so.shop_owner_address, so.shop_owner_phone, p.payment_method, p.bkash_txid, pr.price
        FROM orders o
        LEFT JOIN shop_owners so ON o.shop_owner_id = so.shop_owner_id
        LEFT JOIN payments p ON o.order_id = p.order_id
        LEFT JOIN products pr ON o.product_id = pr.product_id
        LEFT JOIN deliveryman_cancelled_orders dco ON o.order_id = dco.order_id AND dco.delivery_man_id = ?
        WHERE o.status = 'pending' AND dco.order_id IS NULL
        ORDER BY o.order_time DESC
    ";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("i", $deliverymanId);
    $order_stmt->execute();
    $orders = $order_stmt->get_result();
    $order_stmt->close();
}

// =============================
// Accepted Notification Fetch (history)
// =============================
$notifications = null;
if ($isDeliveryman) {
    $notif_sql = "
        SELECT n.*, o.customer_name, o.customer_phone, o.customer_address, o.customer_comment,
        so.shop_name, so.shop_owner_address, so.shop_owner_phone, pr.price, o.quantity, o.delivery_charge,
        p.payment_method, p.bkash_txid
        FROM notifications n
        LEFT JOIN orders o ON n.order_id = o.order_id
        LEFT JOIN shop_owners so ON o.shop_owner_id = so.shop_owner_id
        LEFT JOIN payments p ON o.order_id = p.order_id
        LEFT JOIN products pr ON o.product_id = pr.product_id
        WHERE n.user_id = ? AND n.user_type = 'delivery_man'
        ORDER BY n.created_at DESC
    ";
    $notif_stmt = $conn->prepare($notif_sql);
    $notif_stmt->bind_param("i", $deliverymanId);
    $notif_stmt->execute();
    $notifications = $notif_stmt->get_result();
    $notif_stmt->close();
}
// Fetch warning for this delivery man (if any)
$warning_message = null;
if (isset($_SESSION['delivery_man_id'])) {
    $deliveryManId = $_SESSION['delivery_man_id'];
    $warnSql = "SELECT reason, warned_at FROM warned_users WHERE user_type='delivery_man' AND user_id=?";
    $warnStmt = $conn->prepare($warnSql);
    $warnStmt->bind_param("i", $deliveryManId);
    $warnStmt->execute();
    $warnStmt->bind_result($reason, $warned_at);
    if ($warnStmt->fetch()) {
        $warning_message = [
            'reason' => $reason,
            'warned_at' => $warned_at
        ];
    }
    $warnStmt->close();
}
?>
<!-- Your HTML and sidebar code will go below this as before -->
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../CSS/Delivaryman_Home.css">
    <style>
        .notif-badge {
            background: #ff5722;
            color: #fff;
            border-radius: 50%;
            padding: 2px 7px;
            font-size: 12px;
            position: absolute;
            top: -4px; right: -4px;
        }
        .notification-item.unread {background:#fff7e6;}
        .review-author {font-weight:bold;}
        .review-rating {color: #ffa726;}
        .sidebar-content .notification-item {margin-bottom: 12px; border-bottom:1px solid #eee; padding-bottom:8px;}
        .accept-btn, .cancel-btn {
            padding: 3px 10px;
            margin: 2px 5px 2px 0;
            border: none;
            border-radius: 4px;
            font-size: 1em;
            cursor: pointer;
        }
        .accept-btn { background: #1c7c54; color: #fff; }
        .cancel-btn { background: #e94f37; color: #fff; }
        .accept-info {color:green;}
        .tabs {display:flex;gap:10px;margin-bottom:10px;}
        .tab-btn {padding:6px 16px;border:1px solid #ccc;background:#f7f7f7;cursor:pointer;}
        .tab-btn.active {background:#1c7c54;color:#fff;}
        .tab-content {display:none;}
        .tab-content.active {display:block;}
    </style>
</head>
<body>

    <!-- HEADER SECTION -->
    <header>
        <div class="logo">
            <img src="../Images/Logo.png" alt="Liberty Logo">
            <h2>সহজ যোগান</h2>
        </div>
        <?php if ($isDeliveryman): ?>
        <div class="icons">
            <button id="userIcon">
                <img src="../Images/Sample_User_Icon.png" alt="User">
            </button>
            <button id="notificationIcon" style="position:relative;">
                <img src="../Images/notification.png" alt="Notifications">
                <?php
                // Unread count for notification history
                $unread = 0;
                if ($notifications) {
                    foreach($notifications as $n) {
                        if ($n['is_read']==0) $unread++;
                    }
                    if ($unread > 0) echo "<span class=\"notif-badge\">$unread</span>";
                    $notifications->data_seek(0);
                }
                ?>
            </button>
        </div>
        <?php endif; ?>
    </header>

    <!-- OVERLAY & SIDEBAR: শুধু ডেলিভারিম্যান নিজে দেখবে -->
    <?php if ($isDeliveryman): ?>
    <div id="overlay" class="overlay"></div>
    <div id="userSidebar" class="sidebar">
    <span id="closeUserSidebar" class="close-btn">&times;</span>
    <h3>ডেলিভারিম্যান মেনু</h3>
    <div class="sidebar-content">
        <a href="../Html/Delivaryman_setting.php">সেটিংস</a>
        <a href="../Html/Deliveryman_ChangePassword.php">পাসওয়ার্ড পরিবর্তন</a>
        <a href="../Html/Deliveryman_MyDeliveries.php">আমার ডেলিভারি</a>
        <a href="#" id="logoutLink">লগ আউট</a>
    </div>
</div>
    <?php endif; ?>
   <div id="notificationSidebar" class="sidebar">
    <span id="closeNotification" class="close-btn">&times;</span>
    <h3>নোটিফিকেশন</h3>
    <!-- Warning message at top -->
    <?php if (isset($warning_message) && $warning_message): ?>
        <div style="background:#fff3cd;color:#856404;padding:12px 16px;border-radius:8px;margin-bottom:11px;border:1px solid #ffeeba;font-size:1.02em;">
            <b>⚠️ সতর্কতা / Warning!</b><br>
            <?= nl2br(htmlspecialchars($warning_message['reason'])) ?><br>
            <span style="font-size:0.93em;color:#b28b00;">
                তারিখ: <?= htmlspecialchars(date('d M Y, h:i A', strtotime($warning_message['warned_at']))) ?>
            </span>
        </div>
    <?php endif; ?>

    <div class="tabs">
        <button class="tab-btn active" data-tab="pending-orders">Pending Orders</button>
        <button class="tab-btn" data-tab="accepted-orders">Accepted/History</button>
    </div>

    <div class="sidebar-content" style="max-height: 400px; overflow-y: auto;">
        <!-- Pending Orders -->
        <div class="tab-content active" id="pending-orders">
        <?php if ($orders && $orders->num_rows > 0): ?>
            <?php while($row = $orders->fetch_assoc()): ?>
            <div class="notification-item">
                <p>নতুন অর্ডার: #<?= htmlspecialchars($row['order_id']) ?></p>
                <?php if(!empty($row['shop_name'])): ?>
                    <small>দোকান: <b><?= htmlspecialchars($row['shop_name']) ?></b></small><br>
                <?php endif; ?>
                <?php if(!empty($row['shop_owner_address'])): ?>
                    <small>দোকান মালিকের ঠিকানা: <?= htmlspecialchars($row['shop_owner_address']) ?></small><br>
                <?php endif; ?>
                <?php if(!empty($row['shop_owner_phone'])): ?>
                    <small>দোকান মালিকের ফোন: <?= htmlspecialchars($row['shop_owner_phone']) ?></small><br>
                <?php endif; ?>
                <small>অর্ডার মূল্য: <?= htmlspecialchars($row['price'] * $row['quantity'] + $row['delivery_charge']) ?> টাকা</small><br>
                <small>কাস্টমার: <?= htmlspecialchars($row['customer_name']) ?> (<?= htmlspecialchars($row['customer_phone']) ?>)</small><br>
                <small>ঠিকানা: <?= htmlspecialchars($row['customer_address']) ?></small><br>
                <?php if(!empty($row['customer_comment'])): ?>
                    <small>কমেন্ট: <?= htmlspecialchars($row['customer_comment']) ?></small><br>
                <?php endif; ?>
                <?php if(!empty($row['payment_method'])): ?>
                    <small>পেমেন্ট: 
                        <?= $row['payment_method']=='bkash' ? 'bKash (TxID: '.htmlspecialchars($row['bkash_txid']).')' : 'Cash On Delivery' ?>
                    </small><br>
                <?php endif; ?>
                <small><?= date('d M, H:i', strtotime($row['order_time'])) ?></small><br>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="accept">
                    <input type="hidden" name="oid" value="<?= $row['order_id'] ?>">
                    <button type="submit" class="accept-btn">গ্রহণ করুন</button>
                </form>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="cancel_pending">
                    <input type="hidden" name="oid" value="<?= $row['order_id'] ?>">
                    <button type="submit" class="cancel-btn">বাতিল</button>
                </form>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>নতুন কোনো অর্ডার নেই</p>
        <?php endif; ?>
        </div>

        <!-- Accepted Orders / History -->
        <div class="tab-content" id="accepted-orders">
        <?php if ($notifications && $notifications->num_rows > 0): ?>
            <?php while($row = $notifications->fetch_assoc()): ?>
            <div class="notification-item<?= (isset($row['is_read']) && $row['is_read']==0) ? ' unread' : '' ?>">
                <p><?= htmlspecialchars($row['message']) ?></p>
                <?php if(!empty($row['shop_name'])): ?>
                    <small>দোকান: <b><?= htmlspecialchars($row['shop_name']) ?></b></small><br>
                <?php endif; ?>
                <?php if(!empty($row['shop_owner_address'])): ?>
                    <small>দোকান মালিকের ঠিকানা: <?= htmlspecialchars($row['shop_owner_address']) ?></small><br>
                <?php endif; ?>
                <?php if(!empty($row['shop_owner_phone'])): ?>
                    <small>দোকান মালিকের ফোন: <?= htmlspecialchars($row['shop_owner_phone']) ?></small><br>
                <?php endif; ?>
                <?php if(isset($row['price']) && isset($row['quantity'])): ?>
                    <small>অর্ডার মূল্য: <?= htmlspecialchars($row['price'] * $row['quantity'] + $row['delivery_charge']) ?> টাকা</small><br>
                <?php endif; ?>
                <?php if(!empty($row['customer_name'])): ?>
                    <small>কাস্টমার: <?= htmlspecialchars($row['customer_name']) ?> (<?= htmlspecialchars($row['customer_phone']) ?>)</small><br>
                    <small>ঠিকানা: <?= htmlspecialchars($row['customer_address']) ?></small><br>
                <?php endif; ?>
                <?php if(!empty($row['customer_comment'])): ?>
                    <small>কমেন্ট: <?= htmlspecialchars($row['customer_comment']) ?></small><br>
                <?php endif; ?>
                <?php if(!empty($row['payment_method'])): ?>
                    <small>পেমেন্ট: 
                        <?= $row['payment_method']=='bkash' ? 'bKash (TxID: '.htmlspecialchars($row['bkash_txid']).')' : 'Cash On Delivery' ?>
                    </small><br>
                <?php endif; ?>
                <small><?= date('d M, H:i', strtotime($row['created_at'])) ?></small><br>
                <?php if (!empty($row['accepted_by'])): ?>
                    <div class="accept-info">
                        <b>Accepted By:</b>
                        <?= htmlspecialchars($row['accepted_by_name']) ?> (<?= htmlspecialchars($row['accepted_by_phone']) ?>)<br>
                        <b>Time:</b> <?= htmlspecialchars($row['accepted_at']) ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>নতুন কোনো নোটিফিকেশন নেই</p>
        <?php endif; ?>
        </div>
    </div>
</div>
    <main>
      <section class="deliveryman-banner-section">
        <div class="deliveryman-banner">
          <img src="../Images/deliveryman.jpeg" alt="Deliveryman Background" class="banner-bg-img" />
          <div class="deliveryman-info-box">
            <img 
              src="../uploads/<?php echo htmlspecialchars($deliverymanPic); ?>" 
              alt="Deliveryman Image" 
              class="deliveryman-img" 
            />
            <div class="deliveryman-name">
              <h2><?php echo htmlspecialchars($deliverymanName); ?></h2>
            </div>
          </div>
          <?php if (!$isDeliveryman): ?>
          <button class="report-btn" type="button" onclick="window.location.href='../Html/report.html'">
            রিপোর্ট করুন
          </button>
          <?php endif; ?>
          <button class="review-toggle-btn" type="button">
            রিভিউ দেখুন
          </button>
        </div>
      </section>
    </main>

    <section class="review-section">
        <h2>রিভিউ</h2>
        <div class="review-list">
            <div class="review-item">
                <div class="review-author">জন ডো</div>
                <div class="review-text">অসাধারণ সেবা! পণ্যটি দ্রুত পেয়েছি এবং গুণগত মান খুবই ভালো।</div>
                <div class="review-rating">⭐⭐⭐⭐⭐</div>
            </div>
            <div class="review-item">
                <div class="review-author">মি. শাহিন</div>
                <div class="review-text">ভাল পণ্য, কিন্তু ডেলিভারি একটু দেরি হয়েছিল।</div>
                <div class="review-rating">⭐⭐⭐⭐</div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-links">
            <div class="footer-column">
                <h4>শপিং অনলাই</h4>
                <ul>
                    <li><a href="#">ডেলিভারি</a></li>
                    <li><a href="#">অর্ডার হিস্টোরি</a></li>
                    <li><a href="#">উইস লিস্ট</a></li>
                    <li><a href="#">পেমেন্ট</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>আমাদের সম্পর্কে</h4>
                <ul>
                    <li>
                        <a href="../Html/About_us.html">
                            <img src="../Images/light-bulb.png" alt="info icon" class="link-icon">
                            আমাদের সম্পর্কে বিস্তারিত জানুন
                        </a>
                    </li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>যোগাযোগের তথ্য</h4>
                <ul>
                    <li><a href="#">📞 ফোন</a></li>
                    <li><a href="#">✉ ইমেইল</a></li>
                </ul>
            </div> 
        </div>
    </footer>

    <script src="../java_script/DeliveryMan_home.js"></script>
    <script>
    // Tabs for sidebar
    document.addEventListener("DOMContentLoaded", function(){
        var tabs = document.querySelectorAll('.tab-btn');
        var tabContents = document.querySelectorAll('.tab-content');
        tabs.forEach(function(tab){
            tab.onclick = function(){
                tabs.forEach(t=>t.classList.remove('active'));
                tabContents.forEach(c=>c.classList.remove('active'));
                tab.classList.add('active');
                document.getElementById(tab.getAttribute('data-tab')).classList.add('active');
            }
        });
        // Overlay sidebar
        var overlay = document.getElementById('overlay');
        var userSidebar = document.getElementById('userSidebar');
        var notifSidebar = document.getElementById('notificationSidebar');
        var userIcon = document.getElementById('userIcon');
        var notifIcon = document.getElementById('notificationIcon');
        if(userIcon) userIcon.onclick = function(){
            userSidebar.style.display = 'block';
            overlay.style.display = 'block';
        };
        if(notifIcon) notifIcon.onclick = function(){
            notifSidebar.style.display = 'block';
            overlay.style.display = 'block';
        };
        var closeBtns = document.querySelectorAll('.close-btn');
        closeBtns.forEach(function(btn){
            btn.onclick = function(){
                userSidebar && (userSidebar.style.display = 'none');
                notifSidebar && (notifSidebar.style.display = 'none');
                overlay.style.display = 'none';
            };
        });
        if(overlay) overlay.onclick = function(){
            userSidebar && (userSidebar.style.display = 'none');
            notifSidebar && (notifSidebar.style.display = 'none');
            overlay.style.display = 'none';
        };
    });
    </script>
</body>
</html>