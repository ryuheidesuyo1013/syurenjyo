<?php

declare(strict_types=1);

function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'use_strict_mode' => true,
        ]);
    }
}

function isAdminLoggedIn(): bool
{
    startSession();

    return isset($_SESSION['admin_user_id']);
}

function requireAdminLogin(): void
{
    if (isAdminLoggedIn()) {
        return;
    }

    header('Location: login.php');
    exit;
}

function loginAdmin(int $userId, string $username): void
{
    startSession();

    session_regenerate_id(true);

    $_SESSION['admin_user_id'] = $userId;
    $_SESSION['admin_username'] = $username;
}

function logoutAdmin(): void
{
    startSession();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $parameters = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $parameters['path'],
            $parameters['domain'],
            $parameters['secure'],
            $parameters['httponly']
        );
    }

    session_destroy();
}