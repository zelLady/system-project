<?php
session_start();
include 'connect.php';
include 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: products_browse.php");
    exit();
}

$feedback_id = (int)$_POST['feedback_id'];
$feedback_message = trim($_POST['feedback_message']);
$feedback_rating = (int)$_POST['feedback_rating'];

// Look up product_id first (needed for redirect) and confirm ownership
$check = $conn->prepare("SELECT product_id FROM customer_feedbacks WHERE feedback_id = ? AND user_id = ?");
$check->bind_param("ii", $feedback_id, $current_user_id);
$check->execute();
$row = $check->get_result()->fetch_assoc();

if (!$row) {
    header("Location: products_browse.php?error=" . urlencode("You don't have permission to edit this feedback."));
    exit();
}

$product_id = $row['product_id'];

if ($feedback_message === '' || $feedback_rating < 1 || $feedback_rating > 5) {
    header("Location: product_page.php?id=$product_id&error=" . urlencode("Please provide a valid rating and message."));
    exit();
}

$stmt = $conn->prepare("UPDATE customer_feedbacks SET feedback_message = ?, feedback_rating = ? WHERE feedback_id = ? AND user_id = ?");
$stmt->bind_param("siii", $feedback_message, $feedback_rating, $feedback_id, $current_user_id);
$stmt->execute();

header("Location: product_page.php?id=$product_id&fbsuccess=updated");
exit();
?>