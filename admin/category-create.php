<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/flash.php';
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

            setFlashMessage(
                'カテゴリを作成しました。',
                'success'
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
$pageTitle = 'カテゴリ作成';

require __DIR__ . '/../templates/admin-header.php';
?>

<div class="page-header">
    <div class="page-header__content">
        <h1 class="page-title">
            カテゴリ作成
        </h1>

        <p class="page-description">
            記事を分類する新しいカテゴリを登録します。
        </p>
    </div>

    <div class="page-actions">
        <a
            class="button button--outline"
            href="category-list.php"
        >
            カテゴリ管理へ戻る
        </a>
    </div>
</div>

<section class="section">
    <?php if ($errors !== []): ?>
        <div
            class="alert alert--error"
            role="alert"
        >
            <p class="alert__title">
                入力内容を確認してください
            </p>

            <ul class="alert__list">
                <?php foreach ($errors as $error): ?>
                    <li>
                        <?= escape($error) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card form-card">
        <?php require __DIR__ . '/../templates/category-form.php'; ?>
    </div>
</section>

<?php require __DIR__ . '/../templates/admin-footer.php'; ?>