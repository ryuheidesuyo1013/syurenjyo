<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/ArticleRepository.php';

requireAdminLogin();

$repository = new ArticleRepository($pdo);

$sortOptions = [
    'created_desc' => '作成日が新しい順',
    'created_asc' => '作成日が古い順',
    'updated_desc' => '更新日が新しい順',
    'updated_asc' => '更新日が古い順',
    'published_desc' => '公開日が新しい順',
    'published_asc' => '公開日が古い順',
    'title_asc' => 'タイトル昇順',
    'title_desc' => 'タイトル降順',
    'category_asc' => 'カテゴリ順',
    'status_asc' => '公開記事を先に表示',
    'status_desc' => '下書きを先に表示',
];

$sort = filter_input(INPUT_GET, 'sort');

if (
    !is_string($sort)
    || !array_key_exists($sort, $sortOptions)
) {
    $sort = 'created_desc';
}

$perPage = 10;
$totalArticles = $repository->countAll();
$totalPages = max(
    1,
    (int) ceil($totalArticles / $perPage)
);

$page = filter_input(
    INPUT_GET,
    'page',
    FILTER_VALIDATE_INT,
    [
        'options' => [
            'min_range' => 1,
        ],
    ]
);

if (!is_int($page)) {
    $page = 1;
}

if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $perPage;

$articles = $repository->findAll(
    $sort,
    $perPage,
    $offset
);

$firstArticleNumber = $totalArticles === 0
    ? 0
    : $offset + 1;

$lastArticleNumber = min(
    $offset + count($articles),
    $totalArticles
);

$buildPageUrl = static function (
    int $targetPage,
    string $sort
): string {
    return 'article-list.php?' . http_build_query([
        'sort' => $sort,
        'page' => $targetPage,
    ]);
};

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
        <div>
            <h2 class="section-title">
                登録記事
            </h2>

            <span class="text-muted">
                全<?= escape((string) $totalArticles) ?>件
                <?php if ($totalArticles > 0): ?>
                    （<?= escape((string) $firstArticleNumber) ?>
                    ～<?= escape((string) $lastArticleNumber) ?>件目）
                <?php endif; ?>
            </span>
        </div>

        <form
            class="sort-form"
            method="get"
            action="article-list.php"
        >
            <label
                class="sort-form__label"
                for="sort"
            >
                並び順
            </label>

            <select
                class="form-control sort-form__select"
                id="sort"
                name="sort"
                onchange="this.form.submit()"
            >
                <?php foreach ($sortOptions as $value => $label): ?>
                    <option
                        value="<?= escape($value) ?>"
                        <?= $sort === $value
                            ? 'selected'
                            : '' ?>
                    >
                        <?= escape($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <noscript>
                <button
                    class="button button--small"
                    type="submit"
                >
                    並び替え
                </button>
            </noscript>
        </form>
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
                                    <?= escape(
                                        (string) $article['published_at']
                                    ) ?>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= escape(
                                    (string) $article['updated_at']
                                ) ?>
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

        <?php if ($totalPages > 1): ?>
            <nav
                class="pagination"
                aria-label="記事一覧のページ切り替え"
            >
                <?php if ($page > 1): ?>
                    <a
                        class="pagination__link pagination__link--previous"
                        href="<?= escape(
                            $buildPageUrl(
                                $page - 1,
                                $sort
                            )
                        ) ?>"
                    >
                        前へ
                    </a>
                <?php else: ?>
                    <span
                        class="pagination__link pagination__link--disabled"
                        aria-disabled="true"
                    >
                        前へ
                    </span>
                <?php endif; ?>

                <div class="pagination__pages">
                    <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                        <?php if ($pageNumber === $page): ?>
                            <span
                                class="pagination__link pagination__link--current"
                                aria-current="page"
                            >
                                <?= escape((string) $pageNumber) ?>
                            </span>
                        <?php else: ?>
                            <a
                                class="pagination__link"
                                href="<?= escape(
                                    $buildPageUrl(
                                        $pageNumber,
                                        $sort
                                    )
                                ) ?>"
                            >
                                <?= escape((string) $pageNumber) ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>

                <?php if ($page < $totalPages): ?>
                    <a
                        class="pagination__link pagination__link--next"
                        href="<?= escape(
                            $buildPageUrl(
                                $page + 1,
                                $sort
                            )
                        ) ?>"
                    >
                        次へ
                    </a>
                <?php else: ?>
                    <span
                        class="pagination__link pagination__link--disabled"
                        aria-disabled="true"
                    >
                        次へ
                    </span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../templates/admin-footer.php'; ?>
