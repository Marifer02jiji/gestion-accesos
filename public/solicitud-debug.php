<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$root = dirname(__DIR__);

try {
    require $root . '/vendor/autoload.php';
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'step' => 'autoload', 'error' => $e->getMessage()]);
    exit;
}

$classes = [
    'App\\Http\\Controllers\\Api\\SolicitudApiController',
    'App\\Services\\FlujoAccesoService',
    'App\\Services\\SolicitudExtensionTiempoService',
];

$results = [];

foreach ($classes as $class) {
    try {
        new ReflectionClass($class);
        $results[$class] = 'ok';
    } catch (Throwable $e) {
        $results[$class] = $e->getMessage();
    }
}

echo json_encode([
    'ok'      => !in_array(false, array_map(fn ($v) => $v === 'ok', $results), true),
    'classes' => $results,
    'helpers' => is_readable($root . '/app/helpers.php')
        ? substr(file_get_contents($root . '/app/helpers.php') ?: '', 0, 120)
        : 'missing',
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
