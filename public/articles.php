<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/ArticleRepository.php';

$repository = new ArticleRepository($pdo);

$keyword = isset($_GET['keyword']) && is_string($_GET['keyword'])
    ? trim($_GET['keyword'])
    : '';

$category = isset($_GET['category']) && is_string($_GET['category'])
    ? trim($_GET['category'])
    : '';

$pageInput = $_GET['page'] ?? '1';

if (
    !is_string($pageInput)
    || !ctype_digit($pageInput)
    || (int) $pageInput < 1
) {
    $currentPage = 1;
} else {
    $currentPage = (int) $pageInput;
}

$perPage = 5;
$totalArticles = $repository->countPublishedSearch($keyword, $category);
$totalPages = max(1, (int) ceil($totalArticles / $perPage));

if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

$offset = ($currentPage - 1) * $perPage;

$articles = $repository->searchPublishedWithPagination(
    $keyword,
    $category,
    $perPage,
    $offset
);

$buildPageUrl = static function (int $page) use ($keyword, $category): string {
    $queryParameters = [];

    if ($keyword !== '') {
        $queryParameters['keyword'] = $keyword;
    }

    if ($category !== '') {
        $queryParameters['category'] = $category;
    }

    $queryParameters['page'] = $page;

    return 'articles.php?' . http_build_query($queryParameters);
};

$pageTitle = '記事一覧｜蹴練場';
$metaDescription = 'サッカーの技術、戦術、フィジカル、メンタル、栄養に関する記事を検索・閲覧できます。';
$canonicalUrl = '';
$ogImage = '';
$ogType = 'website';
$robots = 'index, follow';
$bodyClass = 'articles-page';

require __DIR__ . '/../templates/public-header.php';
?>

    <main class="site-main">
        <div class="site-container">
            <header class="page-hero">
                <p class="page-hero__eyebrow">ARTICLES</p>
                <h1 class="page-hero__title">記事一覧</h1>
                <p class="page-hero__description">
                    サッカーに関する知識や練習方法を、カテゴリやキーワードから探せます。
                </p>
            </header>

            <section class="public-section">
                <div class="public-card search-card">
                    <div class="section-heading">
                        <h2 class="section-heading__title">記事を検索</h2>
                        <p class="section-heading__description">
                            キーワードとカテゴリを組み合わせて絞り込めます。
                        </p>
                    </div>

                    <form class="public-search-form" method="get" action="articles.php">
                        <div class="public-form-field">
                            <label class="public-form-label" for="keyword">キーワード</label>
                            <input
                                class="public-form-control"
                                type="search"
                                id="keyword"
                                name="keyword"
                                value="<?= escape($keyword) ?>"
                                placeholder="タイトル・概要・本文から検索"
                            >
                        </div>

                        <div class="public-form-field">
                            <label class="public-form-label" for="category">カテゴリ</label>
                            <select class="public-form-control" id="category" name="category">
                                <option value="">すべてのカテゴリ</option>
                                <?php foreach (['技術', '戦術', 'フィジカル', 'メンタル', '栄養', 'その他'] as $categoryOption): ?>
                                    <option
                                        value="<?= escape($categoryOption) ?>"
                                        <?= $category === $categoryOption ? 'selected' : '' ?>
                                    >
                                        <?= escape($categoryOption) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="public-search-form__actions">
                            <button class="public-button" type="submit">検索</button>
                            <a class="public-button public-button--outline" href="articles.php">
                                条件をリセット
                            </a>
                        </div>
                    </form>
                </div>
            </section>

            <section class="public-section">
                <div class="section-heading section-heading--row">
                    <div>
                        <h2 class="section-heading__title">検索結果</h2>
                        <p class="section-heading__description">
                            <?= escape((string) $totalArticles) ?>件の記事が見つかりました。
                        </p>
                    </div>
                </div>

                <?php if ($articles === []): ?>
                    <div class="public-card empty-state">
                        <p>条件に一致する公開記事はありません。</p>
                    </div>
                <?php else: ?>
                    <div class="article-list">
                        <?php foreach ($articles as $article): ?>
                            <article class="article-card">
                                <div class="article-card__body">
                                    <p class="article-card__category">
                                        <?= escape((string) $article['category']) ?>
                                    </p>

                                    <h2 class="article-card__title">
                                        <a href="article.php?slug=<?= urlencode((string) $article['slug']) ?>">
                                            <?= escape((string) $article['title']) ?>
                                        </a>
                                    </h2>

                                    <?php if (!empty($article['summary'])): ?>
                                        <p class="article-card__summary">
                                            <?= escape((string) $article['summary']) ?>
                                        </p>
                                    <?php endif; ?>

                                    <div class="article-card__footer">
                                        <p class="article-card__date">
                                            公開日：<?= escape((string) $article['published_at']) ?>
                                        </p>

                                        <a
                                            class="article-card__link"
                                            href="article.php?slug=<?= urlencode((string) $article['slug']) ?>"
                                        >
                                            続きを読む
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <nav class="public-pagination" aria-label="ページネーション">
                            <?php if ($currentPage > 1): ?>
                                <a
                                    class="public-pagination__link public-pagination__link--wide"
                                    href="<?= escape($buildPageUrl($currentPage - 1)) ?>"
                                >
                                    ← 前へ
                                </a>
                            <?php else: ?>
                                <span
                                    class="public-pagination__link public-pagination__link--wide public-pagination__link--disabled"
                                    aria-disabled="true"
                                >
                                    ← 前へ
                                </span>
                            <?php endif; ?>

                            <div class="public-pagination__numbers">
                                <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                                    <?php if ($page === $currentPage): ?>
                                        <span
                                            class="public-pagination__link public-pagination__link--current"
                                            aria-current="page"
                                        >
                                            <?= $page ?>
                                        </span>
                                    <?php else: ?>
                                        <a
                                            class="public-pagination__link"
                                            href="<?= escape($buildPageUrl($page)) ?>"
                                        >
                                            <?= $page ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>

                            <?php if ($currentPage < $totalPages): ?>
                                <a
                                    class="public-pagination__link public-pagination__link--wide"
                                    href="<?= escape($buildPageUrl($currentPage + 1)) ?>"
                                >
                                    次へ →
                                </a>
                            <?php else: ?>
                                <span
                                    class="public-pagination__link public-pagination__link--wide public-pagination__link--disabled"
                                    aria-disabled="true"
                                >
                                    次へ →
                                </span>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </section>
        </div>
    </main>

<?php require __DIR__ . '/../templates/public-footer.php'; ?>