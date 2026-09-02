<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    private function __construct() {}

    public static function render(string $view, array $data = []): string
    {
        $viewFile = dirname(__DIR__) . '/Views/' . $view . '.php';
        $layoutFile = dirname(__DIR__) . '/Views/layout.php';
        if (!is_file($viewFile)) {
            throw new \RuntimeException("View não encontrada: {$view}");
        }

        extract($data, EXTR_SKIP);
        $flashMessages = Flash::pull();
        $currentUser = Auth::user();

        ob_start();
        require $viewFile;
        $content = (string) ob_get_clean();

        ob_start();
        require $layoutFile;
        return (string) ob_get_clean();
    }
}
