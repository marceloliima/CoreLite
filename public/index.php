<?php

declare(strict_types=1);

use App\Core\Logger;
use App\Core\Request;
use App\Core\Router;
use App\Core\Security;
use App\Core\View;

require dirname(__DIR__) . '/app/bootstrap.php';

Security::headers();
$router = new Router();
require dirname(__DIR__) . '/routes/web.php';

try {
    $router->dispatch(new Request());
} catch (Throwable $e) {
    Logger::error('unhandled_exception', [
        'type' => $e::class,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);

    http_response_code(500);
    if (config('app.debug', false)) {
        echo '<pre>' . e($e) . '</pre>';
    } else {
        echo View::render('errors/500');
    }
}
