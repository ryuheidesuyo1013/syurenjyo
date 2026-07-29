<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';

requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('許可されていない操作です。');
}

requireValidCsrfToken();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id <= 0) {
    http_response_code(400);
    exit('不正なリクエストです。');
}

$sql = '
    DELETE FROM articles
    WHERE id = :id
';

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id' => $id,
]);

header('Location: article-list.php');
exit;