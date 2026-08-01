<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/flash.php';
require_once __DIR__ . '/../src/http.php';
require_once __DIR__ . '/../src/ImageRepository.php';

requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    abort(
        405,
        'このページは画像の削除操作以外では利用できません。'
    );
}

requireValidCsrfToken();

$relativePath = $_POST['path'] ?? '';

if (
    !is_string($relativePath)
    || trim($relativePath) === ''
) {
    abort(
        400,
        '削除する画像のパスが正しくありません。'
    );
}

$repository = new ImageRepository(
    dirname(__DIR__) . '/uploads',
    '../uploads'
);

$image = $repository->findByRelativePath(
    $relativePath
);

if ($image === false) {
    abort(
        404,
        '削除する画像が見つかりません。'
    );
}

if (!$repository->delete($relativePath)) {
    setFlashMessage(
        '画像を削除できませんでした。',
        'error'
    );

    header('Location: image-list.php');
    exit;
}

setFlashMessage(
    '画像を削除しました。',
    'success'
);

header('Location: image-list.php');
exit;