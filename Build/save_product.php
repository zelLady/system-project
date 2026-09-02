<?php
session_start();
include 'connect.php';
include 'includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$current_user_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manage_products.php");
    exit();
}

$shop_id = get_shop_id_for_user($conn, $current_user_id);
if (!$shop_id) {
    header("Location: manage_products.php?error=" . urlencode("You need a shop before adding products."));
    exit();
}

$mode = $_POST['mode'] ?? 'add';
$category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
$product_nameEng = trim($_POST['product_nameEng']);
$product_nameCeb = trim($_POST['product_nameCeb']);
$product_descriptionEng = trim($_POST['product_descriptionEng']);
$product_descriptionCeb = trim($_POST['product_descriptionCeb']);
$product_price = (float)$_POST['product_price'];

if ($product_nameEng === '' || $product_price < 0) {
    header("Location: manage_products.php?error=" . urlencode("Please fill in the required fields correctly."));
    exit();
}

// Handle optional image upload
$image_path = null;
if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed)) {
        $upload_dir = __DIR__ . '/uploads/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $filename = 'product_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_dir . $filename)) {
            $image_path = 'uploads/products/' . $filename;
        }
    }
}

if ($mode === 'add') {
    if ($image_path) {
        $stmt = $conn->prepare("INSERT INTO products 
            (user_id, product_shop, category_id, product_nameEng, product_nameCeb, product_descriptionEng, product_descriptionCeb, product_price, product_img_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiissssds", $current_user_id, $shop_id, $category_id, $product_nameEng, $product_nameCeb, $product_descriptionEng, $product_descriptionCeb, $product_price, $image_path);
    } else {
        $stmt = $conn->prepare("INSERT INTO products 
            (user_id, product_shop, category_id, product_nameEng, product_nameCeb, product_descriptionEng, product_descriptionCeb, product_price) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiissssd", $current_user_id, $shop_id, $category_id, $product_nameEng, $product_nameCeb, $product_descriptionEng, $product_descriptionCeb, $product_price);
    }

    if ($stmt->execute()) {
        header("Location: manage_products.php?success=added");
    } else {
        header("Location: manage_products.php?error=" . urlencode("Failed to add product."));
    }
    exit();
}

if ($mode === 'edit') {
    $product_id = (int)$_POST['product_id'];

    // Ownership check
    $check = $conn->prepare("SELECT product_id FROM products WHERE product_id = ? AND product_shop = ?");
    $check->bind_param("ii", $product_id, $shop_id);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        header("Location: manage_products.php?error=" . urlencode("You don't have permission to edit this product."));
        exit();
    }

    if ($image_path) {
        $stmt = $conn->prepare("UPDATE products SET category_id=?, product_nameEng=?, product_nameCeb=?, product_descriptionEng=?, product_descriptionCeb=?, product_price=?, product_img_path=? WHERE product_id=? AND product_shop=?");
        $stmt->bind_param("issssdssi", $category_id, $product_nameEng, $product_nameCeb, $product_descriptionEng, $product_descriptionCeb, $product_price, $image_path, $product_id, $shop_id);
    } else {
        $stmt = $conn->prepare("UPDATE products SET category_id=?, product_nameEng=?, product_nameCeb=?, product_descriptionEng=?, product_descriptionCeb=?, product_price=? WHERE product_id=? AND product_shop=?");
        $stmt->bind_param("issssdii", $category_id, $product_nameEng, $product_nameCeb, $product_descriptionEng, $product_descriptionCeb, $product_price, $product_id, $shop_id);
    }

    if ($stmt->execute()) {
        header("Location: manage_products.php?success=updated");
    } else {
        header("Location: manage_products.php?error=" . urlencode("Failed to update product."));
    }
    exit();
}

header("Location: manage_products.php");
exit();
?>