<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/ProductController.php';

// Go back to the page the customer came from.
$back = previous_page();

// ?add_cart=N must be a real product
$product_id = filter_var($_GET['add_cart'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$product_id || !(new ProductController())->getProductById($product_id)) {
  $_SESSION['error'] = 'That product could not be found.';
  redirect($back);
}

$ip = get_ip();
$controller = new CartController();

if ($controller->isInCart($product_id, $ip)) {
  $_SESSION['error'] = 'This item is already in your cart. Go to cart to update quantity.';
  redirect($back);
}

if ($controller->addToCart($product_id, 1, $ip, $_SESSION['customer_id'] ?? null)) {
  $_SESSION['success'] = 'Added to your cart.';
} else {
  $_SESSION['error'] = 'Could not add the item to your cart. Please try again.';
}
redirect($back);
