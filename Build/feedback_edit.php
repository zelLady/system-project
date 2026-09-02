<?php
session_start();
include 'connect.php';
include 'includes/auth.php';

$feedback_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM customer_feedbacks WHERE feedback_id = ? AND user_id = ?");
$stmt->bind_param("ii", $feedback_id, $current_user_id);
$stmt->execute();
$feedback = $stmt->get_result()->fetch_assoc();

if (!$feedback) {
    header("Location: products_browse.php?error=" . urlencode("Feedback not found or not yours to edit."));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Feedback</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
<?php $active = 'browse'; include 'includes/nav.php'; ?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-edit"></i> Edit Your Feedback</h1>
    </div>
    <div class="card">
        <form action="feedback_update.php" method="POST">
            <input type="hidden" name="feedback_id" value="<?php echo $feedback['feedback_id']; ?>">
            <div class="form-group">
                <label>Rating</label>
                <select name="feedback_rating" required>
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?php echo $i; ?>" <?php echo $feedback['feedback_rating'] == $i ? 'selected' : ''; ?>>
                            <?php echo str_repeat('★', $i); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Your Feedback</label>
                <textarea name="feedback_message" required><?php echo htmlspecialchars($feedback['feedback_message']); ?></textarea>
            </div>
            <button type="submit" class="btn"><i class="fas fa-save"></i> Update Feedback</button>
            <a href="product_page.php?id=<?php echo $feedback['product_id']; ?>" class="btn btn-outline" style="margin-left:8px;">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>