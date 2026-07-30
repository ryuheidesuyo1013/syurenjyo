<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/http.php';
require_once __DIR__ . '/../src/category-validator.php';
require_once __DIR__ . '/../src/CategoryRepository.php';

requireAdminLogin();

$repository = new CategoryRepository($pdo);

$errors = [];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id <= 0) {
    abort(404, 'カテゴリが見つかりません。');
}

$category = $repository->findById($id);

if ($category === false) {
    abort(404, 'カテゴリが見つかりません。');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken();

    $formData = getCategoryFormData($_POST);

    $errors = validateCategoryData($formData);

    $category = array_merge(
        $category,
        $formData
    );

    if ($errors === []) {
        try {
            $repository->update(
                $id,
                $category['name'],
                $category['slug']
            );

            header('Location: category-list.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = '同じスラッグのカテゴリが既に存在します。';
            } else {
                $errors[] = 'カテゴリの更新に失敗しました。';
            }
        }
    }
}

$formAction = 'category-edit.php?id=' . $id;
$submitLabel = '変更を保存';
?>

<!DOCTYPE html>
<html lang="ja">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>カテゴリ編集｜蹴練場 管理画面</title>

</head>

<body>

<main>

<h1>カテゴリ編集</h1>

<p>

<a href="category-list.php">

カテゴリ管理へ戻る

</a>

</p>

<?php if ($errors !== []): ?>

<div>

<p>入力内容を確認してください。</p>

<ul>

<?php foreach ($errors as $error): ?>

<li><?= escape($error) ?></li>

<?php endforeach; ?>

</ul>

</div>

<?php endif; ?>

<?php require __DIR__ . '/../templates/category-form.php'; ?>

</main>

</body>

</html>