<?php
// Categories and brands for the side menu (included by header.php, so it shows on every page).
require_once __DIR__ . '/../../controllers/ProductController.php';

$productController = new ProductController();
$categories = $productController->getAllCategories();
$brands = $productController->getAllBrands();
?>
<p class="side-heading">Categories</p>
<ul>
  <?php foreach ($categories as $cat): ?>
    <li><a href="<?= BASE_URL ?>index.php?cat=<?= e($cat['cat_id']) ?>"><?= e($cat['cat_name']) ?></a></li>
  <?php endforeach; ?>
  <?php if (!$categories): ?><li>No categories yet.</li><?php endif; ?>
</ul>

<p class="side-heading">Brands</p>
<ul>
  <?php foreach ($brands as $brand): ?>
    <li><a href="<?= BASE_URL ?>index.php?brand=<?= e($brand['brand_id']) ?>"><?= e($brand['brand_name']) ?></a></li>
  <?php endforeach; ?>
  <?php if (!$brands): ?><li>No brands yet.</li><?php endif; ?>
</ul>
