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
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>記事管理｜蹴練場 管理画面</title>
</head>

<body>
    <main>
        <h1>記事管理</h1>
        <p>
            ログイン中：
            <?= escape($_SESSION['admin_username'] ?? '') ?>
        </p>

        <form method="post" action="logout.php">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= escape(getCsrfToken()) ?>"
            >

            <button type="submit">
                ログアウト
            </button>
        </form>
        <p>
            <a href="article-create.php">新しい記事を作成</a>
        </p>

        <p>
            <a href="../public/articles.php">公開記事一覧を見る</a>
        </p>

        <?php if ($articles === []): ?>
            <p>登録されている記事はありません。</p>
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
                    <?php foreach ($articles as $article): ?>
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
                                <?= escape($article['published_at']) ?>
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

                                <form
                                    method="post"
                                    action="article-delete.php"
                                    style="display: inline;"
                                    onsubmit="return confirm('この記事を削除しますか？');"
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

                                    <button type="submit">
                                        削除
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>
</html>