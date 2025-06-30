<?php
include '../PHP/db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw_id'])) {
    $withdraw_id = intval($_POST['withdraw_id']);

    $stmt = $conn->prepare("UPDATE delivery_withdraw_request SET status = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $withdraw_id);

    if ($stmt->execute()) {
        $_SESSION['msg'] = "Withdraw request rejected!";
    } else {
        $_SESSION['msg'] = "Failed to reject request.";
    }
    $stmt->close();
}

$conn->close();
header("Location: ../Html/Admin.php");
exit;
?>