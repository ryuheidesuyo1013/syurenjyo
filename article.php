<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$slug = trim($_GET['slug'] ?? '');

if ($slug === '') {
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
        thumbnail,
        published_at
    FROM articles
    WHERE slug = :slug
      AND status = :status
    LIMIT 1
';

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ':slug' => $slug,
    ':status' => 'published',
]);

$article = $stmt->fetch();

if ($article === false) {
    http_response_code(404);
    exit('記事が見つかりません。');
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
    <title>
        <?= htmlspecialchars(
            $article['title'],
            ENT_QUOTES,
            'UTF-8'
        ) ?>｜蹴練場
    </title>
</head>

<body>
    <main>
        <p>
            <a href="articles.php">記事一覧へ戻る</a>
        </p>

        <article>
            <p>
                <?= htmlspecialchars(
                    $article['category'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

            <h1>
                <?= htmlspecialchars(
                    $article['title'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </h1>

            <p>
                公開日：
                <?= htmlspecialchars(
                    $article['published_at'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

            <?php if (!empty($article['summary'])): ?>
                <p>
                    <?= htmlspecialchars(
                        $article['summary'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </p>
            <?php endif; ?>

            <div class="article-content">
                <?= nl2br(
                    htmlspecialchars(
                        $article['content'],
                        ENT_QUOTES,
                        'UTF-8'
                    )
                ) ?>
            </div>
        </article>
    </main>
</body>
</html>