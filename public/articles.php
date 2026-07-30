<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/ArticleRepository.php';

$repository = new ArticleRepository($pdo);

$keyword = trim($_GET['keyword'] ?? '');
$category = trim($_GET['category'] ?? '');

$articles = $repository->searchPublished(
    $keyword,
    $category
);
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

                        <option
                            value="技術"
                            <?= $category === '技術' ? 'selected' : '' ?>
                        >
                            技術
                        </option>

                        <option
                            value="戦術"
                            <?= $category === '戦術' ? 'selected' : '' ?>
                        >
                            戦術
                        </option>

                        <option
                            value="フィジカル"
                            <?= $category === 'フィジカル' ? 'selected' : '' ?>
                        >
                            フィジカル
                        </option>

                        <option
                            value="メンタル"
                            <?= $category === 'メンタル' ? 'selected' : '' ?>
                        >
                            メンタル
                        </option>

                        <option
                            value="栄養"
                            <?= $category === '栄養' ? 'selected' : '' ?>
                        >
                            栄養
                        </option>

                        <option
                            value="その他"
                            <?= $category === 'その他' ? 'selected' : '' ?>
                        >
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
                    <?= count($articles) ?>件
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
                            <a
                                href="article.php?slug=<?= urlencode($article['slug']) ?>"
                            >
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
            <?php endif; ?>
        </section>
    </main>
</body>
</html>