<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Tecnofit\Config\Database;
use Tecnofit\Services\RankingService;
use Tecnofit\Controllers\MovementController;

header('Content-Type: application/json');

try {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $path = parse_url($requestUri, PHP_URL_PATH);
    
    $path = str_replace('/index.php', '', $path);

    if ($path === '/' || $path === '/health' || empty($path)) {
        echo json_encode([
            'status' => 'OK',
            'message' => 'Tecnofit Movement Ranking API',
            'timestamp' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION
        ]);
        exit;
    }

    if (preg_match('#^/api/movements/([^/]+)/ranking/?$#', $path, $matches)) {
        $movementId = $matches[1];
        
        $database = Database::getInstance();
        $rankingService = new RankingService($database);
        $controller = new MovementController($rankingService);
        
        $controller->getRanking($movementId);
        exit;
    }

    http_response_code(404);
    echo json_encode([
        'error' => 'Endpoint not found', 
        'path' => $path,
        'request_uri' => $requestUri
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} 