<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/paystack_config.php';
require_once __DIR__ . '/../controllers/CartController.php';

// Orders belong to a customer, so guests log in first and then come straight back here.
if (!is_logged_in()) {
  $_SESSION['after_login'] = BASE_URL . 'views/payment.php';
  $_SESSION['error'] = 'Please log in to check out. Your cart will be kept.';
  redirect(BASE_URL . 'views/login.php');
}

$controller = new CartController();
$items = $controller->getCartItems(get_ip());
if (!$items) {
  $_SESSION['error'] = 'Your cart is empty.';
  redirect(BASE_URL . 'views/cart.php');
}
$usd_total = round($controller->getCartTotal(get_ip()), 2);

// Paystack charges in cedis. Lock today's rate for this checkout so the amount charged is exactly
// the amount shown here, even if the live rate changes before the customer finishes paying.
$rate = usd_to_ghs_rate();
$ghs_total = usd_to_ghs($usd_total, $rate);
$_SESSION['checkout'] = ['usd_total' => $usd_total, 'rate' => $rate, 'ghs_total' => $ghs_total, 'time' => time()];
$cur = current_currency();

$page_title = 'Payment - Shoppn';
require __DIR__ . '/layout/header.php';
?>
<h1>Checkout</h1>

<table class="wide">
  <tr>
    <th>Product</th>
    <th>Qty</th>
    <th>Unit Price</th>
    <th>Total</th>
  </tr>
  <?php foreach ($items as $item): ?>
    <tr>
      <td><?= e($item['product_title']) ?></td>
      <td><?= e($item['qty']) ?></td>
      <td><?= money($item['product_price'], $cur, $rate) ?></td>
      <td><?= money($item['qty'] * $item['product_price'], $cur, $rate) ?></td>
    </tr>
  <?php endforeach; ?>
  <tr>
    <th colspan="3" class="subtotal">Total</th>
    <th><?= $cur === 'GHS' ? cedis($ghs_total) : money($usd_total, 'USD') ?></th>
  </tr>
</table>

<p class="rate-note">
  Paystack charges in Ghana cedis<?= $cur === 'USD' ? ', so your ' . money($usd_total, 'USD') . ' order will be charged as <strong>' . cedis($ghs_total) . '</strong>' : '' ?>.
  Exchange rate: 1 USD = GHS <?= number_format($rate, 2) ?>.
</p>
<p>You will pay securely with Paystack (card or mobile money). A receipt will go to <strong><?= e($_SESSION['customer_email']) ?></strong>.</p>
<p>
  <button type="button" onclick="payWithPaystack()">Pay <?= cedis($ghs_total) ?></button>
  <a href="cart.php" class="btn">Back to Cart</a>
</p>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
  function payWithPaystack() {
    var handler = PaystackPop.setup({
      key: '<?= e(PAYSTACK_PUBLIC_KEY) ?>',
      email: '<?= e($_SESSION['customer_email']) ?>',
      amount: <?= to_pesewas($ghs_total) ?>, // in pesewas (GHS x 100)
      currency: '<?= PAYSTACK_CURRENCY ?>',
      // v1 inline.js names these callback / onClose (onSuccess / onCancel are the v2 names).
      callback: function (response) {
        // Paystack says it worked, but the server checks with Paystack before recording anything.
        window.location = '<?= BASE_URL ?>actions/process_payment_action.php?ref=' + encodeURIComponent(response.reference);
      },
      onClose: function () {
        alert('Payment cancelled.');
      }
    });
    handler.openIframe();
  }
</script>
<?php require __DIR__ . '/layout/footer.php'; ?>
