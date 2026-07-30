<?php

declare(strict_types=1);

if (
    !isset($article)
    || !isset($categories)
    || !isset($formAction)
    || !isset($submitLabel)
) {
    throw new RuntimeException(
        'フォームに必要な変数が設定されていません。'
    );
}

$selectedCategoryId = isset($article['category_id'])
    ? (int) $article['category_id']
    : 0;
?>

<form method="post" action="<?= escape($formAction) ?>">
    <input
        type="hidden"
        name="csrf_token"
        value="<?= escape(getCsrfToken()) ?>"
    >

    <div>
        <label for="title">タイトル</label>

        <input
            type="text"
            id="title"
            name="title"
            value="<?= escape($article['title'] ?? '') ?>"
            required
        >
    </div>

    <div>
        <label for="slug">スラッグ</label>

        <input
            type="text"
            id="slug"
            name="slug"
            value="<?= escape($article['slug'] ?? '') ?>"
            required
        >

        <p>例：shoot-training-basic</p>
    </div>

    <div>
        <label for="category_id">カテゴリ</label>

        <select
            id="category_id"
            name="category_id"
            required
        >
            <option value="">カテゴリを選択してください</option>

            <?php foreach ($categories as $category): ?>
                <?php
                $categoryId = (int) $category['id'];
                $categoryName = (string) $category['name'];
                ?>

                <option
                    value="<?= $categoryId ?>"
                    <?= $selectedCategoryId === $categoryId
                        ? 'selected'
                        : '' ?>
                >
                    <?= escape($categoryName) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="summary">概要</label>

        <textarea
            id="summary"
            name="summary"
            rows="4"
        ><?= escape($article['summary'] ?? '') ?></textarea>
    </div>

    <div>
        <label for="content">本文</label>

        <textarea
            id="content"
            name="content"
            rows="15"
            required
        ><?= escape($article['content'] ?? '') ?></textarea>
    </div>

    <div>
        <label for="status">公開状態</label>

        <select id="status" name="status">
            <option
                value="draft"
                <?= ($article['status'] ?? 'draft') === 'draft'
                    ? 'selected'
                    : '' ?>
            >
                下書き
            </option>

            <option
                value="published"
                <?= ($article['status'] ?? '') === 'published'
                    ? 'selected'
                    : '' ?>
            >
                公開
            </option>
        </select>
    </div>

    <button type="submit">
        <?= escape($submitLabel) ?>
    </button>
</form>