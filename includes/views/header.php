<?php
$seo = $seo ?? seoDefaults();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <?php renderHead($seo); ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="<?= e(url('public/assets/app.js')) ?>"></script>
    <style>
        .glass { backdrop-filter: blur(8px); background: rgba(255,255,255,.15); }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 pb-20">
<header class="bg-white shadow sticky top-0 z-20">
    <div class="max-w-6xl mx-auto p-4 flex justify-between items-center">
        <a href="<?= e(url()) ?>" class="font-black text-xl">MZ Smart Life</a>
        <nav class="hidden md:flex gap-4 text-sm">
            <a href="<?= e(url('news')) ?>">News</a>
            <a href="<?= e(url('shop')) ?>">Shop</a>
            <a href="<?= e(url('admin')) ?>">Admin</a>
        </nav>
    </div>
</header>
<main class="max-w-6xl mx-auto p-4">
