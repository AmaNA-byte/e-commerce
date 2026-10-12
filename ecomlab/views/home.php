<?php
// Loaded by index.php, which has already required core.php.
require_once __DIR__ . '/../controllers/ProductController.php';
$controller = new ProductController();

// ?cat=N or ?brand=N filters the products; otherwise show a random selection.
$int = ['options' => ['min_range' => 1]];
$cat_id = filter_var($_GET['cat'] ?? '', FILTER_VALIDATE_INT, $int);
$brand_id = filter_var($_GET['brand'] ?? '', FILTER_VALIDATE_INT, $int);
$filtered = isset($_GET['cat']) || isset($_GET['brand']);

if (isset($_GET['cat'])) {
  $category = $cat_id ? $controller->getCategoryById($cat_id) : false;
  $products = $category ? $controller->getProductsByCategory($cat_id) : [];
  $heading = $category ? 'Category: ' . $category['cat_name'] : 'Products';
} elseif (isset($_GET['brand'])) {
  $brand = $brand_id ? $controller->getBrandById($brand_id) : false;
  $products = $brand ? $controller->getProductsByBrand($brand_id) : [];
  $heading = $brand ? 'Brand: ' . $brand['brand_name'] : 'Products';
} else {
  $products = $controller->getFeaturedProducts();
  $heading = 'Featured Products';
}

$brand_collection = $filtered ? [] : $controller->getAllBrands();

$page_title = 'Home - Shoppn';
require __DIR__ . '/layout/header.php';
?>
<?php if (!$filtered): ?>
  <div class="hero">
    <?php if (is_logged_in()): ?>
      <h1>Hello, <?= e($_SESSION['customer_name']) ?>!</h1>
      <p>Welcome back to Shoppn.</p>
    <?php else: ?>
      <h1>Welcome to Shoppn</h1>
      <p>Your one-stop shop for great products at great prices.</p>
    <?php endif; ?>
  </div>
<?php endif; ?>

<section class="content">
    <h2><?= e($heading) ?></h2>
    <?php if ($products): ?>
      <div class="product-grid">
        <?php foreach ($products as $product): ?>
          <?php require __DIR__ . '/layout/product_card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p>No products found.</p>
    <?php endif; ?>

    <?php if ($filtered): ?>
      <p><a href="<?= BASE_URL ?>index.php">&larr; All products</a></p>
    <?php elseif ($brand_collection): ?>
      <h2>Our Brands</h2>
      <div class="brand-grid">
        <?php foreach ($brand_collection as $brand): ?>
          <a href="<?= BASE_URL ?>index.php?brand=<?= e($brand['brand_id']) ?>" class="brand-card">
            <?php if (!empty($brand['brand_image'])): ?>
              <img src="<?= BASE_URL . e($brand['brand_image']) ?>" alt="<?= e($brand['brand_name']) ?>">
            <?php else: ?>
              <div class="no-image"><?= e(strtoupper(substr($brand['brand_name'], 0, 1))) ?></div>
            <?php endif; ?>
            <p><?= e($brand['brand_name']) ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/layout/footer.php'; ?>
