<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/http.php';
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

$articleCount = $repository->countArticles($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken();

    if ($articleCount > 0) {
        $errors[] = '記事が登録されているカテゴリは削除できません。';
    }

    if ($errors === []) {
        try {
            $repository->delete($id);

            header('Location: category-list.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = '記事が登録されているカテゴリは削除できません。';
            } else {
                $errors[] = 'カテゴリの削除に失敗しました。';
            }
        }
    }
}

$pageTitle = 'カテゴリ削除';

require __DIR__ . '/../templates/admin-header.php';
?>

<div class="page-header">
    <div class="page-header__content">
        <h1 class="page-title">
            カテゴリ削除
        </h1>

        <p class="page-description">
            削除するカテゴリの情報を確認してください。
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
                カテゴリを削除できませんでした
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

    <div class="card delete-card">
        <div class="delete-card__header">
            <h2 class="section-title">
                削除対象
            </h2>

            <?php if ($articleCount > 0): ?>
                <span class="status-badge status-badge--draft">
                    削除不可
                </span>
            <?php else: ?>
                <span class="status-badge status-badge--danger">
                    削除確認
                </span>
            <?php endif; ?>
        </div>

        <dl class="detail-list">
            <div class="detail-list__item">
                <dt class="detail-list__term">
                    カテゴリ名
                </dt>

                <dd class="detail-list__description">
                    <?= escape((string) $category['name']) ?>
                </dd>
            </div>

            <div class="detail-list__item">
                <dt class="detail-list__term">
                    スラッグ
                </dt>

                <dd class="detail-list__description">
                    <?= escape((string) $category['slug']) ?>
                </dd>
            </div>

            <div class="detail-list__item">
                <dt class="detail-list__term">
                    登録記事数
                </dt>

                <dd class="detail-list__description">
                    <?= escape((string) $articleCount) ?>件
                </dd>
            </div>
        </dl>

        <?php if ($articleCount > 0): ?>
            <div
                class="alert alert--warning"
                role="alert"
            >
                <p class="alert__title">
                    このカテゴリは削除できません
                </p>

                <p>
                    このカテゴリには記事が登録されています。
                    削除するには、先に対象記事のカテゴリを変更してください。
                </p>
            </div>

            <div class="form-actions">
                <a
                    class="button button--outline"
                    href="article-list.php"
                >
                    記事管理を確認
                </a>

                <a
                    class="button"
                    href="category-list.php"
                >
                    カテゴリ管理へ戻る
                </a>
            </div>
        <?php else: ?>
            <div
                class="alert alert--warning"
                role="alert"
            >
                <p class="alert__title">
                    この操作は元に戻せません
                </p>

                <p>
                    「<?= escape((string) $category['name']) ?>」を完全に削除します。
                </p>
            </div>

            <form
                method="post"
                action="category-delete.php?id=<?= (int) $id ?>"
                onsubmit="return confirm('このカテゴリを削除しますか？この操作は元に戻せません。');"
            >
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escape(getCsrfToken()) ?>"
                >

                <div class="form-actions">
                    <button
                        class="button button--danger"
                        type="submit"
                    >
                        カテゴリを削除
                    </button>

                    <a
                        class="button button--outline"
                        href="category-list.php"
                    >
                        キャンセル
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../templates/admin-footer.php'; ?>