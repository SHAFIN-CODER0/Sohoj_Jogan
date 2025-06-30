<?php
session_start();
include '../PHP/db_connect.php';

// সেশন থেকে ডেলিভারি ম্যান আইডি বের করো
$delivery_man_id = $_SESSION['delivery_man_id'] ?? 0;
if (!$delivery_man_id) {
    die("ডেলিভারি ম্যান লগইন নেই!");
}

// COD Delivered Order List (TxID সহ)
$sql = "
SELECT 
    o.order_id,
    o.delivery_charge,
    p.bkash_txid
FROM orders o
JOIN payments p ON o.order_id = p.order_id
WHERE o.status = 'delivered'
  AND p.payment_method = 'cod'
  AND o.delivery_man_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $delivery_man_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// TxID সাবমিট হ্যান্ডলিং
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_txid'])) {
    $order_id = intval($_POST['order_id']);
    $txid = trim($_POST['bkash_txid'] ?? '');

    if ($order_id && $txid !== '') {
        $stmt = $conn->prepare("UPDATE payments SET bkash_txid = ? WHERE order_id = ?");
        $stmt->bind_param("si", $txid, $order_id);
        if ($stmt->execute()) {
            echo "<div style='color:green;text-align:center;'>TxID সংরক্ষণ হয়েছে!</div>";
            echo "<script>location.href=location.href;</script>";
        } else {
            echo "<div style='color:red;text-align:center;'>TxID সংরক্ষণে সমস্যা হয়েছে!</div>";
        }
        $stmt->close();
    } else {
        echo "<div style='color:red;text-align:center;'>TxID দিন।</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>COD Delivered Orders</title>
    <link rel="stylesheet" href="../CSS/Customer_Home.css?=1">
    <style>
  .cod-order-list {
    max-width: 650px;
    margin: 320px auto 40px auto; /* top 120px, দুইপাশে auto, নিচে 40px */
    background: #f9f9f9;
    border-radius: 12px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.07);
    padding: 25px 20px;
    font-family: 'SolaimanLipi', 'Noto Sans Bengali', Arial, sans-serif;
}
    .cod-order-list table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        margin-top: 10px;
    }
    .cod-order-list th, .cod-order-list td {
        border: 1px solid #e3e3e3;
        padding: 8px 6px;
        text-align: center;
        font-size: 1rem;
    }
    .cod-order-list th {
        background: #fffcf0;
        color: #b57915;
    }
    .cod-order-list tr:nth-child(even) td {
        background: #fff9ea;
    }
    .cod-order-list tr:hover td {
        background: #fff1cc;
    }
    .cod-order-list button {
        padding: 3px 10px;
        background: #1976d2;
        color: #fff;
        border: none;
        border-radius: 5px;
        font-size: 1rem;
        cursor: pointer;
    }
    .cod-order-list button:hover {
        background: #1256a3;
    }
    @media (max-width: 520px) {
        .cod-order-list {
            padding: 12px 4px;
        }
        .cod-order-list th, .cod-order-list td {
            font-size: 0.90rem;
            padding: 7px 2px;
        }
    }
    </style>
</head>
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
            window.location.href = '../Html/DeliveryMan_Home.php';
        });
    </script>
    <nav></nav>
</header>
<body>
<main>
    <div class="cod-order-list">
        <h3 style="margin:0 0 14px 0;text-align:center;color:#b57915;">Cash On Delivery Delivered Order List</h3>
        <div style="margin-bottom:8px;color:#b57915;font-weight:bold;">
            বিকাশ নম্বর: 01569129533 (এখানে টাকা পাঠান এবং TxID দিন)
        </div>
        <?php
        if ($result->num_rows > 0) {
            echo "<table><tr><th>Order ID</th><th>Delivery Charge</th><th>TxID</th></tr>";
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>" . htmlspecialchars($row['order_id']) . "</td>
                    <td>৳" . htmlspecialchars($row['delivery_charge']) . "</td>
                    <td>";
                if (!empty($row['bkash_txid'])) {
                    // TxID দেওয়া থাকলে শুধু দেখাও, ফর্ম নয়
                    echo "<span style='color:green;font-weight:bold;'>".htmlspecialchars($row['bkash_txid'])."</span>";
                } else {
                    // TxID না থাকলে ফর্ম দেখাও
                    echo "
                        <form method='POST' style='display:inline;'>
                            <input type='hidden' name='order_id' value='" . htmlspecialchars($row['order_id']) . "'>
                            <input type='text' name='bkash_txid' placeholder='bKash TxID' required style='width:110px;'>
                            <button type='submit' name='submit_txid'>সাবমিট</button>
                        </form>
                    ";
                }
                echo "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<div style='text-align:center;color:#888;'>কোনো Cash On Delivery অর্ডার পাওয়া যায়নি।</div>";
        }
        ?>
    </div>
</main>
</body>
</html>