<?php

declare(strict_types=1);

$flashMessage = getFlashMessage();

if ($flashMessage === null) {
    return;
}

$message = $flashMessage['message'];
$type = $flashMessage['type'];

$alertClass = match ($type) {
    'success' => 'alert alert--success',
    'error' => 'alert alert--error',
    'warning' => 'alert alert--warning',
    default => 'alert alert--info',
};

$title = match ($type) {
    'success' => '完了しました',
    'error' => '処理に失敗しました',
    'warning' => '確認してください',
    default => 'お知らせ',
};
?>

<div
    class="<?= escape($alertClass) ?> flash-message"
    role="<?= $type === 'error' ? 'alert' : 'status' ?>"
>
    <div class="flash-message__content">
        <p class="alert__title">
            <?= escape($title) ?>
        </p>

        <p class="flash-message__text">
            <?= escape($message) ?>
        </p>
    </div>

    <button
        class="flash-message__close"
        type="button"
        aria-label="メッセージを閉じる"
    >
        ×
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const flashMessage = document.querySelector('.flash-message');

    if (!flashMessage) {
        return;
    }

    const closeButton = flashMessage.querySelector(
        '.flash-message__close'
    );

    const closeFlashMessage = () => {
        flashMessage.classList.add('flash-message--hidden');

        window.setTimeout(() => {
            flashMessage.remove();
        }, 250);
    };

    closeButton?.addEventListener(
        'click',
        closeFlashMessage
    );

    window.setTimeout(
        closeFlashMessage,
        5000
    );
});
</script>