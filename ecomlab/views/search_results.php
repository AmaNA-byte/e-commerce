<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

$user_query = trim($_GET['user_query'] ?? '');
$products = $user_query !== '' ? (new ProductController())->searchProducts($user_query) : [];

$page_title = 'Search - Shoppn';
require __DIR__ . '/layout/header.php';
?>
<section class="content">
    <?php if ($user_query === ''): ?>
      <h2>Search</h2>
      <p>Type a product name or keyword in the search box above.</p>
    <?php else: ?>
      <h2>Search results for "<?= e($user_query) ?>"</h2>
      <?php if ($products): ?>
        <div class="product-grid">
          <?php foreach ($products as $product): ?>
            <?php require __DIR__ . '/layout/product_card.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p>No products found.</p>
      <?php endif; ?>
    <?php endif; ?>
    <p><a href="<?= BASE_URL ?>index.php">&larr; All products</a></p>
</section>
<?php require __DIR__ . '/layout/footer.php'; ?>
