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
        preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $article['slug']) !== 1
    ) {
        $errors[] = 'スラッグは半角英小文字・数字・ハイフンで入力してください。';
    }

    if ($article['category_id'] <= 0) {
        $errors[] = 'カテゴリを選択してください。';
    }

    if (mb_strlen($article['summary']) > 500) {
        $errors[] = '概要は500文字以内で入力してください。';
    }

    if ($article['content'] === '') {
        $errors[] = '本文を入力してください。';
    }

    if (!in_array($article['status'], ['draft', 'published'], true)) {
        $errors[] = '公開状態が不正です。';
    }

    return $errors;
}