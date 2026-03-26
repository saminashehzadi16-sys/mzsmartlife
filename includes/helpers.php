<?php
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? '';
    return trim($value, '-') ?: 'item-' . time();
}

function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function generateUniqueSlug(PDO $db, string $table, string $baseSlug, ?int $ignoreId = null): string
{
    $slug = $baseSlug;
    $counter = 1;
    while (true) {
        $sql = "SELECT id FROM {$table} WHERE slug = :slug" . ($ignoreId ? ' AND id != :id' : '');
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':slug', $slug);
        if ($ignoreId) {
            $stmt->bindValue(':id', $ignoreId, PDO::PARAM_INT);
        }
        $stmt->execute();
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }
}

function seoDefaults(string $title = 'MZ Smart Life'): array
{
    return [
        'title' => $title,
        'description' => 'Latest news and smart shopping deals at MZ Smart Life.',
        'keywords' => 'news, ecommerce, smart life, deals, sports, showbiz',
    ];
}

function renderHead(array $seo): void
{
    echo '<title>' . e($seo['title']) . "</title>\n";
    echo '<meta name="description" content="' . e($seo['description']) . '">' . "\n";
    echo '<meta name="keywords" content="' . e($seo['keywords']) . '">' . "\n";
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";
    echo '<link rel="canonical" href="' . e((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '')) . '">' . "\n";
}
