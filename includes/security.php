<?php
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(?string $token): bool
{
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

function requirePostCsrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verifyCsrf($_POST['csrf_token'] ?? null)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }
}

function validateUpload(array $file, array $allowed = ['image/jpeg', 'image/png', 'image/webp']): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('File too large.');
    }

    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed, true)) {
        throw new RuntimeException('Invalid file type.');
    }

    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => throw new RuntimeException('Unsupported image format.'),
    };

    $name = bin2hex(random_bytes(12)) . '.' . $ext;
    $targetDir = __DIR__ . '/../uploads/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $target = $targetDir . $name;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Upload failed.');
    }

    return 'uploads/' . $name;
}
