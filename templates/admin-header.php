<?php

declare(strict_types=1);

if (!isset($pageTitle) || !is_string($pageTitle)) {
    $pageTitle = '管理画面';
}

$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');

$articlePages = [
    'article-list.php',
    'article-create.php',
    'article-edit.php',
    'article-delete.php',
];

$categoryPages = [
    'category-list.php',
    'category-create.php',
    'category-edit.php',
    'category-delete.php',
];

$imagePages = [
    'image-list.php',
    'image-delete.php',
];

$isDashboardPage = $currentPage === 'index.php';
$isArticlePage = in_array($currentPage, $articlePages, true);
$isCategoryPage = in_array($currentPage, $categoryPages, true);
$isImagePage = in_array($currentPage, $imagePages, true);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= escape($pageTitle) ?>｜蹴練場 管理画面
    </title>

    <link
        rel="stylesheet"
        href="../public/css/admin.css"
    >
</head>

<body>
    <div class="admin-layout">
        <aside id="admin-sidebar" class="admin-sidebar">
            <div class="admin-sidebar__header">
                <a
                    class="admin-logo"
                    href="index.php"
                >
                    <span class="admin-logo__title">
                        蹴練場
                    </span>

                    <span class="admin-logo__subtitle">
                        管理画面
                    </span>
                </a>
            </div>

            <nav
                class="admin-navigation"
                aria-label="管理画面メニュー"
            >
                <ul class="admin-navigation__list">
                    <li class="admin-navigation__item">
                        <a
                            class="admin-navigation__link<?= $isDashboardPage
                                ? ' is-active'
                                : '' ?>"
                            href="index.php"
                            <?= $isDashboardPage
                                ? 'aria-current="page"'
                                : '' ?>
                        >
                            ダッシュボード
                        </a>
                    </li>

                    <li class="admin-navigation__item">
                        <a
                            class="admin-navigation__link<?= $isArticlePage
                                ? ' is-active'
                                : '' ?>"
                            href="article-list.php"
                            <?= $isArticlePage
                                ? 'aria-current="page"'
                                : '' ?>
                        >
                            記事管理
                        </a>
                    </li>

                    <li class="admin-navigation__item">
                        <a
                            class="admin-navigation__link<?= $isCategoryPage
                                ? ' is-active'
                                : '' ?>"
                            href="category-list.php"
                            <?= $isCategoryPage
                                ? 'aria-current="page"'
                                : '' ?>
                        >
                            カテゴリ管理
                        </a>
                    </li>

                    <li class="admin-navigation__item">
                        <a
                            class="admin-navigation__link<?= $isImagePage
                                ? ' is-active'
                                : '' ?>"
                            href="image-list.php"
                            <?= $isImagePage
                                ? 'aria-current="page"'
                                : '' ?>
                        >
                            画像管理
                        </a>
                    </li>

                    <li class="admin-navigation__item">
                        <a
                            class="admin-navigation__link"
                            href="../public/articles.php"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            公開記事一覧
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="admin-sidebar__footer">
                <p class="admin-user">
                    <span class="admin-user__label">
                        ログイン中
                    </span>

                    <span class="admin-user__name">
                        <?= escape($_SESSION['admin_username'] ?? '') ?>
                    </span>
                </p>

                <form
                    class="admin-logout-form"
                    method="post"
                    action="logout.php"
                >
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= escape(getCsrfToken()) ?>"
                    >

                    <button
                        class="button button--secondary button--full"
                        type="submit"
                    >
                        ログアウト
                    </button>
                </form>
            </div>
        </aside>

        <div class="admin-content">
            <header class="admin-topbar">
                <button
                    class="admin-menu-button"
                    type="button"
                    aria-label="管理メニューを開閉"
                    aria-expanded="false"
                    aria-controls="admin-sidebar"
                >
                    メニュー
                </button>

                <p class="admin-topbar__title">
                    <?= escape($pageTitle) ?>
                </p>
            </header>

            <main class="admin-main">