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
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>ダッシュボード｜蹴練場 管理画面</title>
</head>

<body>
    <header>
        <p>
            蹴練場 管理画面
        </p>

        <nav aria-label="管理画面メニュー">
            <ul>
                <li>
                    <a href="index.php">
                        ダッシュボード
                    </a>
                </li>

                <li>
                    <a href="article-list.php">
                        記事管理
                    </a>
                </li>

                <li>
                    <a href="category-list.php">
                        カテゴリ管理
                    </a>
                </li>

                <li>
                    <a href="../public/articles.php">
                        公開記事一覧
                    </a>
                </li>
            </ul>
        </nav>

        <p>
            ログイン中：
            <?= escape($_SESSION['admin_username'] ?? '') ?>
        </p>

        <form
            method="post"
            action="logout.php"
        >
            <input
                type="hidden"
                name="csrf_token"
                value="<?= escape(getCsrfToken()) ?>"
            >

            <button type="submit">
                ログアウト
            </button>
        </form>
    </header>

    <main>
        <h1>ダッシュボード</h1>

        <section>
            <h2>サイトの状況</h2>

            <div>
                <article>
                    <h3>記事数</h3>

                    <p>
                        <?= $totalArticleCount ?>
                    </p>

                    <p>
                        <a href="article-list.php">
                            記事を確認
                        </a>
                    </p>
                </article>

                <article>
                    <h3>公開記事</h3>

                    <p>
                        <?= $publishedArticleCount ?>
                    </p>

                    <p>
                        現在公開されている記事
                    </p>
                </article>

                <article>
                    <h3>下書き</h3>

                    <p>
                        <?= $draftArticleCount ?>
                    </p>

                    <p>
                        公開前の記事
                    </p>
                </article>

                <article>
                    <h3>カテゴリ</h3>

                    <p>
                        <?= $categoryCount ?>
                    </p>

                    <p>
                        <a href="category-list.php">
                            カテゴリを確認
                        </a>
                    </p>
                </article>
            </div>
        </section>

        <section>
            <h2>クイックメニュー</h2>

            <ul>
                <li>
                    <a href="article-create.php">
                        新しい記事を作成
                    </a>
                </li>

                <li>
                    <a href="article-list.php">
                        記事を管理
                    </a>
                </li>

                <li>
                    <a href="category-create.php">
                        新しいカテゴリを作成
                    </a>
                </li>

                <li>
                    <a href="category-list.php">
                        カテゴリを管理
                    </a>
                </li>
            </ul>
        </section>

        <section>
            <h2>最新の記事</h2>

            <?php if ($latestArticles === []): ?>
                <p>
                    登録されている記事はありません。
                </p>
            <?php else: ?>
                <table border="1">
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
                                    <?= escape($article['title']) ?>
                                </td>

                                <td>
                                    <?= escape($article['category']) ?>
                                </td>

                                <td>
                                    <?= $article['status'] === 'published'
                                        ? '公開'
                                        : '下書き' ?>
                                </td>

                                <td>
                                    <?php if ($article['published_at'] === null): ?>
                                        未公開
                                    <?php else: ?>
                                        <?= escape($article['published_at']) ?>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= escape($article['updated_at']) ?>
                                </td>

                                <td>
                                    <a
                                        href="article-edit.php?id=<?= (int) $article['id'] ?>"
                                    >
                                        編集
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <p>
                    <a href="article-list.php">
                        すべての記事を見る
                    </a>
                </p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>