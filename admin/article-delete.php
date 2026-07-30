<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/http.php';
require_once __DIR__ . '/../src/ArticleRepository.php';

requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    abort(405, 'このページは、記事の削除操作以外では利用できません。');
}

requireValidCsrfToken();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id <= 0) {
    abort(400, '記事IDが正しくありません。');
}

$repository = new ArticleRepository($pdo);

$article = $repository->findById($id);

if ($article === false) {
    abort(404, '削除する記事が見つかりません。');
}

try {
    $repository->delete($id);

    header('Location: article-list.php');
    exit;
} catch (PDOException $e) {
    abort(500, '記事の削除に失敗しました。時間を置いてもう一度お試しください。');
}