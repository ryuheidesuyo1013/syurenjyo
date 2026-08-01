<?php

declare(strict_types=1);

if (
    !isset($breadcrumbs)
    || !is_array($breadcrumbs)
) {
    throw new RuntimeException(
        'パンくずリストのデータが設定されていません。'
    );
}

$normalizedBreadcrumbs = [];

foreach ($breadcrumbs as $breadcrumb) {
    if (!is_array($breadcrumb)) {
        continue;
    }

    $label = $breadcrumb['label'] ?? '';
    $url = $breadcrumb['url'] ?? '';

    if (!is_string($label) || trim($label) === '') {
        continue;
    }

    if (!is_string($url)) {
        $url = '';
    }

    $normalizedBreadcrumbs[] = [
        'label' => trim($label),
        'url' => trim($url),
    ];
}

if ($normalizedBreadcrumbs === []) {
    return;
}
?>

<nav
    class="breadcrumb"
    aria-label="パンくずリスト"
>
    <ol class="breadcrumb__list">
        <?php foreach ($normalizedBreadcrumbs as $index => $breadcrumb): ?>
            <?php
            $isCurrent = $index === count($normalizedBreadcrumbs) - 1;
            ?>

            <li class="breadcrumb__item">
                <?php if (!$isCurrent && $breadcrumb['url'] !== ''): ?>
                    <a
                        class="breadcrumb__link"
                        href="<?= escape($breadcrumb['url']) ?>"
                    >
                        <?= escape($breadcrumb['label']) ?>
                    </a>
                <?php else: ?>
                    <span
                        class="breadcrumb__current"
                        <?= $isCurrent
                            ? 'aria-current="page"'
                            : '' ?>
                    >
                        <?= escape($breadcrumb['label']) ?>
                    </span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>