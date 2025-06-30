<?php
include '../PHP/db_connect.php';

if (isset($_POST['withdraw_id'], $_POST['tx_id']) && trim($_POST['tx_id']) !== '') {
    $id = intval($_POST['withdraw_id']);
    $tx_id = trim($_POST['tx_id']);
    $stmt = $conn->prepare("UPDATE withdraw_request SET status='approved', tx_id=? WHERE id=?");
    $stmt->bind_param("si", $tx_id, $id);
    if ($stmt->execute()) {
        // Success
        header('Location: ../Html/Admin.php?tab=withdrawTab&msg=approved');
        exit;
    } else {
        // Failure
        header('Location: ../Html/Admin.php?tab=withdrawTab&msg=error');
        exit;
    }
} else {
    // Invalid input
    header('Location: ../Html/Admin.php?tab=withdrawTab&msg=invalid');
    exit;
}
?>