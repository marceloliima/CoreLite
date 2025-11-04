<?php
declare(strict_types=1);

namespace App\Core;

/**
 * ===============================================================
 * 🔐 Classe CSRF (estilo Laravel)
 * ===============================================================
 *
 * Gera e valida tokens CSRF por contexto/formulário.
 * Uso típico:
 * 
 * <input type="hidden" name="csrf_token" value="<?= Csrf::token('login'); ?>">
 *
 * if (!Csrf::check('login', $_POST['csrf_token'] ?? null)) {
 *     // Token inválido
 * }
 */
final class Csrf
{
    private const SESSION_KEY = '_csrf';
    private const DEFAULT_EXPIRATION = 3600; // 1 hora

    private function __construct() {}
    private function __clone() {}

    /** Garante sessão ativa */
    private static function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Gera ou retorna token existente para um contexto
     */
    public static function token(string $key, ?int $expiration = null): string
    {
        self::ensureSession();

        $expiration = $expiration ?? self::DEFAULT_EXPIRATION;

        // Retorna token existente válido
        if (isset($_SESSION[self::SESSION_KEY][$key])) {
            $data = $_SESSION[self::SESSION_KEY][$key];
            if ($data['expires_at'] >= time()) {
                return $data['token'];
            }
        }

        // Cria novo token
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::SESSION_KEY][$key] = [
            'token' => $token,
            'expires_at' => time() + $expiration
        ];

        return $token;
    }

    /**
     * Verifica se token enviado é válido
     */
    public static function check(string $key, ?string $token, bool $remove = true): bool
    {
        self::ensureSession();

        if (!isset($_SESSION[self::SESSION_KEY][$key]) || !is_string($token)) {
            return false;
        }

        $data = $_SESSION[self::SESSION_KEY][$key];

        // Expirado
        if ($data['expires_at'] < time()) {
            unset($_SESSION[self::SESSION_KEY][$key]);
            return false;
        }

        $valid = hash_equals($data['token'], $token);

        if ($remove) {
            unset($_SESSION[self::SESSION_KEY][$key]);
        }

        return $valid;
    }

    /**
     * Remove token específico
     */
    public static function forget(string $key): void
    {
        self::ensureSession();
        unset($_SESSION[self::SESSION_KEY][$key]);
    }

    /**
     * Limpa todos os tokens CSRF
     */
    public static function flush(): void
    {
        self::ensureSession();
        unset($_SESSION[self::SESSION_KEY]);
    }

    /**
     * Retorna token sem gerar novo (null se inexistente ou expirado)
     */
    public static function get(string $key): ?string
    {
        self::ensureSession();

        if (!isset($_SESSION[self::SESSION_KEY][$key])) {
            return null;
        }

        $data = $_SESSION[self::SESSION_KEY][$key];
        if ($data['expires_at'] < time()) {
            unset($_SESSION[self::SESSION_KEY][$key]);
            return null;
        }

        return $data['token'];
    }

    /**
     * Atualiza token existente ou gera novo
     */
    public static function refresh(string $key, ?int $expiration = null): string
    {
        self::forget($key);
        return self::token($key, $expiration);
    }

    /**
     * Retorna se existe token válido
     */
    public static function exists(string $key): bool
    {
        return self::get($key) !== null;
    }
}