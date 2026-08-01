<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/ImageRepository.php';

requireAdminLogin();

/**
 * ファイル容量を読みやすい形式へ変換する。
 */
function formatFileSize(int $bytes): string
{
    if ($bytes < 1024) {
        return $bytes . ' B';
    }

    if ($bytes < 1024 * 1024) {
        return number_format(
            $bytes / 1024,
            1
        ) . ' KB';
    }

    return number_format(
        $bytes / (1024 * 1024),
        1
    ) . ' MB';
}

$uploadRootPath = dirname(__DIR__) . '/uploads';
$uploadBaseUrl = '../uploads';

$repository = new ImageRepository(
    $uploadRootPath,
    $uploadBaseUrl
);

$perPage = 20;
$totalImages = $repository->countAll();

$totalPages = max(
    1,
    (int) ceil($totalImages / $perPage)
);

$page = filter_input(
    INPUT_GET,
    'page',
    FILTER_VALIDATE_INT,
    [
        'options' => [
            'min_range' => 1,
        ],
    ]
);

if (!is_int($page)) {
    $page = 1;
}

if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $perPage;

$images = $repository->findAll(
    $perPage,
    $offset
);

$firstImageNumber = $totalImages === 0
    ? 0
    : $offset + 1;

$lastImageNumber = min(
    $offset + count($images),
    $totalImages
);

$buildPageUrl = static function (
    int $targetPage
): string {
    return 'image-list.php?' . http_build_query([
        'page' => $targetPage,
    ]);
};

$pageTitle = '画像管理';

require __DIR__ . '/../templates/admin-header.php';
?>

<div class="page-header">
    <div class="page-header__content">
        <h1 class="page-title">
            画像管理
        </h1>

        <p class="page-description">
            記事へアップロードした画像の確認、URLのコピー、削除ができます。
        </p>
    </div>

    <div class="page-actions">
        <a
            class="button button--outline"
            href="article-create.php"
        >
            記事を作成
        </a>
    </div>
</div>

<section class="section">
    <div class="section-header">
        <div>
            <h2 class="section-title">
                アップロード画像
            </h2>

            <span class="text-muted">
                全<?= escape((string) $totalImages) ?>件

                <?php if ($totalImages > 0): ?>
                    （<?= escape((string) $firstImageNumber) ?>
                    ～<?= escape((string) $lastImageNumber) ?>件目）
                <?php endif; ?>
            </span>
        </div>
    </div>

    <?php if ($images === []): ?>
        <div class="card">
            <p class="empty-message">
                アップロードされている画像はありません。
            </p>
        </div>
    <?php else: ?>
        <div class="image-library-grid">
            <?php foreach ($images as $image): ?>
                <article class="image-library-card">
                    <a
                        class="image-library-card__preview"
                        href="<?= escape($image['url']) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <img
                            class="image-library-card__image"
                            src="<?= escape($image['url']) ?>"
                            alt=""
                            loading="lazy"
                        >
                    </a>

                    <div class="image-library-card__body">
                        <h3 class="image-library-card__title">
                            <?= escape($image['file_name']) ?>
                        </h3>

                        <dl class="image-library-card__details">
                            <div class="image-library-card__detail">
                                <dt>形式</dt>

                                <dd>
                                    <?= escape(
                                        strtoupper(
                                            (string) $image['extension']
                                        )
                                    ) ?>
                                </dd>
                            </div>

                            <div class="image-library-card__detail">
                                <dt>画像サイズ</dt>

                                <dd>
                                    <?= escape((string) $image['width']) ?>
                                    ×
                                    <?= escape((string) $image['height']) ?>
                                    px
                                </dd>
                            </div>

                            <div class="image-library-card__detail">
                                <dt>容量</dt>

                                <dd>
                                    <?= escape(
                                        formatFileSize(
                                            (int) $image['file_size']
                                        )
                                    ) ?>
                                </dd>
                            </div>

                            <div class="image-library-card__detail">
                                <dt>アップロード日時</dt>

                                <dd>
                                    <?= escape(
                                        date(
                                            'Y年m月d日 H:i',
                                            (int) $image['uploaded_at']
                                        )
                                    ) ?>
                                </dd>
                            </div>
                        </dl>

                        <div class="image-library-card__url">
                            <label
                                class="form-label"
                                for="image-url-<?= escape(
                                    md5(
                                        (string) $image['relative_path']
                                    )
                                ) ?>"
                            >
                                画像URL
                            </label>

                            <input
                                class="form-control image-library-url"
                                id="image-url-<?= escape(
                                    md5(
                                        (string) $image['relative_path']
                                    )
                                ) ?>"
                                type="text"
                                value="<?= escape($image['url']) ?>"
                                readonly
                            >
                        </div>

                        <div class="image-library-card__actions">
                            <button
                                class="button button--small button--outline js-copy-image-url"
                                type="button"
                                data-image-url="<?= escape($image['url']) ?>"
                            >
                                URLをコピー
                            </button>

                            <form
                                method="post"
                                action="image-delete.php"
                                onsubmit="return confirm('この画像を削除しますか？記事内で使用中の場合、画像が表示されなくなります。');"
                            >
                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= escape(getCsrfToken()) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="path"
                                    value="<?= escape(
                                        $image['relative_path']
                                    ) ?>"
                                >

                                <button
                                    class="button button--small button--danger"
                                    type="submit"
                                >
                                    削除
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav
                class="pagination"
                aria-label="画像一覧のページ切り替え"
            >
                <?php if ($page > 1): ?>
                    <a
                        class="pagination__link pagination__link--previous"
                        href="<?= escape(
                            $buildPageUrl($page - 1)
                        ) ?>"
                    >
                        前へ
                    </a>
                <?php else: ?>
                    <span
                        class="pagination__link pagination__link--disabled"
                        aria-disabled="true"
                    >
                        前へ
                    </span>
                <?php endif; ?>

                <div class="pagination__pages">
                    <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                        <?php if ($pageNumber === $page): ?>
                            <span
                                class="pagination__link pagination__link--current"
                                aria-current="page"
                            >
                                <?= escape((string) $pageNumber) ?>
                            </span>
                        <?php else: ?>
                            <a
                                class="pagination__link"
                                href="<?= escape(
                                    $buildPageUrl($pageNumber)
                                ) ?>"
                            >
                                <?= escape((string) $pageNumber) ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>

                <?php if ($page < $totalPages): ?>
                    <a
                        class="pagination__link pagination__link--next"
                        href="<?= escape(
                            $buildPageUrl($page + 1)
                        ) ?>"
                    >
                        次へ
                    </a>
                <?php else: ?>
                    <span
                        class="pagination__link pagination__link--disabled"
                        aria-disabled="true"
                    >
                        次へ
                    </span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>

<script>
    'use strict';

    document
        .querySelectorAll('.js-copy-image-url')
        .forEach((button) => {
            button.addEventListener('click', async () => {
                const imageUrl = button.dataset.imageUrl;

                if (typeof imageUrl !== 'string') {
                    return;
                }

                try {
                    await navigator.clipboard.writeText(
                        imageUrl
                    );

                    const originalText = button.textContent;

                    button.textContent = 'コピーしました';

                    window.setTimeout(() => {
                        button.textContent = originalText;
                    }, 1500);
                } catch (error) {
                    console.error(error);

                    window.prompt(
                        '以下のURLをコピーしてください。',
                        imageUrl
                    );
                }
            });
        });
</script>

<?php require __DIR__ . '/../templates/admin-footer.php'; ?>