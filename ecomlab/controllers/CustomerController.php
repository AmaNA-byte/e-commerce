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
}
