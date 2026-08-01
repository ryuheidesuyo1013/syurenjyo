<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/ArticleRepository.php';

header(
    'Content-Type: application/rss+xml; charset=UTF-8'
);

/**
 * XML用に文字列をエスケープする。
 */
function escapeFeedXml(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_XML1 | ENT_QUOTES,
        'UTF-8'
    );
}

/**
 * RSS用の日付形式へ変換する。
 */
function formatFeedDate(string $dateTime): ?string
{
    $timestamp = strtotime($dateTime);

    if ($timestamp === false) {
        return null;
    }

    return date(
        DATE_RSS,
        $timestamp
    );
}

$repository = new ArticleRepository($pdo);

$articles = $repository->findPublishedForFeed(20);

$siteBaseUrl = 'https://蹴練場.jp';
$feedUrl = $siteBaseUrl . '/public/feed.php';
$articlesUrl = $siteBaseUrl . '/public/articles.php';

$latestArticleDate = null;

if ($articles !== []) {
    $firstArticleDate = trim(
        (string) ($articles[0]['updated_at'] ?? '')
    );

    if ($firstArticleDate !== '') {
        $latestArticleDate = formatFeedDate(
            $firstArticleDate
        );
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "\n";
echo '<rss version="2.0">';
echo "\n";
echo "    <channel>\n";

echo "        <title>";
echo escapeFeedXml('蹴練場 役立つコラム');
echo "</title>\n";

echo "        <link>";
echo escapeFeedXml($articlesUrl);
echo "</link>\n";

echo "        <description>";
echo escapeFeedXml(
    'サッカーの技術、戦術、フィジカル、メンタル、栄養に関する最新記事を配信します。'
);
echo "</description>\n";

echo "        <language>ja</language>\n";

echo "        <atom:link";
echo ' href="' . escapeFeedXml($feedUrl) . '"';
echo ' rel="self"';
echo ' type="application/rss+xml"';
echo ' xmlns:atom="http://www.w3.org/2005/Atom"';
echo " />\n";

if ($latestArticleDate !== null) {
    echo "        <lastBuildDate>";
    echo escapeFeedXml($latestArticleDate);
    echo "</lastBuildDate>\n";
}

foreach ($articles as $article) {
    $title = trim(
        (string) ($article['title'] ?? '')
    );

    $slug = trim(
        (string) ($article['slug'] ?? '')
    );

    if (
        $title === ''
        || $slug === ''
    ) {
        continue;
    }

    $summary = trim(
        strip_tags(
            (string) ($article['summary'] ?? '')
        )
    );

    $articleUrl = $siteBaseUrl
        . '/public/article.php?'
        . http_build_query([
            'slug' => $slug,
        ]);

    $publishedAt = trim(
        (string) ($article['published_at'] ?? '')
    );

    $publishedDate = $publishedAt !== ''
        ? formatFeedDate($publishedAt)
        : null;

    echo "        <item>\n";

    echo "            <title>";
    echo escapeFeedXml($title);
    echo "</title>\n";

    echo "            <link>";
    echo escapeFeedXml($articleUrl);
    echo "</link>\n";

    echo "            <guid isPermaLink=\"true\">";
    echo escapeFeedXml($articleUrl);
    echo "</guid>\n";

    if ($summary !== '') {
        echo "            <description>";
        echo escapeFeedXml($summary);
        echo "</description>\n";
    }

    if ($publishedDate !== null) {
        echo "            <pubDate>";
        echo escapeFeedXml($publishedDate);
        echo "</pubDate>\n";
    }

    echo "        </item>\n";
}

echo "    </channel>\n";
echo '</rss>';
echo "\n";