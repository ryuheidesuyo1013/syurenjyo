<?php

declare(strict_types=1);

if (!isset($pageTitle) || !is_string($pageTitle) || $pageTitle === '') {
    $pageTitle = '蹴練場';
}

if (!isset($metaDescription) || !is_string($metaDescription)) {
    $metaDescription = '';
}

if (!isset($canonicalUrl) || !is_string($canonicalUrl)) {
    $canonicalUrl = '';
}

if (!isset($ogImage) || !is_string($ogImage)) {
    $ogImage = '';
}

if (!isset($ogType) || !is_string($ogType) || $ogType === '') {
    $ogType = 'website';
}

if (!isset($robots) || !is_string($robots) || $robots === '') {
    $robots = 'index, follow';
}

if (!isset($bodyClass) || !is_string($bodyClass)) {
    $bodyClass = '';
}

$siteName = '蹴練場';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title><?= escape($pageTitle) ?></title>

    <?php if ($metaDescription !== ''): ?>
        <meta
            name="description"
            content="<?= escape($metaDescription) ?>"
        >
    <?php endif; ?>

    <meta
        name="robots"
        content="<?= escape($robots) ?>"
    >

    <?php if ($canonicalUrl !== ''): ?>
        <link
            rel="canonical"
            href="<?= escape($canonicalUrl) ?>"
        >
    <?php endif; ?>

    <meta
        property="og:site_name"
        content="<?= escape($siteName) ?>"
    >

    <meta
        property="og:type"
        content="<?= escape($ogType) ?>"
    >

    <meta
        property="og:title"
        content="<?= escape($pageTitle) ?>"
    >

    <?php if ($metaDescription !== ''): ?>
        <meta
            property="og:description"
            content="<?= escape($metaDescription) ?>"
        >
    <?php endif; ?>

    <?php if ($canonicalUrl !== ''): ?>
        <meta
            property="og:url"
            content="<?= escape($canonicalUrl) ?>"
        >
    <?php endif; ?>

    <?php if ($ogImage !== ''): ?>
        <meta
            property="og:image"
            content="<?= escape($ogImage) ?>"
        >

        <meta
            name="twitter:card"
            content="summary_large_image"
        >
    <?php else: ?>
        <meta
            name="twitter:card"
            content="summary"
        >
    <?php endif; ?>

    <meta
        name="twitter:title"
        content="<?= escape($pageTitle) ?>"
    >

    <?php if ($metaDescription !== ''): ?>
        <meta
            name="twitter:description"
            content="<?= escape($metaDescription) ?>"
        >
    <?php endif; ?>

    <link
        rel="stylesheet"
        href="css/style.css"
    >
</head>

<body<?= $bodyClass !== ''
    ? ' class="' . escape($bodyClass) . '"'
    : '' ?>>