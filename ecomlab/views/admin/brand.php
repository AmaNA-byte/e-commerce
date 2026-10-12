<?php
require_once __DIR__ . '/../../core/core.php';
require_admin();
require_once __DIR__ . '/../../controllers/ProductController.php';

$controller = new ProductController();

// ?edit_id=N switches the form from "add" to "edit" and pre-fills it.
$editing = null;
if (isset($_GET['edit_id'])) {
  $edit_id = filter_var($_GET['edit_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
  $editing = $edit_id ? $controller->getBrandById($edit_id) : false;
  if (!$editing) {
    $_SESSION['error'] = 'Brand not found.';
    redirect('brand.php');
  }
}

$brands = $controller->getAllBrands();

$page_title = 'Brands - Shoppn';
require __DIR__ . '/../layout/header.php';
?>
<h1>Brands</h1>

<?php if (isset($_SESSION['success'])): ?>
  <p class="success"><?= e($_SESSION['success']) ?></p>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
  <p class="error"><?= e($_SESSION['error']) ?></p>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if ($editing): ?>
  <form action="../../actions/update_brand_action.php" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <h2>Edit Brand</h2>
    <input type="hidden" name="brand_id" value="<?= e($editing['brand_id']) ?>">
    <label>Brand Name</label>
    <input type="text" name="brand_name" maxlength="100" value="<?= e($editing['brand_name']) ?>" required>

    <label>Brand Picture</label>
    <?php if (!empty($editing['brand_image'])): ?>
      <img src="<?= BASE_URL . e($editing['brand_image']) ?>" alt="" class="thumb">
    <?php endif; ?>
    <input type="file" name="brand_image" accept="image/*">
    <span class="hint">Leave empty to keep the current picture.</span>
    <button type="submit">Update Brand</button>
    <a href="brand.php" class="cancel">Cancel</a>
  </form>
<?php else: ?>
  <form action="../../actions/add_brand_action.php" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <h2>Add Brand</h2>
    <label>Brand Name</label>
    <input type="text" name="brand_name" maxlength="100" required>

    <label>Brand Picture (optional)</label>
    <input type="file" name="brand_image" accept="image/*">
    <button type="submit">Add Brand</button>
  </form>
<?php endif; ?>

<h2>All Brands</h2>
<?php if ($brands): ?>
  <table>
    <tr>
      <th>ID</th>
      <th>Picture</th>
      <th>Brand Name</th>
      <th>Actions</th>
    </tr>
    <?php foreach ($brands as $brand): ?>
      <tr>
        <td><?= e($brand['brand_id']) ?></td>
        <td>
          <?php if (!empty($brand['brand_image'])): ?>
            <img src="<?= BASE_URL . e($brand['brand_image']) ?>" alt="" class="thumb">
          <?php else: ?>
            No picture
          <?php endif; ?>
        </td>
        <td><?= e($brand['brand_name']) ?></td>
        <td>
          <a href="brand.php?edit_id=<?= e($brand['brand_id']) ?>" class="btn">Edit</a>
          <form action="../../actions/delete_brand_action.php" method="POST" class="inline-form" data-confirm="Delete this brand? This cannot be undone.">
            <?= csrf_field() ?>
            <input type="hidden" name="brand_id" value="<?= e($brand['brand_id']) ?>">
            <button type="submit" class="danger">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php else: ?>
  <p>No brands yet.</p>
<?php endif; ?>

<script src="../../js/confirm.js"></script>
<?php require __DIR__ . '/../layout/footer.php'; ?>
