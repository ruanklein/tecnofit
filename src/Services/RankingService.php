<?php declare(strict_types=1);

namespace Tecnofit\Services;

use Tecnofit\Config\Database;
use Exception;

class RankingService
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function getMovementRanking(string $movementId): array
    {
        $sql = "
            SELECT 
                m.name as movement_name,
                u.name as user_name,
                user_records.personal_record,
                user_records.record_date,
                RANK() OVER (ORDER BY user_records.personal_record DESC) as position
            FROM movements m
            JOIN users u ON 1=1
            JOIN (
                SELECT 
                    pr.user_id,
                    pr.movement_id,
                    MAX(pr.value) as personal_record,
                    (
                        SELECT pr2.date 
                        FROM personal_records pr2 
                        WHERE pr2.user_id = pr.user_id 
                        AND pr2.movement_id = pr.movement_id 
                        AND pr2.value = MAX(pr.value)
                        ORDER BY pr2.date DESC 
                        LIMIT 1
                    ) as record_date
                FROM personal_records pr
                GROUP BY pr.user_id, pr.movement_id
            ) user_records ON user_records.user_id = u.id AND user_records.movement_id = m.id
            WHERE (m.id = ? OR m.name = ?)
            AND user_records.personal_record IS NOT NULL
            ORDER BY user_records.personal_record DESC
        ";

        $stmt = $this->database->query($sql, [$movementId, $movementId]);
        $results = $stmt->fetchAll();

        if (empty($results)) {
            throw new Exception('Movement not found');
        }

        return [
            'movement' => $results[0]['movement_name'],
            'ranking' => array_map(function($row) {
                return [
                    'position' => (int)$row['position'],
                    'user_name' => $row['user_name'],
                    'personal_record' => (float)$row['personal_record'],
                    'record_date' => $row['record_date']
                ];
            }, $results)
        ];
    }
} 