<?php
require_once __DIR__ . '/../core/core.php';
require_admin();
require_once __DIR__ . '/../controllers/ProductController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('../views/admin/category.php');
}

// cat_id must be a positive whole number
$id = filter_var($_POST['cat_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$name = trim(strip_tags($_POST['cat_name'] ?? ''));

if ($id === false) {
  $_SESSION['error'] = 'Invalid category.';
  redirect('../views/admin/category.php');
}

if ($name === '' || strlen($name) > 100) {
  $_SESSION['error'] = 'Please enter a category name (100 characters or fewer).';
  redirect('../views/admin/category.php?edit_id=' . $id);
}

$controller = new ProductController();

if (!$controller->getCategoryById($id)) {
  $_SESSION['error'] = 'That category no longer exists.';
  redirect('../views/admin/category.php');
}

if ($controller->categoryNameExists($name, $id)) {
  $_SESSION['error'] = 'Another category is already called "' . $name . '".';
  redirect('../views/admin/category.php?edit_id=' . $id);
}

if ($controller->updateCategory($id, $name)) {
  $_SESSION['success'] = 'Category updated.';
} else {
  $_SESSION['error'] = 'Could not update the category. Please try again.';
}
redirect('../views/admin/category.php');
