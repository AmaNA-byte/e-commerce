<?php
require_once __DIR__ . '/../../controllers/ProductController.php';

$productController = new ProductController();
$categories = $productController->getCategories();
$brands = $productController->getBrands();
?>
<aside class="sidebar">
  <h3>Categories</h3>
  <ul>
    <?php foreach ($categories as $cat): ?>
      <li><?= e($cat['cat_name']) ?></li>
    <?php endforeach; ?>
    <?php if (!$categories): ?><li>No categories yet.</li><?php endif; ?>
  </ul>

  <h3>Brands</h3>
  <ul>
    <?php foreach ($brands as $brand): ?>
      <li><?= e($brand['brand_name']) ?></li>
    <?php endforeach; ?>
    <?php if (!$brands): ?><li>No brands yet.</li><?php endif; ?>
  </ul>
</aside>
