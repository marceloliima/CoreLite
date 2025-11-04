<?php
namespace App\Core;

final class Env
{
    private function __construct() {}
    private function __clone() {}

    /**
     * Carrega variáveis de ambiente a partir de arquivo .env
     *
     * @param string $file
     */
    public static function load(string $file): void
    {
        if (!file_exists($file)) {
            error_log("Arquivo .env não encontrado em {$file}");
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));
            $value = trim($value, "\"'");

            if (getenv($key) === false) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}
