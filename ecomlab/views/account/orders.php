<?php
require_once __DIR__ . '/../../core/core.php';
require_once __DIR__ . '/../../controllers/CartController.php';
require_login();

$orders = (new CartController())->getOrdersByCustomer($_SESSION['customer_id']);

$page_title = 'My Orders - Shoppn';
require __DIR__ . '/../layout/header.php';
?>
<h1>My Orders</h1>

<?php if ($orders): ?>
  <table class="wide">
    <tr>
      <th>Order #</th>
      <th>Invoice</th>
      <th>Date</th>
      <th>Status</th>
      <th>Amount Paid</th>
      <th></th>
    </tr>
    <?php foreach ($orders as $order): ?>
      <tr>
        <td><?= e($order['order_id']) ?></td>
        <td><?= e($order['invoice_no']) ?></td>
        <td><?= e($order['order_date']) ?></td>
        <td><?= e(ucfirst($order['order_status'])) ?></td>
        <td><?= e($order['currency']) ?> <?= number_format((float) $order['amt'], 2) ?></td>
        <td><a href="<?= BASE_URL ?>views/payment_success.php?order_id=<?= e($order['order_id']) ?>" class="btn">View</a></td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php else: ?>
  <p>You have not placed any orders yet.</p>
  <a href="<?= BASE_URL ?>index.php" class="btn">Start Shopping</a>
<?php endif; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
