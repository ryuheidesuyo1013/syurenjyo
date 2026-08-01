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
                    class="ql-image"
                    type="button"
                    aria-label="画像を挿入"
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
            打ち消し線、コードブロック、画像などを使用できます。
            リンクは文字を選択してから押してください。
            画像はJPEG・PNG・WebP形式、5MB以下にしてください。
        </p>
    </div>


    <div class="form-group">
        <div class="seo-settings">
            <div class="seo-settings__header">
                <div>
                    <h2 class="seo-settings__title">
                        SEO設定
                    </h2>

                    <p class="seo-settings__description">
                        未入力の場合は、記事タイトルや概要など既存の情報を利用します。
                    </p>
                </div>
            </div>

            <div class="seo-settings__body">
                <div class="form-group">
                    <label
                        class="form-label"
                        for="seo_title"
                    >
                        SEOタイトル
                    </label>

                    <input
                        class="form-control"
                        type="text"
                        id="seo_title"
                        name="seo_title"
                        value="<?= escape($article['seo_title'] ?? '') ?>"
                        maxlength="255"
                        autocomplete="off"
                    >

                    <p class="form-help">
                        検索結果やSNS共有時に使うタイトルです。
                        未入力の場合は記事タイトルを使用します。
                    </p>
                </div>

                <div class="form-group">
                    <label
                        class="form-label"
                        for="meta_description"
                    >
                        メタディスクリプション
                    </label>

                    <textarea
                        class="form-control"
                        id="meta_description"
                        name="meta_description"
                        rows="4"
                        maxlength="320"
                    ><?= escape($article['meta_description'] ?? '') ?></textarea>

                    <p class="form-help">
                        検索結果に表示される記事説明です。
                        未入力の場合は概要を使用します。
                    </p>
                </div>

                <div class="form-group">
                    <label
                        class="form-label"
                        for="og_image"
                    >
                        OGP画像URL
                    </label>

                    <input
                        class="form-control"
                        type="url"
                        id="og_image"
                        name="og_image"
                        value="<?= escape($article['og_image'] ?? '') ?>"
                        maxlength="500"
                        placeholder="https://example.com/uploads/2026/08/image.webp"
                        autocomplete="url"
                    >

                    <p class="form-help">
                        SNSで共有されたときに表示する画像URLです。
                        画像管理画面からURLをコピーして入力できます。
                    </p>
                </div>

                <div class="form-group">
                    <label
                        class="form-label"
                        for="canonical_url"
                    >
                        canonical URL
                    </label>

                    <input
                        class="form-control"
                        type="url"
                        id="canonical_url"
                        name="canonical_url"
                        value="<?= escape($article['canonical_url'] ?? '') ?>"
                        maxlength="500"
                        placeholder="https://example.com/article-page"
                        autocomplete="url"
                    >

                    <p class="form-help">
                        同じ内容のページが複数ある場合に、優先するURLを指定します。
                        通常は未入力で問題ありません。
                    </p>
                </div>

                <div class="form-group">
                    <label class="checkbox-field">
                        <input
                            class="checkbox-field__input"
                            type="checkbox"
                            name="noindex"
                            value="1"
                            <?= !empty($article['noindex'])
                                ? 'checked'
                                : '' ?>
                        >

                        <span class="checkbox-field__content">
                            <span class="checkbox-field__label">
                                検索エンジンに登録させない
                            </span>

                            <span class="checkbox-field__help">
                                下書き確認用や一時的な記事など、
                                検索結果へ表示したくない場合に選択します。
                            </span>
                        </span>
                    </label>
                </div>
            </div>
        </div>
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