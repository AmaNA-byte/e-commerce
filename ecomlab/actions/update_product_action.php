<?php
require_once __DIR__ . '/../core/core.php';
require_admin();
require_once __DIR__ . '/../controllers/ProductController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('../views/admin/product.php');
}

// product_id must be a positive whole number
$id = filter_var($_POST['product_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$controller = new ProductController();

if ($id === false || !$controller->getProductById($id)) {
  $_SESSION['error'] = 'Product not found.';
  redirect('../views/admin/product.php');
}

// Send the admin back to the edit form with what they typed still filled in.
function fail($message) {
  global $id;
  $_SESSION['error'] = $message;
  $_SESSION['old'] = $_POST;
  redirect('../views/admin/product.php?edit_id=' . $id);
}

[$data, $error] = $controller->validateProduct($_POST);
if ($error) {
  fail($error);
}

// Keep the existing picture (from the hidden field) unless a new one is uploaded.
// basename() stops the hidden field being used to point at a file in another folder.
$old_filename = basename($_POST['existing_image'] ?? '');
$filename = $old_filename !== '' ? $old_filename : null;

if (!empty($_FILES['product_image']['name'])) {
  $upload = save_product_image($_FILES['product_image']);
  if (isset($upload['error'])) {
    fail($upload['error']);
  }
  $filename = $upload['filename'];
}

if (!$controller->updateProduct($id, $data['cat'], $data['brand'], $data['title'], $data['price'], $data['desc'], $filename, $data['keywords'])) {
  if ($filename !== $old_filename && $filename) {
    unlink(__DIR__ . '/../images/products/' . $filename);
  }
  fail('Could not update the product. Please try again.');
}

// Remove the replaced picture file
if ($filename !== $old_filename && $old_filename !== '' && is_file(__DIR__ . '/../images/products/' . $old_filename)) {
  unlink(__DIR__ . '/../images/products/' . $old_filename);
}

$_SESSION['success'] = 'Product updated.';
redirect('../views/admin/product.php');
