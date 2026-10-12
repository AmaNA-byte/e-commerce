<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/ProductController.php';

// ?pro_id=N must be a real product, otherwise go home.
$pro_id = filter_var($_GET['pro_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$product = $pro_id ? (new ProductController())->getProductById($pro_id) : false;
if (!$product) {
  redirect(BASE_URL . 'index.php');
}

$page_title = $product['product_title'] . ' - Shoppn';
require __DIR__ . '/layout/header.php';
?>
<div class="product-detail">
  <?php if ($product['product_image']): ?>
    <img src="<?= BASE_URL ?>images/products/<?= e($product['product_image']) ?>" alt="<?= e($product['product_title']) ?>">
  <?php endif; ?>

  <div class="info">
    <h1><?= e($product['product_title']) ?></h1>
    <p class="price">$<?= number_format($product['product_price'], 2) ?></p>
    <p><strong>Category:</strong> <a href="<?= BASE_URL ?>index.php?cat=<?= e($product['product_cat']) ?>"><?= e($product['cat_name']) ?></a></p>
    <p><strong>Brand:</strong> <a href="<?= BASE_URL ?>index.php?brand=<?= e($product['product_brand']) ?>"><?= e($product['brand_name']) ?></a></p>
    <p><strong>Description:</strong> <?= nl2br(e($product['product_desc'])) ?></p>
    <p><strong>Keywords:</strong> <?= e($product['product_keywords']) ?></p>
    <a href="<?= BASE_URL ?>actions/add_to_cart_action.php?add_cart=<?= e($product['product_id']) ?>" class="btn">Add to Cart</a>
  </div>
</div>

<p><a href="<?= BASE_URL ?>index.php">&larr; Back to products</a></p>
<?php require __DIR__ . '/layout/footer.php'; ?>
