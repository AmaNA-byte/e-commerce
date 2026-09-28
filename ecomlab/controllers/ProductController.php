<?php
require_once __DIR__ . '/../classes/ProductClass.php';

class ProductController {
  private $product;

  public function __construct() {
    $this->product = new ProductClass();
  }

  public function getCategories() {
    return $this->product->getCategories();
  }

  public function getBrands() {
    return $this->product->getBrands();
  }
}
