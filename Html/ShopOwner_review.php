<?php
session_start();
include '../PHP/db_connect.php';

$isOwner = false;
$isCustomer = false;
$canReview = false;
$shopOwnerId = null;
$customerId = null;
$customerName = null;

// Determine who is viewing
if (isset($_SESSION['shop_owner_email']) && !isset($_GET['shop_owner_id'])) {
    // Shop owner viewing their own page
    $isOwner = true;
    $email = $_SESSION['shop_owner_email'];
    $sql = "SELECT shop_owner_id, shop_owner_name FROM shop_owners WHERE shop_owner_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $shopOwnerId = $row['shop_owner_id'];
    } else {
        echo "<script>alert('Shop owner not found!'); window.location.href='../Html/index.php';</script>";
        exit();
    }
    $stmt->close();
} else if (isset($_GET['shop_owner_id'])) {
    // Visitor or customer viewing a shop's reviews
    $shopOwnerId = intval($_GET['shop_owner_id']);
} else {
    echo "<script>alert('Shop not found!'); window.location.href='../Html/index.php';</script>";
    exit();
}

// Determine if a logged-in customer
if (isset($_SESSION['customer_email'])) {
    $isCustomer = true;
    $email = $_SESSION['customer_email'];
    $sql = "SELECT customer_id, customer_name FROM customers WHERE customer_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $customerId = $row['customer_id'];
        $customerName = $row['customer_name'];
    }
    $stmt->close();
}

// Prevent shop owner from reviewing own shop
if ($isCustomer && $shopOwnerId && $customerId) {
    // Find the shop owner's email
    $ownerEmailSql = "SELECT shop_owner_email FROM shop_owners WHERE shop_owner_id = ?";
    $ownerEmailStmt = $conn->prepare($ownerEmailSql);
    $ownerEmailStmt->bind_param("i", $shopOwnerId);
    $ownerEmailStmt->execute();
    $ownerEmailResult = $ownerEmailStmt->get_result();
    $ownerEmail = null;
    if ($row = $ownerEmailResult->fetch_assoc()) {
        $ownerEmail = $row['shop_owner_email'];
    }
    $ownerEmailStmt->close();

    if ($ownerEmail && isset($email) && $ownerEmail == $email) {
        // This customer is actually the shop owner (by email), so can't review
        $canReview = false;
    } else {
        $canReview = true;
    }
}

// Handle Review Submission
$reviewError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canReview) {
    $rating = intval($_POST['rating'] ?? 0);
    $reviewText = trim($_POST['review_text'] ?? '');
    $formShopOwnerId = intval($_POST['shop_owner_id'] ?? 0);

    // Check duplicate review
    $dupSql = "SELECT review_id FROM shop_reviews WHERE shop_owner_id = ? AND customer_id = ?";
    $dupStmt = $conn->prepare($dupSql);
    $dupStmt->bind_param("ii", $formShopOwnerId, $customerId);
    $dupStmt->execute();
    $dupResult = $dupStmt->get_result();
    if ($dupResult->num_rows > 0) {
        $reviewError = "আপনি ইতিমধ্যে এই দোকানে একটি রিভিউ দিয়েছেন।";
    } else if ($rating >= 1 && $rating <= 5 && strlen($reviewText) > 0) {
        $insertSql = "INSERT INTO shop_reviews (shop_owner_id, customer_id, review_text, rating) VALUES (?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("iisi", $formShopOwnerId, $customerId, $reviewText, $rating);
        if ($insertStmt->execute()) {
            // Success, reload to avoid resubmit
            header("Location: ShopOwner_review.php?shop_owner_id=" . $formShopOwnerId);
            exit();
        } else {
            $reviewError = "রিভিউ সাবমিট করা যায়নি। আবার চেষ্টা করুন।";
        }
        $insertStmt->close();
    } else {
        $reviewError = "রেটিং ও রিভিউ দিন।";
    }
    $dupStmt->close();
}

// Fetch Reviews
$reviewSql = "SELECT r.*, c.customer_name FROM shop_reviews r
              JOIN customers c ON r.customer_id = c.customer_id
              WHERE r.shop_owner_id = ?
              ORDER BY r.created_at DESC";
$reviewStmt = $conn->prepare($reviewSql);
$reviewStmt->bind_param("i", $shopOwnerId);
$reviewStmt->execute();
$reviewResult = $reviewStmt->get_result();
$reviews = [];
while ($row = $reviewResult->fetch_assoc()) {
    $reviews[] = $row;
}
$reviewStmt->close();

function bnDate($datetime) {
    // Converts date to Bangla date like "১২ জুন ২০২৫"
    $months = ['জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
    $bnDigits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    $enDigits = ['0','1','2','3','4','5','6','7','8','9'];
    $ts = strtotime($datetime);
    $d = date('j', $ts);
    $m = date('n', $ts)-1;
    $y = date('Y', $ts);
    $d = str_replace($enDigits, $bnDigits, $d);
    $y = str_replace($enDigits, $bnDigits, $y);
    return $d . ' ' . $months[$m] . ' ' . $y;
}
function getStars($rating) {
    $star = str_repeat('★', $rating) . str_repeat('☆', 5-$rating);
    return $star;
}

// Notification fetch (shop owner)
$shopOwnerNotifications = [];
if ($isOwner && isset($shopOwnerId)) {
    $notifSql = "
       SELECT n.*, o.customer_name, o.customer_phone, pr.product_name, pr.price, o.quantity,
       dm.delivery_man_name, dm.delivery_man_phone
FROM notifications n
LEFT JOIN orders o ON n.order_id = o.order_id
LEFT JOIN products pr ON o.product_id = pr.product_id
LEFT JOIN delivery_men dm ON n.accepted_by = dm.delivery_man_id
WHERE n.user_id = ? AND n.user_type = 'shop_owner'
ORDER BY n.created_at DESC
LIMIT 30
    ";
    $stmt = $conn->prepare($notifSql);
    $stmt->bind_param("i", $shopOwnerId);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $shopOwnerNotifications[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>রিভিউ</title>
    <link rel="stylesheet" href="../Css/ShopOwner_review.css?v=1">
</head>
<body>
<header>
    <div class="logo">
        <img src="../Images/Logo.png" alt="Liberty Logo" />
        <h2>সহজ যোগান</h2>
    </div>
</header>
<main>
<section class="review-section">
    <h3>রিভিউসমূহ</h3>
    <div class="review-list">
        <?php if (count($reviews) === 0): ?>
            <div style="text-align:center; color:gray;">এখনো কোনো রিভিউ নেই।</div>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <div class="review-header">
                        <span class="reviewer"><?php echo htmlspecialchars($review['customer_name']); ?></span>
                        <span class="review-rating"><?php echo getStars($review['rating']); ?></span>
                        <span class="review-date"><?php echo bnDate($review['created_at']); ?></span>
                    </div>
                    <div class="review-text">
                        <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <hr>
    <?php if ($isOwner): ?>
        <p style="color:gray;">শপ মালিক রিভিউ দিতে পারবেন না। শুধু রিভিউ দেখতে পারবেন।</p>
    <?php elseif ($isCustomer && $canReview): ?>
        <h4>নতুন রিভিউ দিন</h4>
        <?php if ($reviewError): ?>
            <div style="color:red; margin-bottom:10px;"><?php echo htmlspecialchars($reviewError); ?></div>
        <?php endif; ?>
        <form method="POST" class="review-form" onsubmit="return validateReviewForm()">
            <input type="hidden" name="shop_owner_id" value="<?php echo (int)$shopOwnerId; ?>">
            <label for="rating">রেটিং:</label>
            <select name="rating" id="rating" required>
                <option value="">--নির্বাচন করুন--</option>
                <option value="5">5 ★</option>
                <option value="4">4 ★</option>
                <option value="3">3 ★</option>
                <option value="2">2 ★</option>
                <option value="1">1 ★</option>
            </select>
            <br>
            <textarea name="review_text" rows="3" cols="40" placeholder="আপনার রিভিউ লিখুন..." required></textarea><br>
            <button type="submit">রিভিউ দিন</button>
        </form>
    <?php elseif ($isCustomer && !$canReview): ?>
        <div style="color:gray;">আপনি এই দোকানে রিভিউ দিতে পারবেন না।</div>
    <?php else: ?>
        <div style="color:gray;">রিভিউ দিতে চাইলে লগইন করুন।</div>
    <?php endif; ?>
</section>
</main>
<script>
function validateReviewForm() {
    const rating = document.getElementById('rating').value;
    const reviewText = document.querySelector('textarea[name="review_text"]').value.trim();

    if (!rating) {
        alert("রেটিং নির্বাচন করুন।");
        return false;
    }
    if (reviewText.length === 0) {
        alert("রিভিউ লিখুন।");
        return false;
    }
    return true;
}
</script>
</body>
</html>