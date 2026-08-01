<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';

requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('許可されていない操作です。');
}

requireValidCsrfToken();

/**
 * POST値を安全に文字列として取得する。
 */
function getImportPostString(
    array $postData,
    string $key
): string {
    $value = $postData[$key] ?? '';

    if (!is_string($value)) {
        return '';
    }

    return trim($value);
}

$title = getImportPostString(
    $_POST,
    'title'
);

$metaDescription = getImportPostString(
    $_POST,
    'meta_description'
);

$canonicalUrl = getImportPostString(
    $_POST,
    'canonical_url'
);

$content = getImportPostString(
    $_POST,
    'content'
);

if ($content === '') {
    $_SESSION['article_import_error'] =
        'インポートする本文を確認できませんでした。';

    header('Location: article-import.php');
    exit;
}

$_SESSION['import_article'] = [
    'title' => $title,
    'slug' => '',
    'category_id' => 0,
    'summary' => $metaDescription,
    'seo_title' => $title,
    'meta_description' => $metaDescription,
    'og_image' => '',
    'canonical_url' => $canonicalUrl,
    'noindex' => 0,
    'content' => $content,
    'status' => 'draft',
];

$_SESSION['article_import_notice'] =
    'HTML記事を読み込みました。'
    . 'スラッグ、カテゴリ、画像、本文を確認して保存してください。';

header('Location: article-create.php?import=1');
exit;