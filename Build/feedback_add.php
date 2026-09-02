<?php
session_start();
include 'connect.php';
include 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: products_browse.php");
    exit();
}

$product_id = (int)$_POST['product_id'];
$shop_id = (int)$_POST['shop_id'];
$feedback_message = trim($_POST['feedback_message']);
$feedback_rating = (int)$_POST['feedback_rating'];

if ($feedback_message === '' || $feedback_rating < 1 || $feedback_rating > 5) {
    header("Location: product_page.php?id=$product_id&error=" . urlencode("Please provide a valid rating and message."));
    exit();
}

$stmt = $conn->prepare("INSERT INTO customer_feedbacks (user_id, shop_id, product_id, feedback_message, feedback_rating)
                        VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iiisi", $current_user_id, $shop_id, $product_id, $feedback_message, $feedback_rating);
$stmt->execute();

header("Location: product_page.php?id=$product_id&fbsuccess=added");
exit();
?>