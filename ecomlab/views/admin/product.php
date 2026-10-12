<?php
require_once __DIR__ . '/../../core/core.php';
require_admin();
require_once __DIR__ . '/../../controllers/ProductController.php';

$controller = new ProductController();

// ?edit_id=N switches the form from "add" to "edit" and pre-fills it.
$editing = null;
if (isset($_GET['edit_id'])) {
  $edit_id = filter_var($_GET['edit_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
  $editing = $edit_id ? $controller->getProductById($edit_id) : false;
  if (!$editing) {
    $_SESSION['error'] = 'Product not found.';
    redirect('product.php');
  }
}

$categories = $controller->getAllCategories();
$brands = $controller->getAllBrands();
$products = $controller->getAllProducts();

// Form values: what was typed before a failed submit, else the product being edited, else empty.
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
function field($name) {
  global $old, $editing;
  return $old[$name] ?? $editing[$name] ?? '';
}

$page_title = 'Products - Shoppn';
require __DIR__ . '/../layout/header.php';
?>
<h1>Products</h1>

<?php if (isset($_SESSION['success'])): ?>
  <p class="success"><?= e($_SESSION['success']) ?></p>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
  <p class="error"><?= e($_SESSION['error']) ?></p>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (!$categories || !$brands): ?>
  <p class="error">Add at least one category and one brand before adding products.</p>
<?php endif; ?>

<form id="productForm" method="POST" enctype="multipart/form-data"
      action="../../actions/<?= $editing ? 'update_product_action.php' : 'add_product_action.php' ?>">
  <?= csrf_field() ?>
  <h2><?= $editing ? 'Edit Product' : 'Add Product' ?></h2>
  <?php if ($editing): ?>
    <input type="hidden" name="product_id" value="<?= e($editing['product_id']) ?>">
    <input type="hidden" name="existing_image" value="<?= e($editing['product_image']) ?>">
  <?php endif; ?>

  <label>Product Title</label>
  <input type="text" name="product_title" maxlength="200" value="<?= e(field('product_title')) ?>" required>

  <label>Price ($)</label>
  <input type="number" name="product_price" min="0.01" step="0.01" value="<?= e(field('product_price')) ?>" required>

  <label>Category</label>
  <select name="product_cat" required>
    <option value="">-- Select category --</option>
    <?php foreach ($categories as $cat): ?>
      <option value="<?= e($cat['cat_id']) ?>" <?= field('product_cat') == $cat['cat_id'] ? 'selected' : '' ?>><?= e($cat['cat_name']) ?></option>
    <?php endforeach; ?>
  </select>

  <label>Brand</label>
  <select name="product_brand" required>
    <option value="">-- Select brand --</option>
    <?php foreach ($brands as $brand): ?>
      <option value="<?= e($brand['brand_id']) ?>" <?= field('product_brand') == $brand['brand_id'] ? 'selected' : '' ?>><?= e($brand['brand_name']) ?></option>
    <?php endforeach; ?>
  </select>

  <label>Description</label>
  <textarea name="product_desc" maxlength="500" rows="4"><?= e(field('product_desc')) ?></textarea>

  <label>Keywords</label>
  <input type="text" name="product_keywords" maxlength="100" value="<?= e(field('product_keywords')) ?>">
  <span class="hint">Words customers might search for, separated by commas.</span>

  <label>Product Picture</label>
  <?php if ($editing && $editing['product_image']): ?>
    <img src="<?= BASE_URL ?>images/products/<?= e($editing['product_image']) ?>" alt="Current picture" class="preview">
    <span class="hint">Current picture. Choose a file only if you want to replace it.</span>
  <?php endif; ?>
  <input type="file" name="product_image" accept="image/*,.heic,.heif" <?= $editing ? '' : 'required' ?>>
  <span class="hint">Any picture, 8 MB or smaller.</span>
  <span class="error" id="product_image_error"></span>

  <button type="submit"><?= $editing ? 'Update Product' : 'Add Product' ?></button>
  <?php if ($editing): ?>
    <a href="product.php" class="cancel">Cancel</a>
  <?php endif; ?>
</form>

<h2>All Products</h2>
<?php if ($products): ?>
  <table class="wide">
    <tr>
      <th>Picture</th>
      <th>Title</th>
      <th>Price</th>
      <th>Category</th>
      <th>Brand</th>
      <th>Actions</th>
    </tr>
    <?php foreach ($products as $product): ?>
      <tr>
        <td>
          <?php if ($product['product_image']): ?>
            <img src="<?= BASE_URL ?>images/products/<?= e($product['product_image']) ?>" alt="" class="thumb">
          <?php else: ?>
            No picture
          <?php endif; ?>
        </td>
        <td><?= e($product['product_title']) ?></td>
        <td>$<?= number_format($product['product_price'], 2) ?></td>
        <td><?= e($product['cat_name']) ?></td>
        <td><?= e($product['brand_name']) ?></td>
        <td>
          <a href="product.php?edit_id=<?= e($product['product_id']) ?>" class="btn">Edit</a>
          <form action="../../actions/delete_product_action.php" method="POST" class="inline-form" data-confirm="Delete this product? This cannot be undone.">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= e($product['product_id']) ?>">
            <button type="submit" class="danger">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php else: ?>
  <p>No products yet.</p>
<?php endif; ?>

<script src="../../js/product.js"></script>
<script src="../../js/confirm.js"></script>
<?php require __DIR__ . '/../layout/footer.php'; ?>
