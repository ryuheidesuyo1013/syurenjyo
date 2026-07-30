<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

/**
 * HTTPエラーページを表示して処理を終了する
 */
function abort(
    int $statusCode,
    string $message
): never {
    http_response_code($statusCode);

    $errorTitle = match ($statusCode) {
        400 => '不正なリクエスト',
        401 => '認証が必要です',
        403 => 'アクセスできません',
        404 => 'ページが見つかりません',
        405 => '許可されていない操作です',
        500 => 'サーバーエラー',
        default => 'エラーが発生しました',
    };

    require __DIR__ . '/../templates/error.php';

    exit;
}