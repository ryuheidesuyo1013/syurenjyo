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

<form
    method="post"
    action="<?= escape($formAction) ?>"
>
    <input
        type="hidden"
        name="csrf_token"
        value="<?= escape(getCsrfToken()) ?>"
    >

    <div class="form-group">
        <label
            class="form-label"
            for="name"
        >
            カテゴリ名

            <span class="form-required">
                必須
            </span>
        </label>

        <input
            class="form-control"
            type="text"
            id="name"
            name="name"
            value="<?= escape($category['name'] ?? '') ?>"
            maxlength="100"
            autocomplete="off"
            required
        >

        <p class="form-help">
            記事の分類として管理画面や公開ページに表示される名称です。
        </p>
    </div>

    <div class="form-group">
        <label
            class="form-label"
            for="slug"
        >
            スラッグ

            <span class="form-required">
                必須
            </span>
        </label>

        <input
            class="form-control"
            type="text"
            id="slug"
            name="slug"
            value="<?= escape($category['slug'] ?? '') ?>"
            maxlength="100"
            pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
            autocomplete="off"
            required
        >

        <p class="form-help">
            半角英小文字・数字・ハイフンで入力してください。
            例：physical-training
        </p>
    </div>

    <div class="form-actions">
        <button
            class="button"
            type="submit"
        >
            <?= escape($submitLabel) ?>
        </button>

        <a
            class="button button--outline"
            href="category-list.php"
        >
            キャンセル
        </a>
    </div>
</form>