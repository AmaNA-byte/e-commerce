<?php
require_once __DIR__ . '/../core/core.php';
require_admin();
require_once __DIR__ . '/../controllers/CustomerController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('../views/admin/customers.php');
}

$id = filter_var($_POST['customer_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$role = (int) ($_POST['user_role'] ?? 0);
$controller = new CustomerController();
$customer = $id ? $controller->getCustomerById($id) : false;

if (!$customer || !in_array($role, [ROLE_ADMIN, ROLE_CUSTOMER], true)) {
  $_SESSION['error'] = 'Customer not found.';
  redirect('../views/admin/customers.php');
}

// Admins cannot remove their own admin rights, so there is always at least one admin left.
if ($id === (int) $_SESSION['customer_id']) {
  $_SESSION['error'] = 'You cannot change your own role.';
  redirect('../views/admin/customers.php');
}

if ($controller->updateRole($id, $role)) {
  $_SESSION['success'] = $customer['customer_name'] . ($role === ROLE_ADMIN ? ' is now an admin.' : ' is no longer an admin.');
} else {
  $_SESSION['error'] = 'Could not change the role. Please try again.';
}
redirect('../views/admin/customers.php');
