<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/CategoryRepository.php';

requireAdminLogin();

$repository = new CategoryRepository($pdo);

$categories = $repository->findAll();

$pageTitle = 'カテゴリ管理';

require __DIR__ . '/../templates/admin-header.php';
?>

<div class="page-header">
    <div class="page-header__content">
        <h1 class="page-title">
            カテゴリ管理
        </h1>

        <p class="page-description">
            記事を分類するカテゴリの追加、編集、削除ができます。
        </p>
    </div>

    <div class="page-actions">
        <a
            class="button button--outline"
            href="article-list.php"
        >
            記事管理
        </a>

        <a
            class="button"
            href="category-create.php"
        >
            新しいカテゴリを追加
        </a>
    </div>
</div>

<section class="section">
    <div class="section-header">
        <h2 class="section-title">
            登録カテゴリ
        </h2>

        <span class="text-muted">
            <?= escape((string) count($categories)) ?>件
        </span>
    </div>

    <?php if ($categories === []): ?>
        <div class="card">
            <p class="empty-message">
                登録されているカテゴリはありません。
            </p>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="admin-table">
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
                                <?= escape((string) $category['name']) ?>
                            </td>

                            <td>
                                <?= escape((string) $category['slug']) ?>
                            </td>

                            <td>
                                <?= escape((string) $category['article_count']) ?>件
                            </td>

                            <td>
                                <?= escape((string) $category['created_at']) ?>
                            </td>

                            <td>
                                <div class="table-actions">
                                    <a
                                        class="button button--small button--outline"
                                        href="category-edit.php?id=<?= (int) $category['id'] ?>"
                                    >
                                        編集
                                    </a>

                                    <a
                                        class="button button--small button--danger"
                                        href="category-delete.php?id=<?= (int) $category['id'] ?>"
                                    >
                                        削除
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