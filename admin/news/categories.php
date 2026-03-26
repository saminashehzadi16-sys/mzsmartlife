<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireLogin();
requirePermission($db, 'news.categories');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requirePostCsrf();
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $slug = generateUniqueSlug($db, 'categories', slugify($name));
        $stmt = $db->prepare('INSERT INTO categories (name, slug, created_at) VALUES (:name,:slug,:created_at)');
        $stmt->execute([':name'=>$name,':slug'=>$slug,':created_at'=>now()]);
        cacheBust();
    }
}
$categories = $db->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$seo = seoDefaults('Manage Categories');
include __DIR__ . '/../../includes/views/header.php';
?>
<h1 class="text-xl font-bold mb-3">Categories</h1>
<form method="post" class="bg-white p-3 rounded shadow mb-3 flex gap-2">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <input class="border p-2 rounded flex-1" name="name" placeholder="New category">
    <button class="bg-slate-900 text-white rounded px-3">Add</button>
</form>
<?php foreach($categories as $c): ?><div class="bg-white p-3 rounded mb-2 shadow"><?= e($c['name']) ?> <span class="text-slate-500 text-xs">/<?= e($c['slug']) ?></span></div><?php endforeach; ?>
<?php include __DIR__ . '/../../includes/views/footer.php'; ?>
