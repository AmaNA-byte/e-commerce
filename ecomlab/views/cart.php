<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

$controller = new CartController();
$items = $controller->getCartItems(get_ip());
$subtotal = $controller->getCartTotal(get_ip());

$page_title = 'Shopping Cart - Shoppn';
require __DIR__ . '/layout/header.php';
?>
<h1>Shopping Cart</h1>

<?php if ($items): ?>
  <form method="POST" class="cart-form">
    <?= csrf_field() ?>
    <table class="wide">
      <tr>
        <th>Remove</th>
        <th>Product</th>
        <th>Qty</th>
        <th>Unit Price</th>
        <th>Total</th>
      </tr>
      <?php foreach ($items as $item): ?>
        <tr>
          <td><input type="checkbox" name="remove[]" value="<?= e($item['p_id']) ?>"></td>
          <td>
            <a href="single_product.php?pro_id=<?= e($item['p_id']) ?>" class="cart-product">
              <?php if ($item['product_image']): ?>
                <img src="<?= BASE_URL ?>images/products/<?= e($item['product_image']) ?>" alt="" class="thumb">
              <?php endif; ?>
              <?= e($item['product_title']) ?>
            </a>
          </td>
          <td><input type="number" name="qty[<?= e($item['p_id']) ?>]" value="<?= e($item['qty']) ?>" min="1" max="99" required></td>
          <td>$<?= number_format($item['product_price'], 2) ?></td>
          <td>$<?= number_format($item['qty'] * $item['product_price'], 2) ?></td>
        </tr>
      <?php endforeach; ?>
      <tr>
        <th colspan="4" class="subtotal">Subtotal</th>
        <th>$<?= number_format($subtotal, 2) ?></th>
      </tr>
    </table>

    <button type="submit" formaction="../actions/remove_from_cart_action.php" formnovalidate>Remove from Cart</button>
    <button type="submit" formaction="../actions/update_cart_action.php">Update Quantity</button>
    <a href="<?= BASE_URL ?>index.php" class="btn">Continue Shopping</a>
    <span class="btn disabled" title="Checkout will be added in a later task">Checkout</span>
  </form>
<?php else: ?>
  <p>Your cart is empty.</p>
  <a href="<?= BASE_URL ?>index.php" class="btn">Continue Shopping</a>
<?php endif; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>
