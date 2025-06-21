<?php
session_start();
date_default_timezone_set('Asia/Dhaka'); // টাইমজোন সঠিক রাখুন
include 'db_connect.php';

// PHPMailer autoload (Composer দিয়ে ইনস্টল করো: composer require phpmailer/phpmailer)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

function sendOtpMail($to, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sohojjogan@gmail.com';
        $mail->Password   = 'sogm kxea jqcb ocxz'; // <-- আপনার Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->setFrom('sohojjogan@gmail.com', 'Sohojjogan');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP';
        $mail->Body    = "আপনার ওটিপি হলো: <b>$otp</b><br>মেয়াদ: ১০ মিনিটের জন্য বৈধ।";
        $mail->AltBody = "আপনার ওটিপি হলো: $otp\nমেয়াদ: ১০ মিনিটের জন্য বৈধ।";
        $mail->send();
        return true;
    } catch (Exception $e) {
        // echo "Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}

// Table map
$table_map = [
    'customer' => ['table'=>'customers', 'id'=>'customer_id','email'=>'customer_email', 'pass'=>'customer_password'],
    'shop_owner' => ['table'=>'shop_owners', 'id'=>'shop_owner_id','email'=>'shop_owner_email', 'pass'=>'shop_owner_password'],
    'delivery_man' => ['table'=>'delivery_men', 'id'=>'delivery_man_id','email'=>'delivery_man_email', 'pass'=>'delivery_man_password']
];

// Determine stage
$stage = $_POST['stage'] ?? 'email'; // email, otp, reset
$msg = "";

// Stage 1: Email input
if ($stage==='email' && $_SERVER['REQUEST_METHOD']==='POST') {
    $email = trim($_POST['resetEmailOrPhone'] ?? '');
    $userType = '';
    $userId = 0;

    // Try all user tables
    foreach ($table_map as $type=>$map) {
        $sql = "SELECT {$map['id']} as user_id FROM {$map['table']} WHERE {$map['email']}=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute(); $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $userType = $type;
            $userId = $row['user_id'];
            break;
        }
    }
    if ($userType) {
        $otp = rand(100000,999999);
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $ins = $conn->prepare("INSERT INTO password_resets (user_type, user_id, reset_code, expires_at) VALUES (?, ?, ?, ?)");
        $ins->bind_param('siss', $userType, $userId, $otp, $expires); // otp integer, তাই 'siss'
        $ins->execute();
        // এখানে PHPMailer দিয়ে পাঠান
        if (sendOtpMail($email, $otp)) {
            $_SESSION['reset_type'] = $userType;
            $_SESSION['reset_id'] = $userId;
            $_SESSION['reset_email'] = $email;
            $msg = "ইমেইলে OTP পাঠানো হয়েছে।";
            $stage = 'otp';
        } else {
            $msg = "OTP পাঠাতে সমস্যা হয়েছে! পরে চেষ্টা করুন।";
        }
    } else {
        // Not found: show message then redirect (JS alert + redirect)
        echo "<script>
            alert('এই ইমেইল কোনো অ্যাকাউন্টের সাথে মেলেনি!');
            window.location.href = '../Html/index.php';
        </script>";
        exit;
    }
}

// Stage 2: OTP verify
elseif ($stage==='otp' && $_SERVER['REQUEST_METHOD']==='POST') {
    $input_code = intval($_POST['otp_code'] ?? ''); // intval দিয়ে integer বানালাম
    $userType = $_SESSION['reset_type'] ?? '';
    $userId = $_SESSION['reset_id'] ?? 0;
    $sql = "SELECT * FROM password_resets WHERE user_type=? AND user_id=? AND reset_code=? AND used=0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sii', $userType, $userId, $input_code); // 'sii' কারণ OTP integer
    $stmt->execute(); $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION['reset_verified'] = true;
        $msg = "OTP যাচাই হয়েছে, নতুন পাসওয়ার্ড দিন।";
        $stage = 'reset';
    } else {
        $msg = "ভুল বা মেয়াদোত্তীর্ণ OTP!";
    }
}

// Stage 3: Password reset
elseif ($stage==='reset' && $_SERVER['REQUEST_METHOD']==='POST') {
    if (!($_SESSION['reset_verified']??false)) { header("Location: ".$_SERVER['PHP_SELF']); exit; }
    $new_pass = $_POST['new_password'] ?? '';
    $userType = $_SESSION['reset_type'];
    $userId = $_SESSION['reset_id'];
    $map = $table_map[$userType];
    $hash = password_hash($new_pass, PASSWORD_DEFAULT);
    $sql = "UPDATE {$map['table']} SET {$map['pass']}=? WHERE {$map['id']}=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $hash, $userId);
    $stmt->execute();
    $upd = $conn->prepare("UPDATE password_resets SET used=1 WHERE user_type=? AND user_id=? AND used=0");
    $upd->bind_param('si', $userType, $userId); $upd->execute();
    session_unset(); session_destroy();
    $msg = "পাসওয়ার্ড সফলভাবে পরিবর্তন হয়েছে! <a href='../Html/index.php'>লগইন করুন</a>";
    $stage = 'done';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>পাসওয়ার্ড রিসেট</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;700&display=swap" rel="stylesheet">
    <script>
      function closeModal() {document.getElementById('forgotPasswordModal').style.display='none';}
    </script>
    <style>
    /* Modal Overlay */
    .modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
      z-index: 9999;
      display: flex;
    }
    /* Success message styling */
    .success-message {
        background: #e8f8f5;
        color: #27ae60;
        border: 1px solid #bff0e2;
        border-radius: 5px;
        padding: 15px 20px;
        margin: 20px auto 15px auto;
        text-align: center;
        font-size: 1.12em;
        box-shadow: 0 2px 8px rgba(39, 174, 96, 0.08);
        max-width: 350px;
    }

    .success-message a {
        color: #219150;
        font-weight: bold;
        text-decoration: none;
        margin-left: 8px;
        transition: color 0.2s;
    }

    .success-message a:hover {
        text-decoration: underline;
        color: #145b36;
    }
    /* Modal Box */
    .modal-content {
      background-color: #fff;
      padding: 30px;
      border-radius: 12px;
      max-width: 400px;
      width: 90%;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
      animation: fadeIn 0.3s ease-in-out;
      position: relative;
      font-family: 'Noto Sans Bengali', sans-serif;
    }

    /* Close Button */
    .forgot-close-btn {
      position: absolute;
      top: 15px;
      right: 20px;
      font-size: 24px;
      font-weight: bold;
      color: #888;
      cursor: pointer;
      transition: color 0.3s ease;
    }
    .forgot-close-btn:hover {
      color: #e74c3c;
    }

    /* Headings */
    .modal-content h2 {
      font-size: 22px;
      margin-bottom: 20px;
      color: #333;
      text-align: center;
    }

    /* Form Elements */
    .modal-content form {
      display: flex;
      flex-direction: column;
    }
    .modal-content label {
      margin-bottom: 8px;
      color: #555;
      font-weight: bold;
    }
    .modal-content input[type="text"],
    .modal-content input[type="password"] {
      padding: 10px 12px;
      margin-bottom: 16px;
      border: 1px solid #ccc;
      border-radius: 6px;
      transition: border-color 0.3s ease;
    }
    .modal-content input:focus {
      border-color: #007bff;
      outline: none;
    }

    /* Submit Button */
    .modal-content button[type="submit"] {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 10px 16px;
      font-size: 16px;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .modal-content button[type="submit"]:hover {
      background-color: #0056b3;
    }

    /* Success/Error Messages */
    .msg {
      margin-bottom: 15px;
      padding: 10px;
      background-color: #fce4e4;
      color: #b71c1c;
      border-left: 4px solid #f44336;
      border-radius: 6px;
    }
    .msg.success {
      background-color: #e8f5e9;
      color: #2e7d32;
      border-left-color: #4caf50;
    }

    /* Animation */
    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }
    </style>
</head>
<body>
<div id="forgotPasswordModal" class="modal">
  <div class="modal-content" style="position:relative;">
    <span class="forgot-close-btn" id="closeForgotModal" onclick="closeModal()">&times;</span>
    <h2>পাসওয়ার্ড রিসেট করুন</h2>
    <?php if ($msg): ?>
      <div class="<?= $stage==='done' ? 'success-message' : ($stage==='otp' || $stage==='reset' ? 'msg' : 'msg') ?><?= $stage==='done'?' success':'' ?>">
        <?= $msg ?>
      </div>
    <?php endif; ?>
    <?php if ($stage==='email'): ?>
      <form method="post" action="">
        <input type="hidden" name="stage" value="email">
        <label for="resetEmailOrPhone">ইমেইল :</label>
        <input type="text" id="resetEmailOrPhone" name="resetEmailOrPhone" required>
        <button type="submit">রিসেট OTP পাঠান</button>
      </form>
    <?php elseif ($stage==='otp'): ?>
      <form method="post" action="">
        <input type="hidden" name="stage" value="otp">
        <label>ইমেইলে পাওয়া OTP দিন:</label>
        <input type="text" name="otp_code" required>
        <button type="submit">যাচাই করুন</button>
      </form>
    <?php elseif ($stage==='reset'): ?>
      <form method="post" action="">
        <input type="hidden" name="stage" value="reset">
        <label>নতুন পাসওয়ার্ড দিন:</label>
        <input type="password" name="new_password" required>
        <button type="submit">পাসওয়ার্ড পরিবর্তন</button>
      </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>