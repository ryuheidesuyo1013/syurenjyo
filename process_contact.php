<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require("./PHPMailer/src/PHPMailer.php");
require("./PHPMailer/src/Exception.php");
require("./PHPMailer/src/SMTP.php");



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // フォームからのデータを取得
    $name = $_POST['名前'] ?? '';
    $club = $_POST['クラブ'] ?? '';
    $role = $_POST['役割'] ?? '';
    $email = $_POST['メール'] ?? '';
    $confirm_email = $_POST['メール確認'] ?? '';
    $inquiry = $_POST['問い合わせ'] ?? '';

    // 入力データのバリデーション
    $errors = [];
    if (empty($name)) {
        $errors[] = "名前を入力してください。";
    }
    if ($role === '選択してください') {
        $errors[] = "チーム内役割を選択してください。";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "有効なメールアドレスを入力してください。";
    }
    if ($email !== $confirm_email) {
        $errors[] = "メールアドレスが一致しません。";
    }
    if (empty($inquiry)) {
        $errors[] = "お問い合わせ内容を入力してください。";
    }

    // エラーがなければメール送信
    if (empty($errors)) {
        try {
            $mail = new PHPMailer(true);

            // サーバー設定
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // 使用するSMTPサーバー
            $mail->SMTPAuth = true;
            $mail->Username = 'syurenjyo@gmail.com'; // Gmailのアドレス
            $mail->Password = 'retu quno kxhh fkbe'; // Gmailのパスワード（またはAppパスワード）
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            // 送信者・受信者設定
            $mail->setFrom($email, $name);
            $mail->addAddress('syurenjyo@gmail.com', '蹴練場管理者'); // 管理者のメールアドレス

            // メール内容設定
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'お問い合わせフォームからのメッセージ';
            $mail->Body = "
                <p><strong>名前:</strong> {$name}</p>
                <p><strong>クラブ:</strong> {$club}</p>
                <p><strong>役割:</strong> {$role}</p>
                <p><strong>メール:</strong> {$email}</p>
                <p><strong>内容:</strong></p>
                <p>{$inquiry}</p>
            ";

            // メール送信
            $mail->send();
            echo "お問い合わせが正常に送信されました。";
        } catch (Exception $e) {
            echo "メール送信中にエラーが発生しました: {$mail->ErrorInfo}";
        }
    } else {
        // エラーを表示
        foreach ($errors as $error) {
            echo "<p>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</p>";
        }
    }
}
?>