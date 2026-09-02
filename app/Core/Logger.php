<?php

declare(strict_types=1);

namespace App\Core;

final class Logger
{
    private function __construct() {}

    public static function error(string $event, array $context = []): void
    {
        self::write('ERROR', $event, $context);
    }

    public static function info(string $event, array $context = []): void
    {
        self::write('INFO', $event, $context);
    }

    private static function write(string $level, string $event, array $context): void
    {
        $dir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }

        foreach (['password', 'password_hash', 'csrf_token'] as $sensitive) {
            unset($context[$sensitive]);
        }

        $line = sprintf("[%s] %s %s %s\n", date('c'), $level, $event, json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        @file_put_contents($dir . '/app.log', $line, FILE_APPEND | LOCK_EX);
    }
}
