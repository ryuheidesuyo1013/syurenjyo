<?php

declare(strict_types=1);

final class HtmlArticleImporter
{
    private const MAX_FILE_SIZE = 2 * 1024 * 1024;

    /**
     * HTMLファイルを解析し、記事フォームへ渡すデータを返す。
     *
     * @return array{
     *     title: string,
     *     meta_description: string,
     *     canonical_url: string,
     *     content: string,
     *     image_paths: array<int, string>,
     *     warnings: array<int, string>
     * }
     */
    public function importFromUploadedFile(
        array $uploadedFile
    ): array {
        $this->validateUploadedFile($uploadedFile);

        $temporaryPath = (string) $uploadedFile['tmp_name'];

        $html = file_get_contents($temporaryPath);

        if ($html === false) {
            throw new RuntimeException(
                'HTMLファイルを読み込めませんでした。'
            );
        }

        return $this->importFromHtml($html);
    }

    /**
     * HTML文字列を解析する。
     *
     * @return array{
     *     title: string,
     *     meta_description: string,
     *     canonical_url: string,
     *     content: string,
     *     image_paths: array<int, string>,
     *     warnings: array<int, string>
     * }
     */
    public function importFromHtml(
        string $html
    ): array {
        if (trim($html) === '') {
            throw new RuntimeException(
                'HTMLファイルの内容が空です。'
            );
        }

        $document = $this->createDocument($html);
        $xpath = new DOMXPath($document);

        $title = $this->extractTitle($xpath);

        $metaDescription = $this->extractMetaContent(
            $xpath,
            'description'
        );

        $canonicalUrl = $this->extractCanonicalUrl($xpath);

        $contentNode = $this->findArticleContentNode($xpath);

        if (!$contentNode instanceof DOMElement) {
            throw new RuntimeException(
                '記事本文を特定できませんでした。'
            );
        }

        $workingNode = $contentNode->cloneNode(true);

        if (!$workingNode instanceof DOMElement) {
            throw new RuntimeException(
                '記事本文を複製できませんでした。'
            );
        }

        $this->removeUnnecessaryNodes($workingNode);
        $this->normalizeHeadingLevels($workingNode);
        $this->normalizeLegacyHighlights($workingNode);
        $this->removeUnwantedAttributes($workingNode);

        $imagePaths = $this->collectImagePaths($workingNode);
        $warnings = [];

        if ($imagePaths !== []) {
            $warnings[] =
                '本文内の画像はまだCMSへアップロードされていません。'
                . '画像管理画面へアップロード後、URLを差し替えてください。';
        }

        $content = $this->getInnerHtml($workingNode);

        if (trim(strip_tags($content)) === '') {
            throw new RuntimeException(
                '取り込める記事本文がありませんでした。'
            );
        }

        return [
            'title' => $title,
            'meta_description' => $metaDescription,
            'canonical_url' => $canonicalUrl,
            'content' => trim($content),
            'image_paths' => $imagePaths,
            'warnings' => $warnings,
        ];
    }

    /**
     * アップロードファイルを検証する。
     */
    private function validateUploadedFile(
        array $uploadedFile
    ): void {
        $error = isset($uploadedFile['error'])
            ? (int) $uploadedFile['error']
            : UPLOAD_ERR_NO_FILE;

        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException(
                $this->getUploadErrorMessage($error)
            );
        }

        $temporaryPath = $uploadedFile['tmp_name'] ?? '';
        $fileName = $uploadedFile['name'] ?? '';
        $fileSize = isset($uploadedFile['size'])
            ? (int) $uploadedFile['size']
            : 0;

        if (
            !is_string($temporaryPath)
            || $temporaryPath === ''
            || !is_uploaded_file($temporaryPath)
        ) {
            throw new RuntimeException(
                'アップロードされたファイルを確認できませんでした。'
            );
        }

        if (
            !is_string($fileName)
            || $fileName === ''
        ) {
            throw new RuntimeException(
                'ファイル名を確認できませんでした。'
            );
        }

        $extension = strtolower(
            pathinfo(
                $fileName,
                PATHINFO_EXTENSION
            )
        );

        if (!in_array($extension, ['html', 'htm'], true)) {
            throw new RuntimeException(
                'HTMLまたはHTM形式のファイルを選択してください。'
            );
        }

        if (
            $fileSize <= 0
            || $fileSize > self::MAX_FILE_SIZE
        ) {
            throw new RuntimeException(
                'HTMLファイルは2MB以下にしてください。'
            );
        }
    }

    /**
     * DOMDocumentを生成する。
     */
    private function createDocument(
        string $html
    ): DOMDocument {
        $document = new DOMDocument(
            '1.0',
            'UTF-8'
        );

        $previousState = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadHTML(
                '<?xml encoding="UTF-8">'
                . $html,
                LIBXML_NONET
                | LIBXML_NOERROR
                | LIBXML_NOWARNING
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousState);
        }

        if ($loaded !== true) {
            throw new RuntimeException(
                'HTMLを解析できませんでした。'
            );
        }

        return $document;
    }

    /**
     * title要素を取得する。
     */
    private function extractTitle(
        DOMXPath $xpath
    ): string {
        $node = $xpath->query('//title')->item(0);

        if (!$node instanceof DOMNode) {
            return '';
        }

        return trim($node->textContent);
    }

    /**
     * meta要素のcontent属性を取得する。
     */
    private function extractMetaContent(
        DOMXPath $xpath,
        string $name
    ): string {
        $query = sprintf(
            '//meta[translate(@name, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")="%s"]',
            strtolower($name)
        );

        $node = $xpath->query($query)->item(0);

        if (!$node instanceof DOMElement) {
            return '';
        }

        return trim(
            $node->getAttribute('content')
        );
    }

    /**
     * canonical URLを取得する。
     */
    private function extractCanonicalUrl(
        DOMXPath $xpath
    ): string {
        $node = $xpath
            ->query(
                '//link[contains(concat(" ", normalize-space(@rel), " "), " canonical ")]'
            )
            ->item(0);

        if (!$node instanceof DOMElement) {
            return '';
        }

        return trim(
            $node->getAttribute('href')
        );
    }

    /**
     * 記事本文候補を優先順で取得する。
     */
    private function findArticleContentNode(
        DOMXPath $xpath
    ): ?DOMElement {
        $queries = [
            '//*[contains(concat(" ", normalize-space(@class), " "), " article-article ")]',
            '//*[contains(concat(" ", normalize-space(@class), " "), " article-main ")]',
            '//article',
            '//main',
        ];

        foreach ($queries as $query) {
            $node = $xpath->query($query)->item(0);

            if ($node instanceof DOMElement) {
                return $node;
            }
        }

        return null;
    }

    /**
     * 目次、広告、スクリプトなど本文に不要な要素を除去する。
     */
    private function removeUnnecessaryNodes(
        DOMElement $root
    ): void {
        $xpath = new DOMXPath($root->ownerDocument);

        $queries = [
            './/script',
            './/style',
            './/noscript',
            './/iframe',
            './/*[contains(concat(" ", normalize-space(@class), " "), " article-table ")]',
            './/*[contains(concat(" ", normalize-space(@class), " "), " adsbygoogle ")]',
        ];

        foreach ($queries as $query) {
            $nodes = $xpath->query(
                $query,
                $root
            );

            if ($nodes === false) {
                continue;
            }

            $removeNodes = [];

            foreach ($nodes as $node) {
                $removeNodes[] = $node;
            }

            foreach ($removeNodes as $node) {
                $node->parentNode?->removeChild($node);
            }
        }
    }

    /**
     * 旧記事のh3/h4をCMS本文向けのh2/h3へ変換する。
     */
    private function normalizeHeadingLevels(
        DOMElement $root
    ): void {
        $document = $root->ownerDocument;

        if (!$document instanceof DOMDocument) {
            return;
        }

        $this->replaceElements(
            $root,
            'h3',
            'h2',
            $document
        );

        $this->replaceElements(
            $root,
            'h4',
            'h3',
            $document
        );
    }

    /**
     * 旧CSSの強調spanをmarkへ変換する。
     */
    private function normalizeLegacyHighlights(
        DOMElement $root
    ): void {
        $document = $root->ownerDocument;

        if (!$document instanceof DOMDocument) {
            return;
        }

        $xpath = new DOMXPath($document);

        $nodes = $xpath->query(
            './/span[contains(concat(" ", normalize-space(@class), " "), " heighlight ")]',
            $root
        );

        if ($nodes === false) {
            return;
        }

        $targets = [];

        foreach ($nodes as $node) {
            if ($node instanceof DOMElement) {
                $targets[] = $node;
            }
        }

        foreach ($targets as $node) {
            $replacement = $document->createElement('mark');

            while ($node->firstChild !== null) {
                $replacement->appendChild(
                    $node->firstChild
                );
            }

            $node->parentNode?->replaceChild(
                $replacement,
                $node
            );
        }
    }

    /**
     * 不要なclass/style/event属性を除去する。
     */
    private function removeUnwantedAttributes(
        DOMElement $root
    ): void {
        $elements = [
            $root,
        ];

        foreach ($root->getElementsByTagName('*') as $element) {
            if ($element instanceof DOMElement) {
                $elements[] = $element;
            }
        }

        foreach ($elements as $element) {
            $attributesToRemove = [];

            foreach ($element->attributes as $attribute) {
                $name = strtolower($attribute->name);

                if (
                    $name === 'class'
                    || $name === 'style'
                    || str_starts_with($name, 'on')
                ) {
                    $attributesToRemove[] = $attribute->name;
                }
            }

            foreach ($attributesToRemove as $attributeName) {
                $element->removeAttribute($attributeName);
            }

            if ($element->tagName === 'a') {
                $href = trim(
                    $element->getAttribute('href')
                );

                if (
                    $href === ''
                    || str_starts_with(
                        strtolower($href),
                        'javascript:'
                    )
                ) {
                    $element->removeAttribute('href');
                }
            }

            if ($element->tagName === 'img') {
                $element->removeAttribute('width');
                $element->removeAttribute('height');
            }
        }
    }

    /**
     * 指定タグを別タグへ置換する。
     */
    private function replaceElements(
        DOMElement $root,
        string $oldTagName,
        string $newTagName,
        DOMDocument $document
    ): void {
        $nodes = [];

        foreach ($root->getElementsByTagName($oldTagName) as $node) {
            if ($node instanceof DOMElement) {
                $nodes[] = $node;
            }
        }

        foreach ($nodes as $node) {
            $replacement = $document->createElement(
                $newTagName
            );

            if ($node->hasAttribute('id')) {
                $replacement->setAttribute(
                    'id',
                    $node->getAttribute('id')
                );
            }

            while ($node->firstChild !== null) {
                $replacement->appendChild(
                    $node->firstChild
                );
            }

            $node->parentNode?->replaceChild(
                $replacement,
                $node
            );
        }
    }

    /**
     * 本文内画像のsrc一覧を取得する。
     *
     * @return array<int, string>
     */
    private function collectImagePaths(
        DOMElement $root
    ): array {
        $paths = [];

        foreach ($root->getElementsByTagName('img') as $image) {
            if (!$image instanceof DOMElement) {
                continue;
            }

            $source = trim(
                $image->getAttribute('src')
            );

            if ($source === '') {
                continue;
            }

            $paths[] = $source;
        }

        return array_values(
            array_unique($paths)
        );
    }

    /**
     * 要素内のHTMLを取得する。
     */
    private function getInnerHtml(
        DOMElement $element
    ): string {
        $document = $element->ownerDocument;

        if (!$document instanceof DOMDocument) {
            return '';
        }

        $html = '';

        foreach ($element->childNodes as $childNode) {
            $fragment = $document->saveHTML(
                $childNode
            );

            if ($fragment !== false) {
                $html .= $fragment;
            }
        }

        return $html;
    }

    /**
     * アップロードエラーのメッセージを返す。
     */
    private function getUploadErrorMessage(
        int $errorCode
    ): string {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE
                => 'HTMLファイルのサイズが上限を超えています。',

            UPLOAD_ERR_PARTIAL
                => 'HTMLファイルのアップロードが途中で中断されました。',

            UPLOAD_ERR_NO_FILE
                => 'HTMLファイルが選択されていません。',

            UPLOAD_ERR_NO_TMP_DIR
                => '一時保存フォルダが見つかりません。',

            UPLOAD_ERR_CANT_WRITE
                => 'HTMLファイルを一時保存できませんでした。',

            UPLOAD_ERR_EXTENSION
                => 'サーバー設定によりアップロードが停止されました。',

            default
                => 'HTMLファイルのアップロードに失敗しました。',
        };
    }
}