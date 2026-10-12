<?php // One product card. Expects $product (a row from the products table). ?>
<div class="product-card">
  <?php if ($product['product_image']): ?>
    <img src="<?= BASE_URL ?>images/products/<?= e($product['product_image']) ?>" alt="<?= e($product['product_title']) ?>">
  <?php endif; ?>
  <h3><?= e($product['product_title']) ?></h3>
  <p class="price">$<?= number_format($product['product_price'], 2) ?></p>
  <a href="<?= BASE_URL ?>views/single_product.php?pro_id=<?= e($product['product_id']) ?>" class="btn">Details</a>
  <a href="<?= BASE_URL ?>actions/add_to_cart_action.php?add_cart=<?= e($product['product_id']) ?>" class="btn">Add to Cart</a>
</div>
