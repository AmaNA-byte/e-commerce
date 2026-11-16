<?php
require_once __DIR__ . '/../core/db_class.php';

class CustomerClass extends Database {

  public function emailExists($email) {
    $stmt = $this->conn->prepare('SELECT customer_email FROM customer WHERE customer_email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
  }

  // Returns the new customer's id, or false on failure.
  public function addCustomer($name, $email, $pass, $country, $city, $contact, $image = null) {
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $stmt = $this->conn->prepare(
      'INSERT INTO customer (customer_name, customer_email, customer_pass, customer_country, customer_city, customer_contact, customer_image)
       VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('sssssss', $name, $email, $hash, $country, $city, $contact, $image);
    return $stmt->execute() ? $this->conn->insert_id : false;
  }

  // Returns one customer row, or false if not found.
  public function getCustomerByEmail($email) {
    $stmt = $this->conn->prepare('SELECT * FROM customer WHERE customer_email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ?: false;
  }

  public function updatePassword($id, $pass) {
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $stmt = $this->conn->prepare('UPDATE customer SET customer_pass = ? WHERE customer_id = ?');
    $stmt->bind_param('si', $hash, $id);
    return $stmt->execute();
  }

  public function getAllCustomers() {
    return $this->conn->query(
      'SELECT customer_id, customer_name, customer_email, user_role FROM customer ORDER BY customer_name ASC'
    )->fetch_all(MYSQLI_ASSOC);
  }

  // Returns one customer row, or false if not found.
  public function getCustomerById($id) {
    $stmt = $this->conn->prepare('SELECT * FROM customer WHERE customer_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ?: false;
  }

  // $role: 1 = admin, 2 = customer
  public function updateRole($id, $role) {
    $stmt = $this->conn->prepare('UPDATE customer SET user_role = ? WHERE customer_id = ?');
    $stmt->bind_param('ii', $role, $id);
    return $stmt->execute();
  }

  // ---- 6-digit email codes: 'verify' (confirm email after sign-up) and 'reset' (forgot password) ----

  const CODE_MAX_ATTEMPTS = 5;

  private function codeHash($customer_id, $purpose, $code) {
    return hash('sha256', $customer_id . ':' . $purpose . ':' . $code);
  }

  // Makes a new 6-digit code valid for $minutes and returns it. Only its hash is stored.
  // Any older code for the same purpose stops working.
  public function createCode($customer_id, $purpose, $minutes = 15) {
    $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $hash = $this->codeHash($customer_id, $purpose, $code);
    $now = date('Y-m-d H:i:s');
    $expires = date('Y-m-d H:i:s', time() + $minutes * 60);

    $this->deleteCodes($customer_id, $purpose);
    $stmt = $this->conn->prepare('INSERT INTO email_codes (customer_id, purpose, code_hash, expires_at, created_at) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('issss', $customer_id, $purpose, $hash, $expires, $now);
    return $stmt->execute() ? $code : false;
  }

  // When the last code of this kind was sent (to limit how often codes can be emailed), or null.
  public function lastCodeTime($customer_id, $purpose) {
    $stmt = $this->conn->prepare('SELECT MAX(created_at) FROM email_codes WHERE customer_id = ? AND purpose = ?');
    $stmt->bind_param('is', $customer_id, $purpose);
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
  }

  // Checks a typed code. Returns 'ok', 'wrong' (try again) or 'none' (expired, used up, or too many wrong tries).
  public function checkCode($customer_id, $purpose, $code) {
    $now = date('Y-m-d H:i:s');
    $stmt = $this->conn->prepare('SELECT id, code_hash, attempts FROM email_codes WHERE customer_id = ? AND purpose = ? AND expires_at > ?');
    $stmt->bind_param('iss', $customer_id, $purpose, $now);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row || $row['attempts'] >= self::CODE_MAX_ATTEMPTS) {
      return 'none';
    }
    if (hash_equals($row['code_hash'], $this->codeHash($customer_id, $purpose, (string) $code))) {
      return 'ok';
    }
    $stmt = $this->conn->prepare('UPDATE email_codes SET attempts = attempts + 1 WHERE id = ?');
    $stmt->bind_param('i', $row['id']);
    $stmt->execute();
    return $row['attempts'] + 1 >= self::CODE_MAX_ATTEMPTS ? 'none' : 'wrong';
  }

  public function deleteCodes($customer_id, $purpose) {
    $stmt = $this->conn->prepare('DELETE FROM email_codes WHERE customer_id = ? AND purpose = ?');
    $stmt->bind_param('is', $customer_id, $purpose);
    return $stmt->execute();
  }

  public function markVerified($customer_id) {
    $stmt = $this->conn->prepare('UPDATE customer SET is_verified = 1 WHERE customer_id = ?');
    $stmt->bind_param('i', $customer_id);
    return $stmt->execute();
  }

  // Returns the customer row if the password is correct, otherwise false.
  public function login($email, $pass) {
    $row = $this->getCustomerByEmail($email);
    if ($row && password_verify($pass, $row['customer_pass'])) {
      return $row;
    }
    return false;
  }
}
