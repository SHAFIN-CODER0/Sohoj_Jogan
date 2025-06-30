<?php
session_start();
include '../PHP/db_connect.php';

// PHPMailer autoload (Composer দিয়ে ইনস্টল করো: composer require phpmailer/phpmailer)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

// ======= AJAX: OTP কোড জেনারেট ও পাঠানো (Email-এ) =======
if (isset($_POST['ajax_generate_code']) && isset($_POST['order_id']) && isset($_POST['customer_email'])) {
    $order_id = intval($_POST['order_id']);
    $customer_email = trim($_POST['customer_email']);

    $code = rand(100000, 999999); // 6-digit random

    // DB-তে সেভ
    $sql = "UPDATE payments SET delivery_confirm_code=? WHERE order_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $code, $order_id);
    $stmt->execute();
    $stmt->close();

    // Email পাঠানো
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sohojjogan@gmail.com'; //  Gmail
        $mail->Password   = 'nipm vcuc jgwf xbyb';     // 16 Digit App Password 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('sohojjogan@gmail.com', 'Sohaj Jogan');
        $mail->addAddress($customer_email);

        $mail->isHTML(true);
      $Subject = 'আপনার নিশ্চিতকরণ কোড (OTP)';
$mail->Body = "আপনার এককালীন পাসকোড (OTP): <b>$code</b><br>অনুগ্রহ করে এই কোডটি ডেলিভারিম্যানকে প্রদান করুন অথবা নিশ্চিতকরণের সময় ব্যবহার করুন।";


        $mail->send();
        echo "success";
    } catch (Exception $e) {
        echo "error";
    }
    exit();
}

// ====== আগের ডেলিভারি কোড ======

if (!isset($_SESSION['delivery_man_email'])) {
    echo "<script>alert('You must log in first!');window.location.href='../Html/index.php';</script>";
    exit();
}

$email = $_SESSION['delivery_man_email'];

// Get deliveryman info
$sql = "SELECT delivery_man_id, delivery_man_name FROM delivery_men WHERE delivery_man_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if (!$result->num_rows) {
    echo "<script>alert('ডেলিভারিম্যান পাওয়া যায়নি!');window.location.href='../Html/index.php';</script>";
    exit();
}
$row = $result->fetch_assoc();
$deliverymanId = $row['delivery_man_id'];
$deliverymanName = $row['delivery_man_name'];
$stmt->close();

// ========== Delivery Confirmation Handler ==========
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'deliver') {
    $oid = intval($_POST['oid']);
    $input_code = isset($_POST['bkash_code']) ? trim($_POST['bkash_code']) : null;

    // কোড যাচাই (bKash হলে)
    $sql = "SELECT p.delivery_confirm_code, p.payment_method FROM payments p WHERE p.order_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $oid);
    $stmt->execute();
    $stmt->bind_result($real_code, $payment_method);
    $stmt->fetch();
    $stmt->close();

    if ($payment_method == 'bkash') {
        if ($input_code === $real_code && $real_code != '') {
            // Success, delivery confirm
            $pay_sql = "UPDATE payments SET delivery_confirm_time=NOW(), payment_status='delivered' WHERE order_id=?";
            $pay_stmt = $conn->prepare($pay_sql);
            $pay_stmt->bind_param("i", $oid);
            $pay_stmt->execute();
            $pay_stmt->close();

            // ডেলিভারি সম্পন্ন হলে deliveryman_id সেভ করো
            $order_sql = "UPDATE orders SET status='delivered', delivery_man_id=? WHERE order_id=? AND status='accepted'";
            $order_stmt = $conn->prepare($order_sql);
            $order_stmt->bind_param("ii", $deliverymanId, $oid);
            $order_stmt->execute();
            $order_stmt->close();

            echo "<script>alert('ডেলিভারি সফলভাবে সম্পন্ন হয়েছে!');window.location='Deliveryman_MyDeliveries.php';</script>";
            exit();
        } else {
            echo "<script>alert('ভুল কোড! ডেলিভারি কনফার্ম হয়নি।');window.location='Deliveryman_MyDeliveries.php';</script>";
            exit();
        }
    } else {
        // For Cash on Delivery: No code check needed
        $pay_sql = "UPDATE payments SET delivery_confirm_time=NOW(), payment_status='delivered' WHERE order_id=?";
        $pay_stmt = $conn->prepare($pay_sql);
        $pay_stmt->bind_param("i", $oid);
        $pay_stmt->execute();
        $pay_stmt->close();

        // ডেলিভারি সম্পন্ন হলে deliveryman_id সেভ করো
        $order_sql = "UPDATE orders SET status='delivered', delivery_man_id=? WHERE order_id=? AND status='accepted'";
        $order_stmt = $conn->prepare($order_sql);
        $order_stmt->bind_param("ii", $deliverymanId, $oid);
        $order_stmt->execute();
        $order_stmt->close();

        echo "<script>alert('ডেলিভারি সফলভাবে সম্পন্ন হয়েছে!');window.location='Deliveryman_MyDeliveries.php';</script>";
        exit();
    }
}

// ========== Fetch All My Deliveries (accepted, delivered) from notifications ==========
$sql = "
    SELECT o.*, c.customer_name, c.customer_phone, c.customer_email, c.customer_address, pr.product_name, pr.price, o.quantity,
           p.payment_method, p.bkash_txid, p.delivery_confirm_code, p.delivery_confirm_time
    FROM notifications n
    JOIN orders o ON n.order_id = o.order_id
    LEFT JOIN customers c ON o.customer_id = c.customer_id
    LEFT JOIN products pr ON o.product_id = pr.product_id
    LEFT JOIN payments p ON o.order_id = p.order_id
    WHERE n.user_type='delivery_man'
      AND n.accepted_by = ?
      AND o.status IN ('accepted', 'delivered')
    ORDER BY o.status ASC, o.order_time DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $deliverymanId);
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>আমার ডেলিভারি</title>
    <link rel="stylesheet" href="../CSS/Delivaryman_Home.css">
   
    <script>
    function sendCode(orderId, customerEmail) {
        var btn = document.getElementById('otpbtn_' + orderId);
        var statusEl = document.getElementById('otpstatus_' + orderId);
        btn.disabled = true;
        statusEl.innerText = 'অপেক্ষা করুন...';
        fetch('', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'ajax_generate_code=1&order_id='+orderId+'&customer_email='+encodeURIComponent(customerEmail)
        })
        .then(res => res.text())
        .then(data => {
            if(data.trim() == 'success') {
                statusEl.innerText = 'কোড ইমেইলে পাঠানো হয়েছে!';
            } else {
                statusEl.innerText = 'ত্রুটি! আবার চেষ্টা করুন';
                btn.disabled = false;
            }
        })
        .catch(() => {
            statusEl.innerText = 'ত্রুটি!';
            btn.disabled = false;
        });
    }
    </script>
</head>
<body>
    <header>
      <div class="logo" id="logoClickable" style="cursor:pointer;">
        <img src="../Images/Logo.png" alt="Liberty Logo">
        <h2>সহজ যোগান</h2>
    </div>
    <script>
        document.getElementById('logoClickable').addEventListener('click', function() {
            window.location.href = '../Html/Deliveryman_Home.php';
        });
    </script>
    </header>
    <main>
        <h2 style="text-align:center;">আমার ডেলিভারি</h2>
        <table class="delivery-table">
            <tr>
                <th>অর্ডার #</th>
                <th>কাস্টমার</th>
                <th>মোবাইল</th>
                <th>ইমেইল</th>
                <th>ঠিকানা</th>
                <th>পণ্য</th>
                <th>পরিমাণ</th>
                <th>মোট মূল্য</th>
                <th>পেমেন্ট</th>
                <th>স্ট্যাটাস</th>
                <th>ডেলিভারি কনফার্ম</th>
            </tr>
            <?php if($orders && $orders->num_rows): while($row = $orders->fetch_assoc()): ?>
            <tr>
                <td>#<?= htmlspecialchars($row['order_id']) ?></td>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td><?= htmlspecialchars($row['customer_phone']) ?></td>
                <td><?= htmlspecialchars($row['customer_email']) ?></td>
                <td><?= htmlspecialchars($row['customer_address']) ?></td>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= htmlspecialchars($row['quantity']) ?></td>
                <td><?= htmlspecialchars($row['price'] * $row['quantity']) ?> টাকা</td>
                <td>
                    <?php if($row['payment_method']=='bkash'): ?>
                        বিকাশ<br>TxID: <?= htmlspecialchars($row['bkash_txid']) ?>
                    <?php else: ?>
                     ক্যাশ অন ডেলিভারি 
                    <?php endif; ?>
                </td>
                <td class="status-<?= $row['status'] ?>">
                    <?= $row['status']=='delivered' ? 'ডেলিভারড' : 'অর্ডার গ্রহণ করা হয়েছে' ?>
                </td>
                <td>
                  <?php if($row['status']=='accepted'): ?>
                    <?php if($row['payment_method']=='bkash' && !$row['delivery_confirm_code']): ?>
                        <button type="button" class="otp-btn" id="otpbtn_<?= $row['order_id'] ?>"
                            onclick="sendCode(<?= $row['order_id'] ?>, '<?= htmlspecialchars($row['customer_email']) ?>')">
                            কোড ইমেইলে পাঠাও
                        </button>
                        <span class="otp-status" id="otpstatus_<?= $row['order_id'] ?>"></span>
                        <br>
                    <?php endif; ?>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="action" value="deliver">
                        <input type="hidden" name="oid" value="<?= $row['order_id'] ?>">
                        <?php if($row['payment_method']=='bkash'): ?>
                            <input type="text" class="input-bkash" name="bkash_code" placeholder="bKash কোড/OTP" required>
                            <br>
                            <small style="color:gray;">কাস্টমারের ইমেইল: <?= htmlspecialchars($row['customer_email']) ?></small>
                        <?php endif; ?>
                        <button type="submit" class="deliver-btn">ডেলিভারি সম্পন্ন</button>
                    </form>
                  <?php else: ?>
                    <?php if($row['payment_method']=='bkash' && $row['delivery_confirm_code']): ?>
                        <b>কোড:</b> <?= htmlspecialchars($row['delivery_confirm_code']) ?><br>
                    <?php endif; ?>
                    <small>
                        <?= $row['delivery_confirm_time'] ? date('d M, H:i', strtotime($row['delivery_confirm_time'])) : '' ?>
                    </small>
                  <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="11" style="text-align:center;">কোনো ডেলিভারি নেই</td></tr>
            <?php endif; ?>
        </table>
    </main>
</body>
<style>/* ===== Modern Base Reset ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: #f4f6f9;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* ===== Main Section ===== */
main {
    padding: 30px 20px;
    max-width: 1880px;
    margin: auto;
    margin-top: 130px;
}

/* ===== Heading ===== */
main h2 {
    font-size: 28px;
    margin-bottom: 25px;
    color: #2c3e50;
}

/* ===== Delivery Table ===== */
.delivery-table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 10px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.delivery-table th {
    background: #2d6a4f;
    color: #fff;
    text-align: center;
    padding: 14px 10px;
    font-weight: 600;
    font-size: 15px;
}

.delivery-table td {
    padding: 14px 10px;
    text-align: center;
    border-bottom: 1px solid #f0f0f0;
    font-size: 14px;
    color: #333;
}

/* Status Styles */
.status-delivered {
    color: #27ae60;
    font-weight: bold;
}

.status-accepted {
    color: #f39c12;
    font-weight: bold;
}

/* ===== Buttons ===== */
.otp-btn,
.deliver-btn {
    background: #1d3557;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    margin-top: 5px;
    transition: background 0.3s ease;
}

.otp-btn:hover,
.deliver-btn:hover {
    background: #0e2e4d;
}

/* ===== Input Style ===== */
.input-bkash {
    padding: 8px 10px;
    border: 1px solid #d1d1d1;
    border-radius: 6px;
    width: 90%;
    margin-top: 6px;
    font-size: 14px;
    outline: none;
    transition: border 0.2s;
}
.input-bkash:focus {
    border-color: #2d6a4f;
}

/* ===== Small text & Code Highlight ===== */
small {
    font-size: 12px;
    color: #777;
}

b {
    font-weight: 600;
    color: #34495e;
}

/* ===== OTP Status Message ===== */
.otp-status {
    display: block;
    margin-top: 4px;
    font-size: 13px;
    color: #555;
}
/* ===== Responsive Table ===== */
@media (max-width: 768px) {
    .delivery-table th, .delivery-table td {
        padding: 10px 6px;
        font-size: 12px;
    }

    .otp-btn, .deliver-btn {
        padding: 6px 10px;
        font-size: 12px;
    }

    .input-bkash {
        width: 100%;
    }

    main h2 {
        font-size: 22px;
    }
}
</style>
</html>