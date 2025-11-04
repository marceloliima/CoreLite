<?php
declare(strict_types=1);

use App\Core\Env;

/**
 * ===============================================================
 * 🔹 AUTOLOAD PSR-4 SIMPLIFICADO (estilo Laravel)
 * ===============================================================
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../App/';

    if (str_starts_with($class, $prefix)) {
        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }

        if (ini_get('display_errors')) {
            throw new \RuntimeException("Autoload falhou: classe {$class} não encontrada em {$file}");
        } else {
            error_log("Autoload: classe {$class} não encontrada em {$file}");
        }
    }
});

/**
 * ===============================================================
 * 🔹 CARREGAMENTO DE VARIÁVEIS DE AMBIENTE (.env)
 * ===============================================================
 */
Env::load(__DIR__ . '/../.env');

/**
 * ===============================================================
 * 🔹 FUNÇÃO AUXILIAR env() (como Laravel)
 * ===============================================================
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function env(string $key, mixed $default = null): mixed
{
    $value = getenv($key) ?: $_ENV[$key] ?? $_SERVER[$key] ?? null;

    if ($value === null) {
        return $default;
    }

    // Converte strings booleanas e numéricas
    $lower = strtolower($value);
    return match ($lower) {
        'true', '(true)' => true,
        'false', '(false)' => false,
        'null', '(null)' => null,
        default => is_numeric($value) ? (strpos($value, '.') !== false ? (float)$value : (int)$value) : $value
    };
}
