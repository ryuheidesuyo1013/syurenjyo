<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('許可されていない操作です。');
}

logoutAdmin();

header('Location: login.php');
exit;