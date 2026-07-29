<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';

$errors = [];

$title = '';
$slug = '';
$category = '';
$summary = '';
$content = '';
$status = 'draft';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = $_POST['status'] ?? 'draft';

    if ($title === '') {
        $errors[] = 'タイトルを入力してください。';
    }

    if ($slug === '') {
        $errors[] = 'スラッグを入力してください。';
    }

    if ($category === '') {
        $errors[] = 'カテゴリを入力してください。';
    }

    if ($content === '') {
        $errors[] = '本文を入力してください。';
    }

    if (!in_array($status, ['draft', 'published'], true)) {
        $errors[] = '公開状態が不正です。';
    }

    if ($errors === []) {
        $publishedAt = $status === 'published'
            ? date('Y-m-d H:i:s')
            : null;

        $sql = '
            INSERT INTO articles (
                title,
                slug,
                category,
                summary,
                content,
                status,
                published_at
            ) VALUES (
                :title,
                :slug,
                :category,
                :summary,
                :content,
                :status,
                :published_at
            )
        ';

        try {
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':title' => $title,
                ':slug' => $slug,
                ':category' => $category,
                ':summary' => $summary,
                ':content' => $content,
                ':status' => $status,
                ':published_at' => $publishedAt,
            ]);

            header('Location: ../public/articles.php');
            exit;
        } catch (PDOException $e) {
            if ((int) $e->getCode() === 23000) {
                $errors[] = '同じスラッグの記事が既に存在します。';
            } else {
                $errors[] = '記事の保存に失敗しました。';
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
    <title>記事作成｜蹴練場 管理画面</title>
</head>

<body>
    <main>
        <h1>記事作成</h1>

        <p>
            <a href="../public/articles.php">公開記事一覧へ戻る</a>
        </p>

        <?php if ($errors !== []): ?>
            <div>
                <p>入力内容を確認してください。</p>

                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li>
                            <?= htmlspecialchars(
                                $error,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <div>
                <label for="title">タイトル</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="<?= htmlspecialchars(
                        $title,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    required
                >
            </div>

            <div>
                <label for="slug">スラッグ</label>
                <input
                    type="text"
                    id="slug"
                    name="slug"
                    value="<?= htmlspecialchars(
                        $slug,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    required
                >
                <p>例：shoot-training-basic</p>
            </div>

            <div>
                <label for="category">カテゴリ</label>
                <input
                    type="text"
                    id="category"
                    name="category"
                    value="<?= htmlspecialchars(
                        $category,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    required
                >
            </div>

            <div>
                <label for="summary">概要</label>
                <textarea
                    id="summary"
                    name="summary"
                    rows="4"
                ><?= htmlspecialchars(
                    $summary,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?></textarea>
            </div>

            <div>
                <label for="content">本文</label>
                <textarea
                    id="content"
                    name="content"
                    rows="15"
                    required
                ><?= htmlspecialchars(
                    $content,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?></textarea>
            </div>

            <div>
                <label for="status">公開状態</label>
                <select id="status" name="status">
                    <option
                        value="draft"
                        <?= $status === 'draft' ? 'selected' : '' ?>
                    >
                        下書き
                    </option>

                    <option
                        value="published"
                        <?= $status === 'published' ? 'selected' : '' ?>
                    >
                        公開
                    </option>
                </select>
            </div>

            <button type="submit">記事を保存</button>
        </form>
    </main>
</body>
</html>