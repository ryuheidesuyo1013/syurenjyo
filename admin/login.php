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

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

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
            && password_verify($password, $adminUser['password_hash'])
        ) {
            loginAdmin(
                (int) $adminUser['id'],
                $adminUser['username']
            );

            header('Location: article-list.php');
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
</head>

<body>
    <main>
        <h1>管理画面ログイン</h1>

        <?php if ($errors !== []): ?>
            <div>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= escape($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escape(getCsrfToken()) ?>"
                >
            <div>
                <label for="username">ユーザー名</label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?= escape($username) ?>"
                    autocomplete="username"
                    required
                >
            </div>

            <div>
                <label for="password">パスワード</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit">ログイン</button>
        </form>
    </main>
</body>
</html>