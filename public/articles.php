<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';

$sql = '
    SELECT
        id,
        title,
        slug,
        category,
        summary,
        published_at
    FROM articles
    WHERE status = :status
    ORDER BY published_at DESC
';

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':status' => 'published',
]);

$articles = $stmt->fetchAll();
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
                    <p>
                        <?= htmlspecialchars(
                            $article['category'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>

                    <h2>
                        <a href="article.php?slug=<?= urlencode($article['slug']) ?>">
                            <?= htmlspecialchars(
                                $article['title'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </a>
                    </h2>

                    <p>
                        <?= htmlspecialchars(
                            $article['summary'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>

                    <p>
                        公開日：
                        <?= htmlspecialchars(
                            $article['published_at'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </p>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</body>
</html>