<?php

declare(strict_types=1);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title><?= escape($errorTitle) ?>｜蹴練場</title>
</head>

<body>
    <main>
        <p>
            エラーコード：
            <?= escape((string) $statusCode) ?>
        </p>

        <h1><?= escape($errorTitle) ?></h1>

        <p><?= escape($message) ?></p>

        <p>
            <a href="../public/articles.php">
                記事一覧へ戻る
            </a>
        </p>
    </main>
</body>
</html>