<?php

declare(strict_types=1);

final class ImageRepository
{
    private const ALLOWED_EXTENSIONS = [
        'jpg',
        'jpeg',
        'png',
        'webp',
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
     * 登録されている画像の総件数を取得する。
     */
    public function countAll(): int
    {
        return count($this->findAllFiles());
    }

    /**
     * 指定された範囲の画像を取得する。
     *
     * @return array<int, array{
     *     relative_path: string,
     *     absolute_path: string,
     *     url: string,
     *     file_name: string,
     *     extension: string,
     *     mime_type: string,
     *     file_size: int,
     *     width: int,
     *     height: int,
     *     uploaded_at: int
     * }>
     */
    public function findAll(
        int $limit = 20,
        int $offset = 0
    ): array {
        $limit = max(1, $limit);
        $offset = max(0, $offset);

        $files = $this->findAllFiles();

        usort(
            $files,
            static function (
                array $first,
                array $second
            ): int {
                return $second['uploaded_at']
                    <=> $first['uploaded_at'];
            }
        );

        return array_slice(
            $files,
            $offset,
            $limit
        );
    }

    /**
     * 相対パスを指定して画像情報を取得する。
     *
     * @return array{
     *     relative_path: string,
     *     absolute_path: string,
     *     url: string,
     *     file_name: string,
     *     extension: string,
     *     mime_type: string,
     *     file_size: int,
     *     width: int,
     *     height: int,
     *     uploaded_at: int
     * }|false
     */
    public function findByRelativePath(
        string $relativePath
    ): array|false {
        $normalizedPath = $this->normalizeRelativePath(
            $relativePath
        );

        if ($normalizedPath === null) {
            return false;
        }

        $absolutePath = $this->uploadRootPath
            . DIRECTORY_SEPARATOR
            . str_replace(
                '/',
                DIRECTORY_SEPARATOR,
                $normalizedPath
            );

        if (
            !is_file($absolutePath)
            || !$this->isPathInsideUploadRoot($absolutePath)
            || !$this->isAllowedImage($absolutePath)
        ) {
            return false;
        }

        return $this->buildImageData(
            $absolutePath,
            $normalizedPath
        );
    }

    /**
     * 相対パスを指定して画像を削除する。
     */
    public function delete(
        string $relativePath
    ): bool {
        $image = $this->findByRelativePath(
            $relativePath
        );

        if ($image === false) {
            return false;
        }

        if (!unlink($image['absolute_path'])) {
            return false;
        }

        $this->removeEmptyParentDirectories(
            dirname($image['absolute_path'])
        );

        return true;
    }

    /**
     * uploads配下の画像ファイルをすべて取得する。
     *
     * @return array<int, array{
     *     relative_path: string,
     *     absolute_path: string,
     *     url: string,
     *     file_name: string,
     *     extension: string,
     *     mime_type: string,
     *     file_size: int,
     *     width: int,
     *     height: int,
     *     uploaded_at: int
     * }>
     */
    private function findAllFiles(): array
    {
        if (!is_dir($this->uploadRootPath)) {
            return [];
        }

        $images = [];

        $directoryIterator = new RecursiveDirectoryIterator(
            $this->uploadRootPath,
            FilesystemIterator::SKIP_DOTS
        );

        $iterator = new RecursiveIteratorIterator(
            $directoryIterator,
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $fileInfo) {
            if (
                !$fileInfo instanceof SplFileInfo
                || !$fileInfo->isFile()
            ) {
                continue;
            }

            $absolutePath = $fileInfo->getPathname();

            if (!$this->isAllowedImage($absolutePath)) {
                continue;
            }

            $relativePath = $this->createRelativePath(
                $absolutePath
            );

            if ($relativePath === null) {
                continue;
            }

            $imageData = $this->buildImageData(
                $absolutePath,
                $relativePath
            );

            if ($imageData === false) {
                continue;
            }

            $images[] = $imageData;
        }

        return $images;
    }

    /**
     * 画像情報を構築する。
     */
    private function buildImageData(
        string $absolutePath,
        string $relativePath
    ): array|false {
        $imageInformation = @getimagesize(
            $absolutePath
        );

        if ($imageInformation === false) {
            return false;
        }

        $width = $imageInformation[0] ?? 0;
        $height = $imageInformation[1] ?? 0;
        $mimeType = $imageInformation['mime'] ?? '';

        if (
            !is_int($width)
            || !is_int($height)
            || $width <= 0
            || $height <= 0
            || !is_string($mimeType)
            || $mimeType === ''
        ) {
            return false;
        }

        $fileSize = filesize($absolutePath);
        $uploadedAt = filemtime($absolutePath);

        if (
            $fileSize === false
            || $uploadedAt === false
        ) {
            return false;
        }

        $extension = strtolower(
            pathinfo(
                $absolutePath,
                PATHINFO_EXTENSION
            )
        );

        return [
            'relative_path' => $relativePath,
            'absolute_path' => $absolutePath,
            'url' => $this->uploadBaseUrl
                . '/'
                . $this->encodeRelativePath($relativePath),
            'file_name' => basename($absolutePath),
            'extension' => $extension,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'width' => $width,
            'height' => $height,
            'uploaded_at' => $uploadedAt,
        ];
    }

    /**
     * 許可された画像形式か確認する。
     */
    private function isAllowedImage(
        string $absolutePath
    ): bool {
        $extension = strtolower(
            pathinfo(
                $absolutePath,
                PATHINFO_EXTENSION
            )
        );

        if (
            !in_array(
                $extension,
                self::ALLOWED_EXTENSIONS,
                true
            )
        ) {
            return false;
        }

        $mimeType = @mime_content_type(
            $absolutePath
        );

        return in_array(
            $mimeType,
            [
                'image/jpeg',
                'image/png',
                'image/webp',
            ],
            true
        );
    }

    /**
     * 絶対パスからuploads配下の相対パスを作成する。
     */
    private function createRelativePath(
        string $absolutePath
    ): ?string {
        $rootRealPath = realpath(
            $this->uploadRootPath
        );

        $fileRealPath = realpath(
            $absolutePath
        );

        if (
            $rootRealPath === false
            || $fileRealPath === false
        ) {
            return null;
        }

        $rootPrefix = rtrim(
            $rootRealPath,
            DIRECTORY_SEPARATOR
        ) . DIRECTORY_SEPARATOR;

        if (!str_starts_with(
            $fileRealPath,
            $rootPrefix
        )) {
            return null;
        }

        $relativePath = substr(
            $fileRealPath,
            strlen($rootPrefix)
        );

        return str_replace(
            DIRECTORY_SEPARATOR,
            '/',
            $relativePath
        );
    }

    /**
     * ユーザー入力の相対パスを安全な形へ変換する。
     */
    private function normalizeRelativePath(
        string $relativePath
    ): ?string {
        $relativePath = trim(
            str_replace(
                '\\',
                '/',
                $relativePath
            )
        );

        $relativePath = ltrim(
            $relativePath,
            '/'
        );

        if (
            $relativePath === ''
            || str_contains($relativePath, "\0")
        ) {
            return null;
        }

        $segments = explode(
            '/',
            $relativePath
        );

        foreach ($segments as $segment) {
            if (
                $segment === ''
                || $segment === '.'
                || $segment === '..'
            ) {
                return null;
            }
        }

        return implode(
            '/',
            $segments
        );
    }

    /**
     * 対象パスがuploads配下にあるか確認する。
     */
    private function isPathInsideUploadRoot(
        string $absolutePath
    ): bool {
        $rootRealPath = realpath(
            $this->uploadRootPath
        );

        $fileRealPath = realpath(
            $absolutePath
        );

        if (
            $rootRealPath === false
            || $fileRealPath === false
        ) {
            return false;
        }

        $rootPrefix = rtrim(
            $rootRealPath,
            DIRECTORY_SEPARATOR
        ) . DIRECTORY_SEPARATOR;

        return str_starts_with(
            $fileRealPath,
            $rootPrefix
        );
    }

    /**
     * URL用に相対パスの各区切りをエンコードする。
     */
    private function encodeRelativePath(
        string $relativePath
    ): string {
        return implode(
            '/',
            array_map(
                'rawurlencode',
                explode('/', $relativePath)
            )
        );
    }

    /**
     * 画像削除後、空になった年月フォルダを削除する。
     */
    private function removeEmptyParentDirectories(
        string $directory
    ): void {
        $rootRealPath = realpath(
            $this->uploadRootPath
        );

        if ($rootRealPath === false) {
            return;
        }

        while (true) {
            $directoryRealPath = realpath(
                $directory
            );

            if (
                $directoryRealPath === false
                || $directoryRealPath === $rootRealPath
                || !str_starts_with(
                    $directoryRealPath,
                    $rootRealPath
                        . DIRECTORY_SEPARATOR
                )
            ) {
                return;
            }

            $files = scandir(
                $directoryRealPath
            );

            if (
                $files === false
                || count($files) > 2
            ) {
                return;
            }

            if (!rmdir($directoryRealPath)) {
                return;
            }

            $directory = dirname(
                $directoryRealPath
            );
        }
    }
}