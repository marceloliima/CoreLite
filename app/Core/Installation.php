<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Controla o fluxo de instalação inicial.
 *
 * A tabela installation_state contém uma única linha (id = 1). O primeiro
 * administrador é criado em transação, bloqueando essa linha com FOR UPDATE.
 */
final class Installation
{
    private const ROW_ID = 1;

    private function __construct() {}

    public static function isInstalled(): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT installed FROM installation_state WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => self::ROW_ID]);
        $value = $stmt->fetchColumn();

        if ($value === false) {
            throw new RuntimeException('Estado de instalação não encontrado. Importe database.sql.');
        }

        return (int) $value === 1;
    }

    public static function createFirstAdmin(string $name, string $email, string $password): int
    {
        $db = Database::connection();
        $db->beginTransaction();

        try {
            $state = $db->prepare(
                'SELECT installed FROM installation_state WHERE id = :id FOR UPDATE'
            );
            $state->execute(['id' => self::ROW_ID]);
            $installed = $state->fetchColumn();

            if ($installed === false) {
                throw new RuntimeException('Estado de instalação não encontrado.');
            }

            if ((int) $installed === 1) {
                throw new RuntimeException('A instalação inicial já foi concluída.');
            }

            // Evita que uma instalação antiga com usuários seja reaberta por engano.
            $userCount = (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
            if ($userCount > 0) {
                throw new RuntimeException('Já existem usuários no banco. A instalação inicial foi bloqueada.');
            }

            $insert = $db->prepare(
                "INSERT INTO users (name, email, password_hash, role, status)
                 VALUES (:name, :email, :password_hash, 'admin', 'active')"
            );
            $insert->execute([
                'name' => $name,
                'email' => strtolower(trim($email)),
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            $userId = (int) $db->lastInsertId();

            $finish = $db->prepare(
                'UPDATE installation_state
                 SET installed = 1, installed_at = NOW()
                 WHERE id = :id'
            );
            $finish->execute(['id' => self::ROW_ID]);

            $db->commit();
            return $userId;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /** Marca a instalação como concluída quando o primeiro admin é criado via CLI. */
    public static function markInstalledIfUsersExist(): void
    {
        $db = Database::connection();
        $count = (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();

        if ($count <= 0) {
            return;
        }

        $stmt = $db->prepare(
            'UPDATE installation_state
             SET installed = 1, installed_at = COALESCE(installed_at, NOW())
             WHERE id = :id'
        );
        $stmt->execute(['id' => self::ROW_ID]);
    }
}
