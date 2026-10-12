<?php
require_once __DIR__ . '/../core/core.php';
require_admin();
require_once __DIR__ . '/../controllers/ProductController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('../views/admin/category.php');
}

$name = trim(strip_tags($_POST['cat_name'] ?? ''));

if ($name === '' || strlen($name) > 100) {
  $_SESSION['error'] = 'Please enter a category name (100 characters or fewer).';
  redirect('../views/admin/category.php');
}

$controller = new ProductController();

if ($controller->categoryNameExists($name)) {
  $_SESSION['error'] = 'A category called "' . $name . '" already exists.';
  redirect('../views/admin/category.php');
}

if ($controller->addCategory($name)) {
  $_SESSION['success'] = 'Category added.';
} else {
  $_SESSION['error'] = 'Could not add the category. Please try again.';
}
redirect('../views/admin/category.php');
