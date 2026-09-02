<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, name, email, role, status, last_login_at, created_at, updated_at, deleted_at
             FROM users WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findActiveByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM users
             WHERE email = :email AND status = 'active' AND deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute(['email' => strtolower(trim($email))]);
        return $stmt->fetch() ?: null;
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE email = :email AND deleted_at IS NULL';
        $params = ['email' => strtolower(trim($email))];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :except_id';
            $params['except_id'] = $exceptId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function countActiveAdmins(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM users
             WHERE role = 'admin' AND status = 'active' AND deleted_at IS NULL"
        )->fetchColumn();
    }

    public function paginate(string $search, string $role, string $status, int $page, int $perPage = 10): array
    {
        $where = ['deleted_at IS NULL'];
        $params = [];

        if ($search !== '') {
            $where[] = '(name LIKE :search OR email LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if (in_array($role, ['admin', 'manager', 'user'], true)) {
            $where[] = 'role = :role';
            $params['role'] = $role;
        }

        if (in_array($status, ['active', 'inactive'], true)) {
            $where[] = 'status = :status';
            $params['status'] = $status;
        }

        $whereSql = implode(' AND ', $where);

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare(
            "SELECT id, name, email, role, status, last_login_at, created_at
             FROM users
             WHERE {$whereSql}
             ORDER BY id DESC
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

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password_hash, role, status)
             VALUES (:name, :email, :password_hash, :role, :status)'
        );
        $stmt->execute([
            'name' => $data['name'],
            'email' => strtolower(trim($data['email'])),
            'password_hash' => $data['password_hash'],
            'role' => $data['role'],
            'status' => $data['status'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sets = [
            'name = :name',
            'email = :email',
            'role = :role',
            'status = :status',
        ];

        $params = [
            'id' => $id,
            'name' => $data['name'],
            'email' => strtolower(trim($data['email'])),
            'role' => $data['role'],
            'status' => $data['status'],
        ];

        if (!empty($data['password_hash'])) {
            $sets[] = 'password_hash = :password_hash';
            $params['password_hash'] = $data['password_hash'];
        }

        $stmt = $this->db->prepare(
            'UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = :id AND deleted_at IS NULL'
        );
        return $stmt->execute($params);
    }

    public function updateOwnProfile(int $id, string $name, string $email): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users
             SET name = :name, email = :email
             WHERE id = :id AND deleted_at IS NULL'
        );
        return $stmt->execute([
            'id' => $id,
            'name' => $name,
            'email' => strtolower(trim($email)),
        ]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET status = :status WHERE id = :id AND deleted_at IS NULL'
        );
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users
             SET deleted_at = NOW(), status = 'inactive'
             WHERE id = :id AND deleted_at IS NULL"
        );
        return $stmt->execute(['id' => $id]);
    }

    public function touchLastLogin(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function updatePasswordHash(int $id, string $hash): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET password_hash = :hash WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute(['hash' => $hash, 'id' => $id]);
    }

    public function passwordHash(int $id): ?string
    {
        $stmt = $this->db->prepare(
            'SELECT password_hash FROM users WHERE id = :id AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $hash = $stmt->fetchColumn();
        return is_string($hash) ? $hash : null;
    }

    public function stats(): array
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) total,
                    SUM(status = 'active') active,
                    SUM(role = 'admin') admins,
                    SUM(role = 'manager') managers
             FROM users
             WHERE deleted_at IS NULL"
        );
        $row = $stmt->fetch() ?: [];

        return [
            'total' => (int) ($row['total'] ?? 0),
            'active' => (int) ($row['active'] ?? 0),
            'admins' => (int) ($row['admins'] ?? 0),
            'managers' => (int) ($row['managers'] ?? 0),
        ];
    }
}
