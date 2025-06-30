<?php
include '../PHP/db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw_id'])) {
    $withdraw_id = intval($_POST['withdraw_id']);

    // Approve the request (set status to 'approved')
    $stmt = $conn->prepare("UPDATE delivery_withdraw_request SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $withdraw_id);

    if ($stmt->execute()) {
        $_SESSION['msg'] = "Withdraw request approved!";
    } else {
        $_SESSION['msg'] = "Failed to approve request.";
    }
    $stmt->close();
}

$conn->close();
header("Location: ../Html/Admin.php");
exit;
?>