<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_login();

$order_id = filter_var($_GET['order_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$order = $order_id ? (new CartController())->getOrderById($order_id) : null;

// Customers may only see their own receipts (admins can see any).
if (!$order || ((int) $order['customer_id'] !== (int) $_SESSION['customer_id'] && !is_admin())) {
  $_SESSION['error'] = 'That order could not be found.';
  redirect(BASE_URL . 'views/account/orders.php');
}

$currency = $order['currency'] ?: 'GHS';
$rate = $order['usd_rate'] ? (float) $order['usd_rate'] : null;
// Product prices are stored in USD: show them in cedis at the rate saved with this payment.
$line_money = function ($usd) use ($currency, $rate) {
  return ($currency === 'GHS' && $rate) ? cedis(usd_to_ghs($usd, $rate)) : money($usd, 'USD');
};
$paid_at = $order['created_at'] ? date('j M Y, g:i a', strtotime($order['created_at'])) : date('j M Y', strtotime($order['order_date']));

$page_title = 'Receipt #' . $order['invoice_no'] . ' - Shoppn';
require __DIR__ . '/layout/header.php';
?>
<div class="receipt">
  <div class="receipt-head">
    <div>
      <p class="receipt-shop">Shoppn</p>
      <p class="muted">Payment receipt</p>
    </div>
    <div class="receipt-paid">PAID</div>
  </div>

  <div class="receipt-meta">
    <div>
      <p class="muted">Billed to</p>
      <p><strong><?= e($order['customer_name']) ?></strong><br>
        <?= e($order['customer_email']) ?><br>
        <?= e($order['customer_contact']) ?><br>
        <?= e($order['customer_city']) ?>, <?= e($order['customer_country']) ?></p>
    </div>
    <div>
      <p><span class="muted">Receipt no.</span> <strong><?= e($order['invoice_no']) ?></strong></p>
      <p><span class="muted">Order no.</span> <?= e($order['order_id']) ?></p>
      <p><span class="muted">Date paid</span> <?= e($paid_at) ?></p>
      <p><span class="muted">Paid with</span> Paystack</p>
      <?php if ($rate): ?>
        <p><span class="muted">Exchange rate</span> 1 USD = GHS <?= number_format($rate, 2) ?></p>
      <?php endif; ?>
      <?php if ($order['transaction_ref']): ?>
        <p><span class="muted">Reference</span> <?= e($order['transaction_ref']) ?></p>
      <?php endif; ?>
    </div>
  </div>

  <table class="wide">
    <tr>
      <th>Product</th>
      <th>Qty</th>
      <th>Unit Price</th>
      <th>Total</th>
    </tr>
    <?php foreach ($order['items'] as $item): ?>
      <tr>
        <td><?= e($item['product_title']) ?></td>
        <td><?= e($item['qty']) ?></td>
        <td><?= $line_money($item['product_price']) ?></td>
        <td><?= $line_money($item['qty'] * $item['product_price']) ?></td>
      </tr>
    <?php endforeach; ?>
    <tr>
      <th colspan="3" class="subtotal">Total paid</th>
      <th><?= e($currency) ?> <?= number_format((float) $order['amt'], 2) ?></th>
    </tr>
  </table>

  <p class="muted receipt-thanks">Thank you for shopping with Shoppn! A copy of this receipt was emailed to <?= e($order['customer_email']) ?>.</p>
</div>

<p class="no-print">
  <button type="button" onclick="window.print()">Print / Save as PDF</button>
  <a href="<?= BASE_URL ?>views/account/orders.php" class="btn">My Orders</a>
  <a href="<?= BASE_URL ?>index.php" class="btn">Continue Shopping</a>
</p>
<?php require __DIR__ . '/layout/footer.php'; ?>
