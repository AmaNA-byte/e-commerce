<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('../views/register.php');
}

function clean($key) {
  return trim(strip_tags($_POST[$key] ?? ''));
}

$data = [
  'name'    => clean('customer_name'),
  'email'   => clean('customer_email'),
  'pass'    => $_POST['customer_pass'] ?? '', // not stripped: it is hashed, never displayed
  'country' => clean('customer_country'),
  'city'    => clean('customer_city'),
  'contact' => clean('customer_contact'),
  'image'   => null,
];

// Validate (max lengths match the customer table columns)
$error = '';
if (in_array('', [$data['name'], $data['email'], $data['pass'], $data['country'], $data['city'], $data['contact']], true)) {
  $error = 'Please fill in all fields.';
} elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
  $error = 'Please enter a valid email address.';
} elseif (strlen($data['name']) > 100 || strlen($data['email']) > 50 || strlen($data['country']) > 30
       || strlen($data['city']) > 30 || strlen($data['contact']) > 15) {
  $error = 'One or more fields are too long.';
} elseif (!password_is_strong($data['pass'])) {
  $error = PASSWORD_RULE;
}

// Optional profile image
if (!$error) {
  $upload = upload_image('customer_image');
  if (isset($upload['error'])) {
    $error = $upload['error'];
  }
  $data['image'] = $upload['path'] ?? null;
}

if ($error) {
  $_SESSION['error'] = $error;
  redirect('../views/register.php');
}

$controller = new CustomerController();
$result = $controller->register($data);

if (!$result['success']) {
  $_SESSION['error'] = $result['error'];
  redirect('../views/register.php');
}

session_regenerate_id(true);
$_SESSION['customer_id'] = $result['customer_id'];
$_SESSION['customer_name'] = $data['name'];
$_SESSION['customer_email'] = $data['email'];
$_SESSION['user_role'] = 2; // new sign-ups are regular customers
$_SESSION['success'] = 'You are now registered as ' . $data['name'] . '.';
redirect('../views/account/my_account.php');
