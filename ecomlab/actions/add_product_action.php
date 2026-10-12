<?php
require_once __DIR__ . '/../core/core.php';
require_admin();
require_once __DIR__ . '/../controllers/ProductController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('../views/admin/product.php');
}

// Send the admin back to the form with what they typed still filled in.
function fail($message) {
  $_SESSION['error'] = $message;
  $_SESSION['old'] = $_POST;
  redirect('../views/admin/product.php');
}

$controller = new ProductController();
[$data, $error] = $controller->validateProduct($_POST);

if ($error) {
  fail($error);
}
if (empty($_FILES['product_image']['name'])) {
  fail('Please choose a product picture.');
}

// Handle image upload (only after the other fields are valid, so no stray files are left behind)
$upload = save_product_image($_FILES['product_image']);
if (isset($upload['error'])) {
  fail($upload['error']);
}
$filename = $upload['filename'];

if (!$controller->addProduct($data['cat'], $data['brand'], $data['title'], $data['price'], $data['desc'], $filename, $data['keywords'])) {
  unlink(__DIR__ . '/../images/products/' . $filename);
  fail('Could not add the product. Please try again.');
}

$_SESSION['success'] = 'Product added.';
redirect('../views/admin/product.php');
