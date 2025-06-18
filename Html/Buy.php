<?php
include '../PHP/db_connect.php';
session_start();

// ---- Customer Coin Fetch ----
function bn_number($number) {
    $bn_digits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    return strtr($number, ['0'=>$bn_digits[0],'1'=>$bn_digits[1],'2'=>$bn_digits[2],'3'=>$bn_digits[3],'4'=>$bn_digits[4],'5'=>$bn_digits[5],'6'=>$bn_digits[6],'7'=>$bn_digits[7],'8'=>$bn_digits[8],'9'=>$bn_digits[9]]);
}

$customer_coins = 0;
if (isset($_SESSION['customer_email'])) {
    $customer_email = $_SESSION['customer_email'];
    $coin_sql = "SELECT customer_coins FROM customers WHERE customer_email = ?";
    $coin_stmt = $conn->prepare($coin_sql);
    $coin_stmt->bind_param('s', $customer_email);
    $coin_stmt->execute();
    $coin_stmt->bind_result($customer_coins);
    $coin_stmt->fetch();
    $coin_stmt->close();
}

// ---- Product Logic ----
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
if ($productId <= 0) {
    echo "সঠিক পণ্য নির্বাচন করুন।";
    exit();
}

$stmt = $conn->prepare("SELECT product_name, price, product_image_path FROM products WHERE product_id = ?");
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
?>


<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>সহজ যোগান (Sohaj Jogan) - অর্ডার করুন</title>
    <link rel="stylesheet" href="../CSS/Buy.css?v=1" />
</head>
<body>
<header>
    <div class="logo">
  <a href="../Html/Customer_Home.php">
    <img src="../Images/Logo.png" alt="Sohaj Jogan Logo">
</a>        <h2>সহজ যোগান</h2>
    </div>
    <div class="icons">
  
<!-- Show Customer Coins -->
<div class="coin-balance">
    <img src="../Images/coin-icon.png" alt="Coins" class="coin-icon">
    <span id="coinCount"><?php echo bn_number($customer_coins); ?></span>
</div>
</div>
</header>
</header>

<main>
    <form id="orderForm" onsubmit="return confirmOrder()">
        <div class="buy-container">
            <!-- গ্রাহকের তথ্য -->
            <section class="customer-info">
                <h3>গ্রাহকের তথ্য</h3>

                <label for="customer-name">নামঃ</label>
                <input type="text" id="customer-name" name="customer_name" placeholder="আপনার নাম লিখুন" required />

                <label for="customer-address">ঠিকানাঃ</label>
                <input type="text" id="customer-address" name="customer_address" placeholder="আপনার ঠিকানা লিখুন" required />

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
                    <label for="home">হোম ডেলিভারি</label>

                    <input type="radio" id="pickup" name="delivery" value="pickup" onchange="updateDelivery()" />
                    <label for="pickup">স্টোর পিকআপ</label>
                      <input type="radio" id="coin" name="delivery" value="coin" onchange="updateDelivery()" />
    <label for="coin">কয়েন ব্যবহার করুন</label>
                </div>
</section>
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
                    <p class="delivery-charge" id="delivery-charge">হোম ডেলিভারি: ২০ টাকা</p>
                    <p class="total" id="total-price">মোট মূল্যঃ <?= number_format($productPrice + 20, 2) ?> টাকা</p>
                </div>

                <button type="submit" class="confirm-btn">অর্ডার নিশ্চিত করুন</button>
            </section>
        </div>
    </form>
</main>
    

<script>
    let quantity = 1;
    const unitPrice = <?= $productPrice ?>; // PHP থেকে প্রোডাক্ট প্রাইস নিচ্ছি
    let deliveryCharge = 20;

    // DOM elements
    const qtyDisplay = document.getElementById('qty-display');
    const qtyPriceDisplay = document.getElementById('qty-display-price');
    const unitPriceElem = document.getElementById('unit-price');
    const deliveryChargeElem = document.getElementById('delivery-charge');
    const totalPriceElem = document.getElementById('total-price');

    // Function to convert English digits to Bengali digits
    function toBengali(num) {
        return num.toString().replace(/\d/g, d => "০১২৩৪৫৬৭৮৯"[d]);
    }

    // Update all price displays
    function updateDisplay() {
        qtyDisplay.textContent = toBengali(quantity);
        qtyPriceDisplay.textContent = toBengali(quantity);
        unitPriceElem.textContent = `মূল্যঃ ${toBengali(unitPrice)} × ${toBengali(quantity)}`;
        deliveryChargeElem.textContent = (deliveryCharge === 0) 
            ? "স্টোর পিকআপ: কোন ডেলিভারি চার্জ নেই" 
            : `হোম ডেলিভারি: ${toBengali(deliveryCharge)} টাকা`;
        const total = (unitPrice * quantity) + deliveryCharge;
        totalPriceElem.textContent = `মোট মূল্যঃ ${toBengali(total.toFixed(2))} টাকা`;
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
        const selected = document.querySelector('input[name="delivery"]:checked').value;
        if (selected === "home") {
            deliveryCharge = 20;
        } else {
            deliveryCharge = 0;
        }
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

        return confirm("আপনি কি অর্ডার নিশ্চিত করতে চান?");
    }

    // Initialize display on page load
    updateDisplay();
    
</script>

</body>
</html>