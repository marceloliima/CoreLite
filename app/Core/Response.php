<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    private function __construct() {}

    public static function abort(int $status, string $message = ''): never
    {
        http_response_code($status);
        $view = match ($status) {
            403 => 'errors/403',
            404 => 'errors/404',
            default => 'errors/500',
        };
        echo View::render($view, ['message' => $message]);
        exit;
    }
}
