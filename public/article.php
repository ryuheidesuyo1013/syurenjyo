<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/http.php';
require_once __DIR__ . '/../src/ArticleRepository.php';

$repository = new ArticleRepository($pdo);

$slug = trim((string) ($_GET['slug'] ?? ''));

if ($slug === '') {
    abort(404, '記事が見つかりません。');
}

$article = $repository->findPublishedBySlug($slug);

if ($article === false) {
    abort(404, '記事が見つかりません。');
}

$articleTitle = (string) $article['title'];

$seoTitle = trim(
    (string) ($article['seo_title'] ?? '')
);

$pageTitle = $seoTitle !== ''
    ? $seoTitle
    : $articleTitle . '｜蹴練場';

$metaDescription = trim(
    (string) ($article['meta_description'] ?? '')
);

if ($metaDescription === '') {
    $metaDescription = trim(
        strip_tags(
            (string) ($article['summary'] ?? '')
        )
    );
}

$ogImage = trim(
    (string) ($article['og_image'] ?? '')
);

$canonicalUrl = trim(
    (string) ($article['canonical_url'] ?? '')
);

if ($canonicalUrl === '') {
    $httpsEnabled = (
        isset($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] !== ''
        && $_SERVER['HTTPS'] !== 'off'
    );

    $scheme = $httpsEnabled
        ? 'https'
        : 'http';

    $host = (string) ($_SERVER['HTTP_HOST'] ?? '');

    if ($host !== '') {
        $canonicalUrl = $scheme
            . '://'
            . $host
            . (string) ($_SERVER['REQUEST_URI'] ?? '');
    }
}

$robots = !empty($article['noindex'])
    ? 'noindex, nofollow'
    : 'index, follow';

$ogType = 'article';
$bodyClass = 'article-page';

require __DIR__ . '/../templates/public-header.php';
?>

    <main class="site-main">
        <p>
            <a href="articles.php">
                記事一覧へ戻る
            </a>
        </p>

        <article class="article">
            <p class="article__category">
                <?= escape((string) $article['category']) ?>
            </p>

            <h1 class="article__title">
                <?= escape($articleTitle) ?>
            </h1>

            <p class="article__published-at">
                公開日：
                <?= escape((string) $article['published_at']) ?>
            </p>

            <?php if (!empty($article['summary'])): ?>
                <p class="article__summary">
                    <?= escape((string) $article['summary']) ?>
                </p>
            <?php endif; ?>

            <div class="article-content">
                <?= (string) $article['content'] ?>
            </div>
        </article>
    </main>

<?php require __DIR__ . '/../templates/public-footer.php'; ?>