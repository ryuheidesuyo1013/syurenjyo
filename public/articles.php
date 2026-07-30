<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/ArticleRepository.php';

$repository = new ArticleRepository($pdo);

/*
 * 検索条件を取得する
 *
 * 不正な形式で配列などが送信された場合に備えて、
 * 文字列であることを確認してからtrim()を使用する。
 */
$keyword = isset($_GET['keyword']) && is_string($_GET['keyword'])
    ? trim($_GET['keyword'])
    : '';

$category = isset($_GET['category']) && is_string($_GET['category'])
    ? trim($_GET['category'])
    : '';

/*
 * 現在のページ番号を取得する
 *
 * pageが未指定、不正な値、0以下の場合は1ページ目にする。
 */
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

/*
 * 1ページあたりの記事数
 */
$perPage = 5;

/*
 * 検索条件に一致する公開記事の総件数を取得する
 */
$totalArticles = $repository->countPublishedSearch(
    $keyword,
    $category
);

/*
 * 総ページ数を計算する
 *
 * 記事が0件の場合も、ページ番号の計算を安定させるため
 * 最低1ページとして扱う。
 */
$totalPages = max(
    1,
    (int) ceil($totalArticles / $perPage)
);

/*
 * 総ページ数を超えるページ番号が指定された場合は、
 * 最後のページを表示する。
 */
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

/*
 * SQLのOFFSETを計算する
 *
 * 1ページ目：0
 * 2ページ目：5
 * 3ページ目：10
 */
$offset = ($currentPage - 1) * $perPage;

/*
 * 現在のページに表示する記事だけ取得する
 */
$articles = $repository->searchPublishedWithPagination(
    $keyword,
    $category,
    $perPage,
    $offset
);

/*
 * ページ移動用URLを生成する
 *
 * キーワードとカテゴリを維持したまま、
 * 指定したページへ移動できるURLを作る。
 */
$buildPageUrl = static function (int $page) use (
    $keyword,
    $category
): string {
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
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>記事一覧｜蹴練場</title>
</head>
<body>
    <main>
        <h1>記事一覧</h1>

        <section>
            <h2>記事を検索</h2>

            <form method="get" action="articles.php">
                <div>
                    <label for="keyword">
                        キーワード
                    </label>

                    <input
                        type="search"
                        id="keyword"
                        name="keyword"
                        value="<?= escape($keyword) ?>"
                        placeholder="タイトル・概要・本文から検索"
                    >
                </div>

                <div>
                    <label for="category">
                        カテゴリ
                    </label>

                    <select
                        id="category"
                        name="category"
                    >
                        <option value="">すべてのカテゴリ</option>

                        <option value="技術" <?= $category === '技術' ? 'selected' : '' ?>>
                            技術
                        </option>

                        <option value="戦術" <?= $category === '戦術' ? 'selected' : '' ?>>
                            戦術
                        </option>

                        <option value="フィジカル" <?= $category === 'フィジカル' ? 'selected' : '' ?>>
                            フィジカル
                        </option>

                        <option value="メンタル" <?= $category === 'メンタル' ? 'selected' : '' ?>>
                            メンタル
                        </option>

                        <option value="栄養" <?= $category === '栄養' ? 'selected' : '' ?>>
                            栄養
                        </option>

                        <option value="その他" <?= $category === 'その他' ? 'selected' : '' ?>>
                            その他
                        </option>
                    </select>
                </div>

                <div>
                    <button type="submit">
                        検索
                    </button>

                    <a href="articles.php">
                        検索条件をリセット
                    </a>
                </div>
            </form>
        </section>

        <section>
            <h2>検索結果</h2>

            <?php if ($keyword !== '' || $category !== ''): ?>
                <p>
                    検索結果：
                    <?= $totalArticles ?>件
                </p>
            <?php endif; ?>

            <?php if ($articles === []): ?>
                <p>条件に一致する公開記事はありません。</p>
            <?php else: ?>

                <?php foreach ($articles as $article): ?>

                    <article>
                        <p>
                            <?= escape($article['category']) ?>
                        </p>

                        <h2>
                            <a href="article.php?slug=<?= urlencode($article['slug']) ?>">
                                <?= escape($article['title']) ?>
                            </a>
                        </h2>

                        <?php if (!empty($article['summary'])): ?>
                            <p>
                                <?= escape($article['summary']) ?>
                            </p>
                        <?php endif; ?>

                        <p>
                            公開日：
                            <?= escape($article['published_at']) ?>
                        </p>
                    </article>

                <?php endforeach; ?>

                <?php if ($totalPages > 1): ?>

                    <nav aria-label="ページネーション">

                        <?php if ($currentPage > 1): ?>

                            <a href="<?= escape($buildPageUrl($currentPage - 1)) ?>">
                                ← 前へ
                            </a>

                        <?php endif; ?>

                        <?php for ($page = 1; $page <= $totalPages; $page++): ?>

                            <?php if ($page === $currentPage): ?>

                                <strong>
                                    <?= $page ?>
                                </strong>

                            <?php else: ?>

                                <a href="<?= escape($buildPageUrl($page)) ?>">
                                    <?= $page ?>
                                </a>

                            <?php endif; ?>

                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>

                            <a href="<?= escape($buildPageUrl($currentPage + 1)) ?>">
                                次へ →
                            </a>

                        <?php endif; ?>

                    </nav>

                <?php endif; ?>

            <?php endif; ?>

        </section>
    </main>
</body>
</html>