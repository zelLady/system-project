<?php
// includes/product_form.php
//
// Expects, set by the including page:
//   $mode        - 'add' or 'edit'
//   $categories  - array of rows from product_category (category_id, category_name)
//   $product     - (edit mode only) the row from `products` being edited
//
// Posts to save_product.php, which reads $_POST['mode'] to decide
// whether to INSERT or UPDATE.

$product = $product ?? [];
$is_edit = ($mode ?? 'add') === 'edit';
?>
<form action="save_product.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?php echo $is_edit ? 'edit' : 'add'; ?>">
    <?php if ($is_edit): ?>
        <input type="hidden" name="product_id" value="<?php echo (int)$product['product_id']; ?>">
    <?php endif; ?>

    <div class="form-row">
        <div class="form-group">
            <label>Product Name (English)</label>
            <input type="text" name="product_nameEng" required
                   value="<?php echo htmlspecialchars($product['product_nameEng'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>Product Name (Cebuano)</label>
            <input type="text" name="product_nameCeb"
                   value="<?php echo htmlspecialchars($product['product_nameCeb'] ?? ''); ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Category</label>
            <select name="category_id">
                <option value="">-- Select category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo (int)$cat['category_id']; ?>"
                        <?php echo (isset($product['category_id']) && (int)$product['category_id'] === (int)$cat['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Price (₱)</label>
            <input type="number" name="product_price" step="0.01" min="0" required
                   value="<?php echo htmlspecialchars($product['product_price'] ?? ''); ?>">
        </div>
    </div>

    <div class="form-group">
        <label>Description (English)</label>
        <textarea name="product_descriptionEng"><?php echo htmlspecialchars($product['product_descriptionEng'] ?? ''); ?></textarea>
    </div>

    <div class="form-group">
        <label>Description (Cebuano)</label>
        <textarea name="product_descriptionCeb"><?php echo htmlspecialchars($product['product_descriptionCeb'] ?? ''); ?></textarea>
    </div>

    <div class="form-group">
        <label>Product Image</label>

        <?php if ($is_edit && !empty($product['product_img_path'])): ?>
            <div class="current-image">
                <img src="<?php echo htmlspecialchars($product['product_img_path']); ?>" alt="">
                <span style="color:#666; font-size:13px;">Current image — upload a new one to replace it.</span>
            </div>
        <?php endif; ?>

        <input type="file" name="product_image" accept=".jpg,.jpeg,.png,.gif,.webp">
    </div>

    <div class="form-actions">
        <button type="submit" class="btn">
            <i class="fas fa-save"></i> <?php echo $is_edit ? 'Update Product' : 'Add Product'; ?>
        </button>
        <a href="manage_products.php" class="btn btn-outline">Cancel</a>
    </div>
</form>
