<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  redirect('../views/cart.php');
}

// qty[product id] = quantity. Check every quantity before changing anything.
$quantities = [];
foreach ((array) ($_POST['qty'] ?? []) as $product_id => $qty) {
  $qty = filter_var($qty, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 99]]);
  if (!ctype_digit((string) $product_id) || $qty === false) {
    $_SESSION['error'] = 'Quantities must be whole numbers from 1 to 99.';
    redirect('../views/cart.php');
  }
  $quantities[(int) $product_id] = $qty;
}

$controller = new CartController();
$ip = get_ip();
foreach ($quantities as $product_id => $qty) {
  $controller->updateQuantity($product_id, $qty, $ip);
}

$_SESSION['success'] = 'Cart updated.';
redirect('../views/cart.php');
