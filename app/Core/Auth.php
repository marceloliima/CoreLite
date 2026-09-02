<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    private static array|false|null $cached = null;
    private function __construct() {}

    public static function attempt(string $email, string $password): bool
    {
        $user = (new User())->findActiveByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            (new User())->updatePasswordHash((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        }

        Session::regenerate();
        $_SESSION['auth_user_id'] = (int) $user['id'];
        self::$cached = null;
        (new User())->touchLastLogin((int) $user['id']);
        return true;
    }

    public static function user(): ?array
    {
                if (is_array(self::$cached)) {
            return self::$cached;
        }

        $id = (int) ($_SESSION['auth_user_id'] ?? 0);
        if ($id <= 0) return null;
        $user = (new User())->find($id);
        if (!$user || $user['status'] !== 'active' || $user['deleted_at'] !== null) {
            self::logout(false);
            return null;
        }
        self::$cached = $user;
        return $user;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    public static function check(): bool { return self::user() !== null; }

    public static function hasRole(string ...$roles): bool
    {
        $user = self::user();
        return $user !== null && in_array($user['role'], $roles, true);
    }

    public static function logout(bool $destroySession = true): void
    {
        unset($_SESSION['auth_user_id']);
        self::$cached = null;
        if ($destroySession) {
            Session::destroy();
        }
    }
}
