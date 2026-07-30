<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/ArticleRepository.php';

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

$repository = new ArticleRepository($pdo);

try {
    $repository->delete($id);

    header('Location: article-list.php');
    exit;
} catch (PDOException $e) {
    http_response_code(500);
    exit('記事の削除に失敗しました。');
}