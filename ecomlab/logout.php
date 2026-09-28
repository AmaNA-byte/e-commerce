<?php
require_once __DIR__ . '/core/core.php';

$_SESSION = [];
session_destroy();
redirect('index.php');
