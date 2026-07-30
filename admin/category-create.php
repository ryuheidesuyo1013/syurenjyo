<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/category-validator.php';
require_once __DIR__ . '/../src/CategoryRepository.php';

requireAdminLogin();

$repository = new CategoryRepository($pdo);

$errors = [];
$category = getEmptyCategoryData();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken();

    $category = getCategoryFormData($_POST);
    $errors = validateCategoryData($category);

    if ($errors === []) {
        try {
            $repository->create(
                $category['name'],
                $category['slug']
            );

            header('Location: category-list.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = '同じスラッグのカテゴリが既に存在します。';
            } else {
                $errors[] = 'カテゴリの作成に失敗しました。';
            }
        }
    }
}

$formAction = 'category-create.php';
$submitLabel = 'カテゴリを作成';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>カテゴリ作成｜蹴練場 管理画面</title>
</head>

<body>
    <main>
        <h1>カテゴリ作成</h1>

        <p>
            <a href="category-list.php">カテゴリ管理へ戻る</a>
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