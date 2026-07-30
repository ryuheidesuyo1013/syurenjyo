<?php

declare(strict_types=1);

/**
 * カテゴリフォームの初期値を返す
 *
 * @return array{
 *     name: string,
 *     slug: string
 * }
 */
function getEmptyCategoryData(): array
{
    return [
        'name' => '',
        'slug' => '',
    ];
}

/**
 * POSTされたカテゴリデータを取得する
 *
 * @param array<string, mixed> $postData
 *
 * @return array{
 *     name: string,
 *     slug: string
 * }
 */
function getCategoryFormData(array $postData): array
{
    return [
        'name' => trim((string) ($postData['name'] ?? '')),
        'slug' => trim((string) ($postData['slug'] ?? '')),
    ];
}

/**
 * カテゴリデータを検証する
 *
 * @param array{
 *     name: string,
 *     slug: string
 * } $category
 *
 * @return array<int, string>
 */
function validateCategoryData(array $category): array
{
    $errors = [];

    if ($category['name'] === '') {
        $errors[] = 'カテゴリ名を入力してください。';
    } elseif (mb_strlen($category['name']) > 100) {
        $errors[] = 'カテゴリ名は100文字以内で入力してください。';
    }

    if ($category['slug'] === '') {
        $errors[] = 'スラッグを入力してください。';
    } elseif (mb_strlen($category['slug']) > 100) {
        $errors[] = 'スラッグは100文字以内で入力してください。';
    } elseif (
        preg_match(
            '/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/',
            $category['slug']
        ) !== 1
    ) {
        $errors[] = 'スラッグは半角英小文字・数字・ハイフンで入力してください。';
    }

    return $errors;
}