<?php
include '../PHP/db_connect.php';
session_start();

// ---- Initialize flags to avoid undefined variable warnings ----
$showSuccess = false;
$showError = false;
$successMessage = "";
$errorMessage = "";

// Check if the user is logged in
if (!isset($_SESSION['customer_email'])) {
    echo "<script>
        alert('You must log in first!');
        window.location.href = '../Html/index.php';
    </script>";
    exit();
}

// ---- Customer Coin Fetch ----
function bn_number($number) {
    $bn_digits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    return strtr($number, ['0'=>$bn_digits[0],'1'=>$bn_digits[1],'2'=>$bn_digits[2],'3'=>$bn_digits[3],'4'=>$bn_digits[4],'5'=>$bn_digits[5],'6'=>$bn_digits[6],'7'=>$bn_digits[7],'8'=>$bn_digits[8],'9'=>$bn_digits[9]]);
}

// Fetch coins and customer_id if logged in
$customer_coins = 0;
$customer_id = 0;
if (isset($_SESSION['customer_email'])) {
    $customer_email = $_SESSION['customer_email'];
    $coin_sql = "SELECT customer_coins, customer_id FROM customers WHERE customer_email = ?";
    $coin_stmt = $conn->prepare($coin_sql);
    $coin_stmt->bind_param('s', $customer_email);
    $coin_stmt->execute();
    $coin_stmt->bind_result($customer_coins, $customer_id);
    $coin_stmt->fetch();
    $coin_stmt->close();
}

// ---- Product Logic ----
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
if (!$productId) { echo "সঠিক পণ্য নির্বাচন করুন।"; exit(); }

// Fetch product (with shop_owner_id)
$stmt = $conn->prepare("SELECT product_name, price, product_image_path, shop_owner_id FROM products WHERE product_id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "পণ্য পাওয়া যায়নি!";
    exit();
}
$product = $result->fetch_assoc();
$productName = htmlspecialchars($product['product_name']);
$productPrice = (float)$product['price'];
$productImage = htmlspecialchars($product['product_image_path']);
$shop_owner_id = (int)$product['shop_owner_id'];

// ---- Shop Location from shop_owners (lat/lng) ----
$sql = "SELECT address_street, address_area, address_city, address_postcode, address_division, shop_latitude, shop_longitude FROM shop_owners WHERE shop_owner_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $shop_owner_id);
$stmt->execute();
$stmt->bind_result($street, $area, $city, $postcode, $division, $shop_latitude, $shop_longitude);
$stmt->fetch();
$stmt->close();

$shop_lat = $shop_latitude;
$shop_lng = $shop_longitude;

// If lat/lng missing, show error
if (!$shop_lat || !$shop_lng) {
    echo "<div style='color:red;text-align:center;margin:25px 0;font-size:1.2em;'>দয়া করে দোকানের লোকেশন সঠিকভাবে যুক্ত করুন।</div>";
    exit();
}

// ---- Main Order Handling (POST to this page) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST ডেটা সংগ্রহ
    $product_id = (int)$_POST['product_id'];
    $quantity = max(1, (int)$_POST['quantity']);
    $delivery = $_POST['delivery'] ?? '';
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_address = trim($_POST['customer_address'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $customer_comment = trim($_POST['customer_comment'] ?? '');
    $distance = isset($_POST['distance']) ? (float)$_POST['distance'] : 0;
    $delivery_charge = isset($_POST['delivery_charge']) ? (int)$_POST['delivery_charge'] : 0;
    $payment_method = $_POST['payment_method'] ?? 'cod';
    $bkash_txid = trim($_POST['bkash_txid'] ?? '');

    // shop_owner_id fetch (again, in case someone tampers with form)
    $shop_owner_id = 0;
    $stmt = $conn->prepare("SELECT shop_owner_id FROM products WHERE product_id = ?");
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $stmt->bind_result($shop_owner_id);
    $stmt->fetch();
    $stmt->close();

    // customer_id fetch (already got above, but double check if not set)
    if (!$customer_id && isset($_SESSION['customer_email'])) {
        $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE customer_email = ?");
        $stmt->bind_param('s', $_SESSION['customer_email']);
        $stmt->execute();
        $stmt->bind_result($customer_id);
        $stmt->fetch();
        $stmt->close();
    }

    // Validation
    if (!$product_id || !$quantity || !$delivery || !$customer_name || !$customer_address || !$customer_phone || !$payment_method) {
        $showError = true;
        $errorMessage = "সব তথ্য সঠিকভাবে পূরণ করুন।";
    } elseif ($payment_method === 'bkash' && !$bkash_txid) {
        $showError = true;
        $errorMessage = "bKash Transaction ID দিন।";
    } else {
        // (১) অর্ডার টেবিলে সেভ করুন (payment_method, bkash_txid সহ)
        $sql = "INSERT INTO orders 
        (product_id, shop_owner_id, customer_id, quantity, delivery_method, customer_name, customer_address, customer_phone, customer_comment, distance, delivery_charge, order_time)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt5 = $conn->prepare($sql);
        $stmt5->bind_param(
            'iiiisssssid',
            $product_id, $shop_owner_id, $customer_id, $quantity,
            $delivery, $customer_name, $customer_address, $customer_phone, $customer_comment,
            $distance, $delivery_charge
        );
        $success = $stmt5->execute();
        $order_id = $conn->insert_id;
        $stmt5->close();

        // Payment Insert
        if ($success) {
            $amount = ($payment_method == 'coin') ? 0 : ($productPrice * $quantity + $delivery_charge);
            $payment_status = 'pending';
            $sql2 = "INSERT INTO payments (order_id, payment_method, bkash_txid, amount, payment_status, payment_time)
                     VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt6 = $conn->prepare($sql2);
            $stmt6->bind_param(
                'issis',
                $order_id, $payment_method, $bkash_txid, $amount, $payment_status
            );
            $stmt6->execute();
            $stmt6->close();
        }
        // (২) product টেবিল থেকে stock কমান
        if ($success) {
            $update_sql = "UPDATE products SET stock = stock - ? WHERE product_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param('ii', $quantity, $product_id);
            $update_stmt->execute();
            $update_stmt->close();

            // (৩) কয়েন দিয়ে ফ্রি ডেলিভারি হলে কয়েন কেটে ফেলুন (if logged in, delivery == coin)
            if ($delivery === 'coin' && isset($_SESSION['customer_email'])) {
                $update_coins_sql = "UPDATE customers SET customer_coins = customer_coins - ? WHERE customer_email = ?";
                $update_coins_stmt = $conn->prepare($update_coins_sql);
                $update_coins_stmt->bind_param('is', $delivery_charge, $_SESSION['customer_email']);
                $update_coins_stmt->execute();
                $update_coins_stmt->close();
            }
            // (১) কাস্টমারকে notification দিন
            $msg_customer = "আপনার অর্ডারটি গ্রহণ করা হয়েছে!";
            $stmt_n1 = $conn->prepare("INSERT INTO notifications (user_id, user_type, order_id, message) VALUES (?, 'customer', ?, ?)");
            $stmt_n1->bind_param('iis', $customer_id, $order_id, $msg_customer);
            $stmt_n1->execute();
            $stmt_n1->close();

            // (২) Shop Owner কে notification দিন
            $msg_shop = "নতুন অর্ডার এসেছে (Order ID: $order_id)। কাস্টমার: $customer_name";
            $stmt_n2 = $conn->prepare("INSERT INTO notifications (user_id, user_type, order_id, message) VALUES (?, 'shop_owner', ?, ?)");
            $stmt_n2->bind_param('iis', $shop_owner_id, $order_id, $msg_shop);
            $stmt_n2->execute();
            $stmt_n2->close();

            // (৩) সফল অর্ডার হলে ০.৫ কয়েন কাস্টমারকে যোগ করুন
            if ($success && $customer_id) {
                $reward_coin = 0.5;
                $update_reward = $conn->prepare("UPDATE customers SET customer_coins = customer_coins + ? WHERE customer_id = ?");
                $update_reward->bind_param("di", $reward_coin, $customer_id);
                $update_reward->execute();
                $update_reward->close();
            }

            $showSuccess = true;
            $successMessage = "✅ অর্ডার সফলভাবে গ্রহণ করা হয়েছে! আপনি কিছুক্ষণের মধ্যে হোম পেজে চলে যাবেন...";
        } else {
            $showError = true;
            $errorMessage = "❌ অর্ডার গ্রহণে ত্রুটি হয়েছে, আবার চেষ্টা করুন।";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>সহজ যোগান (Sohaj Jogan) - অর্ডার করুন</title>
    <link rel="stylesheet" href="../CSS/Buy.css?v=1" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        .coin-info {
            background: #f6f8ff;
            color: #1a237e;
            border-radius: 6px;
            padding: 10px 16px;
            margin-bottom: 16px;
            font-size: 1rem;
            box-shadow: 0 2px 8px rgba(50,50,100,0.06);
        }
        .coin-promo {
          max-width: 940px;
          margin: 24px auto;
          background: linear-gradient(135deg, #ffe082 0%, #e3f2fd 74%);
          border-radius: 18px;
          box-shadow: 0 6px 24px rgba(33, 150, 243, 0.09);
          padding: 22px 28px 16px 28px;
          border: 2.5px solid #fbc02d;
          position: relative;
          overflow: hidden;
          transition: box-shadow 0.25s;
        }
        .coin-promo:hover {
          box-shadow: 0 10px 36px rgba(251, 192, 45, 0.18), 0 2px 8px rgba(33,150,243,0.09);
        }
        .coin-promo-header {
          display: flex;
          align-items: center;
          margin-bottom: 14px;
        }
        .coin-promo-coin {
          width: 42px;
          height: 42px;
          margin-right: 14px;
          filter: drop-shadow(0 1px 2px #fffde7);
        }
        .coin-promo-title {
          font-size: 1.32rem;
          font-weight: 700;
          color: #f9a825;
          letter-spacing: 1px;
        }
        .coin-promo-tagline {
          display: block;
          font-size: 0.98rem;
          color: #1976d2;
          font-weight: 600;
          margin-top: 2px;
        }
        .coin-promo-list {
          margin-top: 6px;
        }
        .coin-promo-list > div {
          font-size: 1.09rem;
          color: #263238;
          margin-bottom: 8px;
          display: flex;
          align-items: flex-start;
        }
        .cp-bullet {
          color: #fbc02d;
          font-size: 1.35em;
          margin-right: 10px;
          font-weight: bold;
          line-height: 1;
          transform: scaleY(1.2);
        }
        @media (max-width: 600px) {
          .coin-promo {
            padding: 12px 6vw 10px 6vw;
            max-width: 99vw;
          }
          .coin-promo-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 3px;
          }
          .coin-promo-coin {
            width: 36px;
            height: 36px;
            margin-bottom: 2px;
          }
        }
        .distance-result {
          font-size: 1.07rem;
          color: #1565c0;
          margin-top: 8px;
        }
        button.distance-check-btn {
          background-color: #43a047;
          color: #fff;
          border: none;
          border-radius: 8px;
          padding: 10px 26px;
          font-size: 1.13rem;
          font-weight: 700;
          margin-left: 10px;
          cursor: pointer;
          box-shadow: 0 3px 12px rgba(67, 160, 71, 0.11);
          transition: background 0.22s, box-shadow 0.18s, color 0.17s, transform 0.12s;
          letter-spacing: 0.015em;
        }
        button.distance-check-btn:hover,
        button.distance-check-btn:focus {
          background-color: #388e3c;
          box-shadow: 0 6px 24px rgba(67, 160, 71, 0.20);
          outline: none;
          transform: translateY(-2px) scale(1.05);
        }
    </style>
</head>
<body>
<header>
    <div class="logo">
        <a href="../Html/Customer_Home.php">
            <img src="../Images/Logo.png" alt="Sohaj Jogan Logo">
        </a>
        <h2>সহজ যোগান</h2>
    </div>
    <div class="icons">
        <!-- Show Customer Coins -->
        <div class="coin-balance">
            <img src="../Images/coin-icon.png" alt="Coins" class="coin-icon">
            <span id="coinCount"><?php echo bn_number($customer_coins); ?></span>
        </div>
    </div>
</header>

<main>
<!-- Success/Error message block -->
<?php if ($showSuccess): ?>
    <div class="order-success" style="background:#c8e6c9;color:#2e7d32;padding:15px 20px;margin:18px auto;max-width:420px;text-align:center;border-radius:8px;font-size:1.18em;">
        <?= $successMessage ?>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = "../Html/Customer_Home.php";
        }, 2500);
    </script>
<?php elseif ($showError): ?>
    <div class="order-error" style="background:#ffcdd2;color:#c62828;padding:15px 20px;margin:18px auto;max-width:420px;text-align:center;border-radius:8px;font-size:1.14em;">
        <?= $errorMessage ?>
    </div>
<?php endif; ?>

<div class="coin-promo">
  <div class="coin-promo-header">
    <img src="../Images/coin-icon.png" alt="coin" class="coin-promo-coin">
    <div>
      <span class="coin-promo-title">কয়েন সিস্টেম</span>
      <span class="coin-promo-tagline">রিওয়ার্ডস পান, সহজে সাশ্রয় করুন!</span>
    </div>
  </div>
  <div class="coin-promo-list">
    <div>
      <span class="cp-bullet">➤</span>
      <b>প্রতি পণ্য অর্ডারে পাবেন ০.৫ কয়েন</b>
    </div>
    <div>
      <span class="cp-bullet">➤</span>
      <b>ডেলিভারি চার্জ: প্রতি ১ কিমি দূরত্বে ২০ টাকা/২০ কয়েন</b>
      <br>
      <span style="color:#1976d2;font-size:.98em;margin-left:24px;">
  (যত কিমি বাড়বে, প্রতি কিমিতে ২০ টাকা/কয়েন যোগ হবে। যেমন: ১.৪ কিমি হলে ৪০ টাকা, ২.৬ কিমি হলে ৬০ টাকা)      </span>
    </div>
    <div>
      <span class="cp-bullet">➤</span>
      <b>গেমে ৫০০ স্কোর = ১ কয়েন</b>
    </div>
  </div>
</div>
<?php if (!$showSuccess): ?>
    <form id="orderForm" action="" method="POST" onsubmit="return confirmOrder()">
        <input type="hidden" name="product_id" value="<?= $productId ?>" />
        <input type="hidden" id="quantity" name="quantity" value="1" />
        <input type="hidden" id="distance" name="distance" />
        <input type="hidden" id="delivery_charge" name="delivery_charge" />
        <div class="buy-container">
            <section class="customer-info">
                <h3>গ্রাহকের তথ্য</h3>
                <label for="customer-name">নামঃ</label>
                <input type="text" id="customer-name" name="customer_name" placeholder="আপনার নাম লিখুন" required />
                <label for="customer-address">ঠিকানাঃ</label>
                <input type="text" id="customer-address" name="customer_address" placeholder="আপনার ঠিকানা লিখুন" required />
                <button type="button" class="distance-check-btn" onclick="findDistance()">দূরত্ব ও চার্জ দেখুন</button>
                <div class="distance-result">
                  <span id="distance-info"></span>
                  <span id="delivery-charge-info"></span>
                </div>
                <label for="customer-phone">ফোন নাম্বারঃ</label>
                <input type="tel" id="customer-phone" name="customer_phone" placeholder="মোবাইল নাম্বার লিখুন" required pattern="[0-9]{10,11}" />
                <small>১০ থেকে ১১ সংখ্যার ফোন নাম্বার দিন</small>
                <label for="customer-comment">মন্তব্য:</label>
                <textarea id="customer-comment" name="customer_comment" placeholder="যদি কিছু জানাতে চান......"></textarea>
            </section>
           <section class="order-summary">
                <div class="delivery-method">
                    <h4>বিলি পদ্ধতি</h4>
                    <input type="radio" id="home" name="delivery" value="home" checked onchange="updateDelivery()" />
                    <label for="home">হোম ডেলিভারি (চার্জ প্রযোজ্য)</label>
                    <input type="radio" id="pickup" name="delivery" value="pickup" onchange="updateDelivery()" />
                    <label for="pickup">স্টোর পিকআপ (চার্জ নেই)</label>
                    <input type="radio" id="coin" name="delivery" value="coin" onchange="updateDelivery()" />
                    <label for="coin">কয়েন দিয়ে ফ্রি ডেলিভারি (চার্জ = কয়েন)</label>
                </div>
                <div class="payment-method">
                    <h4>পেমেন্ট অপশন</h4>
                    <input type="radio" id="cod" name="payment_method" value="cod" checked>
                    <label for="cod">ক্যাশ অন ডেলিভারি</label>
                    <input type="radio" id="bkash" name="payment_method" value="bkash">
                    <label for="bkash">বিকাশ</label>
                </div>
                <div id="bkash-info">
                    <b>বিকাশ নম্বরঃ 01569129533</b>
                    <p>এই নম্বরে Send Money/Payment করুন।</p>
                    <p>পেমেন্ট করার পর Transaction ID (TxID) লিখুন:</p>
                    <input type="text" id="bkash-txid" name="bkash_txid" placeholder="bKash TxID">
                </div>
                <style>.payment-method {
    background: #e3f2fd;
    border-radius: 10px;
    padding: 16px 18px 12px 18px;
    margin-bottom: 18px;
    box-shadow: 0 2px 12px rgba(33, 150, 243, 0.10);
    max-width: 420px;
}

.payment-method h4 {
    color: #1976d2;
    font-size: 1.18em;
    font-weight: 700;
    margin-bottom: 10px;
}

.payment-method input[type="radio"] {
    accent-color: #1976d2;
    margin-right: 6px;
    width: 18px;
    height: 18px;
    vertical-align: middle;
}

.payment-method label {
    margin-right: 18px;
    font-size: 1.07em;
    color: #263238;
    cursor: pointer;
    font-weight: 500;
}

#bkash-info {
    background: #fffde7;
    border-left: 5px solid #fbc02d;
    border-radius: 8px;
    padding: 14px 18px 12px 18px;
    margin: 14px 0 0 0;
    box-shadow: 0 1px 6px rgba(251, 192, 45, 0.11);
    font-size: 1.09em;
    display: none; /* Default: hidden, shown by JS if bKash is selected */
    animation: fadeIn 0.35s ease;
}

#bkash-info b {
    color: #f9a825;
    font-size: 1.12em;
    letter-spacing: 0.5px;
}

#bkash-info p {
    margin: 4px 0 0 0;
    color: #6d4c41;
}

#bkash-txid {
    margin-top: 8px;
    padding: 8px 12px;
    border: 1.5px solid #fbc02d;
    border-radius: 6px;
    font-size: 1.09em;
    background: #fffde7;
    box-shadow: 0 1px 4px rgba(251,192,45,0.06);
    width: 220px;
    transition: border 0.22s;
}

#bkash-txid:focus {
    border-color: #fbc02d;
    outline: 0;
    background: #fffde7;
}

@media (max-width: 600px) {
    .payment-method {
        max-width: 99vw;
        padding: 10px 5vw 8px 5vw;
    }
    #bkash-info {
        padding: 10px 5vw 8px 5vw;
    }
}
@keyframes fadeIn {
    0% { opacity: 0; transform: translateY(10px);}
    100% { opacity: 1; transform: translateY(0);}
}</style>

                <button type="button" class="summary-title">অর্ডার সংক্ষিপ্ত বিবরণ</button>
                <div class="product-details">
                    <label class="product-name">পণ্যের নাম: <span id="product-name"><?= $productName ?></span></label>
                    <img src="<?= $productImage ?>" alt="<?= $productName ?>" class="product-img" />
                    <div class="quantity-selector">
                        <label for="qty-display">পরিমাণ:</label>
                        <button type="button" class="qty-btn" onclick="decreaseQty()">-</button>
                        <span id="qty-display">1</span> কেজি
                        <button type="button" class="qty-btn" onclick="increaseQty()">+</button>
                    </div>
                    <p id="unit-price">মূল্যঃ <?= number_format($productPrice, 2) ?> × <span id="qty-display-price">1</span></p>
                    <p class="delivery-charge" id="delivery-charge">হোম ডেলিভারি চার্জ: নির্ধারিত হয়নি</p>
                    <p class="total" id="total-price">মোট মূল্যঃ <?= number_format($productPrice, 2) ?> টাকা</p>
                </div>
                <button type="submit" class="confirm-btn">অর্ডার নিশ্চিত করুন</button>
            </section>
        </div>
    </form>
<?php endif; ?>
</main>

<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
    // bKash select করলে TxID input দেখাবে, COD হলে লুকাবে
document.getElementById('cod').addEventListener('change', function() {
    document.getElementById('bkash-info').style.display = 'none';
});
document.getElementById('bkash').addEventListener('change', function() {
    document.getElementById('bkash-info').style.display = 'block';
});
window.onload = function() {
    // রিফ্রেশ হলেও সঠিক display
    if(document.getElementById('bkash').checked) {
        document.getElementById('bkash-info').style.display = 'block';
    } else {
        document.getElementById('bkash-info').style.display = 'none';
    }
};

let quantity = 1;
const unitPrice = <?= $productPrice ?>;
const customerCoins = <?= $customer_coins ?>;
const shopLat = <?= $shop_lat ?>;
const shopLng = <?= $shop_lng ?>;

let deliveryCharge = 0;
let currentDistance = 0;

// DOM elements
const qtyDisplay = document.getElementById('qty-display');
const qtyPriceDisplay = document.getElementById('qty-display-price');
const unitPriceElem = document.getElementById('unit-price');
const deliveryChargeElem = document.getElementById('delivery-charge');
const totalPriceElem = document.getElementById('total-price');
const quantityInput = document.getElementById('quantity');
const distanceInput = document.getElementById('distance');
const deliveryChargeInput = document.getElementById('delivery_charge');

// Function to convert English digits to Bengali digits
function toBengali(num) {
    return num.toString().replace(/\d/g, d => "০১২৩৪৫৬৭৮৯"[d]);
}

// Find distance using Nominatim + Leaflet
function findDistance() {
    const address = document.getElementById('customer-address').value;
    if (address.length < 4) {
        alert("ঠিকানা লিখুন");
        return;
    }
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`)
    .then(res => res.json())
    .then(data => {
        if (data.length === 0) {
            document.getElementById('distance-info').textContent = "ঠিকানা পাওয়া যায়নি!";
            document.getElementById('delivery-charge-info').textContent = "";
            distanceInput.value = "";
            deliveryChargeInput.value = "";
            currentDistance = 0;
            deliveryCharge = 0;
            updateDisplay();
            return;
        }
        const userLat = parseFloat(data[0].lat);
        const userLng = parseFloat(data[0].lon);

        // Leaflet distance calculation (meter)
        const distanceMeter = L.latLng(shopLat, shopLng).distanceTo([userLat, userLng]);
        const distanceKm = distanceMeter / 1000; // কিমি
        const baseCharge = Math.ceil(distanceKm) * 20; // ১ কিমি = ২০ কয়েন/২০ টাকা

        document.getElementById('distance-info').textContent = `দূরত্ব: ${toBengali(distanceKm.toFixed(2))} কিমি`;
        document.getElementById('delivery-charge-info').textContent = ` | ডেলিভারি চার্জ: ${toBengali(baseCharge)} টাকা/কয়েন`;

        // Hidden input (backend)
        distanceInput.value = distanceKm.toFixed(2);
        deliveryChargeInput.value = baseCharge;
        currentDistance = distanceKm;
        deliveryCharge = baseCharge;
        updateDisplay();
    });
}

function updateDisplay() {
    qtyDisplay.textContent = toBengali(quantity);
    qtyPriceDisplay.textContent = toBengali(quantity);
    unitPriceElem.textContent = `মূল্যঃ ${toBengali(unitPrice)} × ${toBengali(quantity)}`;

    const selected = document.querySelector('input[name="delivery"]:checked').value;

    // কয়েন প্রয়োজন (ডেলিভারি per kg/piece)
    let coinNeeded = deliveryCharge * quantity;

    // If distance/charge not calculated yet, show notice
    if (!deliveryCharge || !currentDistance) {
        deliveryChargeElem.textContent = "দয়া করে আপনার ঠিকানা লিখে দূরত্ব ও চার্জ দেখুন";
        totalPriceElem.textContent = `মোট মূল্যঃ ${toBengali((unitPrice * quantity).toFixed(2))} টাকা`;
        deliveryChargeInput.value = '';
        return;
    }

    if (selected === "coin") {
        if (customerCoins >= coinNeeded) {
            deliveryChargeElem.textContent = `ফ্রি ডেলিভারি (${toBengali(coinNeeded)} কয়েন কেটে যাবে)`;
            totalPriceElem.textContent = `মোট মূল্যঃ ${toBengali((unitPrice * quantity).toFixed(2))} টাকা`;
        } else {
            deliveryChargeElem.textContent = `আপনার কাছে যথেষ্ট কয়েন নেই (${toBengali(coinNeeded)} দরকার)`;
            totalPriceElem.textContent = "";
        }
        deliveryChargeInput.value = coinNeeded; // কয়েন backend-এ পাঠান
    } else if (selected === "pickup") {
        deliveryChargeElem.textContent = "স্টোর পিকআপ: কোন ডেলিভারি চার্জ নেই";
        totalPriceElem.textContent = `মোট মূল্যঃ ${toBengali((unitPrice * quantity).toFixed(2))} টাকা`;
        deliveryChargeInput.value = 0;
    } else if (selected === "home") {
        deliveryChargeElem.textContent = `হোম ডেলিভারি: ${toBengali(deliveryCharge)} টাকা`;
        totalPriceElem.textContent = `মোট মূল্যঃ ${toBengali(((unitPrice * quantity) + deliveryCharge).toFixed(2))} টাকা`;
        deliveryChargeInput.value = deliveryCharge;
    }
    quantityInput.value = quantity;
}

function increaseQty() {
    quantity++;
    updateDisplay();
}

function decreaseQty() {
    if (quantity > 1) {
        quantity--;
        updateDisplay();
    }
}

function updateDelivery() {
    updateDisplay();
}

function confirmOrder() {
    const name = document.getElementById('customer-name').value.trim();
    const address = document.getElementById('customer-address').value.trim();
    const phone = document.getElementById('customer-phone').value.trim();

    if (!name || !address || !phone) {
        alert("দয়া করে সমস্ত আবশ্যক তথ্য পূরণ করুন।");
        return false;
    }

    const phonePattern = /^[0-9]{10,11}$/;
    if (!phonePattern.test(phone)) {
        alert("সঠিক মোবাইল নাম্বার দিন (১০-১১ সংখ্যার)।");
        return false;
    }

    // ঠিকানা থেকে distance/charge বের হয়েছে কিনা
    if (!deliveryCharge || !currentDistance) {
        alert("ঠিকানা দিয়ে দূরত্ব ও চার্জ দেখুন, তারপর অর্ডার করুন!");
        return false;
    }

    // কয়েন দিয়ে ফ্রি ডেলিভারি অপশন
    const selected = document.querySelector('input[name="delivery"]:checked').value;
    // কয়েন লাগবে (ডেলিভারি per kg/piece)
    let coinNeeded = deliveryCharge * quantity;

    if (selected === "coin") {
        if (customerCoins < coinNeeded) {
            alert("আপনার কাছে যথেষ্ট কয়েন নেই!");
            return false;
        }
    }
    // bKash হলে TxID লাগবে
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    if (paymentMethod === 'bkash') {
        const txid = document.getElementById('bkash-txid').value.trim();
        if (!txid) {
            alert("bKash Transaction ID দিন।");
            return false;
        }
    }
    return confirm("আপনি কি অর্ডার নিশ্চিত করতে চান?");
}

// Initialize display on page load
updateDisplay();
</script>
</body>
</html>