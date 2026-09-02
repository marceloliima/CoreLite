<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = []): string
    {
        return View::render($view, $data);
    }

    protected function verifyCsrf(string $context): void
    {
        $token = $_POST['csrf_token'] ?? null;
        if (!Csrf::validate($context, is_string($token) ? $token : null)) {
            Response::abort(403, 'Token de segurança inválido ou expirado. Recarregue a página e tente novamente.');
        }
    }
}
