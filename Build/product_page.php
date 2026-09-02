<?php
session_start();
include 'connect.php';
include 'includes/auth.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT p.*, s.shop_name, s.shop_id FROM products p
                        JOIN shop s ON p.product_shop = s.shop_id
                        WHERE p.product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: products_browse.php");
    exit();
}

// Get feedbacks for this product, newest first
$fb_stmt = $conn->prepare("SELECT f.*, u.user_name FROM customer_feedbacks f
                           JOIN users u ON f.user_id = u.user_id
                           WHERE f.product_id = ?
                           ORDER BY f.feedback_date DESC");
$fb_stmt->bind_param("i", $product_id);
$fb_stmt->execute();
$feedbacks = $fb_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_nameEng']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
<?php $active = 'browse'; include 'includes/nav.php'; ?>

<div class="container">
    <div class="card">
        <div class="product-detail">
            <div>
                <img src="<?php echo htmlspecialchars($product['product_img_path'] ?: 'https://via.placeholder.com/320x220'); ?>" alt="">
            </div>
            <div>
                <div class="shop-name"><i class="fas fa-store"></i> <?php echo htmlspecialchars($product['shop_name']); ?></div>

                <!-- ===== Language toggle (ENG / CEB) ===== -->
                <div class="lang-toggle">
                    <button type="button" class="lang-btn active" data-lang="eng" onclick="setLanguage('eng')">ENG</button>
                    <button type="button" class="lang-btn" data-lang="ceb" onclick="setLanguage('ceb')">CEB</button>
                </div>

                <!-- Product name: one span per language, DB-driven -->
                <h1>
                    <span class="lang-content" data-lang="eng"><?php echo htmlspecialchars($product['product_nameEng']); ?></span>
                    <span class="lang-content" data-lang="ceb" style="display:none;"><?php echo htmlspecialchars($product['product_nameCeb'] ?: $product['product_nameEng']); ?></span>
                </h1>

                <div class="price-tag">₱<?php echo number_format($product['product_price'], 2); ?></div>

                <!-- Product description: one span per language, DB-driven -->
                <p style="color:#555; line-height:1.6; margin-bottom:20px;">
                    <span class="lang-content" data-lang="eng">
                        <?php echo nl2br(htmlspecialchars($product['product_descriptionEng'] ?: 'No description available.')); ?>
                    </span>
                    <span class="lang-content" data-lang="ceb" style="display:none;">
                        <?php echo nl2br(htmlspecialchars($product['product_descriptionCeb'] ?: 'Walay deskripsyon nga makita.')); ?>
                    </span>
                </p>

                <!-- Add to Cart: automatically saved via JS + PHP (AJAX to cart_add.php) -->
                <div id="cart-msg" class="msg msg-success" style="display:none;"></div>
                <form id="add-cart-form" style="display:flex; gap:10px; align-items:center;">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <label style="margin:0;">Qty</label>
                    <input type="number" name="quantity" value="1" min="1" class="qty-input">
                    <button type="submit" class="btn"><i class="fas fa-cart-plus"></i> Add to Cart</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== Customer Feedback CRUD ===== -->
    <div class="card">
        <h2 style="color:#764ba2; margin-bottom:18px;"><i class="fas fa-comments"></i> Customer Feedback</h2>

        <?php if (isset($_GET['fbsuccess'])): ?>
            <div class="msg msg-success">
                <?php
                $map = ['added' => 'Feedback submitted!', 'updated' => 'Feedback updated!', 'deleted' => 'Feedback removed.'];
                echo $map[$_GET['fbsuccess']] ?? 'Done!';
                ?>
            </div>
        <?php endif; ?>

        <!-- Add feedback form -->
        <form action="feedback_add.php" method="POST" style="margin-bottom:25px;">
            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
            <input type="hidden" name="shop_id" value="<?php echo $product['shop_id']; ?>">
            <div class="form-group">
                <label>Rating</label>
                <select name="feedback_rating" required>
                    <option value="5">★★★★★ Excellent</option>
                    <option value="4">★★★★☆ Good</option>
                    <option value="3">★★★☆☆ Average</option>
                    <option value="2">★★☆☆☆ Poor</option>
                    <option value="1">★☆☆☆☆ Terrible</option>
                </select>
            </div>
            <div class="form-group">
                <label>Your Feedback</label>
                <textarea name="feedback_message" placeholder="Share your thoughts about this product..." required></textarea>
            </div>
            <button type="submit" class="btn"><i class="fas fa-paper-plane"></i> Submit Feedback</button>
        </form>

        <!-- Feedback list -->
        <?php if (empty($feedbacks)): ?>
            <p class="empty-state">No feedback yet. Be the first to review this product!</p>
        <?php else: ?>
            <?php foreach ($feedbacks as $f): ?>
                <div class="feedback-item">
                    <div class="fb-head">
                        <div>
                            <span class="fb-name"><?php echo htmlspecialchars($f['user_name']); ?></span>
                            <span class="stars"><?php echo str_repeat('★', (int)$f['feedback_rating']) . str_repeat('☆', 5 - (int)$f['feedback_rating']); ?></span>
                        </div>
                        <div class="fb-date"><?php echo date('M d, Y g:ia', strtotime($f['feedback_date'])); ?></div>
                    </div>
                    <p style="color:#555;"><?php echo nl2br(htmlspecialchars($f['feedback_message'])); ?></p>

                    <?php if ((int)$f['user_id'] === (int)$current_user_id): ?>
                        <div class="fb-actions">
                            <a href="feedback_edit.php?id=<?php echo $f['feedback_id']; ?>"><i class="fas fa-edit"></i> Edit</a>
                            <a href="feedback_delete.php?id=<?php echo $f['feedback_id']; ?>&product_id=<?php echo $product['product_id']; ?>"
                               onclick="return confirm('Delete your feedback?');"><i class="fas fa-trash"></i> Delete</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// ===== Language toggle: switch product name/description between ENG and CEB =====
function setLanguage(lang) {
    // Show only the content spans matching the chosen language
    document.querySelectorAll('.lang-content').forEach(function (el) {
        el.style.display = (el.dataset.lang === lang) ? '' : 'none';
    });

    // Mark the matching button as active
    document.querySelectorAll('.lang-btn').forEach(function (btn) {
        btn.classList.toggle('active', btn.dataset.lang === lang);
    });
}

// Add to cart automatically via JS + PHP (fetch/AJAX), no page reload
document.getElementById('add-cart-form').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('cart_add.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const msg = document.getElementById('cart-msg');
        msg.style.display = 'block';
        if (data.success) {
            msg.className = 'msg msg-success';
            msg.textContent = 'Added to cart!';
        } else {
            msg.className = 'msg msg-error';
            msg.textContent = data.message || 'Could not add to cart.';
        }
        setTimeout(() => { msg.style.display = 'none'; }, 3000);
    })
    .catch(() => {
        const msg = document.getElementById('cart-msg');
        msg.style.display = 'block';
        msg.className = 'msg msg-error';
        msg.textContent = 'Something went wrong.';
    });
});
</script>
</body>
</html>
