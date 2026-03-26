<?php
require_once __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/xml; charset=utf-8');
$articles = $db->query('SELECT slug, updated_at FROM articles ORDER BY updated_at DESC LIMIT 500')->fetchAll();
$products = $db->query('SELECT slug, updated_at FROM products ORDER BY updated_at DESC LIMIT 500')->fetchAll();
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc><?= e(url()) ?></loc></url>
    <url><loc><?= e(url('news')) ?></loc></url>
    <url><loc><?= e(url('shop')) ?></loc></url>
    <?php foreach ($articles as $a): ?><url><loc><?= e(url('news/'.$a['slug'])) ?></loc><lastmod><?= date('c', strtotime($a['updated_at'])) ?></lastmod></url><?php endforeach; ?>
    <?php foreach ($products as $p): ?><url><loc><?= e(url('product/'.$p['slug'])) ?></loc><lastmod><?= date('c', strtotime($p['updated_at'])) ?></lastmod></url><?php endforeach; ?>
</urlset>
