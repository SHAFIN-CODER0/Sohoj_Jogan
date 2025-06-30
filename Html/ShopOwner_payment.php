<?php
session_start();
include '../PHP/db_connect.php';

// PHPMailer autoload (composer must be installed)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

// Session থেকে shop_owner_id নিন
$shop_owner_id = $_SESSION['shop_owner_id'];

// shop_owners টেবিল থেকে ইমেইল বের করুন (column name is shop_owner_email)
$stmt_email = $conn->prepare("SELECT shop_owner_email FROM shop_owners WHERE shop_owner_id = ?");
$stmt_email->bind_param("i", $shop_owner_id);
$stmt_email->execute();
$stmt_email->bind_result($shop_owner_email);
$stmt_email->fetch();
$stmt_email->close();

// ---- ব্যালেন্স হিসাব ----
$sql1 = "
SELECT 
    SUM((p.price * o.quantity) + (o.delivery_charge * 0.20)) AS total
FROM orders o
INNER JOIN products p ON o.product_id = p.product_id
WHERE o.shop_owner_id = ? AND o.status = 'delivered'
";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("i", $shop_owner_id);
$stmt1->execute();
$stmt1->bind_result($total_delivered_amount);
$stmt1->fetch();
$stmt1->close();

$sql2 = "SELECT SUM(amount) FROM withdraw_request WHERE shop_owner_id = ? AND status = 'approved'";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $shop_owner_id);
$stmt2->execute();
$stmt2->bind_result($total_withdrawn);
$stmt2->fetch();
$stmt2->close();

$balance = floatval($total_delivered_amount) - floatval($total_withdrawn);
if ($balance < 0) $balance = 0;

$show_otp_form = false;

// Success message for GET param (after redirect)
$success = '';
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = "অনুরোধ সফলভাবে গ্রহণ করা হয়েছে!";
}

// Step 1: Withdraw Request - Send OTP to shop_owner_email
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['send_otp'])) {
    $amount = floatval($_POST['amount']);
    $payment_method = $_POST['payment_method'] ?? '';
    $receiver_number = $_POST['receiver_number'] ?? '';

    if ($amount >= 500 && $payment_method && $receiver_number) {
        if ($amount <= $balance) {
            $otp = rand(100000, 999999);

            // Store request as otp_pending (email ফিল্ডে shop_owner_email)
            $stmt = $conn->prepare("INSERT INTO withdraw_request (shop_owner_id, amount, payment_method, receiver_number, otp_code, otp_verified, status, email) VALUES (?, ?, ?, ?, ?, 0, 'otp_pending', ?)");
            $stmt->bind_param("idssss", $shop_owner_id, $amount, $payment_method, $receiver_number, $otp, $shop_owner_email);
            $stmt->execute();
            $request_id = $stmt->insert_id;

            // Send OTP via PHPMailer to shop_owner_email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'sohojjogan@gmail.com';
                $mail->Password   = 'qhni gyjq xccg gpsq'; // App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('sohojjogan@gmail.com', 'Sohaj Jogan');
                $mail->addAddress($shop_owner_email);

                $mail->isHTML(false);
                $mail->Subject = 'Withdraw OTP (Sohaj Jogan)';
                $mail->Body    = "আপনার টাকা উত্তোলনের অনুরোধের OTP: $otp";

                $mail->send();
                $show_otp_form = true;
            } catch (Exception $e) {
                $error = "OTP ইমেইল পাঠানো যায়নি! Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $error = "আপনার ব্যালেন্সে পর্যাপ্ত টাকা নেই!";
        }
    } else {
        $error = "সমস্ত তথ্য সঠিকভাবে পূরণ করুন!";
    }
}

// Step 2: OTP Verification
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verify_otp'])) {
    $otp = $_POST['otp'];
    $request_id = $_POST['request_id'];

    // Check OTP
    $stmt = $conn->prepare("SELECT otp_code FROM withdraw_request WHERE id=? AND shop_owner_id=? AND email=?");
    $stmt->bind_param("iis", $request_id, $shop_owner_id, $shop_owner_email);
    $stmt->execute();
    $stmt->bind_result($db_otp);
    $stmt->fetch();
    $stmt->close();

    if ($otp == $db_otp) {
        $stmt = $conn->prepare("UPDATE withdraw_request SET otp_verified=1, status='pending' WHERE id=?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        // Redirect to GET success after OTP success
        header("Location: ".$_SERVER['PHP_SELF']."?success=1");
        exit;
    } else {
        $error = "OTP ভুল! সঠিক কোড দিন।";
        $show_otp_form = true;
        $_POST['request_id'] = $request_id;
    }
}

// উত্তোলন ইতিহাস
$stmt = $conn->prepare("SELECT amount, payment_method, receiver_number, status, request_date FROM withdraw_request WHERE shop_owner_id = ? ORDER BY request_date DESC");
$stmt->bind_param("i", $shop_owner_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../CSS/Customer_Home.css?=1">
</head>
<body>
<header>
    <div class="logo" id="logoClickable" style="cursor:pointer;">
        <img src="../Images/Logo.png" alt="Liberty Logo">
        <h2>সহজ যোগান</h2>
    </div>
    <script>
        document.getElementById('logoClickable').addEventListener('click', function() {
            window.location.href = '../Html/ShopOwner_Home.php';
        });
    </script>
    <nav></nav>
</header>
<main class="withdraw-section">

    <div style="background:#e8f0fe;color:#1976d2;padding:16px;border-radius:8px;margin-bottom:18px;font-size:1.2rem;text-align:center;">
        আপনার বর্তমান ব্যালেন্স: <b>৳<?= number_format($balance,2) ?></b>
    </div>

    <h3>টাকা উত্তোলন অনুরোধ</h3>
    <?php if (!empty($success)): ?>
        <div class="success"><?= $success ?></div>
    <?php elseif (!empty($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <!-- OTP ফর্ম -->
    <?php if ($show_otp_form): ?>
        <form action="" method="POST" style="max-width:340px;margin:auto;">
            <input type="hidden" name="request_id" value="<?= $request_id ?>">
            <label>আপনার ইমেইলে পাঠানো OTP দিন:</label>
            <input type="text" name="otp" required pattern="\d{6}" maxlength="6">
            <button type="submit" name="verify_otp">ভেরিফাই করুন</button>
        </form>
    <?php elseif (empty($success)): ?>
        <!-- Withdraw ফর্ম -->
        <form action="" method="POST" style="max-width:340px;margin:auto;">
            <label>পেমেন্ট মেথড:</label>
            <select name="payment_method" required>
                <option value="">-- নির্বাচন করুন --</option>
                <option value="bkash">বিকাশ</option>
                <option value="nagad">নগদ</option>
                <option value="rocket">রকেট</option>
                <option value="bank">ব্যাংক</option>
            </select>
            <label>প্রাপক নম্বর/একাউন্ট:</label>
            <input type="text" name="receiver_number" required pattern="^\d{10,16}$" title="সঠিক নাম্বার দিন">
            <label>উত্তোলনের পরিমাণ (৳):</label>
            <input type="number" name="amount" min="500" required max="<?= $balance ?>">
            <button type="submit" name="send_otp" <?= ($balance < 500) ? 'disabled style="background:#ccc;cursor:not-allowed;"' : '' ?>>অনুরোধ পাঠান (OTP যাবে <?= htmlspecialchars($shop_owner_email) ?>)</button>
            <?php if ($balance < 500): ?>
                <div class="error" style="margin-top:10px;">কমপক্ষে ৫০০ টাকা জমা হলে উত্তোলন করতে পারবেন।</div>
            <?php endif; ?>
        </form>
    <?php endif; ?>

    <hr>
    <h4>আপনার উত্তোলন ইতিহাস</h4>
    <div class="withdraw-history">
        <?php
        if ($result->num_rows > 0) {
            echo "<table><tr><th>পরিমাণ</th><th>মেথড</th><th>নম্বর</th><th>স্টেটাস</th><th>তারিখ</th></tr>";
            while($row = $result->fetch_assoc()) {
                $bd_status = $row['status']=='pending' ? 'অপেক্ষমাণ' : (
                    $row['status']=='approved' ? 'অনুমোদিত' : (
                        $row['status']=='otp_pending' ? 'OTP যাচাই বাকি' : 'বাতিল'
                    )
                );
                $method = $row['payment_method'];
                if($method=='bkash') $method = 'বিকাশ';
                elseif($method=='nagad') $method = 'নগদ';
                elseif($method=='rocket') $method = 'রকেট';
                elseif($method=='bank') $method = 'ব্যাংক';

                echo "<tr>
                    <td>৳{$row['amount']}</td>
                    <td>{$method}</td>
                    <td>{$row['receiver_number']}</td>
                    <td>{$bd_status}</td>
                    <td>{$row['request_date']}</td>
                </tr>";
            }
            echo "</table>";
        } else {
            echo "কোনো অনুরোধ নেই।";
        }
        ?>
    </div>
</main>
</body>
<style>
.withdraw-section {
    max-width: 820px;
    margin: 80px auto 50px auto;
    padding: 32px;
    margin-top:180px;
    background: #f9f9f9;
    border-radius: 12px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.07);
    font-family: 'SolaimanLipi', 'Noto Sans Bengali', Arial, sans-serif;
}
.withdraw-section h3 {
    margin-top: 0;
    color: #1976d2;
    font-size: 1.35rem;
    text-align: center;
}
.withdraw-section form {
    margin-bottom: 20px;
}
.withdraw-section label {
    font-weight: 600;
    margin-bottom: 6px;
    display: inline-block;
    color: #333;
}
.withdraw-section input[type="number"],
.withdraw-section input[type="text"],
.withdraw-section select {
    width: 100%;
    padding: 8px 12px;
    margin: 10px 0 16px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 1rem;
    background: #fff;
    box-sizing: border-box;
}
.withdraw-section button[type="submit"],
.withdraw-section button {
    padding: 10px 24px;
    background: #1976d2;
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.2s;
}
.withdraw-section button[type="submit"]:hover,
.withdraw-section button:hover {
    background: #1256a3;
}
.withdraw-section hr {
    margin: 32px 0 20px 0;
    border: none;
    border-top: 1px solid #ddd;
}
.withdraw-section h4 {
    margin: 0 0 12px 0;
    font-size: 1.1rem;
    color: #444;
}
.withdraw-history {
    background: #fff;
    padding: 10px;
    border-radius: 8px;
}
.withdraw-history table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
}
.withdraw-history th, .withdraw-history td {
    border: 1px solid #e3e3e3;
    padding: 8px 6px;
    text-align: center;
    font-size: 1rem;
}
.withdraw-history th {
    background: #e8f0fe;
    color: #1976d2;
}
.withdraw-history tr:nth-child(even) td {
    background: #f6faff;
}
.withdraw-history tr:hover td {
    background: #f0f4fa;
}
.success {
    color: green;
    margin-bottom: 12px;
}
.error {
    color: red;
    margin-bottom: 12px;
}
@media (max-width: 520px) {
    .withdraw-section {
        padding: 16px;
    }
    .withdraw-history th, .withdraw-history td {
        font-size: 0.92rem;
        padding: 7px 3px;
    }
}
</style>
</html>