<?php

declare(strict_types=1);

/**
 * 記事フォームの初期値を返す
 *
 * @return array{
 *     title: string,
 *     slug: string,
 *     category_id: int,
 *     summary: string,
 *     seo_title: string,
 *     meta_description: string,
 *     og_image: string,
 *     canonical_url: string,
 *     noindex: int,
 *     content: string,
 *     status: string
 * }
 */
function getEmptyArticleData(): array
{
    return [
        'title' => '',
        'slug' => '',
        'category_id' => 0,
        'summary' => '',
        'seo_title' => '',
        'meta_description' => '',
        'og_image' => '',
        'canonical_url' => '',
        'noindex' => 0,
        'content' => '',
        'status' => 'draft',
    ];
}

/**
 * POSTされた記事データを取得する
 *
 * @return array{
 *     title: string,
 *     slug: string,
 *     category_id: int,
 *     summary: string,
 *     seo_title: string,
 *     meta_description: string,
 *     og_image: string,
 *     canonical_url: string,
 *     noindex: int,
 *     content: string,
 *     status: string
 * }
 */
function getArticleFormData(array $postData): array
{
    return [
        'title' => trim((string) ($postData['title'] ?? '')),
        'slug' => trim((string) ($postData['slug'] ?? '')),
        'category_id' => (int) ($postData['category_id'] ?? 0),
        'summary' => trim((string) ($postData['summary'] ?? '')),
        'seo_title' => trim((string) ($postData['seo_title'] ?? '')),
        'meta_description' => trim(
            (string) ($postData['meta_description'] ?? '')
        ),
        'og_image' => trim((string) ($postData['og_image'] ?? '')),
        'canonical_url' => trim(
            (string) ($postData['canonical_url'] ?? '')
        ),
        'noindex' => isset($postData['noindex']) ? 1 : 0,
        'content' => trim((string) ($postData['content'] ?? '')),
        'status' => trim((string) ($postData['status'] ?? 'draft')),
    ];
}

/**
 * 記事データを検証する
 *
 * @param array{
 *     title: string,
 *     slug: string,
 *     category_id: int,
 *     summary: string,
 *     seo_title: string,
 *     meta_description: string,
 *     og_image: string,
 *     canonical_url: string,
 *     noindex: int,
 *     content: string,
 *     status: string
 * } $article
 *
 * @return array<int, string>
 */
function validateArticleData(array $article): array
{
    $errors = [];

    if ($article['title'] === '') {
        $errors[] = 'タイトルを入力してください。';
    } elseif (mb_strlen($article['title']) > 255) {
        $errors[] = 'タイトルは255文字以内で入力してください。';
    }

    if ($article['slug'] === '') {
        $errors[] = 'スラッグを入力してください。';
    } elseif (mb_strlen($article['slug']) > 255) {
        $errors[] = 'スラッグは255文字以内で入力してください。';
    } elseif (
        preg_match(
            '/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/',
            $article['slug']
        ) !== 1
    ) {
        $errors[] = 'スラッグは半角英小文字・数字・ハイフンで入力してください。';
    }

    if ($article['category_id'] <= 0) {
        $errors[] = 'カテゴリを選択してください。';
    }

    if (mb_strlen($article['summary']) > 500) {
        $errors[] = '概要は500文字以内で入力してください。';
    }

    if (
        $article['seo_title'] !== ''
        && mb_strlen($article['seo_title']) > 255
    ) {
        $errors[] = 'SEOタイトルは255文字以内で入力してください。';
    }

    if (
        $article['meta_description'] !== ''
        && mb_strlen($article['meta_description']) > 320
    ) {
        $errors[] = 'メタディスクリプションは320文字以内で入力してください。';
    }

    if (
        $article['og_image'] !== ''
        && mb_strlen($article['og_image']) > 500
    ) {
        $errors[] = 'OGP画像URLは500文字以内で入力してください。';
    } elseif (
        $article['og_image'] !== ''
        && filter_var(
            $article['og_image'],
            FILTER_VALIDATE_URL
        ) === false
    ) {
        $errors[] = 'OGP画像URLを正しいURL形式で入力してください。';
    }

    if (
        $article['canonical_url'] !== ''
        && mb_strlen($article['canonical_url']) > 500
    ) {
        $errors[] = 'canonical URLは500文字以内で入力してください。';
    } elseif (
        $article['canonical_url'] !== ''
        && filter_var(
            $article['canonical_url'],
            FILTER_VALIDATE_URL
        ) === false
    ) {
        $errors[] = 'canonical URLを正しいURL形式で入力してください。';
    }

    if (!in_array($article['noindex'], [0, 1], true)) {
        $errors[] = '検索エンジンへの公開設定が不正です。';
    }

    if ($article['content'] === '') {
        $errors[] = '本文を入力してください。';
    }

    if (!in_array($article['status'], ['draft', 'published'], true)) {
        $errors[] = '公開状態が不正です。';
    }

    return $errors;
}