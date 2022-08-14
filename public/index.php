<?php

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $app = require __DIR__ . "/../config/bootstrap.php";
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