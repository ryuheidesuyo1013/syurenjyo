<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/helpers.php';

http_response_code(404);

$pageTitle = 'ページが見つかりません｜蹴練場';
$metaDescription = 'お探しのページは移動または削除された可能性があります。';
$canonicalUrl = '';
$ogImage = '';
$ogType = 'website';
$robots = 'noindex, follow';
$bodyClass = 'error-page error-page--404';

require __DIR__ . '/../templates/public-header.php';
?>

    <main class="site-main">
        <div class="site-container error-page__container">
            <section
                class="error-card"
                aria-labelledby="error-title"
            >
                <p class="error-card__code">
                    404
                </p>

                <h1
                    class="error-card__title"
                    id="error-title"
                >
                    ページが見つかりません
                </h1>

                <p class="error-card__description">
                    URLが間違っているか、ページが移動・削除された可能性があります。
                </p>

                <div class="error-card__actions">
                    <a
                        class="public-button"
                        href="https://蹴練場.jp/"
                    >
                        ホームへ戻る
                    </a>

                    <a
                        class="public-button public-button--outline"
                        href="articles.php"
                    >
                        役立つコラムを見る
                    </a>
                </div>
            </section>
        </div>
    </main>

<?php require __DIR__ . '/../templates/public-footer.php'; ?>