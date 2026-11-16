<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../core/email_codes.php';

// "Send a new code" on the verify-email and reset-password pages.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('../views/login.php');
}

$purpose = ($_POST['purpose'] ?? '') === 'reset' ? 'reset' : 'verify';
$email = $purpose === 'reset' ? ($_SESSION['reset_email'] ?? '') : ($_SESSION['verify_email'] ?? '');
$page = $purpose === 'reset' ? '../views/reset_password.php' : '../views/verify_email.php';
if ($email === '') {
  redirect($purpose === 'reset' ? '../views/forgot_password.php' : '../views/login.php');
}

$controller = new CustomerController();
$customer = $controller->getCustomerByEmail($email);

// For password reset, say the same thing whether or not the account exists.
$status = $customer ? send_code($controller, $customer, $purpose) : 'sent';

if ($status === 'too_soon') {
  $_SESSION['error'] = 'A code was sent less than a minute ago. Please wait a moment before asking for another.';
} elseif ($status === 'failed' && empty($_SESSION['dev_code'])) {
  $_SESSION['error'] = 'We could not send the email right now. Please try again in a few minutes.';
} elseif ($status === 'sent') {
  $_SESSION['success'] = 'A new code has been sent to ' . $email . '.';
}
redirect($page);
