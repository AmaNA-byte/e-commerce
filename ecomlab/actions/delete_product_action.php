<?php
require_once __DIR__ . '/../core/core.php';
require_admin();
require_once __DIR__ . '/../controllers/ProductController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('../views/admin/product.php');
}

$id = filter_var($_POST['product_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$controller = new ProductController();
$product = $id ? $controller->getProductById($id) : false;

if (!$product) {
  $_SESSION['error'] = 'Product not found.';
  redirect('../views/admin/product.php');
}

if ($controller->deleteProduct($id)) {
  // Remove the product's picture file
  $image = __DIR__ . '/../images/products/' . basename($product['product_image'] ?? '');
  if ($product['product_image'] && is_file($image)) {
    unlink($image);
  }
  $_SESSION['success'] = 'Product deleted.';
} else {
  // The database refuses when the product is part of a customer's order.
  $_SESSION['error'] = '"' . $product['product_title'] . '" cannot be deleted because it is part of an order.';
}
redirect('../views/admin/product.php');
