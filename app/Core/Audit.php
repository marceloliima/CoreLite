<?php

declare(strict_types=1);

namespace App\Core;

final class Audit
{
    private function __construct() {}

    public static function log(string $action, ?string $entityType = null, ?int $entityId = null, array $details = []): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip_hash) VALUES (:user_id, :action, :entity_type, :entity_id, :details, :ip_hash)');
        $stmt->execute([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => $details === [] ? null : json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'ip_hash' => Security::hashIdentifier(Security::clientIp()),
        ]);
    }
}
