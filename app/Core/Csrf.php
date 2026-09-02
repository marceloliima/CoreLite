<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    private const KEY = '_csrf';
    private const TTL = 1800;

    private function __construct() {}

    public static function token(string $context): string
    {
        self::cleanExpired();
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::KEY][$context] = [
            'hash' => hash('sha256', $token),
            'expires_at' => time() + self::TTL,
        ];
        return $token;
    }

    public static function validate(string $context, ?string $token): bool
    {
        $stored = $_SESSION[self::KEY][$context] ?? null;
        unset($_SESSION[self::KEY][$context]);

        if (!is_array($stored) || !is_string($token) || $token === '') {
            return false;
        }
        if ((int) ($stored['expires_at'] ?? 0) < time()) {
            return false;
        }
        return hash_equals((string) ($stored['hash'] ?? ''), hash('sha256', $token));
    }

    private static function cleanExpired(): void
    {
        foreach ($_SESSION[self::KEY] ?? [] as $context => $data) {
            if ((int) ($data['expires_at'] ?? 0) < time()) {
                unset($_SESSION[self::KEY][$context]);
            }
        }
    }
}
