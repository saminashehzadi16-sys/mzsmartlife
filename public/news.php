<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$articles = $db->query("SELECT a.title, a.slug, a.featured_image, a.meta_description, c.name category FROM articles a JOIN categories c ON c.id=a.category_id ORDER BY a.published_at DESC LIMIT 30")->fetchAll();
$seo = seoDefaults('Latest News | MZ Smart Life');
include __DIR__ . '/../includes/views/header.php';
?>
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
<?php foreach ($articles as $article): ?>
<article class="bg-white rounded-xl shadow overflow-hidden">
    <?php if ($article['featured_image']): ?><img loading="lazy" src="<?= e(url($article['featured_image'])) ?>" class="w-full h-40 object-cover" alt="<?= e($article['title']) ?>"><?php endif; ?>
    <div class="p-3">
        <div class="text-xs text-slate-500"><?= e($article['category']) ?></div>
        <a href="<?= e(url('news/' . $article['slug'])) ?>" class="font-semibold"><?= e($article['title']) ?></a>
        <p class="text-sm text-slate-600 mt-1"><?= e($article['meta_description'] ?? '') ?></p>
    </div>
</article>
<?php endforeach; ?>
</div>
<?php include __DIR__ . '/../includes/views/footer.php'; ?>
