<?php
// Base class. Every Model class extends this to get $this->conn.
class Database {
  protected $conn;

  public function __construct() {
    require_once __DIR__ . '/db_cred.php';
    mysqli_report(MYSQLI_REPORT_OFF); // so a failed connection shows our message instead of a blank 500 error
    $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($this->conn->connect_error) {
      error_log($this->conn->connect_error);
      die('Connection failed.');
    }
  }
}
