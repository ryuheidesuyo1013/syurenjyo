<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/http.php';
require_once __DIR__ . '/../src/ArticleRepository.php';

$repository = new ArticleRepository($pdo);

$slug = trim($_GET['slug'] ?? '');

if ($slug === '') {
    abort(404, '記事が見つかりません。');
}

$article = $repository->findPublishedBySlug($slug);

if ($article === false) {
    abort(404, '記事が見つかりません。');
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title><?= escape($article['title']) ?>｜蹴練場</title>
</head>

<body>
    <main>
        <p>
            <a href="articles.php">記事一覧へ戻る</a>
        </p>

        <article>
            <p><?= escape($article['category']) ?></p>

            <h1><?= escape($article['title']) ?></h1>

            <p>
                公開日：
                <?= escape($article['published_at']) ?>
            </p>

            <?php if (!empty($article['summary'])): ?>
                <p><?= escape($article['summary']) ?></p>
            <?php endif; ?>

            <div class="article-content">
                <?= nl2br(escape($article['content'])) ?>
            </div>
        </article>
    </main>
</body>
</html>