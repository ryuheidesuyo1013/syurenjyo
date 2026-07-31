<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/flash.php';
require_once __DIR__ . '/../src/http.php';
require_once __DIR__ . '/../src/article-validator.php';
require_once __DIR__ . '/../src/ArticleRepository.php';
require_once __DIR__ . '/../src/CategoryRepository.php';

requireAdminLogin();

$repository = new ArticleRepository($pdo);
$categoryRepository = new CategoryRepository($pdo);

$errors = [];
$categories = $categoryRepository->findAll();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id <= 0) {
    abort(404, '記事が見つかりません。');
}

$article = $repository->findById($id);

if ($article === false) {
    abort(404, '記事が見つかりません。');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken();

    $formData = getArticleFormData($_POST);
    $errors = validateArticleData($formData);

    if (
        $formData['category_id'] > 0
        && !$categoryRepository->existsById($formData['category_id'])
    ) {
        $errors[] = '選択されたカテゴリが存在しません。';
    }

    $article = array_merge(
        $article,
        $formData
    );

    if ($errors === []) {
        if ($article['status'] === 'published') {
            $publishedAt = $article['published_at'] !== null
                ? (string) $article['published_at']
                : date('Y-m-d H:i:s');
        } else {
            $publishedAt = null;
        }

        try {
            $repository->update(
                $id,
                $article,
                $publishedAt
            );

            setFlashMessage(
                '記事を更新しました。',
                'success'
            );

            header('Location: article-list.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = '同じスラッグの記事が既に存在するか、カテゴリが不正です。';
            } else {
                $errors[] = '記事の更新に失敗しました。';
            }
        }
    }
}

$formAction = 'article-edit.php?id=' . $id;
$submitLabel = '変更を保存';
$pageTitle = '記事編集';

require __DIR__ . '/../templates/admin-header.php';
?>

<div class="page-header">
    <div class="page-header__content">
        <h1 class="page-title">
            記事編集
        </h1>

        <p class="page-description">
            「<?= escape((string) $article['title']) ?>」の内容を編集します。
        </p>
    </div>

    <div class="page-actions">
        <a
            class="button button--outline"
            href="article-list.php"
        >
            記事管理へ戻る
        </a>
    </div>
</div>

<section class="section">
    <?php if ($categories === []): ?>
        <div
            class="alert alert--warning"
            role="alert"
        >
            <p class="alert__title">
                利用できるカテゴリがありません
            </p>

            <p>
                記事を保存するには、カテゴリを登録してください。
            </p>

            <p>
                <a
                    class="button button--small"
                    href="category-create.php"
                >
                    カテゴリを作成
                </a>
            </p>
        </div>
    <?php endif; ?>

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
        <?php require __DIR__ . '/../templates/article-form.php'; ?>
    </div>
</section>

<?php require __DIR__ . '/../templates/admin-footer.php'; ?>