<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';

startSession();

if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken();

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $errors[] = 'ユーザー名とパスワードを入力してください。';
    }

    if ($errors === []) {
        $sql = '
            SELECT
                id,
                username,
                password_hash
            FROM admin_users
            WHERE username = :username
            LIMIT 1
        ';

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':username' => $username,
        ]);

        $adminUser = $stmt->fetch();

        if (
            $adminUser !== false
            && password_verify(
                $password,
                (string) $adminUser['password_hash']
            )
        ) {
            loginAdmin(
                (int) $adminUser['id'],
                (string) $adminUser['username']
            );

            header('Location: index.php');
            exit;
        }

        $errors[] = 'ユーザー名またはパスワードが違います。';
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

    <title>管理画面ログイン｜蹴練場</title>

    <link
        rel="stylesheet"
        href="../public/css/admin.css"
    >
</head>

<body class="login-page">
    <main class="login-container">
        <section class="login-card">
            <div class="login-card__header">
                <p class="login-card__site-name">
                    蹴練場
                </p>

                <h1 class="login-card__title">
                    管理画面ログイン
                </h1>

                <p class="login-card__description">
                    管理者アカウントでログインしてください。
                </p>
            </div>

            <?php if ($errors !== []): ?>
                <div
                    class="alert alert--error"
                    role="alert"
                >
                    <p class="alert__title">
                        ログインできませんでした
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

            <form
                class="login-form"
                method="post"
                action="login.php"
            >
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escape(getCsrfToken()) ?>"
                >

                <div class="form-group">
                    <label
                        class="form-label"
                        for="username"
                    >
                        ユーザー名
                    </label>

                    <input
                        class="form-control"
                        type="text"
                        id="username"
                        name="username"
                        value="<?= escape($username) ?>"
                        autocomplete="username"
                        autofocus
                        required
                    >
                </div>

                <div class="form-group">
                    <label
                        class="form-label"
                        for="password"
                    >
                        パスワード
                    </label>

                    <input
                        class="form-control"
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button
                    class="button login-button"
                    type="submit"
                >
                    ログイン
                </button>
            </form>

            <p class="login-card__footer">
                <a href="../public/articles.php">
                    公開記事一覧へ戻る
                </a>
            </p>
        </section>
    </main>
</body>
</html>