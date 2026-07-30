<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/article-validator.php';
require_once __DIR__ . '/../src/ArticleRepository.php';
require_once __DIR__ . '/../src/CategoryRepository.php';

requireAdminLogin();

$repository = new ArticleRepository($pdo);
$categoryRepository = new CategoryRepository($pdo);

$errors = [];
$article = getEmptyArticleData();
$categories = $categoryRepository->findAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken();

    $article = getArticleFormData($_POST);
    $errors = validateArticleData($article);

    if (
        $article['category_id'] > 0
        && !$categoryRepository->existsById($article['category_id'])
    ) {
        $errors[] = '選択されたカテゴリが存在しません。';
    }

    if ($errors === []) {
        $publishedAt = $article['status'] === 'published'
            ? date('Y-m-d H:i:s')
            : null;

        try {
            $repository->create(
                $article,
                $publishedAt
            );

            header('Location: article-list.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = '同じスラッグの記事が既に存在するか、カテゴリが不正です。';
            } else {
                $errors[] = '記事の作成に失敗しました。';
            }
        }
    }
}

$formAction = 'article-create.php';
$submitLabel = '記事を作成';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>記事作成｜蹴練場 管理画面</title>
</head>

<body>
    <main>
        <h1>記事作成</h1>

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