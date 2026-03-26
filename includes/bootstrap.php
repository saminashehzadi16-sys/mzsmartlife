<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/cache.php';
require_once __DIR__ . '/auth.php';

$db = db();
