<?php

declare(strict_types=1);

/**
 * セッションが開始されていなければ開始する。
 */
function startFlashSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    if (function_exists('startSession')) {
        startSession();
        return;
    }

    session_start();
}

/**
 * 次の画面で表示するメッセージを保存する。
 */
function setFlashMessage(
    string $message,
    string $type = 'success'
): void {
    startFlashSession();

    $allowedTypes = [
        'success',
        'error',
        'warning',
        'info',
    ];

    if (!in_array($type, $allowedTypes, true)) {
        $type = 'info';
    }

    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type,
    ];
}

/**
 * 保存されたフラッシュメッセージを取得して削除する。
 *
 * @return array{message: string, type: string}|null
 */
function getFlashMessage(): ?array
{
    startFlashSession();

    $flashMessage = $_SESSION['flash_message'] ?? null;

    unset($_SESSION['flash_message']);

    if (!is_array($flashMessage)) {
        return null;
    }

    $message = $flashMessage['message'] ?? null;
    $type = $flashMessage['type'] ?? null;

    if (!is_string($message) || !is_string($type)) {
        return null;
    }

    return [
        'message' => $message,
        'type' => $type,
    ];
}