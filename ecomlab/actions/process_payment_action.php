<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/paystack_config.php';
require_once __DIR__ . '/../controllers/CartController.php';

// Called by Paystack's success callback in views/payment.php: process_payment_action.php?ref=...
// Anyone can type this address (e.g. ?ref=fake), so we NEVER trust it: the order is only recorded
// after Paystack's Verify API confirms the payment, for the right amount, and that it has not been used before.
require_login();

function payment_failed($message) {
  $_SESSION['error'] = $message;
  redirect('../views/payment_failed.php');
}

$reference = (string) ($_GET['ref'] ?? '');
if (!preg_match('/^[A-Za-z0-9._=-]{1,100}$/', $reference)) {
  payment_failed('No valid payment reference was received.');
}

$controller = new CartController();
$ip = get_ip();
$customer_id = (int) $_SESSION['customer_id'];

// Already recorded? (e.g. the page was refreshed). Show that order again instead of making a second one.
$existing = $controller->getOrderIdByReference($reference);
if ($existing) {
  if ((int) $existing['customer_id'] === $customer_id) {
    redirect('../views/payment_success.php?order_id=' . $existing['order_id']);
  }
  payment_failed('That payment reference has already been used.');
}

$items = $controller->getCartItems($ip);
if (!$items) {
  payment_failed('Your cart is empty, so there is nothing to pay for.');
}
$usd_total = round($controller->getCartTotal($ip), 2);

// Prices are in USD; Paystack charged cedis. Use the rate locked when the payment page was shown
// (kept on the server in the session, so it cannot be changed by the customer), as long as the cart
// has not changed since and it is less than 2 hours old. Otherwise use today's rate.
$checkout = $_SESSION['checkout'] ?? null;
$locked = $checkout && abs($checkout['usd_total'] - $usd_total) < 0.005 && $checkout['time'] > time() - 7200;
$rate = $locked ? $checkout['rate'] : usd_to_ghs_rate();
$total = $locked ? $checkout['ghs_total'] : usd_to_ghs($usd_total, $rate); // in GHS

// Ask Paystack directly.
$reply = paystack_verify($reference);
if ($reply === null) {
  payment_failed("We could not reach Paystack to confirm your payment. If you were charged, contact us with reference $reference.");
}
$data = $reply['data'] ?? [];

if (empty($reply['status']) || ($data['status'] ?? '') !== 'success') {
  payment_failed('Paystack did not confirm this payment' . (!empty($data['gateway_response']) ? ' (' . $data['gateway_response'] . ')' : '') . '.');
}
// The amount actually paid must match the cart (stops paying GHS 1 for a GHS 500 cart).
if ((int) ($data['amount'] ?? 0) !== to_pesewas($total) || ($data['currency'] ?? '') !== PAYSTACK_CURRENCY) {
  error_log("Amount mismatch for $reference: paid " . ($data['amount'] ?? '?') . ' ' . ($data['currency'] ?? '') . ', cart ' . to_pesewas($total));
  payment_failed("The amount paid does not match your cart total. Please contact us with reference $reference.");
}

// Payment confirmed: record the order.
$order_id = $controller->processPayment($customer_id, $ip, $reference, $total, PAYSTACK_CURRENCY, $rate);
unset($_SESSION['checkout']);
if (!$order_id) {
  error_log("Paid but order not saved. Reference: $reference, customer: $customer_id");
  payment_failed("Your payment went through but we could not save your order. Please contact us with reference $reference.");
}

// Receipt email, sent with PHPMailer (settings in core/mail_config.php).
// If it cannot be sent, the order is still saved and the receipt page shows everything.
require_once __DIR__ . '/../core/mail_config.php';
$order = $controller->getOrderById($order_id);
$cur = PAYSTACK_CURRENCY;
$paid = $cur . ' ' . number_format($total, 2);
$when = date('j M Y, g:i a', strtotime($order['created_at']));
$receipt_link = site_url('views/payment_success.php?order_id=' . $order_id);

$lines = '';
$rows = '';
foreach ($order['items'] as $item) {
  $line_total = $cur . ' ' . number_format(usd_to_ghs($item['qty'] * $item['product_price'], $rate), 2);
  $lines .= "- {$item['product_title']} x {$item['qty']} @ $cur " . number_format(usd_to_ghs($item['product_price'], $rate), 2) . " = $line_total\n";
  $rows .= '<tr><td style="padding:8px;border-bottom:1px solid #e2e8f0">' . e($item['product_title']) . '</td>'
         . '<td style="padding:8px;border-bottom:1px solid #e2e8f0;text-align:center">' . (int) $item['qty'] . '</td>'
         . '<td style="padding:8px;border-bottom:1px solid #e2e8f0;text-align:right">' . $cur . ' ' . number_format(usd_to_ghs($item['product_price'], $rate), 2) . '</td>'
         . '<td style="padding:8px;border-bottom:1px solid #e2e8f0;text-align:right">' . $line_total . '</td></tr>';
}
$th = 'style="padding:8px;background:#2563eb;color:#fff;text-align:left"';

$html = '<div style="font-family:Arial,sans-serif;max-width:600px;margin:auto;color:#1f2937">'
      . '<h2 style="color:#1e40af;margin-bottom:0">Shoppn</h2><p style="color:#64748b;margin-top:4px">Payment receipt</p>'
      . '<p>Hi ' . e($order['customer_name']) . ',</p><p>Thank you for your order! Your payment was successful. Here is your receipt.</p>'
      . '<table style="width:100%;font-size:14px;margin-bottom:12px">'
      . '<tr><td style="color:#64748b">Receipt no.</td><td><strong>' . e($order['invoice_no']) . '</strong></td></tr>'
      . '<tr><td style="color:#64748b">Order no.</td><td>' . (int) $order_id . '</td></tr>'
      . '<tr><td style="color:#64748b">Date paid</td><td>' . e($when) . '</td></tr>'
      . '<tr><td style="color:#64748b">Paid with</td><td>Paystack</td></tr>'
      . '<tr><td style="color:#64748b">Exchange rate</td><td>1 USD = GHS ' . number_format($rate, 2) . ' (order value ' . money($usd_total, 'USD') . ')</td></tr>'
      . '<tr><td style="color:#64748b">Reference</td><td>' . e($reference) . '</td></tr></table>'
      . '<table style="width:100%;border-collapse:collapse;font-size:14px">'
      . "<tr><th $th>Product</th><th $th>Qty</th><th $th>Unit Price</th><th $th>Total</th></tr>$rows"
      . '<tr><td colspan="3" style="padding:8px;text-align:right"><strong>Total paid</strong></td>'
      . '<td style="padding:8px;text-align:right"><strong>' . $paid . '</strong></td></tr></table>'
      . '<p><a href="' . e($receipt_link) . '" style="display:inline-block;margin-top:12px;padding:10px 20px;background:#2563eb;color:#fff;text-decoration:none;border-radius:4px">View receipt online</a></p>'
      . '<p style="color:#64748b;font-size:13px">Keep this email for your records.</p></div>';
$text = "Shoppn - Payment receipt\n\nHi {$order['customer_name']},\nThank you for your order! Your payment was successful.\n\n"
      . "Receipt no.: {$order['invoice_no']}\nOrder no.: $order_id\nDate paid: $when\nPaid with: Paystack\nExchange rate: 1 USD = GHS " . number_format($rate, 2) . " (order value " . money($usd_total, "USD") . ")\nReference: $reference\n\n"
      . "$lines\nTotal paid: $paid\n\nView receipt online: $receipt_link\n";

$sent = send_email($order['customer_email'], $order['customer_name'], "Your Shoppn receipt #{$order['invoice_no']}", $html, $text);

$_SESSION['success'] = 'Payment successful!' . ($sent ? ' Your receipt has been emailed to ' . $order['customer_email'] . '.' : '');
redirect('../views/payment_success.php?order_id=' . $order_id);
