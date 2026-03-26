<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$slug = $_GET['slug'] ?? '';
$cacheKey = 'category-' . $slug;
if ($cached = cacheGet($cacheKey, 180)) { echo $cached; exit; }
$stmt = $db->prepare('SELECT * FROM categories WHERE slug=:slug LIMIT 1');
$stmt->execute([':slug'=>$slug]);
$category = $stmt->fetch();
if (!$category) { http_response_code(404); exit('Category not found'); }
$stmt = $db->prepare('SELECT title, slug, meta_description FROM articles WHERE category_id=:category ORDER BY published_at DESC');
$stmt->execute([':category'=>$category['id']]);
$articles = $stmt->fetchAll();
$seo = seoDefaults($category['name'] . ' News | MZ Smart Life');
ob_start(); include __DIR__ . '/../includes/views/header.php';
?>
<h1 class="text-2xl font-bold mb-3"><?= e($category['name']) ?></h1>
<?php foreach ($articles as $a): ?><div class="bg-white p-4 rounded mb-2"><a href="<?= e(url('news/'.$a['slug'])) ?>" class="font-semibold"><?= e($a['title']) ?></a><p class="text-sm"><?= e($a['meta_description'] ?? '') ?></p></div><?php endforeach; ?>
<?php include __DIR__ . '/../includes/views/footer.php'; $html=ob_get_clean(); cacheSet($cacheKey,$html); echo $html; ?>
