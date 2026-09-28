<?php
// Local XAMPP on your computer, or the school server.
if ($_SERVER['SERVER_NAME'] === 'localhost') {
  define('DB_HOST', 'localhost');
  define('DB_USER', 'root');
  define('DB_PASS', '');
  define('DB_NAME', 'shoppn');
} else {
  define('DB_HOST', 'localhost');
  define('DB_USER', 'ama.amoateng');
  define('DB_PASS', '90642027');
  define('DB_NAME', 'ecommerce_2026A_ama_amoateng');
}
