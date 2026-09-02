<?php

declare(strict_types=1);

namespace App\Core;

/** Rate limiting persistente em banco, sem cache externo. */
final class RateLimiter
{
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const MAX_REGISTRATION_ATTEMPTS = 5;

    private function __construct() {}

    public static function tooManyLoginAttempts(string $email): bool
    {
        self::cleanupOccasionally();

        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*)
             FROM login_attempts
             WHERE email_hash = :email_hash
               AND ip_hash = :ip_hash
               AND successful = 0
               AND attempted_at >= (NOW() - INTERVAL 15 MINUTE)'
        );
        $stmt->execute([
            'email_hash' => Security::hashIdentifier(strtolower(trim($email))),
            'ip_hash' => Security::hashIdentifier(Security::clientIp()),
        ]);

        return (int) $stmt->fetchColumn() >= self::MAX_LOGIN_ATTEMPTS;
    }

    public static function recordLoginAttempt(string $email, bool $successful): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO login_attempts (email_hash, ip_hash, successful)
             VALUES (:email_hash, :ip_hash, :successful)'
        );
        $stmt->execute([
            'email_hash' => Security::hashIdentifier(strtolower(trim($email))),
            'ip_hash' => Security::hashIdentifier(Security::clientIp()),
            'successful' => $successful ? 1 : 0,
        ]);
    }

    public static function tooManyRegistrations(): bool
    {
        self::cleanupOccasionally();

        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*)
             FROM registration_attempts
             WHERE ip_hash = :ip_hash
               AND attempted_at >= (NOW() - INTERVAL 60 MINUTE)'
        );
        $stmt->execute([
            'ip_hash' => Security::hashIdentifier(Security::clientIp()),
        ]);

        return (int) $stmt->fetchColumn() >= self::MAX_REGISTRATION_ATTEMPTS;
    }

    public static function recordRegistrationAttempt(string $email, bool $successful): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO registration_attempts (email_hash, ip_hash, successful)
             VALUES (:email_hash, :ip_hash, :successful)'
        );
        $stmt->execute([
            'email_hash' => Security::hashIdentifier(strtolower(trim($email))),
            'ip_hash' => Security::hashIdentifier(Security::clientIp()),
            'successful' => $successful ? 1 : 0,
        ]);
    }

    private static function cleanupOccasionally(): void
    {
        if (random_int(1, 100) > 5) {
            return;
        }

        $db = Database::connection();
        $db->exec('DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 7 DAY)');
        $db->exec('DELETE FROM registration_attempts WHERE attempted_at < (NOW() - INTERVAL 7 DAY)');
    }
}
