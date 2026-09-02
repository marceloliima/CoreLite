<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class AuditLog
{
    public function paginate(string $action, int $page, int $perPage = 20): array
    {
        $db = Database::connection();
        $where = '1 = 1';
        $params = [];

        if ($action !== '') {
            $where .= ' AND a.action LIKE :action';
            $params['action'] = '%' . $action . '%';
        }

        $count = $db->prepare("SELECT COUNT(*) FROM audit_logs a WHERE {$where}");
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare(
            "SELECT a.id, a.action, a.entity_type, a.entity_id, a.details, a.created_at,
                    u.name AS user_name, u.email AS user_email
             FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             WHERE {$where}
             ORDER BY a.id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
        ];
    }
}
