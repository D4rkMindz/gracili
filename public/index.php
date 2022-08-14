<?php

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $app = require __DIR__ . "/../config/bootstrap.php";

    // for testing coverage
    $appConfigFile = __DIR__ . '/../.APP_CONFIG';
    $c3File = __DIR__ . '/../c3.php';
    if (
        file_exists($appConfigFile)
        && strtolower(file_get_contents($appConfigFile)) === 'integration'
        && file_exists($c3File)
    ) {
        putenv('XDEBUG_MODE=coverage');
        require_once $c3File;
        ob_clean();
    }

    $app->run();
} catch (Throwable $t) {
    $data = [
        'message' => 'Error encountered!',
        'success' => false,
    ];
    $config = require __DIR__ . '/../config/config.php';
    if ($config['debug']) {
        $data['debug'] = true;
        $data['exception'] = [
            'message' => $t->getMessage(),
            'trace' => [
                'string' => $t->getTraceAsString(),
                'data' => $t->getTrace(),
            ],
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($data, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE);
}