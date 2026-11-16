<?php
require_once __DIR__ . '/../core/core.php';

// The reason was put in $_SESSION['error'] by process_payment_action.php; the header shows it.
$page_title = 'Payment Failed - Shoppn';
require __DIR__ . '/layout/header.php';
?>
<h1>Payment Failed</h1>
<p>Your order was not placed. Your cart has been kept, so you can try again.</p>
<p>
  <a href="<?= BASE_URL ?>views/payment.php" class="btn">Try Again</a>
  <a href="<?= BASE_URL ?>views/cart.php" class="btn">Back to Cart</a>
</p>
<?php require __DIR__ . '/layout/footer.php'; ?>
