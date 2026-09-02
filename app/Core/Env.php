<?php

declare(strict_types=1);

namespace App\Core;

final class Env
{
    private function __construct() {}

    public static function load(string $file): void
    {
        if (!is_readable($file)) {
            throw new \RuntimeException('Arquivo .env não encontrado. Copie .env.example para .env.');
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            throw new \RuntimeException('Não foi possível ler o arquivo .env.');
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));
            if ($key === '' || preg_match('/^[A-Z0-9_]+$/', $key) !== 1) {
                continue;
            }

            $value = trim($value, "\"'");
            if (getenv($key) === false) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}
