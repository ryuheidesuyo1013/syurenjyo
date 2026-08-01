<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/HtmlArticleImporter.php';
require_once __DIR__ . '/../src/ImportedImageUploader.php';

requireAdminLogin();

$errors = [];
$importResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrfToken();

    try {
        if (
            !isset($_FILES['html_file'])
            || !is_array($_FILES['html_file'])
        ) {
            throw new RuntimeException(
                'HTMLファイルが送信されていません。'
            );
        }

        $importer = new HtmlArticleImporter();

        $importResult = $importer->importFromUploadedFile(
            $_FILES['html_file']
        );

        if (
            isset($_FILES['article_images'])
            && is_array($_FILES['article_images'])
        ) {
            $imageUploader = new ImportedImageUploader(
                dirname(__DIR__) . '/uploads',
                '../uploads'
            );

            $imageMapping = $imageUploader->uploadMultiple(
                $_FILES['article_images']
            );

            if ($imageMapping !== []) {
                $importResult['content'] =
                    $imageUploader->replaceImageSources(
                        $importResult['content'],
                        $imageMapping
                    );

                $unmatchedImages = [];

                foreach ($importResult['image_paths'] as $imagePath) {
                    $path = parse_url(
                        (string) $imagePath,
                        PHP_URL_PATH
                    );

                    if (!is_string($path) || $path === '') {
                        $path = (string) $imagePath;
                    }

                    $fileName = rawurldecode(
                        basename($path)
                    );

                    if (
                        $fileName !== ''
                        && !isset($imageMapping[$fileName])
                    ) {
                        $unmatchedImages[] = (string) $imagePath;
                    }
                }

                $importResult['image_paths'] = $unmatchedImages;

                $importResult['warnings'] = array_values(
                    array_filter(
                        $importResult['warnings'],
                        static fn (string $warning): bool =>
                            !str_contains(
                                $warning,
                                '本文内の画像はまだCMSへアップロードされていません'
                            )
                    )
                );

                if ($unmatchedImages !== []) {
                    $importResult['warnings'][] =
                        '一部の画像を対応付けできませんでした。'
                        . 'ファイル名がHTML内の画像名と一致しているか確認してください。';
                }
            }
        }
    } catch (RuntimeException $exception) {
        $errors[] = $exception->getMessage();
    }
}

$pageTitle = 'HTML記事インポート';

require __DIR__ . '/../templates/admin-header.php';
?>

<div class="page-header">
    <div class="page-header__content">
        <h1 class="page-title">
            HTML記事インポート
        </h1>

        <p class="page-description">
            既存のHTML記事から、タイトル・SEO情報・本文を抽出します。
        </p>
    </div>

    <div class="page-actions">
        <a
            class="button button--outline"
            href="article-list.php"
        >
            記事一覧へ戻る
        </a>
    </div>
</div>

<?php if ($errors !== []): ?>
    <div class="alert alert--error">
        <p>
            HTML記事を解析できませんでした。
        </p>

        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= escape($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<section class="section">
    <div class="card form-card">
        <form
            class="html-import-form"
            method="post"
            action="article-import.php"
            enctype="multipart/form-data"
        >
            <input
                type="hidden"
                name="csrf_token"
                value="<?= escape(getCsrfToken()) ?>"
            >

            <div class="form-group">
                <label class="form-label" for="html_file">
                    HTMLファイル
                    <span class="form-required">必須</span>
                </label>

                <input
                    class="form-control"
                    type="file"
                    id="html_file"
                    name="html_file"
                    accept=".html,.htm,text/html"
                    required
                >

                <p class="form-help">
                    HTML・HTM形式、2MB以下のファイルを選択してください。
                </p>
            </div>

            <div class="form-group">
                <label
                    class="form-label"
                    for="article_images"
                >
                    記事内画像
                </label>

                <input
                    class="form-control"
                    type="file"
                    id="article_images"
                    name="article_images[]"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                >

                <p class="form-help">
                    HTML本文で使用している画像をまとめて選択してください。
                    ファイル名が一致する画像は、自動でCMSのURLへ置き換えます。
                    1枚5MB以下のJPEG・PNG・WebPに対応しています。
                </p>
            </div>

            <div class="form-actions">
                <button class="button" type="submit">
                    HTMLを解析する
                </button>
            </div>
        </form>
    </div>
</section>

<?php if (is_array($importResult)): ?>
    <section class="section">
        <div class="section-header">
            <div>
                <h2 class="section-title">解析結果</h2>
                <p class="text-muted">
                    内容を確認してから、新規記事作成画面へ移動してください。
                </p>
            </div>
        </div>

        <?php if ($importResult['warnings'] !== []): ?>
            <div class="alert alert--warning">
                <p>インポート時の注意事項</p>
                <ul>
                    <?php foreach ($importResult['warnings'] as $warning): ?>
                        <li><?= escape($warning) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card import-preview">
            <dl class="definition-list import-preview__details">
                <dt>タイトル</dt>
                <dd>
                    <?= $importResult['title'] !== ''
                        ? escape($importResult['title'])
                        : '<span class="text-muted">取得できませんでした。</span>' ?>
                </dd>

                <dt>メタディスクリプション</dt>
                <dd>
                    <?= $importResult['meta_description'] !== ''
                        ? nl2br(escape($importResult['meta_description']))
                        : '<span class="text-muted">取得できませんでした。</span>' ?>
                </dd>

                <dt>canonical URL</dt>
                <dd>
                    <?php if ($importResult['canonical_url'] !== ''): ?>
                        <a
                            href="<?= escape($importResult['canonical_url']) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <?= escape($importResult['canonical_url']) ?>
                        </a>
                    <?php else: ?>
                        <span class="text-muted">取得できませんでした。</span>
                    <?php endif; ?>
                </dd>

                <dt>本文内画像</dt>
                <dd>
                    <?php if ($importResult['image_paths'] === []): ?>
                        <span class="text-muted">画像はありません。</span>
                    <?php else: ?>
                        <ul class="import-preview__image-list">
                            <?php foreach ($importResult['image_paths'] as $imagePath): ?>
                                <li><code><?= escape($imagePath) ?></code></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </dd>
            </dl>

            <div class="form-group import-preview__content">
                <span class="form-label">変換後の本文プレビュー</span>
                <div class="import-content-preview article-content">
                    <?= $importResult['content'] ?>
                </div>
            </div>

            <form method="post" action="article-import-confirm.php">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escape(getCsrfToken()) ?>"
                >
                <input
                    type="hidden"
                    name="title"
                    value="<?= escape($importResult['title']) ?>"
                >
                <input
                    type="hidden"
                    name="meta_description"
                    value="<?= escape($importResult['meta_description']) ?>"
                >
                <input
                    type="hidden"
                    name="canonical_url"
                    value="<?= escape($importResult['canonical_url']) ?>"
                >
                <textarea name="content" hidden><?= escape($importResult['content']) ?></textarea>

                <div class="form-actions">
                    <button class="button" type="submit">
                        この記事を新規作成
                    </button>
                    <a class="button button--outline" href="article-import.php">
                        別のHTMLを選ぶ
                    </a>
                </div>
            </form>
        </div>
    </section>
<?php endif; ?>

<?php require __DIR__ . '/../templates/admin-footer.php'; ?>