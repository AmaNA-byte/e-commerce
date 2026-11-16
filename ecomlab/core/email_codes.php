<?php
// Sending 6-digit codes by email, for verifying a new account ('verify') and for forgot password ('reset').
require_once __DIR__ . '/mail_config.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

const CODE_MINUTES = 15;        // how long a code works
const CODE_RESEND_SECONDS = 60; // at most one email per minute per account

// Makes a new code for $customer (a customer row) and emails it.
// Returns 'sent', 'too_soon' (a code was emailed less than a minute ago) or 'failed'.
function send_code(CustomerController $controller, $customer, $purpose) {
  $id = $customer['customer_id'];
  $last = $controller->lastCodeTime($id, $purpose);
  if ($last && strtotime($last) > time() - CODE_RESEND_SECONDS) {
    return 'too_soon';
  }

  $code = $controller->createCode($id, $purpose, CODE_MINUTES);
  if (!$code) {
    return 'failed';
  }

  $name = $customer['customer_name'];
  if ($purpose === 'verify') {
    $subject = "Your Shoppn verification code: $code";
    $title = 'Verify your email';
    $intro = 'Welcome to Shoppn! Enter this code to verify your email address and finish creating your account:';
    $outro = 'If you did not create a Shoppn account, you can ignore this email.';
  } else {
    $subject = "Your Shoppn password reset code: $code";
    $title = 'Reset your password';
    $intro = 'We received a request to reset your Shoppn password. Enter this code to choose a new password:';
    $outro = 'If you did not ask for this, you can ignore this email: your password will not change.';
  }

  $html = '<div style="font-family:Arial,sans-serif;max-width:480px;margin:auto;color:#1f2937">'
        . '<h2 style="color:#1e40af">' . $title . '</h2>'
        . '<p>Hi ' . e($name) . ',</p><p>' . $intro . '</p>'
        . '<p style="font-size:32px;font-weight:bold;letter-spacing:8px;text-align:center;background:#eff6ff;'
        . 'border:1px solid #bfdbfe;border-radius:6px;padding:16px;color:#1e40af">' . $code . '</p>'
        . '<p>The code works for ' . CODE_MINUTES . ' minutes. Never share it with anyone.</p>'
        . '<p style="color:#64748b;font-size:13px">' . $outro . '</p></div>';
  $text = "Hi $name,\n\n$intro\n\n    $code\n\nThe code works for " . CODE_MINUTES . " minutes. Never share it with anyone.\n\n$outro";

  if (send_email($customer['customer_email'], $name, $subject, $html, $text)) {
    return 'sent';
  }

  // On your own computer only: if Gmail could not send it, show the code on the page so you can keep testing.
  if ($_SERVER['SERVER_NAME'] === 'localhost') {
    $_SESSION['dev_code'] = ['code' => $code, 'error' => $GLOBALS['mail_error']];
  }
  return 'failed';
}

// Local testing box shown on the code pages when an email could not be sent (localhost only).
function dev_code_notice() {
  $dev = $_SESSION['dev_code'] ?? null;
  unset($_SESSION['dev_code']);
  if (!$dev) {
    return '';
  }
  return '<p class="hero"><strong>Local testing:</strong> the email could not be sent. ' . e($dev['error'])
       . '<br>Your code is <strong>' . e($dev['code']) . '</strong></p>';
}

// A 6-digit code typed into the form, or '' if it is not 6 digits.
function posted_code() {
  $code = preg_replace('/\D/', '', (string) ($_POST['code'] ?? ''));
  return strlen($code) === 6 ? $code : '';
}
