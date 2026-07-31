<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/ArticleRepository.php';

requireAdminLogin();

$repository = new ArticleRepository($pdo);

$articles = $repository->findAll();

$pageTitle = '記事管理';

require __DIR__ . '/../templates/admin-header.php';
?>

<div class="page-header">
    <div class="page-header__content">
        <h1 class="page-title">
            記事管理
        </h1>

        <p class="page-description">
            記事の作成、編集、削除、公開状態の確認ができます。
        </p>
    </div>

    <div class="page-actions">
        <a
            class="button button--outline"
            href="../public/articles.php"
            target="_blank"
            rel="noopener noreferrer"
        >
            公開記事一覧
        </a>

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
            登録記事
        </h2>

        <span class="text-muted">
            <?= escape((string) count($articles)) ?>件
        </span>
    </div>

    <?php if ($articles === []): ?>
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
                    <?php foreach ($articles as $article): ?>
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

                                    <form
                                        method="post"
                                        action="article-delete.php"
                                        onsubmit="return confirm('この記事を削除しますか？この操作は元に戻せません。');"
                                    >
                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= escape(getCsrfToken()) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $article['id'] ?>"
                                        >

                                        <button
                                            class="button button--small button--danger"
                                            type="submit"
                                        >
                                            削除
                                        </button>
                                    </form>
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