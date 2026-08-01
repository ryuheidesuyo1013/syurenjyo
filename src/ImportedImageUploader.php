<?php

declare(strict_types=1);

final class ImportedImageUploader
{
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct(
        private string $uploadRootPath,
        private string $uploadBaseUrl
    ) {
        $this->uploadRootPath = rtrim(
            $this->uploadRootPath,
            DIRECTORY_SEPARATOR
        );

        $this->uploadBaseUrl = rtrim(
            $this->uploadBaseUrl,
            '/'
        );
    }

    /**
     * 複数画像を保存し、元ファイル名と新URLの対応表を返す。
     *
     * @return array<string, string>
     */
    public function uploadMultiple(
        array $uploadedFiles
    ): array {
        $normalizedFiles = $this->normalizeFilesArray(
            $uploadedFiles
        );

        if ($normalizedFiles === []) {
            return [];
        }

        $mapping = [];

        foreach ($normalizedFiles as $uploadedFile) {
            $originalName = (string) $uploadedFile['name'];

            $imageUrl = $this->uploadSingle(
                $uploadedFile
            );

            $mapping[$originalName] = $imageUrl;
        }

        return $mapping;
    }

    /**
     * HTML本文内の画像srcを、アップロード後のURLへ差し替える。
     */
    public function replaceImageSources(
        string $html,
        array $imageMapping
    ): string {
        if (
            trim($html) === ''
            || $imageMapping === []
        ) {
            return $html;
        }

        $document = new DOMDocument(
            '1.0',
            'UTF-8'
        );

        $previousState = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadHTML(
                '<?xml encoding="UTF-8">'
                . '<div id="import-root">'
                . $html
                . '</div>',
                LIBXML_NONET
                | LIBXML_NOERROR
                | LIBXML_NOWARNING
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousState);
        }

        if ($loaded !== true) {
            return $html;
        }

        $xpath = new DOMXPath($document);

        $root = $xpath
            ->query('//*[@id="import-root"]')
            ->item(0);

        if (!$root instanceof DOMElement) {
            return $html;
        }

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

            $path = parse_url(
                $source,
                PHP_URL_PATH
            );

            if (!is_string($path) || $path === '') {
                $path = $source;
            }

            $fileName = rawurldecode(
                basename($path)
            );

            if (
                $fileName !== ''
                && isset($imageMapping[$fileName])
            ) {
                $image->setAttribute(
                    'src',
                    $imageMapping[$fileName]
                );

                $image->setAttribute(
                    'loading',
                    'lazy'
                );
            }
        }

        return trim(
            $this->getInnerHtml($root)
        );
    }

    /**
     * 単一画像を検証して保存する。
     */
    private function uploadSingle(
        array $uploadedFile
    ): string {
        $errorCode = isset($uploadedFile['error'])
            ? (int) $uploadedFile['error']
            : UPLOAD_ERR_NO_FILE;

        if ($errorCode !== UPLOAD_ERR_OK) {
            throw new RuntimeException(
                $this->getUploadErrorMessage($errorCode)
            );
        }

        $temporaryPath = $uploadedFile['tmp_name'] ?? '';
        $fileSize = isset($uploadedFile['size'])
            ? (int) $uploadedFile['size']
            : 0;

        if (
            !is_string($temporaryPath)
            || $temporaryPath === ''
            || !is_uploaded_file($temporaryPath)
        ) {
            throw new RuntimeException(
                'アップロードされた画像を確認できませんでした。'
            );
        }

        if (
            $fileSize <= 0
            || $fileSize > self::MAX_FILE_SIZE
        ) {
            throw new RuntimeException(
                '画像は1枚につき5MB以下にしてください。'
            );
        }

        $finfo = new finfo(
            FILEINFO_MIME_TYPE
        );

        $mimeType = $finfo->file(
            $temporaryPath
        );

        if (
            !is_string($mimeType)
            || !isset(self::ALLOWED_MIME_TYPES[$mimeType])
        ) {
            throw new RuntimeException(
                'JPEG・PNG・WebP形式の画像だけアップロードできます。'
            );
        }

        if (@getimagesize($temporaryPath) === false) {
            throw new RuntimeException(
                '有効な画像ファイルではありません。'
            );
        }

        $year = date('Y');
        $month = date('m');

        $uploadDirectory = $this->uploadRootPath
            . DIRECTORY_SEPARATOR
            . $year
            . DIRECTORY_SEPARATOR
            . $month;

        if (
            !is_dir($uploadDirectory)
            && !mkdir(
                $uploadDirectory,
                0755,
                true
            )
            && !is_dir($uploadDirectory)
        ) {
            throw new RuntimeException(
                '画像保存フォルダを作成できませんでした。'
            );
        }

        $extension = self::ALLOWED_MIME_TYPES[$mimeType];

        try {
            $fileName = bin2hex(
                random_bytes(16)
            ) . '.' . $extension;
        } catch (Throwable $exception) {
            throw new RuntimeException(
                '画像ファイル名を生成できませんでした。'
            );
        }

        $destinationPath = $uploadDirectory
            . DIRECTORY_SEPARATOR
            . $fileName;

        if (
            !move_uploaded_file(
                $temporaryPath,
                $destinationPath
            )
        ) {
            throw new RuntimeException(
                '画像を保存できませんでした。'
            );
        }

        @chmod(
            $destinationPath,
            0644
        );

        return $this->uploadBaseUrl
            . '/'
            . $year
            . '/'
            . $month
            . '/'
            . rawurlencode($fileName);
    }

    /**
     * PHPの複数ファイル配列を扱いやすい形へ変換する。
     *
     * @return array<int, array{
     *     name: string,
     *     type: string,
     *     tmp_name: string,
     *     error: int,
     *     size: int
     * }>
     */
    private function normalizeFilesArray(
        array $uploadedFiles
    ): array {
        $names = $uploadedFiles['name'] ?? [];

        if (!is_array($names)) {
            return [];
        }

        $files = [];

        foreach ($names as $index => $name) {
            if (
                !is_string($name)
                || $name === ''
            ) {
                continue;
            }

            $files[] = [
                'name' => $name,
                'type' => (string) (
                    $uploadedFiles['type'][$index] ?? ''
                ),
                'tmp_name' => (string) (
                    $uploadedFiles['tmp_name'][$index] ?? ''
                ),
                'error' => (int) (
                    $uploadedFiles['error'][$index]
                    ?? UPLOAD_ERR_NO_FILE
                ),
                'size' => (int) (
                    $uploadedFiles['size'][$index] ?? 0
                ),
            ];
        }

        return $files;
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
                => '画像のファイルサイズが上限を超えています。',

            UPLOAD_ERR_PARTIAL
                => '画像のアップロードが途中で中断されました。',

            UPLOAD_ERR_NO_FILE
                => '画像ファイルが選択されていません。',

            UPLOAD_ERR_NO_TMP_DIR
                => '一時保存フォルダが見つかりません。',

            UPLOAD_ERR_CANT_WRITE
                => '画像を一時保存できませんでした。',

            UPLOAD_ERR_EXTENSION
                => 'サーバー設定により画像アップロードが停止されました。',

            default
                => '画像のアップロードに失敗しました。',
        };
    }
}