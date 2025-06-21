<?php
session_start();
include '../PHP/db_connect.php';

// Check if the shop owner is logged in by email
if (!isset($_SESSION['shop_owner_email'])) {
    echo "<script>alert('You must log in first!'); window.location.href='../Html/index.php';</script>";
    exit();
}

// Get the shop owner id from the database based on email
$email = $_SESSION['shop_owner_email'];
$stmt = $conn->prepare("SELECT shop_owner_id FROM shop_owners WHERE shop_owner_email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($shop_owner_id);
if (!$stmt->fetch()) {
    echo "<script>alert('Shop owner not found!'); window.location.href='../Html/index.php';</script>";
    exit();
}
$stmt->close();

// ---- AUTO DELETE EXPIRED PRODUCTS START ----
// Define expiry calculation function first
function getProductExpiryDate($date_added, $duration) {
    $start = new DateTime($date_added);
    if ($duration === "1") $days = 1;
    elseif ($duration === "1-3") $days = 3;
    elseif ($duration === "1-7") $days = 7;
    else $days = 1;
    $end = clone $start;
    $end->modify("+$days days");
    return $end->format('Y-m-d');
}

// Find and delete expired products
$stmt = $conn->prepare("SELECT product_id, date_added, duration FROM products WHERE shop_owner_id = ?");
$stmt->bind_param("i", $shop_owner_id);
$stmt->execute();
$result = $stmt->get_result();
$expired_ids = [];
while ($row = $result->fetch_assoc()) {
    $expiry_date = getProductExpiryDate($row['date_added'], $row['duration']);
    $now = (new DateTime('now', new DateTimeZone('Asia/Dhaka')))->format('Y-m-d');
    if ($now >= $expiry_date) {
        $expired_ids[] = $row['product_id'];
    }
}
$stmt->close();
if (!empty($expired_ids)) {
    $ids_str = implode(",", array_map('intval', $expired_ids));
    $conn->query("DELETE FROM products WHERE product_id IN ($ids_str) AND shop_owner_id = $shop_owner_id");
}
// ---- AUTO DELETE EXPIRED PRODUCTS END ----

// Handle product delete
if (isset($_POST['delete_product_id'])) {
    $id = intval($_POST['delete_product_id']);
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id=? AND shop_owner_id=?");
    $stmt->bind_param("ii", $id, $shop_owner_id);
    $stmt->execute();
    $stmt->close();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Handle product edit
if (isset($_POST['edit_product_id'])) {
    $edit_id = intval($_POST['edit_product_id']);
    $edit_name = trim($_POST['edit_product_name']);
    $edit_stock = intval($_POST['edit_product_stock']);
    $edit_price = floatval($_POST['edit_product_price']);
    $edit_advertise = trim($_POST['edit_product_advertisement']);

    $stmt = $conn->prepare("UPDATE products SET product_name=?, stock=?, price=?, advertise_text=? WHERE product_id=? AND shop_owner_id=?");
    $stmt->bind_param("sidssi", $edit_name, $edit_stock, $edit_price, $edit_advertise, $edit_id, $shop_owner_id);
    if ($stmt->execute()) {
        $message .= "✅ পণ্য আপডেট হয়েছে!<br>";
    } else {
        $message .= "❌ পণ্য আপডেট হয়নি: " . $stmt->error . "<br>";
    }
    $stmt->close();
}

// Handle product add
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['delete_product_id']) && !isset($_POST['edit_product_id'])) {
    $duration = trim($_POST['duration']);
    $advertise_option = isset($_POST['advertise_option']) ? $_POST['advertise_option'] : "";
    $advertise_text = isset($_POST['advertise_text']) ? trim($_POST['advertise_text']) : "";
    $date_added = !empty($_POST['date_added']) ? $_POST['date_added'] : date('Y-m-d');

    $product_names = isset($_POST['product_name']) ? $_POST['product_name'] : [];
    $stocks = isset($_POST['stock']) ? $_POST['stock'] : [];
    $prices = isset($_POST['price']) ? $_POST['price'] : [];

    $upload_dir = "../uploads/products/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $message = "";

    for ($i = 0; $i < count($product_names); $i++) {
        $product_name = isset($product_names[$i]) ? trim($product_names[$i]) : "";
        $stock = isset($stocks[$i]) ? intval($stocks[$i]) : 0;
        $price = isset($prices[$i]) ? floatval($prices[$i]) : 0;

        // Validation
        if (
            empty($product_name) ||
            strtolower($product_name) === "array" ||
            $stock <= 0 ||
            $price <= 0
        ) {
            continue;
        }

        // Image upload
        $image_path = "";
        if (isset($_FILES['product_image']['name'][$i]) && $_FILES['product_image']['error'][$i] === 0) {
            $tmp_name = $_FILES['product_image']['tmp_name'][$i];
            $file_name = time() . "_" . basename($_FILES['product_image']['name'][$i]);
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($tmp_name, $target_file)) {
                $image_path = $target_file;
            } else {
                $message .= "ছবি আপলোড করতে সমস্যা হয়েছে পণ্যের নাম: $product_name।<br>";
                continue;
            }
        } else {
            $message .= "ছবি নেই পণ্যের নাম: $product_name।<br>";
            continue;
        }

        // Insert into DB
        $stmt = $conn->prepare("INSERT INTO products 
            (shop_owner_id, product_name, product_image_path, stock, price, duration, advertise_option, advertise_text, date_added)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "isssdssss",
            $shop_owner_id,
            $product_name,
            $image_path,
            $stock,
            $price,
            $duration,
            $advertise_option,
            $advertise_text,
            $date_added
        );

        if ($stmt->execute()) {
            $message .= "✅ পণ্য '$product_name' সফলভাবে সংরক্ষিত হয়েছে!<br>";
        } else {
            $message .= "❌ পণ্য '$product_name' সংরক্ষণে সমস্যা হয়েছে: " . $stmt->error . "<br>";
        }
        $stmt->close();
    }
}

// Fetch all products for this shop owner (for display)
$products = [];
$stmt = $conn->prepare("SELECT product_id, product_name, product_image_path, stock, price, advertise_option, advertise_text, date_added, duration FROM products WHERE shop_owner_id = ? ORDER BY date_added DESC, product_id DESC");
$stmt->bind_param("i", $shop_owner_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();

// Helper function for duration (used for display, not deletion)
function isProductActive($date_added, $duration) {
    $start = new DateTime($date_added);
    $now = new DateTime('now', new DateTimeZone('Asia/Dhaka'));
    if ($duration === "1") $days = 1;
    elseif ($duration === "1-3") $days = 3;
    elseif ($duration === "1-7") $days = 7;
    else $days = 1;
    $end = clone $start;
    $end->modify("+$days days");
    return $now < $end;
}  
?>
<!-- rest of your HTML (unchanged) -->
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../Css/ShopOwner_item.css?v=1">
</head>
<body>
    <!-- HEADER SECTION -->
    <header>
        <div class="logo">
            <img src="../Images/Logo.png" alt="Liberty Logo">
            <h2>সহজ যোগান</h2>
        </div>
        <div class="icons">
            <button id="userIcon"><img src="../Images/Sample_User_Icon.png" alt="User"></button>
            <button id="notificationIcon"><img src="../Images/notification.png" alt="Notifications"></button>
            <button id="messengerBtn"><img src="../Images/messenger-icon.png" alt="Messenger"></button>
        </div>
    </header>
    <div class="profile-container">
        <!-- OVERLAY, SIDEBARS -->
        <div id="overlay" class="overlay"></div>
        <div id="userSidebar" class="sidebar">
            <span id="closeUserSidebar" class="close-btn">&times;</span>
            <h3>ব্যবহারকারী মেনু</h3>
            <div class="sidebar-content">
                <a href="../Html/ShopOwner_Home.php" id="profileLink">হোম</a>
                <a href="../Html/ShopOwner_settings.php" id="settingsLink">সেটিংস</a>
                <a href="../Html/ShopOwner_settings_password.php" id="changePasswordLink">পাসওয়ার্ড পরিবর্তন</a>
                <a href="" id="logoutLink">লগ আউট</a>
            </div>
        </div>
        <div id="notificationSidebar" class="sidebar">
            <span id="closeNotification" class="close-btn">&times;</span>
            <h3>নোটিফিকেশন</h3>
            <div class="sidebar-content">
                <p>নতুন কোনো নোটিফিকেশন নেই</p>
            </div>
        </div>
        <div id="messengerSidebar" class="sidebar">
            <span id="closeMessenger" class="close-btn">&times;</span>
            <h3>মেসেজ</h3>
            <div class="sidebar-content">
                <p>কোনো নতুন মেসেজ নেই</p>
            </div>
        </div>
        <h1>নতুন পণ্য যোগ করুন</h1>
        <div class="button-box">
            <!-- Show "নতুন সংযোজন" only if there are NO products -->
            <button id="openFormBtn" type="button" <?php echo (count($products) == 0 ? '' : 'style="display:none;"'); ?>>নতুন সংযোজন</button>
            <!-- Show "+" only if there ARE products -->
            <button id="plusBtn" type="button" <?php echo (count($products) > 0 ? '' : 'style="display:none;"'); ?>>➕</button>
        </div>
        <?php if ($message): ?>
            <div class="alert-message" style="text-align:center; margin:10px; color:green;">
                <?php echo htmlspecialchars_decode($message); ?>
            </div>
        <?php endif; ?>

        <!-- Product Form Popup -->
        <form class="form-popup" id="productForm" method="POST" enctype="multipart/form-data" style="display:none;">
            <button type="button" class="close-form" onclick="document.getElementById('productForm').style.display='none';return false;">×</button>
            <h3>নতুন পণ্য যোগ করুন</h3>
            <label>তারিখ:</label>
            <input type="date" id="dateInput" name="date_added" value="<?php echo date('Y-m-d'); ?>" required>
            <label>পণ্য প্রদর্শনের সময়কাল?</label>
            <select id="duration" name="duration" required>
                <option value="1">১ দিন</option>
                <option value="1-3">১-৩ দিন</option>
                <option value="1-7">১-৭ দিন</option>
            </select>
            <div id="productList">
                <div class="product-entry">
                    <label>পণ্যের ছবি:</label>
                    <input type="file" accept="image/*" name="product_image[]" required>
                    <label>পণ্যের নাম:</label>
                    <input type="text" name="product_name[]" required>
                    <label>মজুদ:</label>
                    <input type="number" name="stock[]" min="0" required>
                    <label>দাম:</label>
                    <input type="text" name="price[]" required>
                </div>
            </div>
            <div class="advertise-section">
                <p>আপনি কি বিজ্ঞাপন দিতে চান?</p>
                <label><input type="radio" name="advertise_option" value="yes" onclick="toggleAdText(true)"> হ্যাঁ</label>
                <label><input type="radio" name="advertise_option" value="no" onclick="toggleAdText(false)" checked> না</label>
                <div id="adTextInput" style="display: none;">
                    <input type="text" name="advertise_text" placeholder="বিজ্ঞাপনের তথ্য লিখুন">
                </div>
            </div>
            <button type="button" id="addProductBtn" onclick="addProductEntry()">আরো পণ্য যোগ করুন</button>
            <button type="submit" id="saveBtn">Save</button>
        </form>

        <section id="productListByDate">
            <h2>পণ্য তালিকা (Date-Based)</h2>
            <div id="productEntriesContainer">
            <?php
            // Group products by date_added and display advertisement only once per date
            $products_by_date = [];
            foreach ($products as $product) {
                if (isProductActive($product['date_added'], $product['duration'])) {
                    $products_by_date[$product['date_added']][] = $product;
                }
            }

            if (empty($products_by_date)) {
                echo "<p style='color:gray;text-align:center;'>কোনো পণ্য নেই</p>";
            } else {
                foreach ($products_by_date as $date => $products_on_date) {
                    // Show the advertisement for the group (from the first product)
                    $ad_text = ($products_on_date[0]['advertise_option'] === "yes" && !empty($products_on_date[0]['advertise_text']))
                        ? htmlspecialchars($products_on_date[0]['advertise_text'])
                        : "না";
                    echo "<div class='product-group' style='background:#fafffa;padding:12px;margin-bottom:15px;border-radius:8px;'>";
                    echo "<div style='font-weight:bold;margin-bottom:5px;'>তারিখ: " . htmlspecialchars($date) . " | বিজ্ঞাপন: " . $ad_text . "</div>";
                    foreach ($products_on_date as $product) {
                        ?>
                        <div class="product-list-entry" style="display:flex;align-items:center;margin-bottom:7px;">
                            <img src="<?php echo htmlspecialchars($product['product_image_path']); ?>" width="60" height="60" style="object-fit:cover;margin-right:10px;">
                            <div style="flex:1;">
                                <strong><?php echo htmlspecialchars($product['product_name']); ?></strong>
                                <span>স্টক: <?php echo htmlspecialchars($product['stock']); ?></span>
                                <span>দাম: <?php echo htmlspecialchars($product['price']); ?></span>
                            </div>
                            <form method="post" style="display:inline;">
    <input type="hidden" name="delete_product_id" value="<?php echo $product['product_id']; ?>">
    <button type="submit" class="delete-btn" onclick="return confirm('ডিলিট করতে চান?');">ডিলিট করতে চান?</button>
</form>
<style>
.delete-btn {
    background: #ff4d4f;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 5px 15px;
    font-size: 15px;
    cursor: pointer;
    margin-left: 3px;
    transition: background 0.17s;
    font-family: inherit;
}
.delete-btn:hover,
.delete-btn:focus {
    background: #d9363e;
}
</style>
                            <button type="button"
    class="edit-btn"
    data-id="<?php echo $product['product_id']; ?>"
    data-name="<?php echo htmlspecialchars($product['product_name']); ?>"
    data-stock="<?php echo htmlspecialchars($product['stock']); ?>"
    data-price="<?php echo htmlspecialchars($product['price']); ?>"
    data-advertise="<?php echo htmlspecialchars($product['advertise_text']); ?>"
    onclick="openEditForm(this)"
    style="margin-left:6px;background:#36b37e;color:#fff;border:none;border-radius:4px;padding:4px 13px;cursor:pointer;">Edit</button>
                        </div>
                        <?php
                    }
                    echo "</div>";
                }
            }
            ?>
            </div>
        </section>
<div id="editProductForm" class="form-popup" style="display:none;"> <button type="button" class="close-form" id="closeEditBtn" onclick="closeEditForm()" 
        style="..."
        aria-label="Close">
    ×
</button>
        
    <h3>পণ্য সম্পাদনা করুন</h3>
    <form method="post" id="editForm">
        <input type="hidden" name="edit_product_id" id="editProductId">
        <label>পণ্যের নাম:</label>
        <input type="text" name="edit_product_name" id="editProductName" required>
        <label>মজুদ:</label>
        <input type="text" name="edit_product_stock" id="editProductStock" required>
        <label>দাম:</label>
        <input type="text" name="edit_product_price" id="editProductPrice" required>
        <label>বিজ্ঞাপন:</label>
        <input type="text" name="edit_product_advertisement" id="editProductAdvertisement">
        <button type="submit" id="saveEditBtn">Save Changes</button>
    </form>
</div>
<script>
// Show/hide edit form
function closeEditForm() {
    document.getElementById('editProductForm').style.display = 'none';
}

// Show/hide advertise text input
function toggleAdText(show) {
    const adTextInput = document.getElementById('adTextInput');
    if (adTextInput) adTextInput.style.display = show ? 'block' : 'none';
}

// Add more product entry fields
function addProductEntry() {
    const container = document.getElementById('productList');
    const entry = document.createElement('div');
    entry.classList.add('product-entry');
    entry.innerHTML = `
        <label>পণ্যের ছবি:</label>
        <input type="file" accept="image/*" name="product_image[]" required>
        <label>পণ্যের নাম:</label>
        <input type="text" name="product_name[]" required>
        <label>মজুদ:</label>
        <input type="number" name="stock[]" min="0" required>
        <label>দাম:</label>
        <input type="text" name="price[]" required>
    `;
    container.appendChild(entry);
}

// Open edit form and fill fields with product data
function openEditForm(btn) {
    document.getElementById('editProductForm').style.display = 'block';
    document.getElementById('editProductId').value = btn.getAttribute('data-id');
    document.getElementById('editProductName').value = btn.getAttribute('data-name');
    document.getElementById('editProductStock').value = btn.getAttribute('data-stock');
    document.getElementById('editProductPrice').value = btn.getAttribute('data-price');
    document.getElementById('editProductAdvertisement').value = btn.getAttribute('data-advertise');
}

document.addEventListener('DOMContentLoaded', function() {
    var openBtn = document.getElementById('openFormBtn');
    var plusBtn = document.getElementById('plusBtn');
    var form = document.getElementById('productForm');
    if (openBtn) openBtn.addEventListener('click', function() {
        form.classList.add('show');
        form.style.display = 'block';
    });
    if (plusBtn) plusBtn.addEventListener('click', function() {
        form.classList.add('show');
        form.style.display = 'block';
    });

    var closeEditBtn = document.getElementById('closeEditBtn');
    if (closeEditBtn) {
        closeEditBtn.addEventListener('click', closeEditForm);
    }
});
</script>
<script src="../java_script/ShopOwner_item.js"></script>
</body>
</html>