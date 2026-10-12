<?php
require_once __DIR__ . '/../core/core.php';
require_admin();
require_once __DIR__ . '/../controllers/ProductController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('../views/admin/category.php');
}

$id = filter_var($_POST['cat_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$controller = new ProductController();
$category = $id ? $controller->getCategoryById($id) : false;

if (!$category) {
  $_SESSION['error'] = 'Category not found.';
  redirect('../views/admin/category.php');
}

// A category that products still belong to cannot be deleted.
$in_use = count($controller->getProductsByCategory($id));
if ($in_use > 0) {
  $_SESSION['error'] = '"' . $category['cat_name'] . '" cannot be deleted because ' . $in_use
    . ($in_use === 1 ? ' product still uses it.' : ' products still use it.') . ' Delete or move those products first.';
  redirect('../views/admin/category.php');
}

if ($controller->deleteCategory($id)) {
  $_SESSION['success'] = 'Category deleted.';
} else {
  $_SESSION['error'] = 'Could not delete the category. Please try again.';
}
redirect('../views/admin/category.php');
