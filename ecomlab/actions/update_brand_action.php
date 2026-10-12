<?php
require_once __DIR__ . '/../core/core.php';
require_admin();
require_once __DIR__ . '/../controllers/ProductController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('../views/admin/brand.php');
}

// brand_id must be a positive whole number
$id = filter_var($_POST['brand_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$name = trim(strip_tags($_POST['brand_name'] ?? ''));

if ($id === false) {
  $_SESSION['error'] = 'Invalid brand.';
  redirect('../views/admin/brand.php');
}

if ($name === '' || strlen($name) > 100) {
  $_SESSION['error'] = 'Please enter a brand name (100 characters or fewer).';
  redirect('../views/admin/brand.php?edit_id=' . $id);
}

$controller = new ProductController();

$brand = $controller->getBrandById($id);
if (!$brand) {
  $_SESSION['error'] = 'That brand no longer exists.';
  redirect('../views/admin/brand.php');
}

if ($controller->brandNameExists($name, $id)) {
  $_SESSION['error'] = 'Another brand is already called "' . $name . '".';
  redirect('../views/admin/brand.php?edit_id=' . $id);
}

// Optional new picture (the old one is kept if none is chosen)
$upload = upload_image('brand_image');
if (isset($upload['error'])) {
  $_SESSION['error'] = $upload['error'];
  redirect('../views/admin/brand.php?edit_id=' . $id);
}
$image = $upload['path'] ?? null;

if ($controller->updateBrand($id, $name, $image)) {
  // Remove the replaced picture file
  if ($image && !empty($brand['brand_image']) && is_file(__DIR__ . '/../' . $brand['brand_image'])) {
    unlink(__DIR__ . '/../' . $brand['brand_image']);
  }
  $_SESSION['success'] = 'Brand updated.';
} else {
  $_SESSION['error'] = 'Could not update the brand. Please try again.';
}
redirect('../views/admin/brand.php');
