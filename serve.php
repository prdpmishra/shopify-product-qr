<?php

declare(strict_types=1);

$port = getenv('PORT') ?: '8080';

$command = PHP_BINARY . ' -S 127.0.0.1:' . $port . ' -t public public/router.php';

passthru($command);
