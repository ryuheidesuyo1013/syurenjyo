<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';

requireAdminLogin();

$errors = [];

$article = [
    'title' => '',
    'slug' => '',
    'category' => '',
    'summary' => '',
    'content' => '',
    'status' => 'draft',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken();
    
    $article = [
        'title' => trim($_POST['title'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'category' => trim($_POST['category'] ?? ''),
        'summary' => trim($_POST['summary'] ?? ''),
        'content' => trim($_POST['content'] ?? ''),
        'status' => $_POST['status'] ?? 'draft',
    ];

    if ($article['title'] === '') {
        $errors[] = 'タイトルを入力してください。';
    }

    if ($article['slug'] === '') {
        $errors[] = 'スラッグを入力してください。';
    }

    if ($article['category'] === '') {
        $errors[] = 'カテゴリを入力してください。';
    }

    if ($article['content'] === '') {
        $errors[] = '本文を入力してください。';
    }

    if (!in_array($article['status'], ['draft', 'published'], true)) {
        $errors[] = '公開状態が不正です。';
    }

    if ($errors === []) {
        $publishedAt = $article['status'] === 'published'
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
                ':title' => $article['title'],
                ':slug' => $article['slug'],
                ':category' => $article['category'],
                ':summary' => $article['summary'],
                ':content' => $article['content'],
                ':status' => $article['status'],
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

<?php
$formAction = 'article-create.php';
$submitLabel = '記事を作成';

require __DIR__ . '/../templates/article-form.php';
?>