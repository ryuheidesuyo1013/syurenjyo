<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/ArticleRepository.php';

$repository = new ArticleRepository($pdo);

$articles = $repository->findPublished();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <title>記事一覧｜蹴練場</title>
</head>

<body>
    <main>
        <h1>記事一覧</h1>

        <?php if ($articles === []): ?>
            <p>公開されている記事はありません。</p>
        <?php else: ?>
            <?php foreach ($articles as $article): ?>
                <article>

                    <p><?= escape($article['category']) ?></p>

                    <h2>
                        <a href="article.php?slug=<?= urlencode($article['slug']) ?>">
                            <?= escape($article['title']) ?>
                        </a>
                    </h2>

                    <p><?= escape($article['summary'] ?? '') ?></p>

                    <p>
                        公開日：
                        <?= escape($article['published_at']) ?>
                    </p>

                </article>
            <?php endforeach; ?>
        <?php endif; ?>

    </main>
</body>
</html>