<?php
require_once __DIR__ . '/../core/core.php';
require_admin();
require_once __DIR__ . '/../controllers/ProductController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('../views/admin/brand.php');
}

$name = trim(strip_tags($_POST['brand_name'] ?? ''));

if ($name === '' || strlen($name) > 100) {
  $_SESSION['error'] = 'Please enter a brand name (100 characters or fewer).';
  redirect('../views/admin/brand.php');
}

$controller = new ProductController();

if ($controller->brandNameExists($name)) {
  $_SESSION['error'] = 'A brand called "' . $name . '" already exists.';
  redirect('../views/admin/brand.php');
}

// Optional brand picture
$upload = upload_image('brand_image');
if (isset($upload['error'])) {
  $_SESSION['error'] = $upload['error'];
  redirect('../views/admin/brand.php');
}
$image = $upload['path'] ?? null;

if ($controller->addBrand($name, $image)) {
  $_SESSION['success'] = 'Brand added.';
} else {
  $_SESSION['error'] = 'Could not add the brand. Please try again.';
}
redirect('../views/admin/brand.php');
