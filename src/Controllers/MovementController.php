<?php declare(strict_types=1);

namespace Tecnofit\Controllers;

use Tecnofit\Services\RankingService;
use Exception;

class MovementController
{
    private RankingService $rankingService;

    public function __construct(RankingService $rankingService)
    {
        $this->rankingService = $rankingService;
    }

    public function getRanking(string $movementId): void
    {
        try {
            $ranking = $this->rankingService->getMovementRanking($movementId);
            
            header('Content-Type: application/json');
            echo json_encode($ranking, JSON_PRETTY_PRINT);
            
        } catch (Exception $e) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
} 