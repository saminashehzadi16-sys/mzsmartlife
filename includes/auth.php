<?php
function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function login(string $email, string $password, PDO $db): bool
{
    $stmt = $db->prepare('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role_id' => (int)$user['role_id'],
            'role' => $user['role_name'],
        ];
        return true;
    }

    return false;
}

function logout(): void
{
    unset($_SESSION['user']);
}

function requireLogin(): void
{
    if (!currentUser()) {
        header('Location: ' . url('admin/login.php'));
        exit;
    }
}

function hasPermission(PDO $db, string $permissionKey): bool
{
    $user = currentUser();
    if (!$user) {
        return false;
    }

    $stmt = $db->prepare('SELECT COUNT(*) FROM role_permissions rp JOIN permissions p ON p.id = rp.permission_id WHERE rp.role_id = :role_id AND p.`key` = :key');
    $stmt->execute([
        ':role_id' => $user['role_id'],
        ':key' => $permissionKey,
    ]);

    return (int)$stmt->fetchColumn() > 0;
}

function requirePermission(PDO $db, string $permissionKey): void
{
    if (!hasPermission($db, $permissionKey)) {
        http_response_code(403);
        exit('Forbidden');
    }
}
