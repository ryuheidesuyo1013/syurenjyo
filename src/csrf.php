<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/http.php';

/**
 * CSRFトークンを取得する
 */
function getCsrfToken(): string
{
    startSession();

    if (
        !isset($_SESSION['csrf_token'])
        || !is_string($_SESSION['csrf_token'])
    ) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * 送信されたCSRFトークンを検証する
 */
function validateCsrfToken(?string $token): bool
{
    startSession();

    if (
        $token === null
        || !isset($_SESSION['csrf_token'])
        || !is_string($_SESSION['csrf_token'])
    ) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * CSRFトークンが不正なら403エラーを表示する
 */
function requireValidCsrfToken(): void
{
    $token = $_POST['csrf_token'] ?? null;

    if (!is_string($token) || !validateCsrfToken($token)) {
        abort(
            403,
            'セキュリティ確認に失敗しました。ページを再読み込みして、もう一度お試しください。'
        );
    }
}