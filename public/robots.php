<?php
require_once __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: text/plain; charset=utf-8');
echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /admin/\n";
echo "Sitemap: " . url('sitemap.xml') . "\n";
