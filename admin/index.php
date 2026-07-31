<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/ArticleRepository.php';
require_once __DIR__ . '/../src/CategoryRepository.php';

requireAdminLogin();

$articleRepository = new ArticleRepository($pdo);
$categoryRepository = new CategoryRepository($pdo);

$totalArticleCount = $articleRepository->countAll();
$publishedArticleCount = $articleRepository->countPublished();
$draftArticleCount = $articleRepository->countDraft();

$categories = $categoryRepository->findAll();
$categoryCount = count($categories);

$latestArticles = $articleRepository->findLatest(5);

$pageTitle = 'ダッシュボード';

require __DIR__ . '/../templates/admin-header.php';
?>

<div class="page-header">
    <div class="page-header__content">
        <h1 class="page-title">
            ダッシュボード
        </h1>

        <p class="page-description">
            記事とカテゴリの登録状況を確認できます。
        </p>
    </div>

    <div class="page-actions">
        <a
            class="button"
            href="article-create.php"
        >
            新しい記事を作成
        </a>
    </div>
</div>

<section class="section">
    <div class="section-header">
        <h2 class="section-title">
            サイトの状況
        </h2>
    </div>

    <div class="dashboard-grid">
        <article class="dashboard-card">
            <h3 class="dashboard-card__title">
                記事数
            </h3>

            <p class="dashboard-card__value">
                <?= escape((string) $totalArticleCount) ?>
            </p>

            <p class="dashboard-card__description">
                <a href="article-list.php">
                    登録記事を確認
                </a>
            </p>
        </article>

        <article class="dashboard-card">
            <h3 class="dashboard-card__title">
                公開記事
            </h3>

            <p class="dashboard-card__value">
                <?= escape((string) $publishedArticleCount) ?>
            </p>

            <p class="dashboard-card__description">
                現在公開されている記事
            </p>
        </article>

        <article class="dashboard-card">
            <h3 class="dashboard-card__title">
                下書き
            </h3>

            <p class="dashboard-card__value">
                <?= escape((string) $draftArticleCount) ?>
            </p>

            <p class="dashboard-card__description">
                公開前の記事
            </p>
        </article>

        <article class="dashboard-card">
            <h3 class="dashboard-card__title">
                カテゴリ
            </h3>

            <p class="dashboard-card__value">
                <?= escape((string) $categoryCount) ?>
            </p>

            <p class="dashboard-card__description">
                <a href="category-list.php">
                    登録カテゴリを確認
                </a>
            </p>
        </article>
    </div>
</section>

<section class="section">
    <div class="section-header">
        <h2 class="section-title">
            クイックメニュー
        </h2>
    </div>

    <ul class="quick-menu">
        <li>
            <a
                class="quick-menu__link"
                href="article-create.php"
            >
                新しい記事を作成
            </a>
        </li>

        <li>
            <a
                class="quick-menu__link"
                href="article-list.php"
            >
                記事を管理
            </a>
        </li>

        <li>
            <a
                class="quick-menu__link"
                href="category-create.php"
            >
                新しいカテゴリを作成
            </a>
        </li>

        <li>
            <a
                class="quick-menu__link"
                href="category-list.php"
            >
                カテゴリを管理
            </a>
        </li>
    </ul>
</section>

<section class="section">
    <div class="section-header">
        <h2 class="section-title">
            最新の記事
        </h2>

        <?php if ($latestArticles !== []): ?>
            <a href="article-list.php">
                すべての記事を見る
            </a>
        <?php endif; ?>
    </div>

    <?php if ($latestArticles === []): ?>
        <div class="card">
            <p class="empty-message">
                登録されている記事はありません。
            </p>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>タイトル</th>
                        <th>カテゴリ</th>
                        <th>公開状態</th>
                        <th>公開日</th>
                        <th>更新日</th>
                        <th>操作</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($latestArticles as $article): ?>
                        <tr>
                            <td>
                                <?= escape((string) $article['title']) ?>
                            </td>

                            <td>
                                <?= escape((string) $article['category']) ?>
                            </td>

                            <td>
                                <?php if ($article['status'] === 'published'): ?>
                                    <span
                                        class="status-badge status-badge--published"
                                    >
                                        公開
                                    </span>
                                <?php else: ?>
                                    <span
                                        class="status-badge status-badge--draft"
                                    >
                                        下書き
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($article['published_at'] === null): ?>
                                    <span class="text-muted">
                                        未公開
                                    </span>
                                <?php else: ?>
                                    <?= escape((string) $article['published_at']) ?>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= escape((string) $article['updated_at']) ?>
                            </td>

                            <td>
                                <div class="table-actions">
                                    <a
                                        class="button button--small button--outline"
                                        href="article-edit.php?id=<?= (int) $article['id'] ?>"
                                    >
                                        編集
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../templates/admin-footer.php'; ?>