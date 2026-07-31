<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/CategoryRepository.php';

requireAdminLogin();

$repository = new CategoryRepository($pdo);

$categories = $repository->findAll();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>カテゴリ管理｜蹴練場 管理画面</title>

    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
        }

        th {
            background: #f5f5f5;
        }

        .actions {
            white-space: nowrap;
        }
    </style>
</head>

<body>

<main>

<h1>カテゴリ管理</h1>

<p>
    <a href="index.php">ダッシュボードへ戻る</a>
</p>

<p>
    <a href="article-list.php">記事管理へ戻る</a>
</p>

<p>
    <a href="category-create.php">
        新しいカテゴリを追加
    </a>
</p>

<table>

<thead>

<tr>
    <th>ID</th>
    <th>カテゴリ名</th>
    <th>スラッグ</th>
    <th>記事数</th>
    <th>作成日</th>
    <th>操作</th>
</tr>

</thead>

<tbody>

<?php foreach ($categories as $category): ?>

<tr>

<td>
    <?= escape((string) $category['id']) ?>
</td>

<td>
    <?= escape($category['name']) ?>
</td>

<td>
    <?= escape($category['slug']) ?>
</td>

<td>
    <?= escape((string) $category['article_count']) ?>
</td>

<td>
    <?= escape($category['created_at']) ?>
</td>

<td class="actions">

<a href="category-edit.php?id=<?= escape((string) $category['id']) ?>">
編集
</a>

|

<a href="category-delete.php?id=<?= escape((string) $category['id']) ?>">
削除
</a>

</td>

</tr>

<?php endforeach; ?>

<?php if ($categories === []): ?>

<tr>

<td colspan="6">

カテゴリはまだありません。

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</main>

</body>
</html>