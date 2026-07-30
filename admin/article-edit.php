<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/article-validator.php';

requireAdminLogin();

$errors = [];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id <= 0) {
    http_response_code(404);
    exit('記事が見つかりません。');
}

$sql = '
    SELECT
        id,
        title,
        slug,
        category,
        summary,
        content,
        status,
        published_at
    FROM articles
    WHERE id = :id
    LIMIT 1
';

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':id' => $id,
]);

$article = $stmt->fetch();

if ($article === false) {
    http_response_code(404);
    exit('記事が見つかりません。');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken();

    $formData = getArticleFormData($_POST);
    $errors = validateArticleData($formData);

    $article = array_merge($article, $formData);

    if ($errors === []) {
        if ($article['status'] === 'published') {
            $publishedAt = $article['published_at'] !== null
                ? $article['published_at']
                : date('Y-m-d H:i:s');
        } else {
            $publishedAt = null;
        }

        $updateSql = '
            UPDATE articles
            SET
                title = :title,
                slug = :slug,
                category = :category,
                summary = :summary,
                content = :content,
                status = :status,
                published_at = :published_at
            WHERE id = :id
        ';

        try {
            $updateStmt = $pdo->prepare($updateSql);

            $updateStmt->execute([
                ':title' => $article['title'],
                ':slug' => $article['slug'],
                ':category' => $article['category'],
                ':summary' => $article['summary'],
                ':content' => $article['content'],
                ':status' => $article['status'],
                ':published_at' => $publishedAt,
                ':id' => $id,
            ]);

            header('Location: article-list.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = '同じスラッグの記事が既に存在します。';
            } else {
                $errors[] = '記事の更新に失敗しました。';
            }
        }
    }
}

$formAction = 'article-edit.php?id=' . $id;
$submitLabel = '変更を保存';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>記事編集｜蹴練場 管理画面</title>
</head>

<body>
    <main>
        <h1>記事編集</h1>

        <p>
            <a href="article-list.php">記事管理へ戻る</a>
        </p>

        <?php if ($errors !== []): ?>
            <div>
                <p>入力内容を確認してください。</p>

                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= escape($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php require __DIR__ . '/../templates/article-form.php'; ?>
    </main>
</body>
</html>