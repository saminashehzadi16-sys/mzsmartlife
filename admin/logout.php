<?php
require_once __DIR__ . '/../includes/bootstrap.php';
logout();
header('Location: ' . url('admin/login.php'));
