<?php
require_once __DIR__ . '/../core/core.php';
require_admin();
require_once __DIR__ . '/../controllers/ProductController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('../views/admin/brand.php');
}

$id = filter_var($_POST['brand_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$controller = new ProductController();
$brand = $id ? $controller->getBrandById($id) : false;

if (!$brand) {
  $_SESSION['error'] = 'Brand not found.';
  redirect('../views/admin/brand.php');
}

// A brand that products still belong to cannot be deleted.
$in_use = count($controller->getProductsByBrand($id));
if ($in_use > 0) {
  $_SESSION['error'] = '"' . $brand['brand_name'] . '" cannot be deleted because ' . $in_use
    . ($in_use === 1 ? ' product still uses it.' : ' products still use it.') . ' Delete or move those products first.';
  redirect('../views/admin/brand.php');
}

if ($controller->deleteBrand($id)) {
  // Remove the brand's picture file
  if (!empty($brand['brand_image']) && is_file(__DIR__ . '/../' . $brand['brand_image'])) {
    unlink(__DIR__ . '/../' . $brand['brand_image']);
  }
  $_SESSION['success'] = 'Brand deleted.';
} else {
  $_SESSION['error'] = 'Could not delete the brand. Please try again.';
}
redirect('../views/admin/brand.php');
