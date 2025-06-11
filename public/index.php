<?php

declare(strict_types=1);

use App\Application\Factory\ChatAppFactory;

require __DIR__ . '/../vendor/autoload.php';

$factory = new ChatAppFactory();
$factory->create();
