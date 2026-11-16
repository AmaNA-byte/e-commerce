<?php
// Paystack settings. Get your TEST keys from https://dashboard.paystack.com/#/settings/developers
// Test keys start with sk_test_ / pk_test_ and never move real money.
// The public key goes in the browser (views/payment.php). The secret key must stay on the server.
// The keys live in core/paystack_keys.php (two define() lines), so they are kept in one small file.
if (file_exists(__DIR__ . '/paystack_keys.php')) {
  require_once __DIR__ . '/paystack_keys.php';
}
if (!defined('PAYSTACK_SECRET_KEY')) {
  define('PAYSTACK_SECRET_KEY', 'sk_test_REPLACE_ME');
  define('PAYSTACK_PUBLIC_KEY', 'pk_test_REPLACE_ME');
}

// A Ghana Paystack account charges in Ghana cedis.
define('PAYSTACK_CURRENCY', 'GHS');

// Asks Paystack's Verify API about a transaction. Returns the decoded JSON reply, or null if Paystack could not be reached.
function paystack_verify($reference) {
  $url = 'https://api.paystack.co/transaction/verify/' . rawurlencode($reference);
  $headers = ['Authorization: Bearer ' . PAYSTACK_SECRET_KEY];

  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_TIMEOUT => 30,
  ]);
  $response = curl_exec($ch);
  if ($response === false) {
    error_log('Paystack verify failed: ' . curl_error($ch));
    curl_close($ch);
    return null;
  }
  curl_close($ch);
  return json_decode($response, true);
}

// Amount in pesewas (the smallest unit Paystack uses), e.g. 410.50 -> 41050.
function to_pesewas($amount) {
  return (int) round($amount * 100);
}
