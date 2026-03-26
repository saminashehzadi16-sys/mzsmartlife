<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
requireLogin();
requirePermission($db, 'news.manage');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requirePostCsrf();
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $categoryId = (int)($_POST['category_id'] ?? 0);
    if ($title && $content && $categoryId) {
        $slug = generateUniqueSlug($db, 'articles', slugify($title));
        $imagePath = null;
        if (!empty($_FILES['featured_image']['name'])) {
            $imagePath = validateUpload($_FILES['featured_image']);
        }
        $stmt = $db->prepare('INSERT INTO articles (user_id, category_id, title, slug, content, featured_image, meta_title, meta_keywords, meta_description, is_breaking, is_featured, published_at, created_at, updated_at) VALUES (:user_id,:category_id,:title,:slug,:content,:featured_image,:meta_title,:meta_keywords,:meta_description,:is_breaking,:is_featured,:published_at,:created_at,:updated_at)');
        $stmt->execute([
            ':user_id' => currentUser()['id'],
            ':category_id' => $categoryId,
            ':title' => $title,
            ':slug' => $slug,
            ':content' => $content,
            ':featured_image' => $imagePath,
            ':meta_title' => trim($_POST['meta_title'] ?? ''),
            ':meta_keywords' => trim($_POST['meta_keywords'] ?? ''),
            ':meta_description' => trim($_POST['meta_description'] ?? ''),
            ':is_breaking' => isset($_POST['is_breaking']) ? 1 : 0,
            ':is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            ':published_at' => now(),
            ':created_at' => now(),
            ':updated_at' => now(),
        ]);
        cacheBust();
    }
}

$categories = $db->query('SELECT id,name FROM categories ORDER BY name')->fetchAll();
$articles = $db->query('SELECT a.id,a.title,a.slug,a.published_at,c.name category FROM articles a JOIN categories c ON c.id=a.category_id ORDER BY a.published_at DESC LIMIT 50')->fetchAll();
$seo = seoDefaults('Manage Articles');
include __DIR__ . '/../../includes/views/header.php';
?>
<h1 class="text-xl font-bold mb-3">News Dashboard</h1>
<form method="post" enctype="multipart/form-data" class="bg-white p-4 rounded shadow grid md:grid-cols-2 gap-2 mb-4">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <input class="border p-2 rounded md:col-span-2" name="title" placeholder="Article title" required>
    <select class="border p-2 rounded" name="category_id" required>
        <option value="">Select category</option>
        <?php foreach($categories as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
    </select>
    <input class="border p-2 rounded" type="file" name="featured_image" accept="image/*">
    <input class="border p-2 rounded md:col-span-2" name="meta_title" placeholder="Meta title">
    <input class="border p-2 rounded md:col-span-2" name="meta_keywords" placeholder="Meta keywords">
    <textarea class="border p-2 rounded md:col-span-2" name="meta_description" placeholder="Meta description"></textarea>
    <textarea class="border p-2 rounded md:col-span-2 min-h-[160px]" name="content" placeholder="Article content (rich HTML allowed)" required></textarea>
    <label class="text-sm"><input type="checkbox" name="is_breaking"> Breaking</label>
    <label class="text-sm"><input type="checkbox" name="is_featured"> Featured</label>
    <button class="bg-slate-900 text-white rounded p-2 md:col-span-2">Publish Article</button>
</form>
<div class="space-y-2">
<?php foreach($articles as $a): ?><div class="bg-white p-3 rounded shadow"><strong><?= e($a['title']) ?></strong> <span class="text-xs text-slate-500">/<?= e($a['slug']) ?> · <?= e($a['category']) ?></span></div><?php endforeach; ?>
</div>
<?php include __DIR__ . '/../../includes/views/footer.php'; ?>
