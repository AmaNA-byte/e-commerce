<?php
// Local XAMPP on your computer, or the school server.
// The server details below are placeholders: put your own username, password and database name here.
if ($_SERVER['SERVER_NAME'] === 'localhost') {
  define('DB_HOST', 'localhost');
  define('DB_USER', 'root');
  define('DB_PASS', '');
  define('DB_NAME', 'shoppn');
} else {
  define('DB_HOST', 'localhost');
  define('DB_USER', 'your_server_username');
  define('DB_PASS', 'your_server_password');
  define('DB_NAME', 'your_server_database');
}
