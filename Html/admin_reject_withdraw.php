<?php
include '../PHP/db_connect.php';

if (isset($_POST['withdraw_id'])) {
    $id = intval($_POST['withdraw_id']);
    $stmt = $conn->prepare("UPDATE withdraw_request SET status='rejected' WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header('Location: ../Html/Admin.php?tab=withdrawTab&msg=reject_success');
        exit;
    } else {
        header('Location: ../Html/Admin.php?tab=withdrawTab&msg=reject_failed');
        exit;
    }
} else {
    header('Location: ../Html/Admin.php?tab=withdrawTab&msg=invalid');
    exit;
}
?>