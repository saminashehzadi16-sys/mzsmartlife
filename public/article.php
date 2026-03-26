<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$slug = $_GET['slug'] ?? '';
$cacheKey = 'article-' . $slug;
if ($cached = cacheGet($cacheKey, 300)) { echo $cached; exit; }
$stmt = $db->prepare('SELECT a.*, c.name category FROM articles a JOIN categories c ON c.id=a.category_id WHERE a.slug=:slug LIMIT 1');
$stmt->execute([':slug' => $slug]);
$article = $stmt->fetch();
if (!$article) { http_response_code(404); exit('Article not found'); }
$seo = [
    'title' => $article['meta_title'] ?: $article['title'],
    'description' => $article['meta_description'] ?: substr(strip_tags($article['content']), 0, 150),
    'keywords' => $article['meta_keywords'] ?: 'news, ' . $article['category'],
];
ob_start();
include __DIR__ . '/../includes/views/header.php';
?>
<article class="bg-white rounded-xl shadow p-5">
    <div class="text-xs uppercase text-slate-500"><?= e($article['category']) ?></div>
    <h1 class="text-3xl font-black mb-3"><?= e($article['title']) ?></h1>
    <?php if ($article['featured_image']): ?><img loading="lazy" class="rounded w-full max-h-[420px] object-cover mb-4" src="<?= e(url($article['featured_image'])) ?>" alt="<?= e($article['title']) ?>"><?php endif; ?>

    <div class="my-5 text-center">
        <div class="text-xs text-slate-500 mb-1">In-Article Ad Placeholder</div>
        <div class="h-24 bg-slate-100 rounded"></div>
    </div>

    <div class="prose max-w-none"><?= $article['content'] ?></div>
</article>
<?php
include __DIR__ . '/../includes/views/footer.php';
$html = ob_get_clean();
cacheSet($cacheKey, $html);
echo $html;
