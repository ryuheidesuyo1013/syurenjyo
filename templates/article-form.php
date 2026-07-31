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

$selectedStatus = isset($article['status'])
    ? (string) $article['status']
    : 'draft';
?>

<form
    class="js-article-form"
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
            for="title"
        >
            タイトル

            <span class="form-required">
                必須
            </span>
        </label>

        <input
            class="form-control"
            type="text"
            id="title"
            name="title"
            value="<?= escape($article['title'] ?? '') ?>"
            maxlength="255"
            autocomplete="off"
            required
        >

        <p class="form-help">
            記事一覧や記事ページに表示されるタイトルです。
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
            value="<?= escape($article['slug'] ?? '') ?>"
            maxlength="255"
            pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
            autocomplete="off"
            required
        >

        <p class="form-help">
            半角英小文字・数字・ハイフンで入力してください。
            例：shoot-training-basic
        </p>
    </div>

    <div class="form-group">
        <label
            class="form-label"
            for="category_id"
        >
            カテゴリ

            <span class="form-required">
                必須
            </span>
        </label>

        <select
            class="form-control"
            id="category_id"
            name="category_id"
            required
        >
            <option value="">
                カテゴリを選択してください
            </option>

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

        <?php if ($categories === []): ?>
            <p class="form-help">
                利用できるカテゴリがありません。
                先にカテゴリを作成してください。
            </p>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label
            class="form-label"
            for="summary"
        >
            概要
        </label>

        <textarea
            class="form-control"
            id="summary"
            name="summary"
            rows="4"
            maxlength="500"
        ><?= escape($article['summary'] ?? '') ?></textarea>

        <p class="form-help">
            記事の内容を簡潔に説明してください。
        </p>
    </div>

    <div class="form-group">
        <span class="form-label">
            本文

            <span class="form-required">
                必須
            </span>
        </span>

        <div
            id="article-toolbar"
            class="article-editor-toolbar"
            hidden
        >
            <span class="ql-formats">
                <select
                    class="ql-header"
                    aria-label="見出し"
                >
                    <option value="2">見出し2</option>
                    <option value="3">見出し3</option>
                    <option selected>本文</option>
                </select>

                <select
                    class="ql-size"
                    aria-label="文字サイズ"
                >
                    <option value="small">小</option>
                    <option selected>標準</option>
                    <option value="large">大</option>
                    <option value="huge">特大</option>
                </select>
            </span>

            <span class="ql-formats">
                <select
                    class="ql-color"
                    aria-label="文字色"
                ></select>

                <select
                    class="ql-background"
                    aria-label="背景色"
                ></select>
            </span>

            <span class="ql-formats">
                <button
                    class="ql-bold"
                    type="button"
                    aria-label="太字"
                ></button>

                <button
                    class="ql-italic"
                    type="button"
                    aria-label="斜体"
                ></button>

                <button
                    class="ql-underline"
                    type="button"
                    aria-label="下線"
                ></button>

                <button
                    class="ql-strike"
                    type="button"
                    aria-label="打ち消し線"
                ></button>
            </span>

            <span class="ql-formats">
                <select
                    class="ql-align"
                    aria-label="文字揃え"
                ></select>
            </span>

            <span class="ql-formats">
                <button
                    class="ql-list"
                    type="button"
                    value="ordered"
                    aria-label="番号付きリスト"
                ></button>

                <button
                    class="ql-list"
                    type="button"
                    value="bullet"
                    aria-label="箇条書き"
                ></button>

                <button
                    class="ql-indent"
                    type="button"
                    value="-1"
                    aria-label="インデントを減らす"
                ></button>

                <button
                    class="ql-indent"
                    type="button"
                    value="+1"
                    aria-label="インデントを増やす"
                ></button>
            </span>

            <span class="ql-formats">
                <button
                    class="ql-blockquote"
                    type="button"
                    aria-label="引用"
                ></button>

                <button
                    class="ql-code-block"
                    type="button"
                    aria-label="コードブロック"
                ></button>

                <button
                    class="ql-link"
                    type="button"
                    aria-label="リンク"
                ></button>

                <button
                    class="ql-clean"
                    type="button"
                    aria-label="書式を解除"
                ></button>
            </span>
        </div>

        <div
            id="article-editor"
            class="article-editor"
            aria-label="記事本文エディター"
            hidden
        ></div>

        <textarea
            class="form-control js-article-content article-content-source"
            id="content"
            name="content"
            rows="18"
            required
        ><?= escape($article['content'] ?? '') ?></textarea>

        <p class="form-help">
            見出し、文字サイズ、文字色、背景色、文字揃え、インデント、
            打ち消し線、コードブロックなどを使用できます。
            リンクは文字を選択してから押してください。
        </p>
    </div>

    <div class="form-group">
        <label
            class="form-label"
            for="status"
        >
            公開状態
        </label>

        <select
            class="form-control"
            id="status"
            name="status"
        >
            <option
                value="draft"
                <?= $selectedStatus === 'draft'
                    ? 'selected'
                    : '' ?>
            >
                下書き
            </option>

            <option
                value="published"
                <?= $selectedStatus === 'published'
                    ? 'selected'
                    : '' ?>
            >
                公開
            </option>
        </select>

        <p class="form-help">
            「公開」を選択すると、保存後に公開記事として表示されます。
        </p>
    </div>

    <div class="form-actions">
        <button
            class="button"
            type="submit"
            <?= $categories === []
                ? 'disabled'
                : '' ?>
        >
            <?= escape($submitLabel) ?>
        </button>

        <a
            class="button button--outline"
            href="article-list.php"
        >
            キャンセル
        </a>
    </div>
</form>