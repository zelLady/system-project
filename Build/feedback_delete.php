<?php
session_start();
include 'connect.php';
include 'includes/auth.php';

$feedback_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

$stmt = $conn->prepare("DELETE FROM customer_feedbacks WHERE feedback_id = ? AND user_id = ?");
$stmt->bind_param("ii", $feedback_id, $current_user_id);
$stmt->execute();

header("Location: product_page.php?id=$product_id&fbsuccess=deleted");
exit();
?>