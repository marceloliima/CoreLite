<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    private function __construct() {}

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? null) === '443');

        session_name((string) config('app.session.name', 'SECUREPANELSESSID'));
        session_start([
            'cookie_httponly' => true,
            'cookie_secure' => $secure,
            'cookie_samesite' => 'Strict',
            'use_strict_mode' => true,
            'use_only_cookies' => true,
            'cookie_path' => '/',
        ]);

        self::enforceLifetime();
    }

    private static function enforceLifetime(): void
    {
        $now = time();
        $idle = (int) config('app.session.idle_timeout', 1800);
        $absolute = (int) config('app.session.absolute_timeout', 28800);

        $_SESSION['_meta']['created_at'] ??= $now;
        $_SESSION['_meta']['last_activity'] ??= $now;

        $idleExpired = ($now - (int) $_SESSION['_meta']['last_activity']) > $idle;
        $absoluteExpired = ($now - (int) $_SESSION['_meta']['created_at']) > $absolute;

        if (($idleExpired || $absoluteExpired) && isset($_SESSION['auth_user_id'])) {
            self::destroy();
            self::start();
            Flash::add('warning', 'Sua sessão expirou. Entre novamente.');
            return;
        }

        $_SESSION['_meta']['last_activity'] = $now;
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
        $_SESSION['_meta']['created_at'] = time();
        $_SESSION['_meta']['last_activity'] = time();
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function flashOldInput(array $input): void
    {
        unset($input['password'], $input['password_confirmation'], $input['csrf_token']);
        $_SESSION['_old'] = $input;
    }

    public static function clearOldInput(): void
    {
        unset($_SESSION['_old']);
    }
}
