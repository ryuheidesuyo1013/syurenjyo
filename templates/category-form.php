<?php

declare(strict_types=1);

if (
    !isset($category)
    || !isset($formAction)
    || !isset($submitLabel)
) {
    throw new RuntimeException(
        'フォームに必要な変数が設定されていません。'
    );
}
?>

<form method="post" action="<?= escape($formAction) ?>">
    <input
        type="hidden"
        name="csrf_token"
        value="<?= escape(getCsrfToken()) ?>"
    >

    <div>
        <label for="name">カテゴリ名</label>

        <input
            type="text"
            id="name"
            name="name"
            value="<?= escape($category['name'] ?? '') ?>"
            maxlength="100"
            required
        >
    </div>

    <div>
        <label for="slug">スラッグ</label>

        <input
            type="text"
            id="slug"
            name="slug"
            value="<?= escape($category['slug'] ?? '') ?>"
            maxlength="100"
            pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
            required
        >

        <p>
            半角英小文字・数字・ハイフンで入力してください。
            例：physical-training
        </p>
    </div>

    <button type="submit">
        <?= escape($submitLabel) ?>
    </button>
</form>