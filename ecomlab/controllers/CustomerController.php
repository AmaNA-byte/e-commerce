<?php
require_once __DIR__ . '/../classes/CustomerClass.php';

class CustomerController {
  private $customer;

  public function __construct() {
    $this->customer = new CustomerClass();
  }

  public function register($data) {
    if ($this->customer->emailExists($data['email'])) {
      return ['success' => false, 'error' => 'Email already registered'];
    }

    $id = $this->customer->addCustomer(
      $data['name'], $data['email'], $data['pass'],
      $data['country'], $data['city'], $data['contact'], $data['image']
    );

    if (!$id) {
      return ['success' => false, 'error' => 'Registration failed. Please try again.'];
    }
    return ['success' => true, 'customer_id' => $id];
  }

  public function login($email, $pass) {
    $customer = $this->customer->login($email, $pass);
    if (!$customer) {
      return ['error' => 'Incorrect email or password.'];
    }
    return $customer;
  }

  public function changePassword($id, $email, $current, $new) {
    if (!$this->customer->login($email, $current)) {
      return ['success' => false, 'error' => 'Your current password is incorrect.'];
    }
    if (!$this->customer->updatePassword($id, $new)) {
      return ['success' => false, 'error' => 'Could not change the password. Please try again.'];
    }
    return ['success' => true];
  }

  public function getAllCustomers() {
    return $this->customer->getAllCustomers();
  }

  public function getCustomerById($id) {
    return $this->customer->getCustomerById($id);
  }

  public function updateRole($id, $role) {
    return $this->customer->updateRole($id, $role);
  }

  public function getCustomerByEmail($email) {
    return $this->customer->getCustomerByEmail($email);
  }

  public function createCode($customer_id, $purpose, $minutes = 15) {
    return $this->customer->createCode($customer_id, $purpose, $minutes);
  }

  public function lastCodeTime($customer_id, $purpose) {
    return $this->customer->lastCodeTime($customer_id, $purpose);
  }

  public function checkCode($customer_id, $purpose, $code) {
    return $this->customer->checkCode($customer_id, $purpose, $code);
  }

  public function deleteCodes($customer_id, $purpose) {
    return $this->customer->deleteCodes($customer_id, $purpose);
  }

  public function markVerified($customer_id) {
    return $this->customer->markVerified($customer_id);
  }

  // Forgot password: new password, code used up, and the email counts as verified (they proved they own it).
  public function resetPassword($customer_id, $pass) {
    if (!$this->customer->updatePassword($customer_id, $pass)) {
      return false;
    }
    $this->customer->deleteCodes($customer_id, 'reset');
    $this->customer->markVerified($customer_id);
    return true;
  }
}
