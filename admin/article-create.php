<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/article-validator.php';
require_once __DIR__ . '/../src/ArticleRepository.php';
require_once __DIR__ . '/../src/CategoryRepository.php';

requireAdminLogin();

$repository = new ArticleRepository($pdo);
$categoryRepository = new CategoryRepository($pdo);

$errors = [];
$article = getEmptyArticleData();
$categories = $categoryRepository->findAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken();

    $article = getArticleFormData($_POST);
    $errors = validateArticleData($article);

    if (
        $article['category_id'] > 0
        && !$categoryRepository->existsById($article['category_id'])
    ) {
        $errors[] = '選択されたカテゴリが存在しません。';
    }

    if ($errors === []) {
        $publishedAt = $article['status'] === 'published'
            ? date('Y-m-d H:i:s')
            : null;

        try {
            $repository->create(
                $article,
                $publishedAt
            );

            header('Location: article-list.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = '同じスラッグの記事が既に存在するか、カテゴリが不正です。';
            } else {
                $errors[] = '記事の作成に失敗しました。';
            }
        }
    }
}

$formAction = 'article-create.php';
$submitLabel = '記事を作成';
$pageTitle = '記事作成';

require __DIR__ . '/../templates/admin-header.php';
?>

<div class="page-header">
    <div class="page-header__content">
        <h1 class="page-title">
            記事作成
        </h1>

        <p class="page-description">
            新しい記事の内容と公開状態を入力してください。
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
                カテゴリが登録されていません
            </p>

            <p>
                記事を作成するには、先にカテゴリを登録してください。
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