<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/ArticleRepository.php';

header('Content-Type: application/xml; charset=UTF-8');

/**
 * XML用に文字列をエスケープする。
 */
function escapeXml(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_XML1 | ENT_QUOTES,
        'UTF-8'
    );
}

/**
 * 日時をサイトマップ用のISO 8601形式へ変換する。
 */
function formatSitemapDate(
    string $dateTime
): ?string {
    $timestamp = strtotime($dateTime);

    if ($timestamp === false) {
        return null;
    }

    return date(
        DATE_ATOM,
        $timestamp
    );
}

$repository = new ArticleRepository($pdo);

$articles = $repository->findPublishedForSitemap();

$siteBaseUrl = 'https://蹴練場.jp';

$urls = [
    [
        'loc' => $siteBaseUrl . '/',
        'lastmod' => null,
        'changefreq' => 'weekly',
        'priority' => '1.0',
    ],
    [
        'loc' => $siteBaseUrl . '/public/articles.php',
        'lastmod' => null,
        'changefreq' => 'daily',
        'priority' => '0.9',
    ],
];

foreach ($articles as $article) {
    $slug = trim(
        (string) ($article['slug'] ?? '')
    );

    if ($slug === '') {
        continue;
    }

    $updatedAt = trim(
        (string) ($article['updated_at'] ?? '')
    );

    $urls[] = [
        'loc' => $siteBaseUrl
            . '/public/article.php?'
            . http_build_query([
                'slug' => $slug,
            ]),
        'lastmod' => $updatedAt !== ''
            ? formatSitemapDate($updatedAt)
            : null,
        'changefreq' => 'monthly',
        'priority' => '0.8',
    ];
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
echo "\n";

foreach ($urls as $url) {
    echo "    <url>\n";

    echo '        <loc>';
    echo escapeXml((string) $url['loc']);
    echo "</loc>\n";

    if (
        isset($url['lastmod'])
        && is_string($url['lastmod'])
        && $url['lastmod'] !== ''
    ) {
        echo '        <lastmod>';
        echo escapeXml($url['lastmod']);
        echo "</lastmod>\n";
    }

    echo '        <changefreq>';
    echo escapeXml((string) $url['changefreq']);
    echo "</changefreq>\n";

    echo '        <priority>';
    echo escapeXml((string) $url['priority']);
    echo "</priority>\n";

    echo "    </url>\n";
}

echo '</urlset>';
echo "\n";