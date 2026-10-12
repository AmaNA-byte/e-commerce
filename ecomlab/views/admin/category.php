<?php
require_once __DIR__ . '/../../core/core.php';
require_admin();
require_once __DIR__ . '/../../controllers/ProductController.php';

$controller = new ProductController();

// ?edit_id=N switches the form from "add" to "edit" and pre-fills it.
$editing = null;
if (isset($_GET['edit_id'])) {
  $edit_id = filter_var($_GET['edit_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
  $editing = $edit_id ? $controller->getCategoryById($edit_id) : false;
  if (!$editing) {
    $_SESSION['error'] = 'Category not found.';
    redirect('category.php');
  }
}

$categories = $controller->getAllCategories();

$page_title = 'Categories - Shoppn';
require __DIR__ . '/../layout/header.php';
?>
<h1>Categories</h1>

<?php if (isset($_SESSION['success'])): ?>
  <p class="success"><?= e($_SESSION['success']) ?></p>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
  <p class="error"><?= e($_SESSION['error']) ?></p>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if ($editing): ?>
  <form action="../../actions/update_category_action.php" method="POST">
    <?= csrf_field() ?>
    <h2>Edit Category</h2>
    <input type="hidden" name="cat_id" value="<?= e($editing['cat_id']) ?>">
    <label>Category Name</label>
    <input type="text" name="cat_name" maxlength="100" value="<?= e($editing['cat_name']) ?>" required>
    <button type="submit">Update Category</button>
    <a href="category.php" class="cancel">Cancel</a>
  </form>
<?php else: ?>
  <form action="../../actions/add_category_action.php" method="POST">
    <?= csrf_field() ?>
    <h2>Add Category</h2>
    <label>Category Name</label>
    <input type="text" name="cat_name" maxlength="100" required>
    <button type="submit">Add Category</button>
  </form>
<?php endif; ?>

<h2>All Categories</h2>
<?php if ($categories): ?>
  <table>
    <tr>
      <th>ID</th>
      <th>Category Name</th>
      <th>Actions</th>
    </tr>
    <?php foreach ($categories as $cat): ?>
      <tr>
        <td><?= e($cat['cat_id']) ?></td>
        <td><?= e($cat['cat_name']) ?></td>
        <td>
          <a href="category.php?edit_id=<?= e($cat['cat_id']) ?>" class="btn">Edit</a>
          <form action="../../actions/delete_category_action.php" method="POST" class="inline-form" data-confirm="Delete this category? This cannot be undone.">
            <?= csrf_field() ?>
            <input type="hidden" name="cat_id" value="<?= e($cat['cat_id']) ?>">
            <button type="submit" class="danger">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php else: ?>
  <p>No categories yet.</p>
<?php endif; ?>

<script src="../../js/confirm.js"></script>
<?php require __DIR__ . '/../layout/footer.php'; ?>
