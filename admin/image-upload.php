<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';

requireAdminLogin();

header('Content-Type: application/json; charset=UTF-8');

/**
 * JSONレスポンスを返して処理を終了する。
 *
 * @param array<string, mixed> $data
 */
function respondJson(
    array $data,
    int $statusCode = 200
): never {
    http_response_code($statusCode);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_THROW_ON_ERROR
    );

    exit;
}

/**
 * アップロードエラーに対応するメッセージを返す。
 */
function getUploadErrorMessage(int $errorCode): string
{
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
            => 'サーバーの設定によりアップロードが停止されました。',

        default
            => '画像のアップロードに失敗しました。',
    };
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respondJson(
        [
            'success' => false,
            'message' => 'POSTリクエストで送信してください。',
        ],
        405
    );
}

$csrfToken = $_POST['csrf_token'] ?? '';

if (
    !is_string($csrfToken)
    || !hash_equals(
        getCsrfToken(),
        $csrfToken
    )
) {
    respondJson(
        [
            'success' => false,
            'message' => '不正なリクエストです。画面を再読み込みしてください。',
        ],
        403
    );
}

if (
    !isset($_FILES['image'])
    || !is_array($_FILES['image'])
) {
    respondJson(
        [
            'success' => false,
            'message' => '画像ファイルが送信されていません。',
        ],
        400
    );
}

$image = $_FILES['image'];

$uploadError = $image['error'] ?? UPLOAD_ERR_NO_FILE;

if (!is_int($uploadError)) {
    $uploadError = (int) $uploadError;
}

if ($uploadError !== UPLOAD_ERR_OK) {
    respondJson(
        [
            'success' => false,
            'message' => getUploadErrorMessage($uploadError),
        ],
        400
    );
}

$tmpName = $image['tmp_name'] ?? '';
$fileSize = $image['size'] ?? 0;

if (
    !is_string($tmpName)
    || $tmpName === ''
    || !is_uploaded_file($tmpName)
) {
    respondJson(
        [
            'success' => false,
            'message' => 'アップロードされたファイルを確認できませんでした。',
        ],
        400
    );
}

if (!is_int($fileSize)) {
    $fileSize = (int) $fileSize;
}

$maximumFileSize = 5 * 1024 * 1024;

if ($fileSize <= 0) {
    respondJson(
        [
            'success' => false,
            'message' => '空のファイルはアップロードできません。',
        ],
        400
    );
}

if ($fileSize > $maximumFileSize) {
    respondJson(
        [
            'success' => false,
            'message' => '画像は5MB以下にしてください。',
        ],
        400
    );
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($tmpName);

if (!is_string($mimeType)) {
    respondJson(
        [
            'success' => false,
            'message' => '画像形式を確認できませんでした。',
        ],
        400
    );
}

$allowedMimeTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];

if (!array_key_exists($mimeType, $allowedMimeTypes)) {
    respondJson(
        [
            'success' => false,
            'message' => 'JPEG・PNG・WebP形式の画像だけアップロードできます。',
        ],
        400
    );
}

$imageInformation = getimagesize($tmpName);

if ($imageInformation === false) {
    respondJson(
        [
            'success' => false,
            'message' => '有効な画像ファイルではありません。',
        ],
        400
    );
}

$imageWidth = $imageInformation[0] ?? 0;
$imageHeight = $imageInformation[1] ?? 0;

if (
    !is_int($imageWidth)
    || !is_int($imageHeight)
    || $imageWidth <= 0
    || $imageHeight <= 0
) {
    respondJson(
        [
            'success' => false,
            'message' => '画像のサイズを確認できませんでした。',
        ],
        400
    );
}

$maximumImageWidth = 8000;
$maximumImageHeight = 8000;

if (
    $imageWidth > $maximumImageWidth
    || $imageHeight > $maximumImageHeight
) {
    respondJson(
        [
            'success' => false,
            'message' => '画像の縦横サイズが大きすぎます。',
        ],
        400
    );
}

$extension = $allowedMimeTypes[$mimeType];

try {
    $randomFileName = bin2hex(
        random_bytes(16)
    );
} catch (Throwable $e) {
    respondJson(
        [
            'success' => false,
            'message' => '画像ファイル名を生成できませんでした。',
        ],
        500
    );
}

$year = date('Y');
$month = date('m');

$uploadDirectory = dirname(__DIR__)
    . '/uploads/'
    . $year
    . '/'
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
    respondJson(
        [
            'success' => false,
            'message' => '画像保存フォルダを作成できませんでした。',
        ],
        500
    );
}

$fileName = $randomFileName . '.' . $extension;
$destinationPath = $uploadDirectory . '/' . $fileName;

if (!move_uploaded_file($tmpName, $destinationPath)) {
    respondJson(
        [
            'success' => false,
            'message' => '画像を保存できませんでした。',
        ],
        500
    );
}

@chmod(
    $destinationPath,
    0644
);

$imageUrl = '../uploads/'
    . $year
    . '/'
    . $month
    . '/'
    . $fileName;

respondJson([
    'success' => true,
    'message' => '画像をアップロードしました。',
    'url' => $imageUrl,
    'file_name' => $fileName,
    'mime_type' => $mimeType,
    'width' => $imageWidth,
    'height' => $imageHeight,
]);