<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/CategoryRepository.php';

$repository = new CategoryRepository($pdo);

$categories = $repository->findAll();

echo '<pre>';

print_r($categories);

echo '</pre>';