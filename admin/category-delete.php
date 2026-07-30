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
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>カテゴリ削除｜蹴練場 管理画面</title>
</head>

<body>
    <main>
        <h1>カテゴリ削除</h1>

        <p>
            <a href="category-list.php">カテゴリ管理へ戻る</a>
        </p>

        <?php if ($errors !== []): ?>
            <div>
                <p>カテゴリを削除できませんでした。</p>

                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= escape($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <dl>
            <dt>カテゴリ名</dt>
            <dd><?= escape($category['name']) ?></dd>

            <dt>スラッグ</dt>
            <dd><?= escape($category['slug']) ?></dd>

            <dt>登録記事数</dt>
            <dd><?= escape((string) $articleCount) ?>件</dd>
        </dl>

        <?php if ($articleCount > 0): ?>
            <p>
                このカテゴリには記事が登録されているため削除できません。
                先に記事のカテゴリを変更してください。
            </p>
        <?php else: ?>
            <p>
                このカテゴリを削除します。削除後は元に戻せません。
            </p>

            <form method="post" action="category-delete.php?id=<?= escape((string) $id) ?>">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escape(getCsrfToken()) ?>"
                >

                <button type="submit">
                    カテゴリを削除
                </button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>