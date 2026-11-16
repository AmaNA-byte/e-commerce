<?php
require_once __DIR__ . '/../core/db_class.php';

// Cart rows are tied to the visitor's IP address, so guests can shop without an account.
class CartClass extends Database {

  public function isInCart($product_id, $ip) {
    $stmt = $this->conn->prepare('SELECT p_id FROM cart WHERE p_id = ? AND ip_add = ?');
    $stmt->bind_param('is', $product_id, $ip);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
  }

  public function addToCart($product_id, $qty, $ip, $customer_id = null) {
    $stmt = $this->conn->prepare('INSERT INTO cart (p_id, ip_add, c_id, qty) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('isii', $product_id, $ip, $customer_id, $qty);
    return $stmt->execute();
  }

  public function getCartItems($ip) {
    $stmt = $this->conn->prepare(
      'SELECT cart.*, products.product_title, products.product_price, products.product_image
       FROM cart JOIN products ON cart.p_id = products.product_id
       WHERE cart.ip_add = ?'
    );
    $stmt->bind_param('s', $ip);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function getCartCount($ip) {
    $stmt = $this->conn->prepare('SELECT COUNT(*) FROM cart WHERE ip_add = ?');
    $stmt->bind_param('s', $ip);
    $stmt->execute();
    return (int) $stmt->get_result()->fetch_row()[0];
  }

  public function getCartTotal($ip) {
    $total = 0.0;
    foreach ($this->getCartItems($ip) as $item) {
      $total += $item['qty'] * $item['product_price'];
    }
    return $total;
  }

  public function removeFromCart($product_id, $ip) {
    $stmt = $this->conn->prepare('DELETE FROM cart WHERE p_id = ? AND ip_add = ?');
    $stmt->bind_param('is', $product_id, $ip);
    return $stmt->execute();
  }

  public function updateQuantity($product_id, $qty, $ip) {
    $stmt = $this->conn->prepare('UPDATE cart SET qty = ? WHERE p_id = ? AND ip_add = ?');
    $stmt->bind_param('iis', $qty, $product_id, $ip);
    return $stmt->execute();
  }

  // ---- Payment and orders (Task 13) ----

  // Turns the paid-for cart into an order, all in one transaction: if any step fails, nothing is saved.
  // Only call this AFTER Paystack has confirmed the payment. Returns the new order_id, or false.
  // $usd_rate: cedis per 1 US dollar used for this payment (prices are stored in USD).
  public function processPayment($customer_id, $ip, $reference, $amount, $currency = 'GHS', $usd_rate = null) {
    $now = date('Y-m-d H:i:s');
    $today = date('Y-m-d');

    $this->conn->begin_transaction();

    // 1. Invoice number
    $invoice_no = mt_rand(100000, 999999);

    // 2. The order
    $stmt = $this->conn->prepare("INSERT INTO orders (customer_id, invoice_no, order_date, order_status, created_at) VALUES (?, ?, ?, 'paid', ?)");
    $stmt->bind_param('iiss', $customer_id, $invoice_no, $today, $now);
    if (!$stmt->execute()) {
      $this->conn->rollback();
      return false;
    }
    $order_id = $this->conn->insert_id;

    // 3. Copy the cart rows into orderdetails
    $stmt = $this->conn->prepare('INSERT INTO orderdetails (order_id, product_id, qty) SELECT ?, p_id, qty FROM cart WHERE ip_add = ?');
    $stmt->bind_param('is', $order_id, $ip);
    if (!$stmt->execute() || $stmt->affected_rows === 0) { // an empty cart is not an order
      $this->conn->rollback();
      return false;
    }

    // Record the payment. transaction_ref is UNIQUE, so the same Paystack payment cannot be used twice.
    $stmt = $this->conn->prepare('INSERT INTO payment (amt, customer_id, order_id, currency, payment_date, transaction_ref, usd_rate) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('diisssd', $amount, $customer_id, $order_id, $currency, $today, $reference, $usd_rate);
    if (!$stmt->execute()) {
      $this->conn->rollback();
      return false;
    }

    // 4. Empty the cart
    $stmt = $this->conn->prepare('DELETE FROM cart WHERE ip_add = ?');
    $stmt->bind_param('s', $ip);
    if (!$stmt->execute()) {
      $this->conn->rollback();
      return false;
    }

    $this->conn->commit();
    // 5. The new order
    return $order_id;
  }

  // One order with its payment, plus 'items': each product's name, price and quantity. Null if not found.
  public function getOrderById($order_id) {
    $stmt = $this->conn->prepare(
      'SELECT orders.*, payment.amt, payment.currency, payment.transaction_ref, payment.usd_rate,
              customer.customer_name, customer.customer_email, customer.customer_contact, customer.customer_city, customer.customer_country
       FROM orders
       LEFT JOIN payment ON payment.order_id = orders.order_id
       JOIN customer ON customer.customer_id = orders.customer_id
       WHERE orders.order_id = ?'
    );
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    if (!$order) {
      return null;
    }

    $stmt = $this->conn->prepare(
      'SELECT orderdetails.product_id, orderdetails.qty, products.product_title, products.product_price, products.product_image
       FROM orderdetails JOIN products ON orderdetails.product_id = products.product_id
       WHERE orderdetails.order_id = ?'
    );
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $order['items'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    return $order;
  }

  // The order a Paystack reference already paid for (so a refresh or a reused reference is caught), or null.
  public function getOrderIdByReference($reference) {
    $stmt = $this->conn->prepare('SELECT order_id, customer_id FROM payment WHERE transaction_ref = ?');
    $stmt->bind_param('s', $reference);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  // A customer's orders, newest first, with the amount paid.
  public function getOrdersByCustomer($customer_id) {
    $stmt = $this->conn->prepare(
      'SELECT orders.*, payment.amt, payment.currency
       FROM orders LEFT JOIN payment ON payment.order_id = orders.order_id
       WHERE orders.customer_id = ?
       ORDER BY orders.order_id DESC'
    );
    $stmt->bind_param('i', $customer_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }
}
