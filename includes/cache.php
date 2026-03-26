<?php
function cachePath(string $key): string
{
    return __DIR__ . '/../cache/' . md5($key) . '.html';
}

function cacheGet(string $key, int $ttl = 300): ?string
{
    $path = cachePath($key);
    if (!file_exists($path)) {
        return null;
    }

    if ((time() - filemtime($path)) > $ttl) {
        @unlink($path);
        return null;
    }

    return file_get_contents($path) ?: null;
}

function cacheSet(string $key, string $content): void
{
    $path = cachePath($key);
    file_put_contents($path, $content, LOCK_EX);
}

function cacheBust(array $patterns = ['home', 'article-', 'category-', 'shop', 'product-']): void
{
    foreach (glob(__DIR__ . '/../cache/*.html') as $file) {
        $name = basename($file);
        foreach ($patterns as $pattern) {
            if (str_contains($name, md5($pattern)) || str_contains($name, $pattern)) {
                @unlink($file);
                break;
            }
        }
        @unlink($file);
    }
}
