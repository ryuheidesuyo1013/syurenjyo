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

$breadcrumbs = [
    [
        'label' => 'ホーム',
        'url' => 'https://蹴練場.jp/',
    ],
    [
        'label' => '役立つコラム',
        'url' => 'articles.php',
    ],
    [
        'label' => (string) $article['category'],
        'url' => 'articles.php?' . http_build_query([
            'category' => (string) $article['category'],
        ]),
    ],
    [
        'label' => $articleTitle,
        'url' => '',
    ],
];

$breadcrumbStructuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [],
];

foreach ($breadcrumbs as $index => $breadcrumb) {
    $breadcrumbUrl = (string) ($breadcrumb['url'] ?? '');

    if ($breadcrumbUrl !== '') {
        if (
            str_starts_with($breadcrumbUrl, 'http://')
            || str_starts_with($breadcrumbUrl, 'https://')
        ) {
            $breadcrumbAbsoluteUrl = $breadcrumbUrl;
        } elseif ($canonicalUrl !== '') {
            $breadcrumbAbsoluteUrl = rtrim(
                dirname($canonicalUrl),
                '/'
            ) . '/' . ltrim($breadcrumbUrl, '/');
        } else {
            $breadcrumbAbsoluteUrl = $breadcrumbUrl;
        }
    } else {
        $breadcrumbAbsoluteUrl = $canonicalUrl;
    }

    $breadcrumbStructuredData['itemListElement'][] = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => (string) $breadcrumb['label'],
        'item' => $breadcrumbAbsoluteUrl,
    ];
}

$structuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $articleTitle,
    'description' => $metaDescription,
    'datePublished' => date(
        DATE_ATOM,
        strtotime((string) $article['published_at'])
    ),
    'dateModified' => date(
        DATE_ATOM,
        strtotime((string) $article['updated_at'])
    ),
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => $canonicalUrl,
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => '蹴練場',
    ],
];

if ($ogImage !== '') {
    $structuredData['image'] = [
        $ogImage,
    ];
}

require __DIR__ . '/../templates/public-header.php';
?>

    <main class="site-main">
        <div class="site-container site-container--article">
            <?php require __DIR__ . '/../templates/breadcrumb.php'; ?>

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
        </div>
    </main>

    <script type="application/ld+json">
<?= json_encode(
    $structuredData,
    JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_HEX_TAG
    | JSON_HEX_AMP
    | JSON_HEX_APOS
    | JSON_HEX_QUOT
    | JSON_PRETTY_PRINT
) ?>
    </script>

    <script type="application/ld+json">
<?= json_encode(
    $breadcrumbStructuredData,
    JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_HEX_TAG
    | JSON_HEX_AMP
    | JSON_HEX_APOS
    | JSON_HEX_QUOT
    | JSON_PRETTY_PRINT
) ?>
    </script>

<?php require __DIR__ . '/../templates/public-footer.php'; ?>